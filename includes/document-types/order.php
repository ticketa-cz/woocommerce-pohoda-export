<?php

$xml->startElementNS('dat', 'dataPackItem', null);
$xml->writeAttribute('id', 'OBJ001');
$xml->writeAttribute('version', '2.0');
// <dat:dataPackItem id="OBJ001" version="2.0"> 
    
    $xml->startElementNS('ord', 'order', null);
    $xml->writeAttribute('version', '2.0');
    // <ord:order version="2.0">
        
        $xml->startElementNS('ord', 'orderHeader', null);
        // <ord:orderHeader>
    
            $xml->writeElementNS('ord', 'orderType', null, 'receivedOrder');
            // <ord:orderType>receivedOrder</ord:orderType>

            $xml->startElementNS('ord', 'number', null);
                $xml->writeElementNS('typ', 'numberRequested', null, $order_number);
            $xml->endElement();
            $xml->writeElementNS('ord', 'numberOrder', null, $order_id);
            $xml->writeElementNS('ord', 'date', null, $order_date);
            $xml->writeElementNS('ord', 'dateFrom', null, $order_date);
            $xml->writeElementNS('ord', 'dateTo', null, $order_date);
            $xml->writeElementNS('ord', 'text', null, $order_heading);
            $xml->startElementNS('ord', 'partnerIdentity', null);
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
            $xml->startElementNS('ord', 'myIdentity', null);
            // start myIdentity //
                $xml->startElementNS('typ', 'address', null);
                    foreach ($billing_info as $billing_item => $billing_data) {
                        $xml->writeElementNS('typ', $billing_item, null, $billing_data);
                    }
                $xml->endElement();
            // end myIdentity //
            $xml->endElement();
            $xml->startElementNS('ord', 'paymentType', null);
                $xml->writeElementNS('typ', 'ids', null, $payment_type_text);
            $xml->endElement();
            $xml->startElementNS('ord', 'priceLevel', null);
                $xml->writeElementNS('typ', 'ids', null, 'Prodejní');
            $xml->endElement();
        
        // end orderHeader //
        $xml->endElement();
        
        $xml->startElementNS('ord', 'orderDetail', null);
        // <inv:orderDetail>

            // order items //

            $order_items = $order->get_items();
            $order_shipping = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'shipping' ));
            $order_fees = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'fee' ));
            $order_items_array = array_merge( $order_items, $order_shipping, $order_fees );
            
            foreach ( $order_items_array as $item_id => $item ) {
                    
                $xml->startElementNS('ord', 'orderItem', null);
                
                    $item_name = $item->get_name();
                    $item_total = $item->get_total();
                    $quantity = $item->get_quantity();
                    if ( empty($quantity) || $quantity == NULL || $quantity == '' ) { $quantity = 1; } 
                    $item_quantity = number_format( $quantity, 0, '.', '' );

                    $item_prices = get_item_prices( $item, $item_total, $item_quantity, $coeficient, $vat_rate );									
                    
                    $xml->writeElementNS('ord', 'text', null, $item_name);
                    $xml->writeElementNS('ord', 'quantity', null, $item_quantity);
                    $xml->writeElementNS('ord', 'delivered', null, 0);
                    $xml->writeElementNS('ord', 'payVAT', null, 'false');
                    $xml->writeElementNS('ord', 'rateVAT', null, $item_prices['item_vat_rate'] );
            
                    $xml->startElementNS('ord', $currency_format, null);
                        $xml->writeElementNS('typ', 'unitPrice', null, number_format( $item_prices['item_unit_without_vat'], 2, '.', '' ) );
                        $xml->writeElementNS('typ', 'price', null, number_format( $item_prices['item_total_without_vat'], 2, '.', '' ) );
                        $xml->writeElementNS('typ', 'priceVAT', null, number_format( $item_prices['item_total_vat'], 2, '.', '' ) );
                        $xml->writeElementNS('typ', 'priceSum', null, number_format( $item_prices['item_total'], 2, '.', '' ) );
                    $xml->endElement();
                    
                $xml->endElement();

            }
        
        // end orderDetail //
        $xml->endElement();
                            
        $xml->startElementNS('ord', 'orderSummary', null);
        // start orderSummary //
                $xml->writeElementNS('ord', 'roundingDocument', null, 'math2one');								
                $xml->startElementNS('ord', $currency_format, null);

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
                                
        // end orderSummary //
        $xml->endElement();
    
    // end invoice //
    $xml->endElement();

// end dataPackItem //
$xml->endElement();