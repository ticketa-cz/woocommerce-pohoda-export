<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


//// create the document and send it ////
	
function create_invoice( $order_id, $export_type = 'to_mserver', $xml = NULL, $document_type = 'invoice' ) {

	//// check if plugin activated ////
		
	$plugin_switch = get_option('wc_settings_pohoda_export_switch');
	if ( $plugin_switch != '1' && $export_type == 'to_mserver' ) {
		return;
	}
	
	//// check if order id ////
		
	if ( !$order_id ) {
		return NULL;
	}

	// if the document from export queue is an order //

	if( strpos( $order_id, 'OBJ' ) !== false ) {

		$order_name = $order_id;
		$order_id = str_replace( "OBJ", "", $order_id );
		$document_type = 'order';

	} else {

		$order_name = "FA" . $order_id;

	}

	//// check if order still exists ////
		
	if ( is_null( get_post( $order_id ) )){

		tckpoh_logs( __( 'Order doesn\'t exist anymore - #', 'tckpoh' ) .$order_id );
		remove_from_unexported( $order_id, $document_type );

		if ( $export_type == 'to_xml_first' || $export_type == "to_xml" ) {
			return $xml;
		} else if ( $export_type == 'to_xml_last' || $export_type == 'to_xml_first_and_last' ) {
			goto xml_ending;
		} else {
			return NULL;
		}
	}

	// quit if is not order and exporting only orders //

	$frequency = get_option('wc_settings_pohoda_export_invoice_export_type');
	if ( $frequency == 'order_only' && ( $document_type != 'order' && $document_type != 'pdf' ) ) {
		return NULL;
	}

	// set output directory //

	$wp_upload_dir = wp_upload_dir();
	$dir = $wp_upload_dir['basedir'] . "/faktury/";
	$url_dir = $wp_upload_dir['baseurl'] . "/faktury/";

	// create document directory //

	if( is_dir( $dir ) === false ) {
		wp_mkdir_p( $dir );
	}
	
	//// log and load order ////

	switch ( $document_type ) {
		case "invoice":	tckpoh_logs( __( 'Tried to create invoice for order #', 'tckpoh' ) .$order_id );	break;
		case "order":	tckpoh_logs( __( 'Tried to create order #', 'tckpoh' ) .$order_id ); 				break;
  	}

	$order = wc_get_order( $order_id );
	
	if ( $order ) {
	

		// date //
		
		$order_date = create_invoice_date( $order_id );
			 
		$date_tax		= intval(get_option('wc_settings_pohoda_export_invoice_data_payment_supply'));
		$date_due		= intval(get_option('wc_settings_pohoda_export_invoice_data_payment_due'));
		$order_date_tax = date('Y-m-d', strtotime($order_date. ' + '. $date_tax .' days'));
		$order_date_due = date('Y-m-d', strtotime($order_date. ' + '. $date_due .' days'));
		

		// invoice numbering //
		
		$invoice_numbering = create_invoice_number( $order_id );
		$invoice_number = get_post_meta( $order_id, 'pohoda_invoice_number', true );
		$invoice_number_core = get_post_meta( $order_id, 'pohoda_invoice_number_core', true );


		// order number //

		$order_number_type = get_option('wc_settings_pohoda_export_invoice_export_orders_number');
		switch ( $order_number_type ) {
			case 'order':			$order_number = $order_id;				break;
			case 'invoice': 		$order_number = $invoice_number;		break;
			case 'invoice_core':	$order_number = $invoice_number_core;	break;
		}
		

		// variable symbol //
		
		$variable_type = get_option('wc_settings_pohoda_export_invoice_data_variable');
		if ($variable_type == 'order') {
			$variable_symbol = $order_id;
		} else if ($variable_type == 'invoice') {
			$variable_symbol = $invoice_number;
		} else if ($variable_type == 'invoice_inner') {
			$variable_symbol = $invoice_number_core;
		}
		

		// specific symbol //
		
		$specific_type = get_option('wc_settings_pohoda_export_invoice_data_specific');
		if ($specific_type == 'order') {
			$specific_symbol = $order_id;
		} else if ($specific_type == 'invoice') {
			$specific_symbol = $invoice_number;
		} else if ($specific_type == 'invoice_inner') {
			$specific_symbol = $invoice_number_core;
		} else if ($specific_type == 'custom') {
			$specific_symbol = get_option('wc_settings_pohoda_export_invoice_data_specific_custom');
		}
		

		// invoice heading //
		
		foreach( $order->get_items() as $first_item_id => $first_item ) break;
		
		$product_categories = get_the_terms( $first_item_id, 'product_cat' );
		if ($product_categories) {
			foreach($product_categories as $product_category) break;
		}
		
		$heading_type = get_option('wc_settings_pohoda_export_invoice_text');
		if ($heading_type == 'first_name' && $first_item->get_name() !== null ) {
			$invoice_heading = $first_item->get_name();
		} else if ($heading_type == 'first_cat' && $product_category->name !== null ) {
			$invoice_heading = $product_category->name;
		} else {
			$invoice_heading = get_option('wc_settings_pohoda_export_invoice_text_custom');
		}

		$order_heading = __( 'New eshop order #', 'tckpoh' );
			
		
		// customer info //
		
		if ( !empty( $order->get_billing_address_2() ) ) {
			$billing_address = $order->get_billing_address_1() . ', ' . $order->get_billing_address_2();
		} else {
			$billing_address = $order->get_billing_address_1();
		}
		if ( !empty( $order->get_shipping_address_2() ) ) {
			$shipping_address = $order->get_shipping_address_1() . ', ' . $order->get_shipping_address_2();
		} else {
			$shipping_address = $order->get_shipping_address_1();
		}

		$customer_ico_field = get_option('wc_settings_pohoda_export_invoice_data_customer_ico');
		if ( $customer_ico_field ) {
			$customer_ico = get_post_meta( $order_id, $customer_ico_field, true );
		} else { $customer_ico = ''; }
		$customer_dic_field = get_option('wc_settings_pohoda_export_invoice_data_customer_dic');
		if ( $customer_dic_field ) {
			$customer_dic = get_post_meta( $order_id, $customer_dic_field, true );
		} else { $customer_dic = ''; }
				
		$customer_info = array(
			'company'	=> $order->get_billing_company(),
			'city' 		=> $order->get_billing_city(),
			'street' 	=> $billing_address,
			'zip'		=> $order->get_billing_postcode(),
			'country'	=> $order->get_billing_country(),
			'ico'		=> $customer_ico,
			'dic'		=> $customer_dic,
			'phone'		=> $order->get_billing_phone(),
			'email'		=> $order->get_billing_email(),
		);
		if ( !empty( $order->get_billing_company() ) ) {
			$customer_info['name'] = $order->get_billing_company() . ', ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		} else {
			$customer_info['name'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		}
		$customer_shipping = array(
			'company' 	=> $order->get_shipping_company(),
			'name'		=> $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
			'city'		=> $order->get_shipping_city(),
			'street'	=> $shipping_address,
			'zip'		=> $order->get_shipping_postcode(),
			'country'	=> $order->get_shipping_country(),
		);
		$billing_info = array(
			'company'	=> get_option('wc_settings_pohoda_export_billing_company'),
			'title'		=> get_option('wc_settings_pohoda_export_billing_company_title'),
			'surname'	=> get_option('wc_settings_pohoda_export_billing_surname'),
			'name'		=> get_option('wc_settings_pohoda_export_billing_name'),
			'street'	=> get_option('wc_settings_pohoda_export_billing_address_street'),
			'number'	=> get_option('wc_settings_pohoda_export_billing_address_street_number'),
			'city'		=> get_option('wc_settings_pohoda_export_billing_address_city'),
			'zip'		=> get_option('wc_settings_pohoda_export_billing_address_code'),
			'ico'		=> get_option('wc_settings_pohoda_export_billing_ico'),
			'dic'		=> get_option('wc_settings_pohoda_export_billing_dic'),
			'phone'		=> get_option('wc_settings_pohoda_export_billing_phone'),
			'email'		=> get_option('wc_settings_pohoda_export_billing_email'),
			'www'		=> get_option('wc_settings_pohoda_export_billing_website'),
		);


		// currency setting //

		$order_currency = $order->get_currency();
		$home_currency = get_option( 'wc_settings_pohoda_export_home_currency' );
		if ( !isset( $home_currency ) || empty( $home_currency ) ) {
			$home_currency = 'CZK';
		}
		
		if ( $order_currency !== $home_currency ) {
			$currency_format = 'foreignCurrency';
		} else {
			$currency_format = 'homeCurrency';
		}
		

		// vat classification //

		$vat_rate = array();
		$vat_rate['high'] = array(
			'coeficient' => 1.21,
			'vat_classification' => 'UD',
			'total_without_vat' => 0,
			'total_vat' => 0,
			'total' => 0,
			'total_without_vat_czk' => 0,
			'total_vat_czk' => 0,
			'total_czk' => 0,
			'name' => 'High',
		);
		$vat_rate['low'] = array(
			'coeficient' => 1.15,
			'vat_classification' => 'UD',
			'total_without_vat' => 0,
			'total_vat' => 0,
			'total' => 0,
			'total_without_vat_czk' => 0,
			'total_vat_czk' => 0,
			'total_czk' => 0,
			'name' => 'Low',
		);
		$vat_rate['third'] = array(
			'coeficient' => 1.10,
			'vat_classification' => 'UD',
			'total_without_vat' => 0,
			'total_vat' => 0,
			'total' => 0,
			'total_without_vat_czk' => 0,
			'total_vat_czk' => 0,
			'total_czk' => 0,
			'name' => '3',
		);
		$vat_rate['none'] = array(
			'coeficient' => 1,
			'vat_classification' => 'UN',
			'total_without_vat' => 0,
			'total_vat' => 0,
			'total' => 0,
			'total_without_vat_czk' => 0,
			'total_vat_czk' => 0,
			'total_czk' => 0,
			'name' => 'None',
		);
		/*
		$vat_rate['historyHigh'] = array(
			'coeficient' => 1,
			'vat_classification' => 'UD',
			'total_without_vat' => 0,
			'total_vat' => 0,
			'total' => 0,
			'total_without_vat_czk' => 0,
			'total_vat_czk' => 0,
			'total_czk' => 0,
			'name' => 'HistoryHigh',
		);*/


		//// ==== get items ==== ////

		$order_items = $order->get_items();
		$order_shipping = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'shipping' ));
		$order_fees = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'fee' ));
		$order_items_array = array_merge( $order_items, $order_shipping, $order_fees );
		$items_array = array();
		$total_discount = 0;

		foreach ( $order_items_array as $item_id => $item ) {

			$item_values = array();
			$item_values['name'] = $item->get_name();

			// quantity //

			$quantity = $item->get_quantity();
			if ( empty($quantity) || $quantity == NULL || $quantity == '' ) { $quantity = 1; } 
			$item_values['item_quantity'] = number_format( $quantity, 2, '.', '' );

			// get item prices //

			$item_prices = get_item_prices( $item, $item_values['item_quantity'], $order );
			$item_values['item_prices'] = $item_prices;

			// add to vat rates //

			$vat_rate[ $item_prices["item_vat_rate"] ]['total_without_vat']	+= $item_prices['item_total_without_vat'];
			$vat_rate[ $item_prices["item_vat_rate"] ]['total_vat']			+= $item_prices['item_total_vat'];
			$vat_rate[ $item_prices["item_vat_rate"] ]['total']				+= $item_prices['item_total'];

			// add to vat rates in CZK //

			$vat_rate[ $item_prices["item_vat_rate"] ]['total_without_vat_czk']	+= floatval( $item_prices['item_total_without_vat'] / $item_prices['rate'] );
			$vat_rate[ $item_prices["item_vat_rate"] ]['total_vat_czk']			+= floatval( $item_prices['item_total_vat'] / $item_prices['rate'] );
			$vat_rate[ $item_prices["item_vat_rate"] ]['total_czk']				+= floatval( $item_prices['item_total'] / $item_prices['rate'] );

			// count total discount //

			if ( isset( $item_prices['discount'] ) ) {
				$total_discount += $item_prices['discount'];
			}

			$items_array[ $item_id ] = $item_values;
		
		}
		

		// == vat setting == //

		if ( wc_tax_enabled() ) {
			$vat_included = 'true';
		} else {
			$vat_included = 'false';
		}

		
		// == total prices == //
		
		$total = 0;
		$total_without_vat = 0;
		$total_vat = 0;
		
		$order_price = array();
		foreach ( $vat_rate as $rate ) {

			if ( $rate['total_without_vat'] > 0 ) {

				$order_price['price'.$rate['name']]				= number_format( $rate['total_without_vat'], 2, '.', '' ); //Cena bez DPH
				if ( $rate['name'] !== 'None' ) {
					$order_price['price'.$rate['name'].'VAT'] 	= number_format( $rate['total_vat'], 2, '.', '' ); //DPH
					$order_price['price'.$rate['name'].'Sum']	= number_format( $rate['total'], 2, '.', '' ); //Cena vcetne DPH
				}

				$total				+= $rate['total'];
				$total_without_vat	+= $rate['total_without_vat'];
				$total_vat			+= $rate['total_vat'];
				
			}
		}
		
		$order_price_round = round( $order->get_total(), 2 ); //Celkova suma zaokrouhleni.
		
		$rounding = get_option('wc_settings_pohoda_export_invoice_data_rounding');
		if ( empty( $rounding ) ) { $rounding == 'math2one'; }

		
		// == payment type && EET == //
		
		$payment_method = $order->get_payment_method();
		$payment_types = array('card', 'cash', 'cod', 'transfer', 'special');
		$eet_sending = get_option('wc_settings_pohoda_export_invoice_export_to_eet');
		$eet_option = 'notEnter';
				
		foreach ( $payment_types as $paytype ) {
			
			$payment_option = get_option('wc_settings_pohoda_export_payment_methods_'.$paytype);
			
			if ( $payment_option == $payment_method ) {
				$payment_type_text = get_option('wc_settings_pohoda_export_payment_methods_'.$paytype.'_text');
				
				if ( $paytype == 'card' ) {
					$eet_option = $eet_sending;
				}
			}
		}


		///////////////////		===========   	create PDF	   ===========    	//////////////////

		if ( $document_type == 'pdf' ) {

			// setup MPDF //

            require_once TICKETAPOH_PATH . '/includes/mpdf8/vendor/autoload.php';

			$mpdf_config_class = new \Mpdf\Config\ConfigVariables();
			$defaultConfig = $mpdf_config_class->getDefaults();
			$fontDirs = $defaultConfig['fontDir'];

			$mpdf_font_config_class = new \Mpdf\Config\FontVariables();
			$defaultFontConfig = $mpdf_font_config_class->getDefaults();

			$fontData = $defaultFontConfig['fontdata'];
			$font_dir = TICKETAPOH_PATH . '/assets/fonts';
			$mpdf = new \Mpdf\Mpdf();
			$mpdf->autoLangToFont = true;

			// QR CODE //

			$iban = get_option( 'wc_settings_pohoda_export_pdf_iban');
			$add_qr = get_option( 'wc_settings_pohoda_export_pdf_qrcode' );
			if ( $iban && $add_qr == "yes" ) {

				$qr_link = 'SPD*1.0*ACC:' . $iban . '*AM:' . number_format( $total, 2 ) . '*CC:' . $order_currency . '*X-VS:' . $variable_symbol;

				require_once TICKETAPOH_PATH . 'includes/mpdf8/qrcode/src/QrCode.php';
				require_once TICKETAPOH_PATH . 'includes/mpdf8/qrcode/src/Output/Svg.php';

				$codeit = new Mpdf\QrCode\QrCode( $qr_link );
				$code_output = new Mpdf\QrCode\Output\Svg();
				$qr_code = $code_output->output( $codeit, 200, 'white', 'black' );
				$qr_code = str_replace( '<?xml version="1.0"?>', '', $qr_code );

				//$qr_code = '<barcode code="'.$qr_link.'" size="1" type="QR" error="M" class="barcode" />';
			} else {
				$qr_code = '';
			}

			// setup the template //

			$document_title = __( 'Invoice - tax document #', 'tckpoh' );
			$mpdf->SetTitle( $document_title . $invoice_number );

			require_once TICKETAPOH_PATH . 'includes/document-types/pdf-invoice/pdf-invoice.php';  
			
			$mpdf_css = file_get_contents( TICKETAPOH_PATH . 'includes/document-types/pdf-invoice/style.css' );
			$mpdf->WriteHTML( $mpdf_css, 1 );

			//// create the pdf ////
			
			$mpdf->WriteHTML( $html, 0 );

			if ( $export_type == "pdf_to_email" ) {

				$mpdf->Output( $dir.'/'.$invoice_number.'.pdf', 'F' );
				$document_path = $dir.'/'.$invoice_number.'.pdf';
				return $document_path;

			} else if ( $export_type == "pdf_to_screen" ) {

				$mpdf->Output();
				return;
			}

		}

		
		///////////////// 	===========	   create XML content	===========    ////////////////////

		if ( $export_type == 'to_mserver' || $export_type == "to_xml_first" || $export_type == 'to_xml_first_and_last' ) {
		
			$xml = new XMLWriter();

			if ( $export_type == "to_mserver" ) {
				$xml->openMemory();
			} else {
				//$xml->openMemory();
				$xml->openURI( $dir. "export-" . date("Y-m-d") . ".xml" );
			}
			$xml->setIndent(true);
			$xml->setIndentString("\t");

			$xml->startDocument('1.0', 'Windows-1250');
			// <xml version="1.0" encoding="windows-1250">
		
			$xml->startElementNS('dat', 'dataPack', "http://www.stormware.cz/schema/version_2/data.xsd");
			$xml->writeAttributeNs("xmlns","inv", null, "http://www.stormware.cz/schema/version_2/invoice.xsd");
			$xml->writeAttributeNs("xmlns","typ", null, "http://www.stormware.cz/schema/version_2/type.xsd");
			$xml->writeAttributeNs("xmlns","ord", null, "http://www.stormware.cz/schema/version_2/order.xsd");
			$xml->writeAttribute('id', $order_name);
			$xml->writeAttribute('ico', get_option('wc_settings_pohoda_export_billing_ico'));
			$xml->writeAttribute('key', get_option('wc_settings_pohoda_export_accounting_key'));
			$xml->writeAttribute('application', 'Ticketa Pohoda Export');
			$xml->writeAttribute('version', '2.0');
			$xml->writeAttribute('note', 'Import faktur a objednávek');
			// <dat:dataPack id="fa001" ico="12345678" application="StwTest" version="2.0" note="Import FA" xmlns:dat="http://www.stormware.cz/schema/version_2/data.xsd" xmlns:inv="http://www.stormware.cz/schema/version_2/invoice.xsd" xmlns:typ="http://www.stormware.cz/schema/version_2/type.xsd">
				
		}


				//// create dataPackItem by document type ////
		
				include( TICKETAPOH_PATH . 'includes/document-types/' . $document_type . '.php' );
				
				//// ==================================== ////
				


		//// send xml to another round if export to xml ////

		if ( $export_type == 'to_xml_first' || $export_type == "to_xml" ) {
			return $xml;
		}

		//// otherwise end it ////

		if ( $export_type == 'to_mserver' || $export_type == 'to_xml_last' || $export_type == 'to_xml_first_and_last' ) {

			// end dataPack if single invoice or xml ending //

			xml_ending:
			$xml->endElement();
			$xml->endDocument();

			if ( $export_type == 'to_mserver' ) {

				// pass data on to the call //
				$xml_output = $xml->outputMemory();

			} else {

				// save the xml if manually exported //
				$xml->flush();
				//$xml_output = $xml->outputMemory();
				//file_put_contents( $dir. "export-" . date("Y-m-d") . ".xml", $xml_output );
				return $url_dir. "export-" . date("Y-m-d") . ".xml";

			}

		}		
		

		//// >>>> ------------------ send the xml -------------------- <<<< ////
		
		$export_response = make_the_call( $xml_output, '', '', '' );

		
		//// check for errors ////
		
		if ( substr($export_response, 0, 5) == "<?xml" ) {
						
			/*
			// save the response //
			file_put_contents($dir.'/re-'.$order_id.'.xml', $export_response);
			// save the call //
			file_put_contents($dir.'/'.$order_id.'.xml', $xml_output);
			*/
								
			// if response is XML //
						
			$response_pack = simplexml_load_string($export_response);
			$ns = $response_pack->getDocNamespaces(); 
									
			if ( $response_pack && $response_pack->attributes()->state != 'error' ) {
								
				$response_pack_item = $response_pack->children($ns['rsp'])->responsePackItem;
				
				if ( $response_pack_item && $response_pack_item->attributes()->state != 'error' ) {
					
					$export_invoice_response = $response_pack_item->children($ns['inv'])->invoiceResponse;
					if ( $document_type == 'order' ) {
						$export_invoice_response = $response_pack_item->children($ns['ord'])->orderResponse;
					}
							
					// everything is ok //
					tckpoh_logs( __( 'Pohoda response: ', 'tckpoh' ) . $export_invoice_response->attributes()->state );
								
					if ( $export_invoice_response->attributes()->state == 'ok' ) {
						
						remove_from_unexported( $order_id, $document_type );
						
					} else {

						// import error //
						tckpoh_logs( __( 'Got import minor error', 'tckpoh' ) );
						submit_export_error( $order_id, $invoice_response_state, $export_response, $document_type );
					}
					
				} else {
					
					// validation error //
					tckpoh_logs( __( 'Got validation error', 'tckpoh' ) );
					submit_export_error( $order_id, 'validation-error', $export_response, $document_type );
				}
				
			} else {
				
				// package error //
				tckpoh_logs( __( 'Got package error', 'tckpoh' ) );
				submit_export_error( $order_id, 'package-error', $export_response, $document_type );
			}
			
		} else {
			
			// if not xml, the response is a code or wp error //
			tckpoh_logs( __( 'Got communication error', 'tckpoh' ) );
			submit_export_error( $order_id, 'not-connected', $export_response, $document_type );

		}	
	
	}
}




