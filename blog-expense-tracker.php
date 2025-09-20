<?php
/**
 * Plugin Name:       Blog Expense Tracker
 * Description:       Track blogging expenses with charts, filters, and CSV import/export — all within wp-admin.
 * Version:           1.0.0
 * Author:            Mohammad Ashif Iqbal
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Text Domain:       blog-expense-tracker
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Constants
define( 'BET_VERSION', '1.0.0' );
define( 'BET_FILE', __FILE__ );
define( 'BET_PATH', plugin_dir_path( __FILE__ ) );
define( 'BET_URL',  plugin_dir_url( __FILE__ ) );

// Includes
$required_files = [
    'helpers.php',
    'capabilities.php',
    'class-bet-activator.php',
    'class-bet-deactivator.php',
    'class-bet-db.php',
    'class-bet-admin.php',
    'class-bet-rest.php',
    'class-bet-utils.php'
];

foreach ($required_files as $file) {
    $path = BET_PATH . 'includes/' . $file;
    if (is_readable($path)) require_once $path;
}

// Activation / Deactivation
register_activation_hook( __FILE__, [ 'BET_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'BET_Deactivator', 'deactivate' ] );

// Bootstrap
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( 'blog-expense-tracker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    if ( class_exists('BET_DB') ) {
        BET_DB::init();
    }

    if ( is_admin() && class_exists('BET_Admin') ) {
        ( new BET_Admin() )->init();
    }

    if ( class_exists('BET_REST') ) {
        ( new BET_REST() )->init();
    }
});
