<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


//// load the xml file ////

function load_my_xml( $xml_name, $ico ) {
	
	$xml_call = file_get_contents( TICKETAPOH_PATH . "/includes/calls/" . $xml_name . ".xml" );
	
	if (isset($ico)) {
		$xml_call = str_replace('12345678', $ico, $xml_call);	
	}
	
	return $xml_call;
	
}



//// connect to mserver and get accountings ////

function pohoda_mserver_connect() {
	
	$host = $_POST['host'];
	$login = $_POST['login'];
	$pass = $_POST['pass'];

	tckpoh_logs( __( 'Connecting to mServer from settings page ...', 'tckpoh' ) );
	
	$data_in = load_my_xml( "ucetni_jednotky", '' );	
	$answer = make_the_call( $data_in, $host, $login, $pass );
		
	if ( substr($answer, 0, 5) == "<?xml" ) {

		$xml = simplexml_load_string( $answer );
	
		$ns = $xml->getDocNamespaces(); 
		$response = $xml->children($ns['rsp']);
		$accounting_list = $response->children($ns['acu'])->listAccountingUnit;
		
		$return = array( 
			'error' => false,
			'units' => array()
		);
		
		foreach ( $accounting_list->children($ns['acu'])->itemAccountingUnit as $unit ) {

			$unit_name = (string)$unit->children($ns['acu'])->accountingUnitIdentity->children($ns['typ'])->address->children($ns['typ'])->company;
			$unit_year = (string)$unit->children($ns['acu'])->year;
			$unit_key = (string)$unit->children($ns['acu'])->key;
			$unit_id = $unit_name. ' - ' .$unit_year;
		
			if ($unit_year == date('Y')) { // Jen tento ucetni rok
				$return['units'][$unit_id] = array($unit_key);
			}
		}

	} else {
		
		// if not xml, the response is a code or wp error //
		tckpoh_logs( __( 'Got communication error: ', 'tckpoh' ) . $answer );
		$return = array( 'error' => true );
			
	}
	
	echo json_encode($return);
	wp_die();
	
}
add_action('wp_ajax_pohoda_mserver_connect', 'pohoda_mserver_connect');



//// load accounting billing info ////

function pohoda_load_billing_info() {
	
	$host = $_POST['host'];
	$login = $_POST['login'];
	$pass = $_POST['pass'];
	$accounting_key = $_POST['accounting_key'];

	tckpoh_logs( __( 'Loading billing info on settings page ...', 'tckpoh' ) );

	$data_in = load_my_xml( "ucetni_jednotky", '' );	
	$answer = make_the_call($data_in, $host, $login, $pass);
	$xml = simplexml_load_string($answer);
		
	if ( $xml ) {
	
		$ns = $xml->getDocNamespaces(true); 
		$response = $xml->children($ns['rsp']);
		$accounting_list = $response->children($ns['acu'])->listAccountingUnit;
		
		$return = array('error' => false, 'units' => array());
		
		foreach ($accounting_list->children($ns['acu'])->itemAccountingUnit as $unit) {

			$unit_key = (string) $unit->children($ns['acu'])->key;
			$unit_year = (string) $unit->children($ns['acu'])->year;
			$unit_type = (string) $unit->children($ns['acu'])->unitType;

			if ($unit_key === $accounting_key && $unit_year === date('Y')) {

				// nazev a klic //

				$company = (string)$unit->children($ns['acu'])
					->accountingUnitIdentity
					->children($ns['typ'])
					->address
					->children($ns['typ'])
					->company;
				$label = $company . ' - ' . $unit_year;
				$unit_details = array();

				// adresa a detaily //

				foreach ( $unit->children($ns['acu'])->accountingUnitIdentity->children($ns['typ'])->address->children($ns['typ']) as $unit_info ) {
					$unit_details[$unit_info->getName()] = $unit_info;
				}

				if (isset($unit_details['ico'])) {
					$ico = $unit_details['ico'];
					
					if ($unit_type) {
						if ($unit_type == 'doubleEntry') {
							$return['predkontace'] = get_minor_data('predkontace_podvojne', $ico, $host, $login, $pass);
						} else {
							$return['predkontace'] = get_minor_data('predkontace_evidence', $ico, $host, $login, $pass);					
						}
					}
								
					$return['bankovni_ucty'] = get_minor_data('bankovni_ucty', $ico, $host, $login, $pass);
					$return['cinnosti'] = get_minor_data('cinnosti', $ico, $host, $login, $pass);
					$return['strediska'] = get_minor_data('strediska', $ico, $host, $login, $pass);
					$return['provozovny'] = get_minor_data('provozovny', $ico, $host, $login, $pass);
					$return['prefixy'] = get_minor_data('prefixy', $ico, $host, $login, $pass);
					$return['formy_uhrad'] = get_minor_data('formy_uhrad', $ico, $host, $login, $pass);
					
				}

				// vysledky //

				$return['units'][$label] = array(
					'unit_key' => $unit_key,
					'unit_details' => $unit_details
				);
			}
		}

	} else {
		
		// if not xml, the response is a code or wp error //
		tckpoh_logs( __( 'Got communication error: ', 'tckpoh' ) . $answer );
		$return = array( 'error' => true );
		
	}
	
	echo json_encode($return);
	wp_die();
	
}
add_action('wp_ajax_pohoda_load_billing_info', 'pohoda_load_billing_info');



