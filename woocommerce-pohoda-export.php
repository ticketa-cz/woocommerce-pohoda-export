<?php

/**
* Plugin Name: Woocommerce Pohoda Export
* Plugin URI: https://www.ticketa.cz/woocommerce-pohoda-export-plugin/
* Description: Export faktur z Woocommerce do účetního systému Pohoda
* Version: 2.2.5
* Author: Ticketa
* Author URI: https://www.ticketa.cz/
* Developer: Ticketa
* Developer URI: https://www.ticketa.cz/
* Text Domain: tckpoh
* Domain Path: /languages
*
* WC requires at least: 3.4
* WC tested up to: 5.0
*/

if ( !defined( 'ABSPATH' ) ) {
    exit;
}


//// freemius check ////

if ( ! function_exists( 'tckpoh_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tckpoh_fs() {
        global $tckpoh_fs;

        if ( ! isset( $tckpoh_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $tckpoh_fs = fs_dynamic_init( [
                'id'                  => '7714',
                'slug'                => 'woocommerce-pohoda-export',
                'type'                => 'plugin',
                'public_key'          => 'pk_4fc0c4f03f5ed2d90cdc63a268217',
                'is_premium'          => true,
                'is_premium_only'     => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'is_org_compliant'    => false,
                'menu'                => [
                    'slug'           => 'woocommerce-pohoda-export',
                    'first-path'     => 'admin.php?page=wc-settings&tab=pohoda_export_tab',
                    'contact'        => false,
                    'support'        => false,
                ],
                // Set the SDK to work in a sandbox mode (for development & testing).
                // IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
                'secret_key'          => 'sk_i0g6;6^?Ki[JK&sw~-ccqy0CA:A!k',
            ] );
        }

        return $tckpoh_fs;
    }

    // Init Freemius.
    tckpoh_fs();
    // Signal that SDK was initiated.
    do_action( 'tckpoh_fs_loaded' );

    function tckpoh_fs_settings_url() {
        return admin_url( 'admin.php?page=wc-settings&tab=pohoda_export_tab' );
    }

    tckpoh_fs()->add_filter('connect_url', 'tckpoh_fs_settings_url');
    tckpoh_fs()->add_filter('after_skip_url', 'tckpoh_fs_settings_url');
    tckpoh_fs()->add_filter('after_connect_url', 'tckpoh_fs_settings_url');
    tckpoh_fs()->add_filter('after_pending_connect_url', 'tckpoh_fs_settings_url');
}


//// defines ////

define('TICKETAPOH_URL', plugin_dir_url( __FILE__ ) );
define('TICKETAPOH_PATH', plugin_dir_path( __FILE__ ) );


// check if WooCommerce is active //
 
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    initiate_tckpoh();
}


// initiate //

function initiate_tckpoh() {
	
	//// includes ////
	include_once ( TICKETAPOH_PATH . 'includes/setup-tab.php' );
	include_once ( TICKETAPOH_PATH . 'includes/setup-ajax-functions.php' );
	include_once ( TICKETAPOH_PATH . 'includes/mserver-call.php' );
	include_once ( TICKETAPOH_PATH . 'includes/create-invoice.php' );
	include_once ( TICKETAPOH_PATH . 'includes/submit-error.php' );
	include_once ( TICKETAPOH_PATH . 'includes/scheduled-actions.php' );
		
	// load scripts //
	add_action('admin_enqueue_scripts', 'tckpoh_styles_and_scripts');
	
	// load language //
	add_action('plugins_loaded', 'tckpoh_localisation');

}


// script and styles //

