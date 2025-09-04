<?php

// addresses //

$company_address = $billing_info['street'];
if ( $billing_info['number'] ) { 
    $company_address .= ' ' . $billing_info['number'];
}
if ( $billing_info['city'] ) {
    $company_address .= ', ' . $billing_info['city'];
}
if ( $billing_info['zip'] ) {
    $company_address .= ', ' . $billing_info['zip'];
}

$client_address = $billing_address;
if ( $customer_info['city'] ) {
    $client_address .= ', ' . $customer_info['city'];
}
if ( $customer_info['zip'] ) {
    $client_address .= ', ' . $customer_info['zip'];
}

$customer_icodic = '';
if ( $customer_ico != '' ) {
    $customer_icodic .= '<tr><td>' . $customer_ico . '</td><td><span>' . __( 'ICO', 'tckpoh' ) . '</span></td></tr>';
}
if ( $customer_dic != '' ) {
    $customer_icodic .= '<tr><td>' . $customer_dic . '</td><td><span>' . __( 'DIC', 'tckpoh' ) . '</span></td></tr>';
}

// dates //

$date_format = date('d.m. Y', strtotime($order_date));
$date_tax_format = date('d.m. Y', strtotime($order_date. ' + '. $date_tax .' days'));
$date_due_format = date('d.m. Y', strtotime($order_date. ' + '. $date_due .' days'));

// logo and background //

$company_logo = get_option('wc_settings_pohoda_export_pdf_logo');
function UR_exists( $url ) {
    if ( empty( $url )) {
        return false;
    }
    $headers = get_headers( $url );
    return stripos( $headers[0],"200 OK" ) ? true : false;
}
if ( UR_exists( $company_logo ) ) {
    $logo   =   '<div id="logo">
                    <img src="' . $company_logo . '" style="width: 250px; height: auto;"/>
                </div>';
} else {
    $logo   =   '';
}

if ( get_option('wc_settings_pohoda_export_pdf_background') == 'yes' ) {
    $background_url = TICKETAPOH_PATH . 'includes/document-types/pdf-invoice/dimension.png';
} else {
    $background_url = '';
}

// HTML //

$html  = '<body>
            <header class="clearfix">
                ' . $logo . '
                <h1 style="background: url('. $background_url . ')">' . $document_title . $invoice_number . '</h1>

                <div id="info">
                    <table>
                        <tr><td>' . __( 'DOCUMENT INFO', 'tckpoh' ) . '</td><td width="65%"></td></tr>

                        <tr><td><span>' . __( 'DOCUMENT NUMBER', 'tckpoh' ) . '</span></td><td>' . $invoice_number . '</td></tr>
                        <tr><td><span>' . __( 'VARIABLE SYMBOL', 'tckpoh' ) . '</span></td><td>' . $variable_symbol . '</td></tr>
                        <tr><td><span>' . __( 'SPECIFIC SYMBOL', 'tckpoh' ) . '</span></td><td>' . $specific_symbol . '</td></tr>
                        <tr><td><span>' . __( 'ORDER NUMBER', 'tckpoh' ) . '</span></td><td>' . $order_number . '</td></tr>

                        <tr><td><span>' . __( 'DATE', 'tckpoh' ) . '</span></td><td>' . $date_format . '</td></tr>  
                        <tr><td><span>' . __( 'DATE DUE', 'tckpoh' ) . '</span></td><td>' . $date_due_format . '</td></tr>  
                        <tr><td><span>' . __( 'DATE TAX', 'tckpoh' ) . '</span></td><td>' . $date_tax_format . '</td></tr>
                        <tr><td><span>' . __( 'PAYMENT TYPE', 'tckpoh' ) . '</span></td><td>' . $order->get_payment_method_title() . '</td></tr>

                    </table>
                </div>

                <div id="company">
                    <table>
                        <tr><td width="65%"></td><td>' . __( 'COMPANY', 'tckpoh' ) . '</td></tr>

                        <tr><td>' . $billing_info['company'] . '</td><td><span>' . __( 'NAME', 'tckpoh' ) . '</span></td></tr>
                        <tr><td>' . $company_address . '</td><td><span>' . __( 'ADDRESS', 'tckpoh' ) . '</span></td></tr>
                        <tr><td>' . $billing_info['phone'] . '</td><td><span>' . __( 'PHONE', 'tckpoh' ) . '</span></td></tr>
                        <tr><td><a href="mailto:' . $billing_info['email'] . '">' . $billing_info['email'] . '</a></td><td><span>' . __( 'EMAIL', 'tckpoh' ) . '</span></td></tr>
                        <tr><td>' . get_option('wc_settings_pohoda_export_billing_account_number') . ' / ' . get_option('wc_settings_pohoda_export_billing_account_bank_id') . '</td><td><span>' . __( 'BANK ACCOUNT', 'tckpoh' ) . '</span></td></tr>
                        <tr><td>' . $billing_info['ico'] . '</td><td><span>' . __( 'ICO', 'tckpoh' ) . '</span></td></tr>
                        <tr><td>' . $billing_info['dic'] . '</td><td><span>' . __( 'DIC', 'tckpoh' ) . '</span></td></tr>

                    </table>
                </div>

                <div id="icebreaker"></div>

                <div id="client">
                    <table>
                        <tr><td width="65%"></td><td>' . __( 'CLIENT', 'tckpoh' ) . '</td></tr>

                        <tr><td>' . $customer_info['name'] . '</td><td><span>' . __( 'NAME', 'tckpoh' ) . '</span></td></tr>
                        <tr><td>' . $client_address . '</td><td><span>' . __( 'ADDRESS', 'tckpoh' ) . '</span></td></tr>
                        <tr><td><a href="mailto:' . $customer_info['email'] . '">' . $customer_info['email'] . '</a></td><td><span>' . __( 'EMAIL', 'tckpoh' ) . '</span></td></tr>
                        <tr><td>' . $customer_info['phone'] . '</td><td><span>' . __( 'PHONE', 'tckpoh' ) . '</span></td></tr>
                        ' . $customer_icodic . '

                    </table>
                </div>

                <div id="qrcode">' . $qr_code . '</div>';

