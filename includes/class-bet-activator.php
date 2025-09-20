<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BET_Activator {

    public static function activate() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bet_expenses';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            amount DECIMAL(10,2) NOT NULL,
            category VARCHAR(100) NOT NULL,
            note TEXT,
            expense_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Add default options
        add_option( 'bet_currency', 'USD' );
        add_option( 'bet_monthly_budget', 0 );
    }
}
