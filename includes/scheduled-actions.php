<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**

 as_schedule_recurring_action( $timestamp, $interval_in_seconds, $hook, $args, $group );
 as_schedule_cron_action( $timestamp, $schedule, $hook, $args, $group );
 as_unschedule_all_actions( $hook, $args, $group );
 
	MINUTE_IN_SECONDS
	HOUR_IN_SECONDS
	DAY_IN_SECONDS
	WEEK_IN_SECONDS
	MONTH_IN_SECONDS
	YEAR_IN_SECONDS

	 *   *    *    *    *    *    *
	 *   ┬    ┬    ┬    ┬    ┬    ┬
	 *   |    |    |    |    |    |
	 *   |    |    |    |    |    + year [optional]
	 *   |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
	 *   |    |    |    +---------- month (1 - 12)
	 *   |    |    +--------------- day of month (1 - 31)
	 *   |    +-------------------- hour (0 - 23)
	 *   +------------------------- min (0 - 59)
	 
*/


//// add status actions ////

add_action( 'woocommerce_order_status_changed', 'create_invoice_or_add_to_unexported', 10, 3 );
add_action( 'woocommerce_checkout_order_processed', 'tckpoh_create_order', 10, 1 ); 


//// turn the actions ON ////

function turn_actions_on() {
	
	tckpoh_logs( __( 'Turned actions on.', 'tckpoh' ) );
		
	// remove previous actions //
	
	as_unschedule_all_actions( 'tckpoh_check_unexported_orders' );
	
	// turn on the export frequency //
	
	$frequency = get_option('wc_settings_pohoda_export_invoice_export_type');
	
	if ( $frequency == 'order' || $frequency == 'order_only' ) {
	
		// turn on the export checking //
		as_schedule_recurring_action( strtotime( 'now' ), HOUR_IN_SECONDS, 'tckpoh_check_unexported_orders' );
		
	} else {
						
		$frequency_time = get_option('wc_settings_pohoda_export_invoice_export_time');
		$time_array = explode(':', $frequency_time);
		$hour = intval($time_array[0] - 1);
		if ($hour < 0) { $hour = 23; }
		$minute = $time_array[1];
		
		switch($frequency) {
			
			case 'daily':
				$cron_string = $minute.' '.$hour.' * * *';
			break;
			case 'weekly':
				$day_in_week = get_option('wc_settings_pohoda_export_invoice_export_day_in_week');
				$cron_string = $minute.' '.$hour.' * '.$day_in_week.' *';
			break;
			case 'monthly':
				$day_in_month = get_option('wc_settings_pohoda_export_invoice_export_day_in_month');
				$cron_string = $minute.' '.$hour.' '.$day_in_month.' * *';
			break;
			
		}
		
		// turn on the frequency //	
		as_schedule_cron_action( strtotime( 'now' ), $cron_string, 'tckpoh_check_unexported_orders' );
		
	}
	
}

//// set the actions OFF ////

function turn_actions_off() {
	
	tckpoh_logs( __( 'Turned actions off.', 'tckpoh' ) );
	
	as_unschedule_all_actions( 'tckpoh_check_unexported_orders' );
	
}

function create_invoice_or_add_to_unexported( $order_id, $old_status, $new_status ) {
	
	//// check if plugin activated ////
		
	$plugin_switch = get_option('wc_settings_pohoda_export_switch');
	
	if ( $plugin_switch != '1' ) {
		return;
	}
	
	//// check if this is the right status change ////
	
	$status_set = str_replace("wc-", "", get_option('wc_settings_pohoda_export_invoice_export_status'));
	
	if( $new_status != $status_set ) {
		return;
	}
	
	//// do the action ////
		
	$frequency = get_option('wc_settings_pohoda_export_invoice_export_type');

	if ( $frequency == 'manual' ) {
		return;
	}
	
	if ( $frequency == 'order' ) {
		
		create_invoice( $order_id );
		
	} else {
		
		add_to_unexported( $order_id );
		
	}
		
}

//// create order export ////

function tckpoh_create_order( $order_id ) {
	
	//// check if plugin activated ////
		
	$plugin_switch = get_option('wc_settings_pohoda_export_switch');
	
	if ( $plugin_switch != '1' ) {
		return;
	}

	// check if orders should be exported //

	$export_orders = get_option('wc_settings_pohoda_export_invoice_export_orders');
	
	if ( $export_orders == 'yes' ) {

		create_invoice( $order_id, 'to_mserver', NULL, 'order' );

	} else {
		return;
	}		
		
}


//// check unexported orders ////

function check_unexported_orders() {
	
	tckpoh_logs( __( 'Checked unexported orders.', 'tckpoh' ) );
	
	$failed_order_exports = get_option('wc_settings_pohoda_export_failed_order_exports');
	$failed_order_array = explode(',', $failed_order_exports);
	
	if ($failed_order_array) {
		foreach( $failed_order_array as $failed_export_order_id ) {
			create_invoice( $failed_export_order_id );
		}
	}
	
	// reschedule if woo version lower than 4 //
	
	$frequency = get_option('wc_settings_pohoda_export_invoice_export_type');
	if( !woo_version_check() && $frequency != 'order' ) {
		turn_actions_on();
	}
	
	
}
add_action( 'tckpoh_check_unexported_orders', 'check_unexported_orders' );