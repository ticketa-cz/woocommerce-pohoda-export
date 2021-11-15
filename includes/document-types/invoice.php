<?php

$xml->startElementNS('dat', 'dataPackItem', null);
$xml->writeAttribute('id', 'AD002');
$xml->writeAttribute('version', '2.0');
// <dat:dataPackItem id="AD002" version="2.0"> 
    
    $xml->startElementNS('inv', 'invoice', null);
    $xml->writeAttribute('version', '2.0');
    // <inv:invoice version="2.0">
        
        $xml->startElementNS('inv', 'invoiceHeader', null);
        // <inv:invoiceHeader>
    
            $xml->writeElementNS('inv', 'invoiceType', null, 'issuedInvoice');
            // <inv:invoiceType>issuedInvoice</inv:invoiceType>
            $xml->startElementNS('inv', 'number', null);
                $xml->writeElementNS('typ', 'numberRequested', null, $invoice_number);
            $xml->endElement();
            $xml->writeElementNS('inv', 'symVar', null, $variable_symbol);
            $xml->writeElementNS('inv', 'date', null, $order_date);
            $xml->writeElementNS('inv', 'dateTax', null, $order_date_tax);
            $xml->writeElementNS('inv', 'dateAccounting', null, $order_date_tax);
            $xml->writeElementNS('inv', 'dateDue', null, $order_date_due);
            $xml->startElementNS('inv', 'accounting', null);
                $xml->writeElementNS('typ', 'ids', null, get_option('wc_settings_pohoda_export_invoice_classification_type'));
            $xml->endElement();
            $xml->startElementNS('inv', 'classificationVAT', null);
                $xml->writeElementNS('typ', 'ids', null, $vat_classification);
            $xml->endElement();
            $xml->writeElementNS('inv', 'text', null, $invoice_heading);
            $xml->startElementNS('inv', 'partnerIdentity', null);
            // start partnerIdentity //
                $xml->startElementNS('typ', 'address', null);
                    foreach ($customer_info as $info_item => $info_data) {
                        $xml->writeElementNS('typ', $info_item, null, $info_data);
                    }
                $xml->endElement();
                $xml->startElementNS('typ', 'shipToAddress', null);
                    foreach ($customer_shipping as $shipto_item => $shipto_data) {
                        $xml->writeElementNS('typ', $shipto_item, null, $shipto_data);
                    }
                $xml->endElement();
            // end partnerIdentity //
            $xml->endElement();
            $xml->startElementNS('inv', 'myIdentity', null);
            // start myIdentity //
                $xml->startElementNS('typ', 'address', null);
                    foreach ($billing_info as $billing_item => $billing_data) {
                        $xml->writeElementNS('typ', $billing_item, null, $billing_data);
                    }
                $xml->endElement();
            // end myIdentity //
            $xml->endElement();
            $xml->writeElementNS('inv', 'numberOrder', null, $order_id);
            $xml->writeElementNS('inv', 'dateOrder', null, $order_date);
            $xml->startElementNS('inv', 'paymentType', null);
                $xml->writeElementNS('typ', 'ids', null, $payment_type_text);
            $xml->endElement();
            $xml->startElementNS('inv', 'account', null);
                $xml->writeElementNS('typ', 'accountNo', null, get_option('wc_settings_pohoda_export_billing_account_number'));
                $xml->writeElementNS('typ', 'bankCode', null, get_option('wc_settings_pohoda_export_billing_account_bank_id'));
            $xml->endElement();
            $xml->startElementNS('inv', 'centre', null);
                $xml->writeElementNS('typ', 'ids', null, get_option('wc_settings_pohoda_export_invoice_center_custom'));
            $xml->endElement();
            $xml->startElementNS('inv', 'activity', null);
                $xml->writeElementNS('typ', 'ids', null, get_option('wc_settings_pohoda_export_invoice_activity_custom'));
            $xml->endElement();
            /*$xml->startElementNS('inv', 'liquidation', null);
                $xml->writeElementNS('typ', 'amountHome', null, $liquidation);
            $xml->endElement();*/
        
        // end invoiceHeader //
        $xml->endElement();
        
        $xml->startElementNS('inv', 'invoiceDetail', null);
        // <inv:invoiceDetail>

            // order items //
            
            foreach ( $items_array as $item_id => $item ) {

                $item_prices = $item['item_prices'];
                    
                $xml->startElementNS('inv', 'invoiceItem', null);
                    
                    $xml->writeElementNS('inv', 'text', null, $item['name']);
                    $xml->writeElementNS('inv', 'quantity', null, $item['item_quantity']);
                    $xml->writeElementNS('inv', 'rateVAT', null, $item_prices['item_vat_rate'] );
                    $xml->writeElementNS('inv', 'payVAT', null, 'false');

                    //tckpoh_logs( 'WC-currency ' . $order->get_currency() );
            
                    $xml->startElementNS('inv', $currency_format, null);
                        $xml->writeElementNS('typ', 'unitPrice', null, number_format( $item_prices['item_unit_without_vat'], 2, '.', '' ) );
                        $xml->writeElementNS('typ', 'price', null, number_format( $item_prices['item_total_without_vat'], 2, '.', '' ) );
                        $xml->writeElementNS('typ', 'priceVAT', null, number_format( $item_prices['item_total_vat'], 2, '.', '' ) );
                        $xml->writeElementNS('typ', 'priceSum', null, number_format( $item_prices['item_total'], 2, '.', '' ) );
                    $xml->endElement();
                    
                $xml->endElement();

            }
        
        // end invoiceDetail //
        $xml->endElement();
                            
        $xml->startElementNS('inv', 'invoiceSummary', null);
        // start invoiceSummary //
                $xml->writeElementNS('inv', 'roundingDocument', null, 'math2one');								
                $xml->startElementNS('inv', $currency_format, null);

                    if ( $currency_format == 'foreignCurrency' ) {

                        $conversion_rate = get_conversion_rate( $order_currency, 'CZK', '1' );

                        $xml->startElementNS('typ', 'currency', null);
                            $xml->writeElementNS('typ', 'ids', null, $order_currency);
                        $xml->endElement();
                        $xml->writeElementNS('typ', 'rate', null, $conversion_rate);
                        $xml->writeElementNS('typ', 'amount', null, '1');

                    } else {

                        foreach ($order_price as $order_price_item => $order_price_data) {
                            $xml->writeElementNS('typ', $order_price_item, null, $order_price_data);
                        }
                    }

                    $xml->startElementNS('typ', 'round', null);
                        $xml->writeElementNS('typ', 'priceRound', null, $order_price_round);
                    $xml->endElement();

                $xml->endElement();	
                                
        // end invoiceSummary //
        $xml->endElement();
        
        $xml->startElementNS('inv', 'EET', null);
        // EET //
            $xml->writeElementNS('typ', 'stateEET', null, $eet_option);			
        $xml->endElement();
    
    // end invoice //
    $xml->endElement();

// end dataPackItem //
$xml->endElement();