function tckpoh_styles_and_scripts($hook) {
		
	// TODO: Verify if additional nonce validation is needed for admin settings page
	$page = sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) );
	$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ?? '' ) );
	
	if ( is_admin() && $page === 'wc-settings' && $tab === 'pohoda_export_tab' ) {
		
		$lastmodtimejs = filemtime(TICKETAPOH_PATH . 'assets/setup-tab.js');
		wp_enqueue_script('tckpoh_js', TICKETAPOH_URL . 'assets/setup-tab.js', [ 'jquery', 'wp-util' ], $lastmodtimejs);
		wp_enqueue_script('tckpoh_serialize', TICKETAPOH_URL . 'assets/serializejson.js', [ 'jquery', 'wp-util' ], $lastmodtimejs);
		wp_enqueue_script('tckpoh_switch', TICKETAPOH_URL . 'assets/lc_switch.js', [ 'jquery', 'wp-util' ], $lastmodtimejs);
		
		wp_localize_script('tckpoh_js', 'tckpoh_lang', [
			'choose_one' => __( 'Choose one...', 'tckpoh' ),
			'mserver_connect' => __( 'Connect and load accountings', 'tckpoh' ),
			'save_options' => __( 'Save settings', 'tckpoh' ),
			'saving_options' => __( 'Saving your settings', 'tckpoh' ),
			'numbering_example' => __( 'Invoice number example: ', 'tckpoh' ),
			'could_not_save' => __( 'Unable to save options...', 'tckpoh' ),
			'billing_info_note' => __( 'You dont need to fill this info, it will be obtained from Pohoda. Fill it only if you plan on using the plugin without mServer connection.', 'tckpoh' ),
			'reset_numbering' => __( 'Note, that if the system finds a duplicate invoice number associated with a woocommerce order, it will delete it and use it with the new order. So beware not to use same numbering twice or not to start with the same number.', 'tckpoh' ),
			'reset_core_number' => __( 'Reset number', 'tckpoh' ),
			'reset_queue' => __( 'Reset export queue', 'tckpoh' ),
			'reset_core_number_erase' => __( 'Erase all invoice numbers', 'tckpoh' ),
			'this_will_reset_core_number' => __( 'Warning! This will reset the number of the last invoice. Continue only if you are changing the invoice numbering. Otherwise it will create duplicate invoice numbers and these invoices will not be imported.', 'tckpoh' ),
			'this_will_reset_queue' => __( 'Warning! This will erase all orders saved in the export queue. You can add them again later by checking last year orders.', 'tckpoh' ),
			'this_will_delete_invoice_numbers' => __( 'Warning!! This will erase all invoice numbers from all orders of this year! If you would check for unexported orders later, all those orders would then appear in the export waiting list.', 'tckpoh' ),
			'reseting_number' => __( 'Reseting', 'tckpoh' ),
			'queue_was_erased' => __( 'The queue was erased.', 'tckpoh' ),
			'info_payments' => __( 'Note, you have to associate each payment type with a payment gateway enabled in woocommerce shown within the dropdowns. Unassociated payment types will be overriden by the default in Pohoda.', 'tckpoh' ),
			'mserver_connect_failed' => __( 'Unable to connect to mserver. Check your login info and make sure mServer is running.', 'tckpoh' ),
			'loader_connecting' => __( 'Connecting to mServer', 'tckpoh' ),
			'loader_loading' => __( 'Loading Pohoda defaults', 'tckpoh' ),
			'accounting_key' => get_option('wc_settings_pohoda_export_accounting_key'),
			'choose_accounting' => __( 'You have to choose an accounting.', 'tckpoh' ),
			'account' => get_option('wc_settings_pohoda_export_billing_account_id'),
			'numbering_type' => get_option('wc_settings_pohoda_export_invoice_numbering_type'),
			'prefix' => get_option('wc_settings_pohoda_export_invoice_prefix_type'),
			'pohoda_prefix' => get_option('wc_settings_pohoda_export_invoice_prefix_pohoda'),
			'export_orders' => get_option('wc_settings_pohoda_export_invoice_export_orders'),
			'export_xml' => __( 'Export XML file', 'tckpoh' ),
			'this_will_export_xml' => __( 'This will export the waiting list into an XML file which you can download. You have to empty the waiting list later.', 'tckpoh' ),
			'check_this_year' => __( 'Find unexported orders', 'tckpoh' ),
			'check_this_year_ok' => __( ' orders are now in waiting list for export. Will be exported as soon as you turn the plugin ON or save the settings.', 'tckpoh' ),
			'check_this_year_error' => __( 'Did not find any unexported orders or an error happened.', 'tckpoh' ),
			'will_check_this_year' => __( 'This action will find all orders made from the beginning of this year and add it to the waiting list for export to Pohoda. Only orders with Processing or Completed status will be added. \n If you want to specify a date range, fill the input with dates exactly in this format YYYY-MM-DD==YYYY-MM-DD', 'tckpoh' ),
			'checking_orders' => __( 'Checking unexported orders', 'tckpoh' ),
			'last_invoice_number' => __( 'Last invoice number: ', 'tckpoh' ) . get_option('wc_settings_pohoda_export_invoice_number_now'),
			'centre' => get_option('wc_settings_pohoda_export_invoice_center_select'),
			'activity' => get_option('wc_settings_pohoda_export_invoice_activity_select'),
			'classification_type' => get_option('wc_settings_pohoda_export_invoice_classification_type'),
			'predkontace_line_item' => get_option('wc_settings_pohoda_export_invoice_predkontace_line_item'),
			'predkontace_shipping' => get_option('wc_settings_pohoda_export_invoice_predkontace_shipping'),
			'predkontace_fee' => get_option('wc_settings_pohoda_export_invoice_predkontace_fee'),
			'export_type' => get_option('wc_settings_pohoda_export_invoice_export_type'),
			'export_status' => get_option('wc_settings_pohoda_export_invoice_export_status'),
			'center_type' => get_option('wc_settings_pohoda_export_invoice_center_type'),
			'activity_type' => get_option('wc_settings_pohoda_export_invoice_activity_type'),
			'specific' => get_option('wc_settings_pohoda_export_invoice_data_specific'),
			'headingtext' => get_option('wc_settings_pohoda_export_invoice_text'),
			'billing_company' => get_option('blogname'),
			'billing_address_street' => get_option('woocommerce_store_address'),
			'billing_address_street_number' => get_option('woocommerce_store_address_2'),
			'billing_address_city' => get_option('woocommerce_store_city'),
			'billing_address_code' => get_option('woocommerce_store_postcode'),
			'billing_email' => get_option('admin_email'),
			'payment_card' => get_option('wc_settings_pohoda_export_payment_methods_card_text'),
			'payment_cash' => get_option('wc_settings_pohoda_export_payment_methods_cash_text'),
			'payment_bacs' => get_option('wc_settings_pohoda_export_payment_methods_transfer_text'),
			'payment_cod' => get_option('wc_settings_pohoda_export_payment_methods_cod_text'),
			'payment_special' => get_option('wc_settings_pohoda_export_payment_methods_special_text'),
			'enable_pdf' => get_option('wc_settings_pohoda_export_pdf_enable'),
			'enable_pdf_emails' => get_option('wc_settings_pohoda_export_pdf_emails'),
			'enable_pdf_qrcode' => get_option('wc_settings_pohoda_export_pdf_qrcode'),
			'action_log_url' => TICKETAPOH_URL . 'log/export.log',
			'plugin_switch' => get_option('wc_settings_pohoda_export_switch'),
			'plugin_switch_note' => __( 'Plugin switch', 'tckpoh' ),
			'switching_plugin' => __( 'Switching the plugin...', 'tckpoh' ),
			'send_log_to_support' => __( 'Send error log to support', 'tckpoh' ),
			'erase_action_log' => __( 'Erase action log', 'tckpoh' ),
			'reload_action_log' => __( 'Reload action log', 'tckpoh' ),
			'erasing_action_log' => __( 'Erasing action log', 'tckpoh' ),
			'sending_log_to_support' => __( 'Sending error log to support...', 'tckpoh' ),
			'export_queue' => __( 'Export queue', 'tckpoh' ),
			'could_not_send' => __( 'Could not send error log because of an error.', 'tckpoh' ),
			'could_not_switch' => __( 'Could not switch the plugin because of an error.', 'tckpoh' ),
			'billing_website' => site_url(),
			'exporting_xml' => __( 'Creating the XML file.', 'tckpoh' ),
			'xml_could_not_be_created' => __( 'The XML file could not be created because of an error.', 'tckpoh' ),
			'xml_zero_added' => __( 'There were no orders or invoices to add to the XML file.', 'tckpoh' ),
			'logo_upload' => __( 'Choose', 'tckpoh' ),
			'logo_upload_url' => get_option('wc_settings_pohoda_export_pdf_logo'),
			'vybrataoriznout' => __( 'Choose and crop', 'tckpoh' ),
			'choose_currency' => __( 'Filter by currency: enter the currency international code. Or leave blank to search all currencies.', 'tckpoh' ),
			'add_all_orders' => __( 'Add all to export', 'tckpoh' ),
			'admin_url' => admin_url(),
		]);
		
		$lastmodtimecss = filemtime(TICKETAPOH_PATH . 'assets/setup-tab.css');
		wp_enqueue_style('tckpoh_css', TICKETAPOH_URL . 'assets/setup-tab.css', [], $lastmodtimecss);
		
	}
	
}


