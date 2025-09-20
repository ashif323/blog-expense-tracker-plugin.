<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists('BET_REST') ) {

    class BET_REST {
        public function init() {
            add_action('rest_api_init', [$this,'register_routes']);
        }

        public function register_routes() {
            register_rest_route('bet/v1','/expenses',[
                'methods'=>'GET',
                'callback'=>[$this,'get_expenses'],
                'permission_callback'=>function(){
                    return current_user_can(BET_Capabilities::CAP);
                }
            ]);
        }

        public function get_expenses($request){
            global $wpdb;
            $table = $wpdb->prefix . 'blogger_expenses';
            $results = $wpdb->get_results("SELECT * FROM $table ORDER BY expense_date DESC", ARRAY_A);
            return rest_ensure_response($results);
        }
    }

}
