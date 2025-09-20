<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists('BET_DB') ) {

    class BET_DB {
        public static $table;

        public static function init() {
            global $wpdb;
            self::$table = $wpdb->prefix . 'blogger_expenses';
        }

        public static function create_tables() {
            global $wpdb;
            self::init();
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE " . self::$table . " (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                expense_date DATE NOT NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                currency CHAR(3) NOT NULL DEFAULT 'INR',
                category VARCHAR(64) NOT NULL,
                payment_method VARCHAR(32) NOT NULL DEFAULT 'cash',
                notes TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY expense_date (expense_date),
                KEY user_id (user_id),
                KEY category (category)
            ) $charset_collate;";

            dbDelta($sql);
        }

        public static function insert_expense($data) {
            global $wpdb; self::init();
            $wpdb->insert(self::$table, [
                'user_id' => get_current_user_id(),
                'expense_date' => BET_Utils::sanitize_date($data['expense_date']),
                'amount' => BET_Utils::sanitize_money($data['amount']),
                'currency' => BET_Utils::sanitize_text($data['currency'], 3),
                'category' => BET_Utils::sanitize_text($data['category']),
                'payment_method' => BET_Utils::sanitize_text($data['payment_method'], 32),
                'notes' => BET_Utils::sanitize_text($data['notes'], 1000),
            ], ['%d','%s','%f','%s','%s','%s','%s']);
            return (int) $wpdb->insert_id;
        }

        public static function delete_expense($id) {
            global $wpdb; self::init();
            return $wpdb->delete(self::$table, ['id' => (int)$id], ['%d']);
        }

        public static function get_expenses($args = []) {
            global $wpdb; self::init();
            $defaults = ['page'=>1,'per_page'=>20,'search'=>'','category'=>'','start'=>'','end'=>''];
            $args = wp_parse_args($args, $defaults);

            $where = ' WHERE 1=1 ';
            $params = [];
            if($args['search']) { $where .= ' AND notes LIKE %s'; $params[] = '%' . $wpdb->esc_like($args['search']) . '%'; }
            if($args['category']) { $where .= ' AND category = %s'; $params[] = $args['category']; }
            if($args['start']) { $where .= ' AND expense_date >= %s'; $params[] = BET_Utils::sanitize_date($args['start']); }
            if($args['end']) { $where .= ' AND expense_date <= %s'; $params[] = BET_Utils::sanitize_date($args['end']); }

            $limit = (int)$args['per_page'];
            $offset = ((int)$args['page'] - 1) * $limit;

            $sql = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM " . self::$table . $where . " ORDER BY expense_date DESC, id DESC LIMIT %d OFFSET %d", array_merge($params, [$limit, $offset]));
            $rows = $wpdb->get_results($sql, ARRAY_A);
            $total = (int) $wpdb->get_var('SELECT FOUND_ROWS()');
            return ['rows'=>$rows,'total'=>$total];
        }

        public static function summary_monthly($year) {
            global $wpdb; self::init();
            $sql = $wpdb->prepare("SELECT DATE_FORMAT(expense_date, '%m') AS month, SUM(amount) AS total FROM " . self::$table . " WHERE YEAR(expense_date)=%d GROUP BY month ORDER BY month ASC", (int)$year);
            return $wpdb->get_results($sql, ARRAY_A);
        }

        public static function summary_by_category($start, $end) {
            global $wpdb; self::init();
            $sql = $wpdb->prepare("SELECT category, SUM(amount) AS total FROM " . self::$table . " WHERE expense_date BETWEEN %s AND %s GROUP BY category ORDER BY total DESC", BET_Utils::sanitize_date($start), BET_Utils::sanitize_date($end));
            return $wpdb->get_results($sql, ARRAY_A);
        }
    }

}
