<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists('BET_Activator') ) {

    class BET_Activator {
        public static function activate() {
            if ( class_exists('BET_DB') ) {
                BET_DB::create_tables();
            }

            if ( class_exists('BET_Capabilities') ) {
                BET_Capabilities::add_caps();
            }

            if ( false === get_option('bet_currency') ) add_option('bet_currency', 'INR');
            if ( false === get_option('bet_monthly_budget') ) add_option('bet_monthly_budget', 0);
        }
    }

}