//// get item prices === price counting ////

function get_item_prices( $item, $item_quantity, $order ) {	

	$return = array();
	$discount_percentage = 0;
	$rate = 1;
	$item_vat_rate = 'none';

	$item_total = $item->get_total();


	// == get tax rate == //

	$tax_obj = new WC_Tax();
	// Get the tax data from customer location and product tax class
	$tax_rates_data = $tax_obj->find_rates( array(
		'country'   => $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country(),
		'state'     => $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state(),
		'city'      => $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city(),
		'postcode'  => $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city(),
		'tax_class' => $item->get_tax_class()
	) );

	if ( $item->is_type('line_item') ) {
		$line_tax_total = floatval( $item->get_subtotal_tax() );
	}

	if ( !empty( $tax_rates_data ) ) {

		$tax_rate = reset( $tax_rates_data )['rate'];

	} else if ( !empty( $line_tax_total ) ) {

		$item_percent = floatval( $item_total / 100 );
		$tax_rate = round( $line_tax_total / $item_percent );

	} else {
		$tax_rate = 0;
	}

	switch( $tax_rate ) {
		case 21:	$item_vat_rate = "high";	break;
		case 20:	$item_vat_rate = "high";	break;
		case 15:	$item_vat_rate = "low";		break;
		case 12:	$item_vat_rate = "low";		break;
		case 10:	$item_vat_rate = "third";	break;
		case 0:		$item_vat_rate = "none";	break;
	}

	// other countries than CZ and SK //
	/*
	$country = $order->get_shipping_country();
	if ( $country !== 'CZ' && $country !== 'SK' ) {
		$item_vat_rate = "historyHigh";
	}*/
	

	// == actual price == //
	
	if ( $item->is_type('fee') || $item->is_type('shipping') ) {

		// lisi se hodnota ulozene dane podle typu //

		$item_total_tax = $item->get_total_tax();
		$item_actual_price = ( $item_total ) / $item_quantity;

	} else {

		$item_total_tax = ( $item_total / 100 ) * $tax_rate;
		
		if ( wc_prices_include_tax() || !wc_tax_enabled() ) {
			$item_actual_price = ( $item_total ) / $item_quantity;
		}
		else {
			$item_actual_price = ( round($item_total,2) ) / $item_quantity;
		}
	}

	//$item_total_tax = $item->get_total_tax();
	$item_unit_tax = $item_total_tax / $item_quantity;
	$item_unit_price = $item_total / $item_quantity;

	//$item_actual_price = floatval( $item_unit_price );


	// == regular price == //

	if ( $item->is_type('line_item') ) {

		if ( $item->get_variation_id() > 0 ) {
			$item_product = new WC_Product_Variation( $item->get_variation_id() );
			$item_regular_price = floatval( $item_product->get_price() );
		} else {
			$item_product = $item->get_product();
			if ( $item_product ) { 
				$item_regular_price = floatval( $item_product->get_regular_price() );
			}
		}

	} else {
		$item_regular_price = $item_unit_price;
	}
	if ( !isset($item_regular_price) ) {
		//$item_regular_price = $order->get_item_subtotal( $item, false );
		$item_regular_price = floatval( $item->get_subtotal() / $item_quantity );
	}
	if ( $item->is_type('line_item') ) {
		if ( wc_prices_include_tax() || !wc_tax_enabled() ) {
			$item_regular_tax = ( $item_regular_price / ( 100 + $tax_rate ) ) * $tax_rate;
			$item_regular_price = $item_regular_price - $item_regular_tax;
		}
	}


	// == foreign currencies == //

	$currency = $order->get_currency();
	$home_currency = get_option( 'wc_settings_pohoda_export_home_currency' );
	if ( !isset( $home_currency ) || empty( $home_currency ) ) {
		$home_currency = 'CZK';
	}

	if ( $currency !== $home_currency ) {

		// CURCY plugin //
		$currency_plugin_curcy = get_post_meta( $item->get_order_id(), 'wmc_order_info', true );
		// WCML multicurrency plugin //
		$currency_plugin_wcml = get_option( '_wcml_settings' );

		if ( $currency_plugin_curcy ) {

			$rate = $currency_plugin_curcy[$currency]['rate'];

		} else if ( $currency_plugin_wcml ) {

			$rate = $currency_plugin_wcml['currency_options'][$currency]['rate'];

		} else {
			$rate = tckpoh_get_conversion_rate( $home_currency, $currency, 1 );
		}

		update_post_meta( $item->get_order_id(), 'pohoda_conversion_rate', $rate );

	}


	// == sleva == //

	if ( round( $item_regular_price ) !== round( $item_actual_price ) && round( $item_regular_price ) > round( $item_actual_price ) ) {
		
		$one_percent = floatval( $item_regular_price / 100 );
		$price_percent = floatval( $item_unit_price / $one_percent ); // >> happens to be 0
		$discount_percentage = floatval( 100 - $price_percent );

		$discount = ( floatval( $item_regular_price ) - floatval( $item_actual_price ) ) * $item_quantity;
		if ( wc_prices_include_tax() ) {
			$return['discount'] = $discount * ( 1 + ( $tax_rate / 100 ) );
		} else {
			$return['discount'] = $discount;
		}

	} else {
		$return['discount'] = 0;
	}

	// accounting //

	$item_type = $item->get_type();
	$return['accounting'] = get_option('wc_settings_pohoda_export_invoice_predkontace_' . $item_type );

	// total //

	$return['item_total_vat'] = floatval( $item_total_tax );
	$return['item_total_without_vat'] = floatval( $item_total );
	$return['item_total'] = floatval( $item_total + $item_total_tax );

	// unit //

	$item_unit_with_vat = round( ( $item_unit_price + $item_unit_tax ), 2 );

	$return['item_unit_vat'] = floatval( $item_unit_tax );
	$return['item_unit_price'] = floatval( $item_unit_price );
	$return['item_unit_with_vat'] = floatval( $item_unit_with_vat );
	if ( $discount_percentage > 0 ) {
		$return['item_unit_without_vat'] = floatval( $item_regular_price );
	} else {
		$return['item_unit_without_vat'] = floatval( $item_actual_price );
	}

	// other //
	if ( $item->is_type('line_item')  ) {
		$return['stock_id'] = $item_product->get_sku();
	} else {
		$return['stock_id'] = 'x';
	}

	$return['item_vat_rate'] = $item_vat_rate;
	$return['item_tax_rate'] = $tax_rate;
	$return['rate'] = $rate;
	$return['item_discount'] = $discount_percentage;

	//error_log('total : ' . $item_total . ' --- total tax : ' . $item_total_tax . ' /// unit : ' . $item_unit_price . ' --- unit tax : ' . $item_unit_tax . ' // unit with tax: ' . $item_unit_with_vat . ' /// regular : '.  $item_regular_price . ' --- actual : ' .  $item_actual_price . ' /// discount: ' . $return['discount'] . ' / percentage: ' . $return['item_discount'] );

	return $return;


	//////// +++++++ COUPONS ++++++++ ////////
	/*
	foreach( $order->get_used_coupons() as $coupon_code ){

		$coupon_post_obj = get_page_by_title( $coupon_code, OBJECT, 'shop_coupon' );
		$coupon_id       = $coupon_post_obj->ID;
		$coupon			 = new WC_Coupon( $coupon_id );
	
		if( $coupon->get_discount_type() == 'percent' ){
			
			$coupon_amount = $coupon->get_amount();
		}
		if( $coupon->is_type( 'fixed_cart' ) ) {

			$coupon_amount = $coupon->get_amount();
		}
		if( $coupon->is_type( 'fixed_product' ) ){
 
			$coupon_amount = $coupon->get_amount();
		}
		$cp_free_shipping = get_post_meta( $coupon_id, 'free_shipping', true ); 
		//error_log( $coupon->get_discount_type() . ' -- ' . $coupon->get_amount() );
	}
	*/
	

}





