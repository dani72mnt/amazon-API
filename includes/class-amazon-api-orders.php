<?php

class AmazonApiOrders {

    // create the amazon_api_orders table
    public static function create_amazon_api_orders_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amazon_api_orders';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            image VARCHAR(255),
            title VARCHAR(255),
            price DECIMAL(10, 0),
            total_price DECIMAL(10, 0),
            quantity DECIMAL(10, 0),
            weight VARCHAR(125),
            dimensions VARCHAR(125),
            address_1 VARCHAR(255),
            address_2 VARCHAR(255),
            city VARCHAR(125),
            state VARCHAR(125),
            country VARCHAR(125),
            postcode VARCHAR(255),
            payment_method VARCHAR(50),
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    // Save amazon purchase data to the table
    public static function save_amazon_purchase_data( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amazon_api_orders';

        $wpdb->insert(
            $table_name,
            $data
        );
    }

    // Get all Amazon purchase data from the table
    public static function get_all_orders() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amazon_api_orders';

        // Get all rows from the table
        $query = "SELECT * FROM $table_name";
        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
    }

    // Retrieve amazon purchase data from the table by order ID
    public static function get_amazon_purchase_data_by_order_id( $order_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amazon_api_orders';

        $query = "SELECT * FROM $table_name WHERE id = %d";
        $data = $wpdb->get_row( $wpdb->prepare( $query, $order_id ), ARRAY_A );

        return $data;
    }

    // Update amazon purchase data in the table by order ID
    public static function update_amazon_purchase_data_by_order_id( $order_id, $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amazon_api_orders';

        $wpdb->update(
            $table_name,
            $data,
            array( 'id' => $order_id )
        );
    }
}
