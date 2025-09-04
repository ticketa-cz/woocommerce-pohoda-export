<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Make an HTTP call to the mServer with XML data
 * 
 * @param string $data_in XML data to send
 * @param string|null $host_filled Optional host override
 * @param string|null $login_filled Optional login override  
 * @param string|null $pass_filled Optional password override
 * @return string Response from mServer or error message
 */
function make_the_call( string $data_in, ?string $host_filled = null, ?string $login_filled = null, ?string $pass_filled = null ): string {
	
	// load login info //
	
	if ( isset($login_filled) && $login_filled != '' ) {
		$login = $login_filled;
	} else {
		$login = get_option('wc_settings_pohoda_export_mserver_login');
	}
	
	if ( isset($pass_filled) && $pass_filled != '' ) {
		$pass = $pass_filled;
	} else {
		$pass = get_option('wc_settings_pohoda_export_mserver_password');
	}
	
	if ( isset($host_filled) && $host_filled != '' ) {
		$address = $host_filled;
	} else {
		$address = get_option('wc_settings_pohoda_export_mserver_address');
	}
		
	// connect //
	
	$response = wp_remote_post( $address, [
		'method' => 'POST',
		'body'    => $data_in,
		'blocking' => true,
		'headers' => [
			'STW-Authorization' => 'Basic ' . base64_encode( $login . ':' . $pass ),
			'Content-type' => 'text/xml; charset=Windows-1250',
		],
		'httpversion' => '1.0',
    	'sslverify'   => true,
	] );
	
	// reply //
	
	if ( is_wp_error( $response ) ) {
		
		$output = $response->get_error_message();
		
	} else if ( wp_remote_retrieve_response_code( $response ) != 200 ) {

		$output = "HTTP error #" . wp_remote_retrieve_response_code( $response );
		
	} else {
		
		$output = wp_remote_retrieve_body( $response );
		
	}
	
	return $output;

}

?>