//// ------ create invoice numbering ------ ////

function create_invoice_number( $order_id ) {
			
	// check if invoice exists //
	
	$saved_invoice_number = get_post_meta( $order_id, 'pohoda_invoice_number', true );
	
	if ( empty( $saved_invoice_number ) ) {

		
		$numbering_type = get_option('wc_settings_pohoda_export_invoice_numbering_type');
		// get number from a plugin //

		if ( $numbering_type == 'yes' ) {

			$plugin_invoice_number_field = get_option('wc_settings_pohoda_export_invoice_plugin_numbering');
			$plugin_invoice_number = get_post_meta( $order_id, $plugin_invoice_number_field, true );

			update_post_meta( $order_id, 'pohoda_invoice_number', $plugin_invoice_number );
			update_post_meta( $order_id, 'pohoda_invoice_number_core', $plugin_invoice_number );

		// create a new number //
		
		} else {

			// prefix //
			$invoice_prefix = get_option('wc_settings_pohoda_export_invoice_prefix');
			$invoice_prefix_type = get_option('wc_settings_pohoda_export_invoice_prefix_type');
				
				if ($invoice_prefix_type == 'date') {
					$prefix = date( create_date_format( $invoice_prefix ) );	
				} else {
					$prefix = $invoice_prefix;
				}
			
			// suffix //
			$invoice_suffix_type = get_option('wc_settings_pohoda_export_invoice_suffix_type');
			$invoice_suffix = get_option('wc_settings_pohoda_export_invoice_suffix');
			
				if ($invoice_suffix_type == 'date') {
					$suffix = '-' . date( create_date_format( $invoice_suffix ) );	
				} else if ($invoice_suffix_type == 'order') {
					$suffix = '-' . $order_id;
				} else if ($invoice_suffix_type == 'text') {
					$suffix = '-' . $invoice_suffix;
				} else {
					$suffix = '';
				}
					
			$invoice_last_number = get_option('wc_settings_pohoda_export_invoice_number_now');
			$invoice_new_number = intval( $invoice_last_number + 1 );
			$invoice_number_count = get_option('wc_settings_pohoda_export_invoice_number_count');
			$invoice_number_core = str_pad( $invoice_new_number, $invoice_number_count, '0', STR_PAD_LEFT );
			$invoice_number = $prefix . $invoice_number_core . $suffix;

			update_post_meta( $order_id, 'pohoda_invoice_number', $invoice_number );
			update_post_meta( $order_id, 'pohoda_invoice_number_core', $invoice_number_core );
			
			update_option('wc_settings_pohoda_export_invoice_number_now', $invoice_new_number, true);

		}
		
	}
}