//// get minor data function ////

function get_minor_data($type, $ico, $host, $login, $pass) {
	
	$data_in = load_my_xml( $type, $ico );	
	$answer = make_the_call( $data_in, $host, $login, $pass );
	$xml = simplexml_load_string( $answer );
		
	if ( $xml ) {
	
		$ns = $xml->getDocNamespaces(); 
		$response = $xml->children($ns['rsp']);
		$data = array();
		
		switch ($type) {
  			case "bankovni_ucty":
				$account_list = $response->children($ns['lst'])->listBankAccount;	
				foreach ( $account_list->children($ns['lst'])->bankAccount as $account ) {
					$account_id = $account->children($ns['bka'])->bankAccountHeader->children($ns['bka'])->id;
					$data['account_'.$account_id] = array(
						'account_id' => $account_id,
						'account_ids' => $account->children($ns['bka'])->bankAccountHeader->children($ns['bka'])->ids,
						'account_number' => $account->children($ns['bka'])->bankAccountHeader->children($ns['bka'])->numberAccount,
						'account_bank' => $account->children($ns['bka'])->bankAccountHeader->children($ns['bka'])->codeBank,
						'account_bank_name' => $account->children($ns['bka'])->bankAccountHeader->children($ns['bka'])->nameBank,
					);
				}
			break;
			case "cinnosti":
				$cinnosti_list = $response->children($ns['lAcv'])->listActivity;
				foreach ( $cinnosti_list->children($ns['lAcv'])->activity as $cinnost ) {
					$cinnost_id = $cinnost->children($ns['acv'])->activityHeader->children($ns['acv'])->id;
					$data['activity_'.$cinnost_id] = array(
						'cinnost_id' => $cinnost_id,
						'cinnost_code' => $cinnost->children($ns['acv'])->activityHeader->children($ns['acv'])->code,
						'cinnost_name' => $cinnost->children($ns['acv'])->activityHeader->children($ns['acv'])->name,
					);
				}
			break;
			case "strediska":
				$centre_list = $response->children($ns['lCen'])->listCentre;
				foreach ( $centre_list->children($ns['lCen'])->centre as $centrum ) {
					$centrum_id = $centrum->children($ns['cen'])->centreHeader->children($ns['cen'])->id;
					$data['centre_'.$centrum_id] = array(
						'centrum_id' => $centrum_id,
						'centrum_code' => $centrum->children($ns['cen'])->centreHeader->children($ns['cen'])->code,
						'centrum_name' => $centrum->children($ns['cen'])->centreHeader->children($ns['cen'])->name,
					);
				}
			break;
			case "provozovny":
				$estab_list = $response->children($ns['lst'])->listEstablishment;
				foreach ( $estab_list->children($ns['lst'])->establishment as $establishment ) {
					$establishment_id = $establishment->children($ns['est'])->establishmentHeader->children($ns['est'])->id;
					$data['establishment_'.$establishment_id] = array(
						'establishment_id' => $establishment_id,
						'establishment_code' => $establishment->children($ns['est'])->establishmentHeader->children($ns['est'])->code,
						'establishment_name' => $establishment->children($ns['est'])->establishmentHeader->children($ns['est'])->name,
					);
				}
			break;
			case "prefixy":
				$prefix_list = $response->children($ns['lst'])->listNumericSeries;
				foreach ( $prefix_list->children($ns['lst'])->itemNumericSeries as $prefix ) {
					$prefix_id = $prefix->attributes()->id;
					$data['prefix_'.$prefix_id] = array(
						'prefix_id' => $prefix_id,
						'prefix_code' => $prefix->attributes()->code,
						'prefix_name' => $prefix->attributes()->name,
					);
				}
			break;
			case "predkontace_podvojne":
				$predkontace_list = $response->children($ns['lst'])->listAccountingDoubleEntry;
				foreach ( $predkontace_list->children($ns['lst'])->itemAccounting as $predkontace ) {
					$predkontace_id = $predkontace->attributes()->id;
					$agenda = $predkontace->attributes()->agenda;
					if ($agenda == 'issuedInvoice') {
						$data['predkontace_'.$predkontace_id] = array(
							'predkontace_id' => $predkontace_id,
							'predkontace_code' => $predkontace->attributes()->code,
							'predkontace_name' => $predkontace->attributes()->accounting,
						);
					}
				}
			break;
			case "predkontace_evidence":
				$predkontace_list = $response->children($ns['lst'])->listAccountingSingleEntry;
				foreach ( $predkontace_list->children($ns['lst'])->itemAccounting as $predkontace ) {
					$predkontace_id = $predkontace->attributes()->id;
					$agenda = $predkontace->attributes()->agenda;
					if ($agenda == 'issuedInvoice') {
						$data['predkontace_'.$predkontace_id] = array(
							'predkontace_id' => $predkontace_id,
							'predkontace_code' => $predkontace->attributes()->code,
							'predkontace_name' => $predkontace->attributes()->accounting,
						);
					}
				}
			break;
			case "formy_uhrad":
				$uhrady_list = $response->children($ns['lst'])->listPayment;
				foreach ( $uhrady_list->children($ns['lst'])->payment as $forma ) {
					
					$payment_data = $forma->children($ns['pay'])->paymentHeader;
					$forma_id = $payment_data->children($ns['pay'])->id;
					$data['forma_'.$forma_id] = array(
						'forma_id' => $forma_id,
						'forma_text' => $payment_data->children($ns['pay'])->text,
						'forma_name' => $payment_data->children($ns['pay'])->name,
					);
				}
			break;		
		}

	} 
	
	return $data;
}