//// jazyk ////
	
function tckpoh_localisation() {
	$plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
	load_plugin_textdomain( 'tckpoh', false, $plugin_rel_path );
}


//// check woo version ////

/**
 * Check if WooCommerce version meets minimum requirements
 * 
 * @return bool True if WooCommerce version is 4.0 or higher
 * @deprecated This function checks for very old WooCommerce version - consider updating minimum requirement
 */
function woo_version_check(): bool {
	$version = '4.0';
	if ( class_exists( 'WooCommerce' ) ) {
		// TODO: Consider using WC()->version instead of global $woocommerce for modern WC
		global $woocommerce;
		if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
			return true;
		}
	}
	return false;
}



/// export logging ////

/**
 * Log messages to the export log file with timestamp
 * 
 * @param string|array $message Message to log - arrays will be JSON encoded
 * @return void
 */
function tckpoh_logs( $message ): void {

    if( is_array( $message ) ) { 
        $message = json_encode( $message ); 
    }

	// Use WordPress timezone setting instead of hardcoded Prague timezone
	$dt = new DateTime();
	$timezone_string = get_option('timezone_string');
	if ( $timezone_string ) {
		$dt->setTimezone(new DateTimeZone($timezone_string));
	} else {
		// Fallback to Prague timezone if WordPress timezone not set
		$dt->setTimezone(new DateTimeZone('Europe/Prague'));
	}

	$log_file_path = TICKETAPOH_PATH . "log/export.log";
	
	// Ensure log directory exists
	$log_dir = dirname( $log_file_path );
	if ( ! is_dir( $log_dir ) ) {
		wp_mkdir_p( $log_dir );
	}

    $logfile = fopen( $log_file_path, "a" );
    if ( $logfile ) {
        fwrite( $logfile, "\n" . $dt->format('d.m Y h:i:s') . " :: " . $message ); 
        fclose( $logfile );
    }
}