//// create invoice date function ////

function create_invoice_date( $order_id ) {

	// try to get saved date //

	$invoice_date = get_post_meta( $order_id, 'pohoda_invoice_date', true );

	if ( !$invoice_date ) {
	
		$order = wc_get_order( $order_id );

		// get date type //
		$invoice_date_type = get_option('wc_settings_pohoda_export_invoice_data_date_type');
		if ( !$invoice_date_type ) { $invoice_date_type = 'created'; }

		// get order date by type //
		switch ( $invoice_date_type ) {
			case "modified":	$invoice_order_date = $order->get_date_modified();	break;
			case "completed":	$invoice_order_date = $order->get_date_completed(); break;
			case "paid":		$invoice_order_date = $order->get_date_paid();		break;
			case "created":		$invoice_order_date = $order->get_date_created();	break;
		}
		if ( !$invoice_order_date ) {
			$invoice_order_date = $order->get_date_created();
		}

		$invoice_date = date('Y-m-d', strtotime( $invoice_order_date ) );
		update_post_meta( $order_id, 'pohoda_invoice_date', $invoice_date );

	}

	return $invoice_date;

}



//// create date format function ////

function create_date_format( $set_format ) {
	
	$set_format = strtolower($set_format);
	
	$year_count = substr_count($set_format, 'y');
	$month_count = substr_count($set_format, 'm');
	$day_count = substr_count($set_format, 'd');
	
	if ($year_count > 2) { $years = 'Y'; } else { $years = 'y'; }
	if ($month_count > 1) { $months = 'm'; } else { $months = 'n'; }
	if ($day_count > 1) { $days = 'd'; } else { $days = 'j'; }
	
	$format = preg_replace("/(.)\\1+/", "$1", $set_format);
	$format_output = str_replace(array('y','m','d'), array($years, $months, $days), $format);
	
	return $format_output;
}


//// get conversion rate ////

function tckpoh_get_conversion_rate( $from, $to, $amount ) {

	$currency_converter_api_key = get_option('wc_settings_pohoda_export_converter_api_key');

	$address = 'https://exchangerate-api.p.rapidapi.com/rapid/latest/'.$from;

	$response = wp_remote_get( $address, array(
		'method' => 'GET',
		'blocking' => true,
		'headers' => array(
			'x-rapidapi-host' => 'exchangerate-api.p.rapidapi.com',
			'x-rapidapi-key' => $currency_converter_api_key
		),
		'httpversion' => '1.0',
    	'sslverify'   => true,
	) );
	
	// reply //
	
	if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
		$conversion_rate = '1';
	} else {
		$result = json_decode( wp_remote_retrieve_body( $response ) );
		$conversion_rate = $result->rates->$to;
	}

	//tckpoh_logs( 'response ' . json_encode($response) );
	//tckpoh_logs( 'rate ' . $conversion_rate );

	return $conversion_rate;
}

?>