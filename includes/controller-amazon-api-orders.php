<?php

// Controller logic for handling purchase data
function handle_amazon_data_after_purchase( $order_id ) {

    // Check if the session variable exists
    if ( isset( $_SESSION['product_data'] ) ) {
        $productData = $_SESSION['product_data'];

        // Access individual data elements
        $product_id = absint( $productData['product_id'] );

        // Get the order object
        $order = wc_get_order( $order_id );

        if ( $order ) {
            $data = array();

            // Get additional order details
            $data['user_id'] = $order->get_user_id();
            $data['product_id'] = $product_id;
            $data['image'] = esc_url_raw( $productData['image'] );
            $data['title'] = sanitize_text_field( $productData['title'] );
            $data['weight'] = sanitize_text_field( $productData['weight'] );
            $data['dimensions'] = sanitize_text_field( $productData['dimensions'] );
            $data['price'] = 0; // Initialize the price as 0
            $data['quantity'] = 0; // Initialize the quantity as 0

            // Loop through each item in the order to find the product with $product_id
            foreach ( $order->get_items() as $item_id => $item ) {
                $product = $item->get_product();

                if ( $product && $product->get_id() === $product_id ) {
                    // If the product matches $product_id, update the price and quantity
                    $data['price'] = $product->get_price();
                    $data['quantity'] += $item->get_quantity();
                }
            }

            $data['total_price'] = $order->get_total();
            $data['address_1'] = $order->get_billing_address_1();
            $data['address_2'] = $order->get_billing_address_2();
            $data['city'] = $order->get_billing_city();
            $data['state'] = $order->get_billing_state();
            $data['country'] = $order->get_billing_country();
            $data['postcode'] = $order->get_billing_postcode();
            $data['payment_method'] = $order->get_payment_method_title();

            AmazonApiOrders::save_amazon_purchase_data( $data );
        }
    }
}

// Controller get all purchase data
function amazon_get_all_orders(){

    $orders = AmazonApiOrders::get_all_orders();
    return $orders;
}