$html .=   '</header>

            <main>
                <table id="prices">
                <thead>
                    <tr>
                        <th class="service">' . __( 'SERVICE', 'tckpoh' ) . '</th>
                        <th>' . __( 'QUANTITY', 'tckpoh' ) . '</th>
                        <th>' . __( 'DISCOUNT', 'tckpoh' ) . '</th>
                        <th>' . __( 'PRICE WITHOUT VAT', 'tckpoh' ) . '</th>
                        <th>' . __( 'VAT %', 'tckpoh' ) . '</th>
                        <th>' . __( 'VAT', 'tckpoh' ) . '</th>
                        <th>' . __( 'PRICE TOTAL', 'tckpoh' ) . '</th>
                    </tr>
                </thead>
                <tbody>';

$currency_symbol = ' ' . get_woocommerce_currency_symbol($order_currency);

//// items ////

foreach ( $items_array as $item_id => $item ) {

    $item_prices = $item['item_prices'];
    $invoice_prices = array();

    foreach ( $item_prices as $price_key => $item_price ) {
        if ( is_numeric( $item_price ) ) {
            $item_price = number_format( $item_price, 2 );
        }
        $invoice_prices[$price_key] = $item_price;
    }
    if ( $invoice_prices['item_discount'] > 0 ) {
        $item_discount = round( $invoice_prices['item_discount'], 0 ) . '%';
    } else {
        $item_discount = '';
    }

    $html .=        '<tr>
                        <td class="item_name">'. $item['name'] .'</td>
                        <td class="item_quantity">'. $item['item_quantity'] . '</td>
                        <td class="item_discount">'. $item_discount . '</td>
                        <td class="price_without_vat">'. $invoice_prices['item_total_without_vat'] . $currency_symbol . '</td>
                        <td class="price_vat_percent">'; if ( $invoice_prices['tax_rate'] > 0 ) { $html .= number_format( $invoice_prices['tax_rate'], 0 ) . '%'; } $html .= '</td>
                        <td class="price_vat">'; if ( $invoice_prices['item_total_vat'] > 0 ) { $html .= $invoice_prices['item_total_vat'] . $currency_symbol; } $html .= '</td>
                        <td class="price_total">'. $invoice_prices['item_total'] . $currency_symbol . '</td>
                    </tr>';
}
if ( intval( $total_discount ) > 0 ) {
    $html .=        '<tr>
                        <td colspan="5" class="prices_total">' . __( 'DISCOUNT', 'tckpoh' ) . '</td>
                        <td colspan="2"><strong>-' . number_format( $total_discount, 2 ) . $currency_symbol . '</strong></td>
                    </tr>';
}                
    $html .=        '<tr>
                        <td colspan="5" class="prices_total subtotal">' . __( 'SUBTOTAL', 'tckpoh' ) . '</td>
                        <td colspan="2" class="subtotal"><strong>' . number_format( $total_without_vat, 2 ) . $currency_symbol .  '</strong></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="prices_total">' . __( 'TAX', 'tckpoh' ) . '</td>
                        <td colspan="2"><strong>' . number_format( $total_vat, 2 ) . $currency_symbol . '</strong></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="prices_total">' . __( 'GRAND TOTAL', 'tckpoh' ) . '</td>
                        <td colspan="2"><strong>' . number_format( $order_price_round, 2 ) . $currency_symbol .  '</strong></td>
                    </tr>
                </tbody>
                </table>
            </main>
            
            <footer>';

            if ( get_option('wc_settings_pohoda_export_export_pdf_dph_rozpocet') !== 'no' ) {

                $html.='<table id="czkvat">
                            <tr><td>' . __( 'VAT RECAP', 'tckpoh' ) . '</td><td>' . __( 'VAT BASE', 'tckpoh' ) . '</td><td>' . __( 'VAT RATE', 'tckpoh' ) . '</td><td>' . __( 'VAT AMOUNT', 'tckpoh' ) . '</td><td>' . __( 'TOTAL WITH VAT', 'tckpoh' ) . '</td></tr>';
                            foreach ( $vat_rate as $rate ) {
                                if ( $rate['total'] > 0 ) {
                                    $html .= '<tr><td></td><td>' . round( $rate['total_without_vat_czk'] ) . ',- Kč</td><td>' . intval( ( 100 * $rate['coeficient'] ) - 100 ) . '%</td><td>' . round( $rate['total_vat_czk'] ) . ',- Kč</td><td>' . round( $rate['total_czk'] ) . ',- Kč</td></tr>';
                                }
                            }
                $html .='</table>';

            }

        $html.='<div id="notices">
                    <div class="notice">' . get_option('wc_settings_pohoda_export_pdf_notice') . '</div>
                </div>
            </footer>';

$html .= '
    </body>';