//// save settings ////

function pohoda_save_options() {
	
	$options = $_POST['options'];
	$power = $_POST['power'];
	
	foreach ( $options as $key => $value ) {
		update_option( $key, strval( $value ), true );
	}
	
	$switched = update_option('wc_settings_pohoda_export_switch', $power, true);
	
	if ( is_wp_error( $switched ) ) {
		
		$return = array('error' => 'true');
		
	} else {
		
		$return = array('error' => 'false');
		
		// switch the scheduled actions //
		
		if ($power == 1) {
			turn_actions_on();
		} else {
			turn_actions_off();
		}
		
	}
	
	echo json_encode($return);
	wp_die();
	
}
add_action('wp_ajax_pohoda_save_options', 'pohoda_save_options');



//// check this years unexported orders ////

function pohoda_check_this_year() {
	
	$dates_chosen = $_POST['dates_chosen'];
	$currency_chosen = $_POST['currency_chosen'];
	$which_orders = $_POST['which_orders'];

	// kdyz se exportujou objednavky, tak vyhledat all namisto selected vvvv
	$frequency = get_option('wc_settings_pohoda_export_invoice_export_type');
	$export_orders = get_option('wc_settings_pohoda_export_invoice_export_orders');
	if ( $frequency == 'order_only' ) {
		$this_years_orders = get_this_years_orders( 'all', $dates_chosen );
	} else {
		$this_years_orders = get_this_years_orders( 'selected', $dates_chosen );
	}
		
	if ( $this_years_orders ) {

		tckpoh_logs( __( 'Searching for all unexported orders of this year.', 'tckpoh' ) );

		$i = 0;
				
		foreach( $this_years_orders as $order_id ) {
			
			$exported_invoice_number = get_post_meta( $order_id, 'pohoda_invoice_number', true );

			if ( isset($currency_chosen) && $currency_chosen != '' ) {

				$check_currency = true;
				$order = wc_get_order( $order_id );
				$order_currency = $order->get_currency();

			} else {
				$check_currency = false;
			}

			if ( ( $check_currency == true && $currency_chosen == $order_currency ) || $check_currency == false ) {
			
				// kdyz hledam jen nevyexportovane //
				if ( $which_orders == 'not_exported' ) {
					if ( !$exported_invoice_number ) {

						add_to_unexported( $order_id );
						$i++;

					}
				}

				// kdyz hledam vsechny //
				if ( $which_orders == 'all' ) {

						add_to_unexported( $order_id );
						$i++;

				}
				
			}
			
		}
		
		$return = array(
			'error' => 'bezproblemu',
			'order_count' => intval( $i ),
		);
		
	} else {
		
		$return = array('error' => 'true');		
		
	}
	
	echo json_encode($return);
	wp_die();
	
}
add_action('wp_ajax_pohoda_check_this_year', 'pohoda_check_this_year');



