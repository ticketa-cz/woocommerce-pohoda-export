<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// submit an error //

function submit_export_error( $order_id, $response_state, $response, $document_type = 'invoice' ) {
	
	// add order to unexported waiting list //
	add_to_unexported( $order_id, $document_type );
	
	if ( $response_state == 'not-connected' ) {
		
		// there was an error connecting //
		tckpoh_logs( __( 'Pohoda export error, could not connect to mserver. Note: ', 'tckpoh' ) . $response );
		
	} else {
		
		// the connection was ok //
		
		$response_pack = simplexml_load_string($response);
		$ns = $response_pack->getDocNamespaces(); 
		$remove_from_unexported = 0;
		
		if ( $response_pack && $response_pack->attributes()->state != 'error' ) {
			
			$response_pack_item = $response_pack->children($ns['rsp'])->responsePackItem;
				
			if ( $response_pack_item && $response_pack_item->attributes()->state != 'error' ) {
				
				$invoice_response = $response_pack_item->children($ns['inv'])->invoiceResponse;
				if ( $document_type == 'order' ) {
					$invoice_response = $response_pack_item->children($ns['ord'])->orderResponse;
				}				
				$invoice_import_details = $invoice_response->children($ns['rdc'])->importDetails;
						
				foreach ( $invoice_import_details->children($ns['rdc'])->detail as $import_info ) {
					
					$state = $import_info->children($ns['rdc'])->state;
					$errno = $import_info->children($ns['rdc'])->errno;
					$note = $import_info->children($ns['rdc'])->note;
					$xpath = $import_info->children($ns['rdc'])->XPath;
					$value_req = $import_info->children($ns['rdc'])->valueRequested;
					$value_prod = $import_info->children($ns['rdc'])->valueProduced;
					$error_log = 'Pohoda export ' . $state . ' #'. $errno.' - ' . $note . ' :: path >> ' . $xpath;

					if ($value_req) {
						$error_log .= __( ' | value requested: ', 'tckpoh' ) . $value_req;
					}
					if ($value_prod) {
						$error_log .= __( ' -- value produced: ', 'tckpoh' ) . $value_prod;
					}
					
					if ( $state == 'error' ) {
						if ( $errno == '109' ) {
							
							$remove_from_unexported = 1;
			
						} else {
							
							//send_error_to_admin( $error_log, '' );
						}				
					}
					
					tckpoh_logs( $error_log );
							
				}
				
			} else {
				
				// validation error //
				$error_log_email = $response_pack_item->attributes()->note;
				tckpoh_logs( $error_log_email );
				//send_error_to_admin( $error_log_email, '' );
			}
			
		} else {
			
			// package error //
			$error_log_email = $response_pack->attributes()->note;
			tckpoh_logs( $error_log_email );
			//send_error_to_admin( $error_log_email, '' );
		}
		
		if ( $remove_from_unexported == 1 ) {
			
			// if the error was a duplicate invoice, then don't log it and remove from unexported //
			remove_from_unexported( $order_id, $document_type );
				
		}		
	}
}

//// send error to admin ////

function send_error_to_admin( $error_log, $recepient ) {

	if ( !isset($recepient) || $recepient == '' ) {
		$recepient = get_bloginfo('admin_email');
	}
	
	$attachment = array( TICKETAPOH_PATH . '/log/export.log' );
	$headers = array( 'Content-Type: text/html; charset=UTF-8','From: '.get_bloginfo('name').' <no-reply@'.$_SERVER['SERVER_NAME'].'>' );
	$email_sent = wp_mail( $recepient, 'Pohoda export error', $error_log, $headers, $attachment );

	return $email_sent;
	
}


///// add unexported order to waiting list ////

function add_to_unexported( $order_id, $document_type = 'invoice' ) {
	
	// create invoice date //

	create_invoice_date( $order_id );
	
	// create invoice number //
	
	create_invoice_number( $order_id );

	// if order, add OBJ //

	$frequency = get_option('wc_settings_pohoda_export_invoice_export_type');
	if ( $frequency == 'order_only' ) {
		$document_type = 'order';
	}

	if ( $document_type == 'order' ) {
		$order_id = 'OBJ' . $order_id;
	}
	
	// add to unexported //
			
	$unexported_orders = get_option('wc_settings_pohoda_export_failed_order_exports');
	$unexported_orders_array = explode(',', $unexported_orders);
	$unexported_orders_merged_array = array_unique( array_merge( (array)$unexported_orders_array, (array)$order_id ) );
	$unexported_orders_merged = implode(',', array_filter($unexported_orders_merged_array));
	$unexported_orders_updated = update_option('wc_settings_pohoda_export_failed_order_exports', $unexported_orders_merged, true);
	
	if ( is_wp_error( $unexported_orders_updated ) ) {
		tckpoh_logs( __( 'Pohoda export error, could not save order #', 'tckpoh' ) . $order_id . __( ' to unexported.', 'tckpoh' ) );
	} else {
		tckpoh_logs( __( 'Added to unexported', 'tckpoh' ) . ' - #' . $order_id );
	}
		
}

//// remove from unexported ////

function remove_from_unexported( $order_id, $document_type = 'invoice' ) {

	// if order, add OBJ //

	if ( $document_type == 'order' ) {
		$order_id = 'OBJ' . $order_id;
	}
			
	// remove invoice from unexported //
	$unexported_orders = get_option('wc_settings_pohoda_export_failed_order_exports');
	$unexported_orders_array = explode(',', $unexported_orders);
	$unexported_orders_unset = implode(',', array_diff( $unexported_orders_array, (array)$order_id ));
	$unexported_orders_updated = update_option('wc_settings_pohoda_export_failed_order_exports', $unexported_orders_unset, true);
	
	if ( is_wp_error( $unexported_orders_updated ) ) {
		tckpoh_logs( __( 'Pohoda export error, could not remove order #', 'tckpoh' ) . $order_id . __( ' from unexported.', 'tckpoh' ) );
	} else {
		tckpoh_logs( __( 'Removed from unexported', 'tckpoh' ) . ' - #' . $order_id );
	}
	
}