//// add pdf export to order table ////

function tckpoh_shop_order_column( $columns ) {

	$enable_pdf = get_option('wc_settings_pohoda_export_pdf_enable');
	if ( $enable_pdf == 'yes' ) {

		$reordered_columns = [];

		// Inserting columns to a specific location
		foreach( $columns as $key => $column){
			$reordered_columns[$key] = $column;
			if( $key ==  'order_total' ){
				// Inserting after "Status" column
				$reordered_columns['tckpoh_pdf'] = __( 'PDF invoice','tckpoh');
			}
		}
		return $reordered_columns;

	} else {
		return $columns;
	}
}
add_filter( 'manage_edit-shop_order_columns', 'tckpoh_shop_order_column', 20 );

function custom_orders_list_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'tckpoh_pdf' :
            
            $pdf_sent = get_post_meta( $post_id, 'tckpoh_pdf_sent', true );
			if ( $pdf_sent == 'yes' ) { $pdf_sent_style = ' style="color: #8cd859;"'; } else { $pdf_sent_style = ''; }

			$pdf_create_link = admin_url('admin.php?page=pohoda-export-pdf&order_id=').$post_id;

            echo '<a class="tckpoh_export_pdf" ' . $pdf_sent_style. ' href="'. $pdf_create_link .'" target="_blank"><i class="dashicons-before dashicons-pdf" style="font-size: 18px;"></i></a>';

        break;
    }
}
add_action( 'manage_shop_order_posts_custom_column' , 'custom_orders_list_column_content', 20, 2 );