//// vynulovat momentalni cislo faktury ////

function pohoda_reset_core_number() {
		
	$reset = update_option('wc_settings_pohoda_export_invoice_number_now', '0', true);

	// delete invoice numbers if wanted //
	
	if ( $_POST['whattodo'] == 'and_erase_invoice_numbers' ) {
				
		$this_years_orders = get_this_years_orders( 'all', 'all' );
		
		foreach( $this_years_orders as $order_id ) {
			
			delete_post_meta( $order_id, 'pohoda_invoice_number' );
			delete_post_meta( $order_id, 'pohoda_invoice_number_core' );
			delete_post_meta( $order_id, 'pohoda_invoice_date' );
		}
		
		tckpoh_logs( __( 'Erased invoice numbers attached to all the orders.', 'tckpoh' ) );
	}
		
	if ( is_wp_error( $reset ) ) {
				
		$return = array('error' => 'true');
		
	} else {
		
		$return = array('error' => 'bezproblemu');
		
	}
	
	echo json_encode($return);
	wp_die();
	
}
add_action('wp_ajax_pohoda_reset_core_number', 'pohoda_reset_core_number');



//// get this years orders ////

function get_this_years_orders( $status, $dates_chosen = null ) {

	if ( $dates_chosen == null ) {

		$initial_date = date('Y-m-d', strtotime('first day of january this year'));
		$final_date = date('Y-m-d', strtotime('last day of December this year'));

	} else if ( $dates_chosen == 'all' ) {

		$initial_date = date('Y-m-d', strtotime('first day of january 2000'));
		$final_date = date('Y-m-d', strtotime('last day of December this year'));

	} else {

		$dates = explode( "==", $dates_chosen );
		$initial_date = date( 'Y-m-d', strtotime($dates[0]) );
		$final_date = date( 'Y-m-d', strtotime($dates[1]) );

	}

	$order_args = array(
		'posts_per_page' => -1,
		'fields' => 'ids',
		'post_type' => 'shop_order',
		'post_status' => 'any',
		'orderby' => 'ID',
		'order' => 'ASC',
		'date_query' => array(
			array(
				'after' 	=> $initial_date,
				'before'    => $final_date,
				'inclusive' => true,
			)
		)
	);

	if ( $status == 'selected' ) {

		$status_set = get_option('wc_settings_pohoda_export_invoice_export_status');
		if ( $status_set == 'wc-processing' ) {
			$order_args['post_status'] = array( 'wc-completed','wc-processing' );
		} else {
			$order_args['post_status'] = array( $status_set );
		}
	}
	
	$found_orders = get_posts( $order_args );	
	return $found_orders;
}


//// vymazat frontu na export ////

