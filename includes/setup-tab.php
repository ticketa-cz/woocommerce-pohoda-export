<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WC_Pohoda_Export_Tab {
	
	// add the settings tab to woocommerce //

    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_pohoda_export_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_pohoda_export_tab', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_pohoda_export_tab', __CLASS__ . '::update_settings' );
    }

    public static function add_pohoda_export_settings_tab( $settings_tabs ) {
        $settings_tabs['pohoda_export_tab'] = __( 'Pohoda Export Settings', 'tckpoh' );
        return $settings_tabs;
    }        
    
	// save and load the option fields //
	
    public static function settings_tab() {
        woocommerce_admin_fields( self::get_pohoda_export_settings() );
    }

    public static function update_settings() {
        woocommerce_update_options( self::get_pohoda_export_settings() );
    }

	// create the option fields //
	
    public static function get_pohoda_export_settings() {
		
		// find payment methods //
		
		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		$payment_methods = array('not_specified' => '');
		
		if( $gateways ) {
			foreach( $gateways as $gateway_id => $gateway ) {
				if( $gateway->enabled == 'yes' ) {
					$payment_methods[$gateway_id] = $gateway->get_title();
				}
			}
		}

		// setup statuses for pdf emails //

		$all_statuses = wc_get_order_statuses();
		$limited_statuses = array();
		foreach ( $all_statuses as $status_key => $status ) {
			if ( in_array( $status_key, array( 'wc-processing', 'wc-on-hold', 'wc-completed' ) ) ) {
				$limited_statuses[$status_key] = $status;
			}
		}
		
		// option fields //
		
		$settings = array(
		
			// MSERVER //
			'mserver_setting' => array(
				'name'     => __( 'mServer setting', 'tckpoh' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_mserver_setting',
			),
			'mserver_address' => array(
				'name' => __( 'mServer address', 'tckpoh' ),
				'type' => 'text',
				'class' => 'obligatorytofill',
				'desc' => __( 'e.g. http://46.234.119.70:4444/xml', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_mserver_address'
			),
			'mserver_login' => array(
				'name' => __( 'mServer login name', 'tckpoh' ),
				'type' => 'text',
				'class' => 'obligatorytofill',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_mserver_login'
			),
			'mserver_password' => array(
				'name' => __( 'mServer password', 'tckpoh' ),
				'type' => 'password',
				'class' => 'obligatorytofill',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_mserver_password'
			),
			'mserver_accounting' => array(
				'name' => __( 'Choose accounting', 'tckpoh' ),
				'type' => 'select',
				'class' => 'obligatorytofill',
				'options' => array(
					'' => __( 'Choose one...', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_mserver_accounting',
			),
			'accounting_key' => array(
				'name' => __( 'Accounting key', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_accounting_key'
			),
			'mserver_section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_pohoda_export_mserver_section_end'
			),
			
			// BILLING INFO //
			'billing_info' => array(
				'name'     => __( 'Billing info', 'tckpoh' ),
				'type'     => 'title',
				'id'       => 'wc_settings_pohoda_export_billing_info'
			),
			'billing_company' => array(
				'name' => __( 'Company name', 'tckpoh' ),
				'type' => 'text',
				'id'   => 'wc_settings_pohoda_export_billing_company',
			),
			'billing_company_title' => array(
				'name' => __( 'Company title', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_company_title'
			),
			'billing_surname' => array(
				'name' => __( 'First name', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_surname'
			),
			'billing_name' => array(
				'name' => __( 'Last name', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_name'
			),
			'billing_ico' => array(
				'name' => __( 'ICO', 'tckpoh' ),
				'type' => 'text',
				'id'   => 'wc_settings_pohoda_export_billing_ico',
			),
			'billing_dic' => array(
				'name' => __( 'DIC', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'class' => 'separated_field',
				'id'   => 'wc_settings_pohoda_export_billing_dic'
			),
			'billing_address_street' => array(
				'name' => __( 'Address street', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_address_street'
			),
			'billing_address_street_number' => array(
				'name' => __( 'Address street number', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_address_street_number'
			),
			'billing_address_city' => array(
				'name' => __( 'Address city', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_address_city'
			),
			'billing_address_code' => array(
				'name' => __( 'Address post code', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_address_code'
			),
			'billing_phone' => array(
				'name' => __( 'Phone', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_phone'
			),
			'billing_email' => array(
				'name' => __( 'Email', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_email'
			),
			'billing_website' => array(
				'name' => __( 'Website', 'tckpoh' ),
				'type' => 'text',
				'class' => 'separated_field',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_website'
			),
			'billing_account' => array(
				'name' => __( 'Choose account', 'tckpoh' ),
				'type' => 'select',
				'options' => array(
					'' => __( 'Choose one...', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_billing_account',
			),
			'billing_account_number' => array(
				'name' => __( 'Account number', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_account_number'
			),
			'billing_account_bank_id' => array(
				'name' => __( 'Bank code', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_account_bank_id'
			),
			'billing_account_id' => array(
				'name' => __( 'Account ID', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_billing_account_id'
			),
			'billing_info_section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_pohoda_export_billing_info_section_end'
			),
			
			// INVOICE NUMBERS //
			'invoice_numbering' => array(
				'name'     => __( 'Invoice numbering', 'tckpoh' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_invoice_numbering',
			),
			'invoice_numbering_type' => array(
				'name' => __( 'Use numbering from a different plugin?', 'tckpoh' ),
				'type' => 'radio',
				'default' => 'no',
				'options' => array(
					'yes' => __( 'Yes', 'tckpoh' ),
					'no' => __( 'No', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_numbering_type',
			),
			'invoice_plugin_numbering' => array(
				'name' => __( 'Custom field value', 'tckpoh' ),
				'type' => 'text',
				'class' => 'befilled',
				'desc' => __( 'Enter the custom field name under which the invoice number is saved by the plugin.', 'tckpoh' ),
				'desc_tip' =>  true,
				'id'   => 'wc_settings_pohoda_export_invoice_plugin_numbering',
			),
			'invoice_prefix_type' => array(
				'name' => __( 'Type of prefix', 'tckpoh' ),
				'type' => 'select',
				'default' => 'date',
				'options' => array(
					'pohoda' => __( 'Pohoda prefix', 'tckpoh' ),
					'date' => __( 'Custom date', 'tckpoh' ),
					'text' => __( 'Own text', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_prefix_type',
			),
			'invoice_prefix_pohoda' => array(
				'name' => __( 'Pohoda predefined prefix', 'tckpoh' ),
				'type' => 'select',
				'options' => array(
					'' => __( 'Choose one...', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_prefix_pohoda',
			),
			'invoice_prefix' => array(
				'name' => __( 'Invoice prefix', 'tckpoh' ),
				'type' => 'text',
				'default' => 'YY',
				'class' => 'separated_field',
				'desc' => __( 'If you have selected "Custom date", set the format using Y, M and D symbols. For example YYMM, or YYYYMMDD...', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_prefix',
				'desc_tip' =>  true,
			),
			'invoice_number_count' => array(
				'name' => __( 'Number of digits of main numbering', 'tckpoh' ),
				'type' => 'select',
				'default' => '5',
				'options' => array('1' => '1','2' => '2','3' => '3','4' => '4','5' => '5','6' => '6','7' => '7','8' => '8','9' => '9'),
				'desc' => __( 'How many digits should the core number have?', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_number_count',
				'desc_tip' =>  true,
			),
			'invoice_start' => array(
				'name' => __( 'Main numbering starting at', 'tckpoh' ),
				'type' => 'number',
				'class' => 'separated_field',
				'default' => '1',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_number_start'
			), // kdyz se zmeni, je potreba zajistit, aby se neduplikovly //
			'invoice_suffix_type' => array(
				'name' => __( 'Type of suffix', 'tckpoh' ),
				'type' => 'select',
				'default' => 'none',
				'options' => array(
					'none' => __( 'None', 'tckpoh' ),
					'order' => __( 'Woocommerce Order number', 'tckpoh' ),
					'date' => __( 'Custom date', 'tckpoh' ),
					'text' => __( 'Own text', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_suffix_type',
			),
			'invoice_suffix' => array(
				'name' => __( 'Invoice suffix', 'tckpoh' ),
				'type' => 'text',
				'class' => 'befilled',
				'desc' => __( 'If you have selected "Custom date", set the format using Y, M and D symbols. For example YYMM, or YYYYMMDD...', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_suffix',
				'desc_tip' =>  true,
			),
			'invoice_section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_pohoda_export_invoice_section_end'
			),
			
			// INVOICE DATA //
			'invoice_data' => array(
				'name'     => __( 'Invoice data', 'tckpoh' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_invoice_data'
			),
			'invoice_data_vat_rate' => array(
				'name' => __( 'Rate of VAT', 'tckpoh' ),
				'type' => 'select',
				'class' => 'obligatorytofill',
				'default' => 'high',
				'options' => array(
					'high' => __( '21%', 'tckpoh' ),
					'low' => __( '15%', 'tckpoh' ),
					'third' => __( '10%', 'tckpoh' ),
					'none' => __( '0%', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_data_vat_rate',
			),
			'invoice_data_rounding' => array(
				'name' => __( 'Rounding', 'tckpoh' ),
				'type' => 'select',
				'default' => 'math2one',
				'options' => array(
					'none' => __( 'None', 'tckpoh' ),
					'math2one' => __( 'Math to one', 'tckpoh' ),
					'math2tenth' => __( 'Math to tenth', 'tckpoh' ),
					'math2half' => __( 'Math to half', 'tckpoh' ),
					'up2one' => __( 'Up to one', 'tckpoh' ),
					'up2tenth' => __( 'Up to tenth', 'tckpoh' ),
					'down2one' => __( 'Down to one', 'tckpoh' ),
					'down2tenth' => __( 'Down to tenth', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_data_rounding',
			),
			/*
			'invoice_foreign_currency' => array(
				'name' => __( 'CZK as main currency', 'tckpoh' ),
				'type' => 'radio',
				'default' => 'yes',
				'options' => array(
					'yes' => __( 'Yes', 'tckpoh' ),
					'no' => __( 'No', 'tckpoh' ),
				),
				'desc' => __( 'If using multiple currencies, should CZK be default and other currencies counted by rate?', 'tckpoh' ),
				'desc_tip' =>  true,
				'id'   => 'wc_settings_pohoda_export_invoice_foreign_currency',
			),*/
			'invoice_foreign_currency_converter_api_key' => array(
				'name' => __( 'Converter API key', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( 'If using different currency than CZK, you have to use converter API. Get your key here: https://rapidapi.com/fyhao/api/currency-exchange', 'tckpoh' ),
				'desc_tip' =>  true,
				'id'   => 'wc_settings_pohoda_export_converter_api_key',
			),			
			// classification type //
			'invoice_classification_type' => array(
				'name' => __( 'Classification type', 'tckpoh' ),
				'type' => 'select',
				'class' => 'separated_field',
				'options' => array(
					'' => __( 'Choose one...', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_classification_type',
			),
			'invoice_data_date_type' => array(
				'name' => __( 'Date type', 'tckpoh' ),
				'type' => 'select',
				'default' => 'created',
				'options' => array(
					'created' => __( 'Order date', 'tckpoh' ),
					'modified' => __( 'Order last modified date', 'tckpoh' ),
					'paid' => __( 'Order paid date', 'tckpoh' ),
					'completed' => __( 'Order completed date', 'tckpoh' ),
				),
				'desc' => __( 'If there is no order paid or completed date, order creation date will be used.', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_data_date_type',
				'desc_tip' =>  true,
			),
			'invoice_data_payment_due' => array(
				'name' => __( 'Payment due date from order date', 'tckpoh' ),
				'type' => 'select',
				'default' => '14',
				'options' => array('0' => '0','1' => '1','3' => '3','7' => '7','14' => '14','30' => '30','60' => '60','90' => '90'),
				'desc' => __( 'How many days is the payment due date from the order date?', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_data_payment_due',
				'desc_tip' =>  true,
			),
			'invoice_data_payment_supply' => array(
				'name' => __( 'Taxable supply date from order date', 'tckpoh' ),
				'type' => 'select',
				'class' => 'separated_field',
				'options' => array('0' => '0','1' => '1','3' => '3','7' => '7','14' => '14'),
				'desc' => __( 'How many days is the taxable supply date from the order date?', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_data_payment_supply',
				'desc_tip' =>  true,
			),
			// variabilni symbol //
			'invoice_data_variable' => array(
				'name' => __( 'Variable symbol', 'tckpoh' ),
				'type' => 'select',
				'default' => 'invoice_inner',
				'options' => array(
					'order' => __( 'Woocommerce Order number', 'tckpoh' ),
					'invoice' => __( 'Invoice number', 'tckpoh' ),
					'invoice_inner' => __( 'Invoice inner number', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_data_variable',
			),
			// specificky symbol //
			'invoice_data_specific' => array(
				'name' => __( 'Specific symbol', 'tckpoh' ),
				'type' => 'select',
				'default' => 'order',
				'options' => array(
					'order' => __( 'Woocommerce Order number', 'tckpoh' ),
					'invoice' => __( 'Invoice number', 'tckpoh' ),
					'invoice_inner' => __( 'Invoice inner number', 'tckpoh' ),
					'custom' => __( 'Custom', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_data_specific',
			),
			'invoice_data_specific_custom' => array(
				'name' => __( 'Custom specific symbol', 'tckpoh' ),
				'type' => 'text',
				'class' => 'separated_field',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_data_specific_custom'
			),
			// customer ico //
			'invoice_data_customer_ico' => array(
				'name' => __( 'Customer ICO field', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( 'Enter the custom field name under which the ICO number is saved.', 'tckpoh' ),
				'desc_tip' =>  true,
				'id'   => 'wc_settings_pohoda_export_invoice_data_customer_ico'
			),
			'invoice_data_customer_dic' => array(
				'name' => __( 'Customer DIC field', 'tckpoh' ),
				'type' => 'text',
				'class' => 'separated_field',
				'desc' => __( 'Enter the custom field name under which the DIC number is saved.', 'tckpoh' ),
				'desc_tip' =>  true,
				'id'   => 'wc_settings_pohoda_export_invoice_data_customer_dic'
			),
			// invoice text //
			'invoice_text' => array(
				'name' => __( 'Invoice heading text', 'tckpoh' ),
				'type' => 'select',
				'default' => 'custom',
				'options' => array(
					'custom' => __( 'Custom', 'tckpoh' ),
					'first_name' => __( 'First product name', 'tckpoh' ),
					'first_cat' => __( 'First product category name', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_text',
			),
			'invoice_text_custom' => array(
				'name' => __( 'Custom heading text', 'tckpoh' ),
				'type' => 'text',
				'class' => 'separated_field',
				'default' => __( 'We are billing you for goods...', 'tckpoh' ),
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_text_custom'
			),
			// center //
			'invoice_center_type' => array(
				'name' => __( 'Invoice center type', 'tckpoh' ),
				'type' => 'select',
				'options' => array(
					'pohoda' => __( 'Choose from Pohoda centers', 'tckpoh' ),
					//'custom_field' => __( 'Choose from WP custom fields', 'tckpoh' ),
					'custom' => __( 'Custom', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_center_type',
			),
			'invoice_center_select' => array(
				'name' => __( 'Center selection', 'tckpoh' ),
				'type' => 'select',
				'options' => array(
					'' => __( '', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_center_select',
			),
			'invoice_center_custom' => array(
				'name' => __( 'Custom center text', 'tckpoh' ),
				'type' => 'text',
				'class' => 'befilled',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_center_custom'
			),
			// activity //
			'invoice_activity_type' => array(
				'name' => __( 'Invoice activity type', 'tckpoh' ),
				'type' => 'select',
				'options' => array(
					'pohoda' => __( 'Choose from Pohoda activities', 'tckpoh' ),
					//'custom_field' => __( 'Choose from WP custom fields', 'tckpoh' ),
					'custom' => __( 'Custom', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_activity_type',
			),
			'invoice_activity_select' => array(
				'name' => __( 'Activity selection', 'tckpoh' ),
				'type' => 'select',
				'options' => array(
					'' => __( '', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_activity_select',
			),
			'invoice_activity_custom' => array(
				'name' => __( 'Custom activity text', 'tckpoh' ),
				'type' => 'text',
				'class' => 'befilled',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_activity_custom'
			),
			'invoice_data_section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_pohoda_export_invoice_data_section_end'
			),	
			
			// EXPORTS //
			'invoice_export' => array(
				'name'     => __( 'Export setting', 'tckpoh' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_invoice_exports',
			),
			'invoice_export_type' => array(
				'name' => __( 'Frequency of invoice export', 'tckpoh' ),
				'type' => 'select',
				'default' => 'order',
				'options' => array(
					'order' => __( 'After each order', 'tckpoh' ),
					'daily' => __( 'Daily', 'tckpoh' ),
					'weekly' => __( 'Weekly', 'tckpoh' ),
					'monthly' => __( 'Monthly', 'tckpoh' ),
					'manual' => __( 'Manually', 'tckpoh' ),
					'order_only' => __( 'Order only', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_export_type',
			),
			// order status select //
			'invoice_export_status' => array(
				'name' => __( 'At which status to export invoice?', 'tckpoh' ),
				'type' => 'select',
				'options' => wc_get_order_statuses(),
				'desc' => __( 'Change this only if you are using a different status for payment. Usually the order goes to Processing status when a payment is made. If a different payment method is chosen, than the invoice is made when you change the status to Processing manually.', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_export_status',
				'desc_tip' =>  true,
			),
			'invoice_export_time' => array(
				'name' => __( 'Time to export invoices', 'tckpoh' ),
				'type' => 'text',
				'default' => '0:00',
				'class' => 'befilled',
				'desc' => __( '', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_invoice_export_time'
			),
			'invoice_export_day_in_week' => array(
				'name' => __( 'Day of week', 'tckpoh' ),
				'type' => 'select',
				'class' => 'befilled',
				'options' => array(
					'1' => __( 'Monday', 'tckpoh' ),
					'2' => __( 'Tuesday', 'tckpoh' ),
					'3' => __( 'Wednesday', 'tckpoh' ),
					'4' => __( 'Thursday', 'tckpoh' ),
					'5' => __( 'Friday', 'tckpoh' ),
					'6' => __( 'Saturday', 'tckpoh' ),
					'7' => __( 'Sunday', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_export_day_in_week',
			),
			'invoice_export_day_in_month' => array(
				'name' => __( 'Day of month', 'tckpoh' ),
				'type' => 'select',
				'class' => 'befilled',
				'options' => array('1' => '1','2' => '2','3' => '3','4' => '4','5' => '5','6' => '6','7' => '7','8' => '8','9' => '9','10' => '10','11' => '11','12' => '12','13' => '13','14' => '14','15' => '15','16' => '16','17' => '17','18' => '18','19' => '19','20' => '20','21' => '21','22' => '22','23' => '23','24' => '24','25' => '25','26' => '26','27' => '27','28' => '28','L' => __( 'Last day', 'tckpoh' )),
				'id'   => 'wc_settings_pohoda_export_invoice_export_day_in_month',
			),
			'invoice_export_to_eet' => array(
				'name' => __( 'Should Pohoda send invoice to EET after import?', 'tckpoh' ),
				'type' => 'select',
				'default' => 'notSend',
				'class' => 'separated_field',
				'desc' => __( 'This only applies to payments made by card.', 'tckpoh' ),
				'desc_tip' =>  true,
				'options' => array(
					'forSending' => __( 'Yes, send to EET from Pohoda', 'tckpoh' ),
					'notSend' => __( 'Dont send to EET from Pohoda', 'tckpoh' ),
					'externally' => __( 'I will send to EET externally', 'tckpoh' ),
					'notEnter' => __( 'EET doesnt apply to my products', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_export_to_eet',
			),

			// order export //
			'invoice_export_orders' => array(
				'name' => __( 'Export orders?', 'tckpoh' ),
				'type' => 'radio',
				'default' => 'no',
				'options' => array(
					'yes' => __( 'Yes', 'tckpoh' ),
					'no' => __( 'No', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_export_orders',
			),
			'invoice_export_orders_number' => array(
				'name' => __( 'Order number type', 'tckpoh' ),
				'type' => 'select',
				'class' => 'befilled',
				'default' => 'date',
				'options' => array(
					'order' => __( 'Order number', 'tckpoh' ),
					'invoice' => __( 'Invoice number', 'tckpoh' ),
					'invoice_core' => __( 'Invoice core number', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_invoice_export_orders_number',
			),
			'invoice_export_section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_pohoda_export_invoice_export_section_end'
			),
			
			// PAYMENTS //
			'payment_methods' => array(
				'name'     => __( 'Payment methods', 'tckpoh' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_payment_methods',
			),
			'payment_methods_card' => array(
				'name' => __( 'Card', 'tckpoh' ),
				'type' => 'select',
				'default' => 'not-specified',
				'options' => $payment_methods,
				'id'   => 'wc_settings_pohoda_export_payment_methods_card',
				'desc' => __( 'Choose a payment type from those turned on in Woocommerce.', 'tckpoh' ),
				'desc_tip' =>  true,
			),
			'payment_methods_card_text' => array(
				'name' => __( '', 'tckpoh' ),
				'default' => __( 'Plat.kartou', 'tckpoh' ),
				'type' => 'select',
				'options' => array(
					'not-specified' => __( '', 'tckpoh' ),
				),
				'class' => 'separated_field',
				'id'   => 'wc_settings_pohoda_export_payment_methods_card_text',
				'desc' => __( 'And assign it to a payment type from Pohoda.', 'tckpoh' ),
				'desc_tip' =>  true,
			),
			'payment_methods_cash' => array(
				'name' => __( 'Cash in shop', 'tckpoh' ),
				'type' => 'select',
				'default' => 'not-specified',
				'options' => $payment_methods,
				'id'   => 'wc_settings_pohoda_export_payment_methods_cash',
			),
			'payment_methods_cash_text' => array(
				'name' => __( '', 'tckpoh' ),
				'default' => __( 'Hotově', 'tckpoh' ),
				'options' => array(
					'not-specified' => __( '', 'tckpoh' ),
				),
				'type' => 'select',
				'class' => 'separated_field',
				'id'   => 'wc_settings_pohoda_export_payment_methods_cash_text',
			),
			'payment_methods_transfer' => array(
				'name' => __( 'Bank transfer', 'tckpoh' ),
				'type' => 'select',
				'default' => 'bacs',
				'options' => $payment_methods,
				'id'   => 'wc_settings_pohoda_export_payment_methods_transfer',
			),
			'payment_methods_transfer_text' => array(
				'name' => __( '', 'tckpoh' ),
				'default' => __( 'Příkazem', 'tckpoh' ),
				'options' => array(
					'not-specified' => __( '', 'tckpoh' ),
				),
				'type' => 'select',
				'class' => 'separated_field',
				'id'   => 'wc_settings_pohoda_export_payment_methods_transfer_text',
			),
			'payment_methods_cod' => array(
				'name' => __( 'Cash on delivery', 'tckpoh' ),
				'type' => 'select',
				'default' => 'cod',
				'options' => $payment_methods,
				'id'   => 'wc_settings_pohoda_export_payment_methods_cod',
			),
			'payment_methods_cod_text' => array(
				'name' => __( '', 'tckpoh' ),
				'default' => __( 'Dobírkou', 'tckpoh' ),
				'options' => array(
					'not-specified' => __( '', 'tckpoh' ),
				),
				'type' => 'select',
				'id'   => 'wc_settings_pohoda_export_payment_methods_cod_text',
			),
			'payment_methods_special' => array(
				'name' => __( 'Own payment method', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( 'Insert the WooCommerce shortcut of your own payment method.', 'tckpoh' ),
				'desc_tip' =>  true,
				'id'   => 'wc_settings_pohoda_export_payment_methods_special',
			),
			'payment_methods_special_text' => array(
				'name' => __( '', 'tckpoh' ),
				'options' => array(
					'not-specified' => __( '', 'tckpoh' ),
				),
				'type' => 'select',
				'id'   => 'wc_settings_pohoda_export_payment_methods_special_text',
			),
			'payment_methods_section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_pohoda_export_payment_methods_section_end'
			),

			// PDF //
			'export_pdf' => array(
				'name'     => __( 'PDF', 'tckpoh' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_pdf',
			),
			'export_pdf_enable' => array(
				'name' => __( 'Enable PDF invoices', 'tckpoh' ),
				'type' => 'radio',
				'default' => 'no',
				'options' => array(
					'yes' => __( 'Yes', 'tckpoh' ),
					'no' => __( 'No', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_pdf_enable',
			),
			'export_pdf_email_banktransfer' => array(
				'name' => __( 'Automatically send invoice on new order when bank transfer payment method used?', 'tckpoh' ),
				'type' => 'radio',
				'default' => 'no',
				'options' => array(
					'yes' => __( 'Yes', 'tckpoh' ),
					'no' => __( 'No', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_pdf_email_banktransfer',
			),
			'export_pdf_email' => array(
				'name' => __( 'Automatically send invoice on a preset order status?', 'tckpoh' ),
				'type' => 'radio',
				'default' => 'no',
				'options' => array(
					'yes' => __( 'Yes', 'tckpoh' ),
					'no' => __( 'No', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_pdf_emails',
			),
			'export_pdf_status' => array(
				'name' => __( 'At which status to send the PDF invoice?', 'tckpoh' ),
				'type' => 'select',
				'class' => 'befilled',
				'options' => $limited_statuses,
				'desc' => __( 'The PDF invoice will be sent to the customer automatically when the order status is changed to this one.', 'tckpoh' ),
				'id'   => 'wc_settings_pohoda_export_pdf_status',
				'desc_tip' =>  true,
			),
			'export_pdf_logo' => array(
				'name' => __( 'Invoice logo', 'tckpoh' ),
				'type' => 'text',
				'desc' => __( 'Insert the link to the uploaded file.', 'tckpoh' ),
				'desc_tip' =>  true,
				'id'   => 'wc_settings_pohoda_export_pdf_logo',
			),
			'export_pdf_background' => array(
				'name' => __( 'Header background', 'tckpoh' ),
				'type' => 'radio',
				'default' => 'yes',
				'options' => array(
					'yes' => __( 'Yes', 'tckpoh' ),
					'no' => __( 'No', 'tckpoh' ),
				),
				'desc' => __( 'Should the header background be used?', 'tckpoh' ),
				'desc_tip' =>  true,
				'id'   => 'wc_settings_pohoda_export_pdf_background',
			),
			'export_pdf_qrcode' => array(
				'name' => __( 'Add QR code to invoice?', 'tckpoh' ),
				'type' => 'radio',
				'default' => 'no',
				'options' => array(
					'yes' => __( 'Yes', 'tckpoh' ),
					'no' => __( 'No', 'tckpoh' ),
				),
				'id'   => 'wc_settings_pohoda_export_pdf_qrcode',
			),
			'export_pdf_iban' => array(
				'name'     => __( 'IBAN', 'tckpoh' ),
				'desc'	   => __( 'If you want to use QR code, you must specify your IBAN code.', 'tckpoh' ),
				'desc_tip' => true,
				'type'     => 'text',
				'class'	   => 'befilled',
				'id'   	   => 'wc_settings_pohoda_export_pdf_iban',
			),
			'export_pdf_notice' => array(
				'name'  => __( 'Notice at the bottom of invoice:', 'tckpoh' ),
				'type'  => 'text',
				'class' => 'befilled',
				'id'	=> 'wc_settings_pohoda_export_pdf_notice',
			),
			'export_pdf_section_end' => array(
				 'type' => 'sectionend',
				 'id' 	=> 'wc_settings_pohoda_export_pdf_section_end'
			),

			// ACTION LOG //
			'action_log' => array(
				'name'     => __( 'Action log', 'tckpoh' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_action_log',
			),
			'action_log_window' => array(
				'name'     => __( '', 'tckpoh' ),
				'type'     => 'text',
				'id'       => 'wc_settings_pohoda_export_action_log_window',
			),
			'action_log_section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_pohoda_export_action_log_section_end'
			),
			
			// ACTIONS //
			'actions' => array(
				'name'     => __( 'Actions', 'tckpoh' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_actions',
			),
			'actions_input' => array(
				'name'     => __( '', 'tckpoh' ),
				'type'     => 'text',
				'desc'     => '',
				'id'       => 'wc_settings_pohoda_export_actions_buttons',
			),
			'actions_section_end' => array(
				 'type' => 'sectionend',
				 'id' => 'wc_settings_pohoda_export_payment_methods_section_end'
			),
			
			//// SKLAD ????
		);
		
		return apply_filters( 'wc_settings_pohoda_export_settings', $settings );
	}
	
}
WC_Pohoda_Export_Tab::init();