//// attach pdf to order email ////
 
function tckpoh_attach_pdf_to_emails( $attachments, $email_id, $order, $email ) {

	// stop if not enabled // or not an order email // or plugin off //

	$emails_enabled = get_option('wc_settings_pohoda_export_pdf_emails');
	$plugin_switch = get_option('wc_settings_pohoda_export_switch');
	if ( ! is_a( $order, 'WC_Order' ) || ! isset( $email_id ) || $emails_enabled != 'yes' || $plugin_switch != '1' ) {
        return $attachments;
    }

	// get settings //

	$order_id = $order->get_id();
	$payment_method = $order->get_payment_method();
	$send_if_banktransfer = get_option('wc_settings_pohoda_export_pdf_email_banktransfer');
	$status_set = get_option('wc_settings_pohoda_export_pdf_status');

	switch ( $status_set ) {
		case 'wc-processing':	$automatic_status = 'customer_processing_order';	break;
		case 'wc-on-hold':		$automatic_status = 'customer_on_hold_order';		break;
		case 'wc-completed':	$automatic_status = 'customer_completed_order';		break;
	}

	// if new bank transfer order // or customer invoice // or automatic sending at status //

    if ( $email_id == 'new_order' && $send_if_banktransfer == 'yes' && $payment_method == 'bacs' || $email_id == 'customer_invoice' || $email_id == $automatic_status ) {

		$invoice_url = create_invoice( $order_id, 'pdf_to_email', null, 'pdf' );
        $upload_dir = wp_upload_dir();
        $attachments[] = $invoice_url;
		update_post_meta( $order_id, 'tckpoh_pdf_sent', 'yes' );

    }
    return $attachments;

}
add_filter( 'woocommerce_email_attachments', 'tckpoh_attach_pdf_to_emails', 10, 4 );



//// create pdf from order list ////

function tckpoh_create_pdf_invoice() {

	// TODO: Add proper nonce validation for security
	// This function creates PDF invoices from order ID - ensure proper authorization
	$order_id = isset($_GET['order_id']) ? (int) sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : 0;
	
	if ( ! $order_id ) {
		wp_die( esc_html__( 'Invalid order ID', 'tckpoh' ) );
	}
	
	create_invoice( $order_id, 'pdf_to_screen', null, 'pdf' );
}

//// create xml for testing purposes ////

function tckpoh_create_xml_invoice() {

	// TODO: Add proper nonce validation for security
	// This function creates XML invoices for testing - ensure proper authorization
	$order_id = isset($_GET['order_id']) ? (int) sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : 0;
	
	if ( ! $order_id ) {
		wp_die( esc_html__( 'Invalid order ID', 'tckpoh' ) );
	}
	
	$xml = create_invoice( $order_id, 'to_xml_preview', null, 'invoice' );
	// TODO: Consider if XML output needs to be displayed or handled differently
	//echo $xml->flush();
}