function pohoda_reset_queue() {	
	
	$erase = update_option('wc_settings_pohoda_export_failed_order_exports', '', true);

	if ( is_wp_error( $erase ) ) {
		$return = 'error';
		tckpoh_logs( __( 'Tried, but unable to erase the export queue.', 'tckpoh' ) );
	} else {
		$return = 'bezproblemu';
		tckpoh_logs( __( 'Erased the export queue.', 'tckpoh' ) );
	}
	
	echo $return;
	wp_die();
	
}
add_action('wp_ajax_pohoda_reset_queue', 'pohoda_reset_queue');



//// odeslat error log na podporu ////

function pohoda_send_log_to_support() {	
	
	$message = get_bloginfo( 'name' ) . __( ' is sending you their error log.', 'tckpoh' );
	$email_sent = send_error_to_admin( $message, 'podpora@ticketa.cz' );

	if ( is_wp_error( $email_sent ) ) {
		$return = 'error';
	} else {
		$return = 'bezproblemu';
	}
	
	echo $return;
	wp_die();
	
}
add_action('wp_ajax_pohoda_send_log_to_support', 'pohoda_send_log_to_support');



//// vytvorit XML soubor z cele fronty ////

function pohoda_export_xml_file() {	
	
	// nahrat frontu //
	$unexported_order_exports = get_option('wc_settings_pohoda_export_failed_order_exports');
	$unexported_order_array = explode(',', $unexported_order_exports);
	$xml_file = 'no_file';
	$xml_output = 'no_output';
	$xml_count = 0;

	tckpoh_logs( __( 'Creating the XML file.', 'tckpoh' ) );

	// vytvorit xml //
	if ( $unexported_order_array ) {

		$order_count = count( $unexported_order_array );
		$export_number = 1;

		foreach( $unexported_order_array as $unexported_export_order_id ) {

			// pokud je jen jedna //
			if ( $export_number == $order_count && $export_number == 1 ) {
				$xml_file = create_invoice( $unexported_export_order_id, 'to_xml_first_and_last' );

			// prvni v rade //
			} else if ( $export_number == 1 ) {
				$xml_output = create_invoice( $unexported_export_order_id, 'to_xml_first' );

			// posledni v rade //
			} else if ( $export_number == $order_count ) {
				$xml_file = create_invoice( $unexported_export_order_id, 'to_xml_last', $xml_output );

			// vsechny mezi tim //
			} else {
				$xml_output = create_invoice( $unexported_export_order_id, 'to_xml', $xml_output );
			}

			// count orders added to xml //
			if ( $xml_file == NULL || $xml_output == NULL ) {
				$order_count--;
			} else {
				$xml_count++;
				$export_number++;
			}
			
		}
	}

	if ( $xml_count == 0 ) {
		$xml_file = 'zero';
	}

	// vratit odkaz //
	echo $xml_file;
	wp_die();
	
}
add_action('wp_ajax_pohoda_export_xml_file', 'pohoda_export_xml_file');



//// vymazat protokol chyb ////

function erase_action_log() {
	
	file_put_contents( TICKETAPOH_PATH . 'log/export.log', '' );
	tckpoh_logs( __( 'Action log erased', 'tckpoh' ) );
	wp_die();
	
}
add_action('wp_ajax_erase_action_log', 'erase_action_log');



//// nahrat frontu pro export ////

function load_export_queue() {
	
	$export_queue = get_option('wc_settings_pohoda_export_failed_order_exports');
	$export_array = explode( ',', $export_queue );
	$queue_array = array();

	if ( $export_queue ) {

		foreach ( $export_array as $key => $value ) {
			$order_id = preg_replace("/[^0-9]/", "", $value );
			$order_date = get_the_date( 'Y-m-d', $order_id );
			$queue_array[$value] = $order_date;
		}

		asort( $queue_array );

		foreach ( $queue_array as $key => $value ) {
			$order_idd = preg_replace("/[^0-9]/", "", $key );
			$order = wc_get_order( $order_idd );
			$queue_array[$key] = array(
				'date' => date( 'd.m.Y', strtotime( $value ) ),
				'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'inv' => get_post_meta( $order_idd, 'pohoda_invoice_number', true )
			);
		}
	}

	echo json_encode( $queue_array );
	wp_die();
	
}
add_action('wp_ajax_load_export_queue', 'load_export_queue');