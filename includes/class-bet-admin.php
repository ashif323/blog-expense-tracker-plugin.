<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists('BET_Admin') ) {

    class BET_Admin {

        public function init() {
            add_action('admin_menu', [$this,'menu']);
            add_action('admin_enqueue_scripts', [$this,'assets']);

            add_action('admin_post_bet_add_expense', [$this,'handle_add_expense']);
            add_action('admin_post_bet_delete_expense', [$this,'handle_delete_expense']);
            add_action('admin_post_bet_export_csv', [$this,'handle_export_csv']);
            add_action('admin_post_bet_import_csv', [$this,'handle_import_csv']);
        }

        public function menu() {
            add_menu_page(__('Expense Tracker','blog-expense-tracker'),'Expense Tracker',BET_Capabilities::CAP,'bet-dashboard',[$this,'page_dashboard'],'dashicons-chart-pie',56);

            add_submenu_page('bet-dashboard', __('Add Expense','blog-expense-tracker'), __('Add Expense','blog-expense-tracker'), BET_Capabilities::CAP, 'bet-add', [$this,'page_add']);
            add_submenu_page('bet-dashboard', __('All Expenses','blog-expense-tracker'), __('All Expenses','blog-expense-tracker'), BET_Capabilities::CAP, 'bet-list', [$this,'page_list']);
            add_submenu_page('bet-dashboard', __('Import/Export','blog-expense-tracker'), __('Import/Export','blog-expense-tracker'), BET_Capabilities::CAP, 'bet-import-export', [$this,'page_import_export']);
            add_submenu_page('bet-dashboard', __('Settings','blog-expense-tracker'), __('Settings','blog-expense-tracker'), BET_Capabilities::CAP, 'bet-settings', [$this,'page_settings']);
        }

        public function assets() {
            wp_enqueue_style('bet-admin', BET_URL.'assets/admin.css', [], BET_VERSION);
            wp_enqueue_script('bet-admin', BET_URL.'assets/admin.js', ['jquery'], BET_VERSION, true);
        }

        public function page_dashboard(){ echo '<h1>Dashboard</h1>'; }
        public function page_add(){ echo '<h1>Add Expense</h1>'; }
        public function page_list(){ echo '<h1>All Expenses</h1>'; }
        public function page_import_export(){ echo '<h1>Import / Export</h1>'; }
        public function page_settings(){ echo '<h1>Settings</h1>'; }

        public function handle_add_expense() { BET_Utils::require_manage_cap(); }
        public function handle_delete_expense() { BET_Utils::require_manage_cap(); }
        public function handle_export_csv() { BET_Utils::require_manage_cap(); }
        public function handle_import_csv() { BET_Utils::require_manage_cap(); }
    }

}