//// save currency rate at new order ////
/// add_action( 'woocommerce_new_order', 'add_order_currency_info' );



//// add menu item ////

function tckpoh_admin_menu() { 

    add_menu_page( __( 'Pohoda Export', 'tckpoh' ), __( 'Pohoda Export', 'tckpoh' ), 'edit_posts', 'woocommerce-pohoda-export', 'tckpoh_plugin_page', 'dashicons-carrot', 67 );
	add_submenu_page( null, __( 'Pohoda Export PDF', 'tckpoh' ), __( 'Pohoda Export PDF', 'tckpoh' ), 'edit_posts', 'pohoda-export-pdf', 'tckpoh_create_pdf_invoice');
	add_submenu_page( null, __( 'Pohoda Export XML', 'tckpoh' ), __( 'Pohoda Export XML', 'tckpoh' ), 'edit_posts', 'pohoda-export-xml', 'tckpoh_create_xml_invoice');

}
add_action( 'admin_menu', 'tckpoh_admin_menu' );

function tckpoh_plugin_page() {

	// redirect to settings tab //
	
	header("Location: " . admin_url('admin.php?page=wc-settings&tab=pohoda_export_tab') );
	exit();	
}


//// freemius messages ////

function tckpoh_fs_custom_connect_message_on_update( $message, $user_first_name, $plugin_title, $user_login, $site_link, $freemius_link ) {
	return sprintf(
		__( 'Hey %1$s' ) . ',<br>' .
		__( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'tckpoh' ),
		$user_first_name,
		'<b>' . $plugin_title . '</b>',
		'<b>' . $user_login . '</b>',
		$site_link,
		$freemius_link
	);
}
function tckpoh_fs_custom_connect_message( $message, $user_first_name, $plugin_title, $user_login, $site_link, $freemius_link ) {
	return sprintf(
		__( 'Hey %1$s' ) . ',<br>' .
		__( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'tckpoh' ),
		$user_first_name,
		'<b>' . $plugin_title . '</b>',
		'<b>' . $user_login . '</b>',
		$site_link,
		$freemius_link
	);
}
tckpoh_fs()->add_filter('connect_message_on_update', 'tckpoh_fs_custom_connect_message_on_update', 10, 6);
tckpoh_fs()->add_filter('connect_message', 'tckpoh_fs_custom_connect_message', 10, 6);



//// plugin update checker ////

function tckpoh_upgrader_process_complete( $upgrader_object, $options ) {
    $tckpoh_updated = false;

    if ( isset( $options['plugins'] ) && is_array( $options['plugins'] ) ) {
        foreach ( $options['plugins'] as $index => $plugin ) {
            if ( 'woocommerce-pohoda-export/woocommerce-pohoda-export.php' === $plugin ) {
                $tckpoh_updated = true;
                break;
            }
        }
    }

    if ( ! $tckpoh_updated ) {
        return;
    }
	$tckpoh_data = get_plugin_data( 'woocommerce-pohoda-export/woocommerce-pohoda-export.php' );

	tckpoh_logs( __( 'WooCommerce Pohoda Export updated to version ', 'tckpoh' ) . $tckpoh_data['Version'] );
    
}
add_action( 'upgrader_process_complete', 'tckpoh_upgrader_process_complete', 10, 2 );


//// remove wp-emojis that freeze the page ////

function disable_emojis_admin() {
	
	// TODO: Consider if this is still necessary for modern WordPress versions
	$page = sanitize_text_field( wp_unslash( $_GET['page'] ?? '' ) );
	$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ?? '' ) );
	
	if ( is_admin() && $page === 'wc-settings' && $tab === 'pohoda_export_tab' ) {

         remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
         remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}
}
add_action( 'admin_init', 'disable_emojis_admin' );


?>