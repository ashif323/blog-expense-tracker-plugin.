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
            add_menu_page(
                __('Expense Tracker','blog-expense-tracker'),
                'Expense Tracker',
                BET_Capabilities::CAP,
                'bet-dashboard',
                [$this,'page_dashboard'],
                'dashicons-chart-pie',
                56
            );

            add_submenu_page('bet-dashboard',
                __('Add Expense','blog-expense-tracker'),
                __('Add Expense','blog-expense-tracker'),
                BET_Capabilities::CAP,
                'bet-add',
                [$this,'page_add']
            );

            add_submenu_page('bet-dashboard',
                __('All Expenses','blog-expense-tracker'),
                __('All Expenses','blog-expense-tracker'),
                BET_Capabilities::CAP,
                'bet-list',
                [$this,'page_list']
            );

            add_submenu_page('bet-dashboard',
                __('Import/Export','blog-expense-tracker'),
                __('Import/Export','blog-expense-tracker'),
                BET_Capabilities::CAP,
                'bet-import-export',
                [$this,'page_import_export']
            );

            add_submenu_page('bet-dashboard',
                __('Settings','blog-expense-tracker'),
                __('Settings','blog-expense-tracker'),
                BET_Capabilities::CAP,
                'bet-settings',
                [$this,'page_settings']
            );
        }

        public function assets() {
            wp_enqueue_style('bet-admin', BET_URL.'assets/admin.css', [], BET_VERSION);
            wp_enqueue_script('bet-admin', BET_URL.'assets/admin.js', ['jquery'], BET_VERSION, true);
        }

        public function page_dashboard() {
            echo '<h1>' . esc_html(__('Dashboard', 'blog-expense-tracker')) . '</h1>';
            echo '<p>Coming soon - dashboard with charts and summaries.</p>';
        }

        public function page_add() {
            if (!BET_Utils::current_user_can_manage()) {
                wp_die(__('Unauthorized', 'blog-expense-tracker'));
            }
            ?>
            <div class="wrap">
                <h1><?php _e('Add Expense', 'blog-expense-tracker'); ?></h1>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                    <input type="hidden" name="action" value="bet_add_expense">
                    <?php wp_nonce_field('bet_add_expense_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="amount"><?php _e('Amount', 'blog-expense-tracker'); ?></label></th>
                            <td><input type="number" step="0.01" id="amount" name="amount" required></td>
                        </tr>
                        <tr>
                            <th><label for="category"><?php _e('Category', 'blog-expense-tracker'); ?></label></th>
                            <td><input type="text" id="category" name="category" required></td>
                        </tr>
                        <tr>
                            <th><label for="expense_date"><?php _e('Date', 'blog-expense-tracker'); ?></label></th>
                            <td><input type="date" id="expense_date" name="expense_date" required value="<?php echo date('Y-m-d'); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="note"><?php _e('Note', 'blog-expense-tracker'); ?></label></th>
                            <td><textarea id="note" name="note"></textarea></td>
                        </tr>
                    </table>
                    <?php submit_button(__('Add Expense', 'blog-expense-tracker')); ?>
                </form>
            </div>
            <?php
        }

        public function handle_add_expense() {
            BET_Utils::require_manage_cap();
            check_admin_referer('bet_add_expense_nonce');

            $data = [
                'amount' => $_POST['amount'] ?? '',
                'category' => $_POST['category'] ?? '',
                'expense_date' => $_POST['expense_date'] ?? '',
                'notes' => $_POST['note'] ?? '',
                'currency' => 'USD', // Change if needed or add a setting for currency
                'payment_method' => 'cash', // Change or make dynamic if you support multiple methods
            ];

            // Sanitize and validate inputs
            $data['amount'] = BET_Utils::sanitize_money($data['amount']);
            $data['category'] = BET_Utils::sanitize_text($data['category']);
            $data['expense_date'] = BET_Utils::sanitize_date($data['expense_date']);
            $data['notes'] = BET_Utils::sanitize_text($data['notes'], 1000);

            if ($data['amount'] <= 0 || empty($data['category']) || empty($data['expense_date'])) {
                wp_redirect(admin_url('admin.php?page=bet-add&message=error'));
                exit;
            }

            $insert_id = BET_DB::insert_expense($data);
            if ($insert_id) {
                wp_redirect(admin_url('admin.php?page=bet-list&message=added'));
                exit;
            } else {
                wp_redirect(admin_url('admin.php?page=bet-add&message=error'));
                exit;
            }
        }

        public function page_list() {
            if (!BET_Utils::current_user_can_manage()) {
                wp_die(__('Unauthorized', 'blog-expense-tracker'));
            }

            $paged = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
            $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

            $per_page = 10;

            $args = [
                'page' => $paged,
                'per_page' => $per_page,
                'search' => $search,
            ];

            $result = BET_DB::get_expenses($args);
            $expenses = $result['rows'];
            $total = $result['total'];
            $num_pages = ceil($total / $per_page);
            $base_url = admin_url('admin.php?page=bet-list');

            ?>
            <div class="wrap">
                <h1><?php _e('All Expenses', 'blog-expense-tracker'); ?></h1>

                <form method="get" class="search-form" style="margin-bottom: 20px;">
                    <input type="hidden" name="page" value="bet-list" />
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search notes...', 'blog-expense-tracker'); ?>">
                    <button class="button"><?php _e('Search', 'blog-expense-tracker'); ?></button>
                    <a href="<?php echo esc_url($base_url); ?>" class="button"><?php _e('Reset', 'blog-expense-tracker'); ?></a>
                </form>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'blog-expense-tracker'); ?></th>
                            <th><?php _e('Amount', 'blog-expense-tracker'); ?></th>
                            <th><?php _e('Category', 'blog-expense-tracker'); ?></th>
                            <th><?php _e('Note', 'blog-expense-tracker'); ?></th>
                            <th><?php _e('Actions', 'blog-expense-tracker'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($expenses)) : ?>
                        <tr><td colspan="5"><?php _e('No expenses found.', 'blog-expense-tracker'); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense) : ?>
                            <tr>
                                <td><?php echo esc_html($expense['expense_date']); ?></td>
                                <td><?php echo esc_html(number_format_i18n($expense['amount'], 2)); ?></td>
                                <td><?php echo esc_html($expense['category']); ?></td>
                                <td><?php echo esc_html($expense['notes']); ?></td>
                                <td>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('<?php _e('Are you sure?', 'blog-expense-tracker'); ?>');">
                                        <?php wp_nonce_field('bet_delete_expense_nonce'); ?>
                                        <input type="hidden" name="action" value="bet_delete_expense">
                                        <input type="hidden" name="id" value="<?php echo esc_attr($expense['id']); ?>">
                                        <button class="button-link-delete" type="submit"><?php _e('Delete', 'blog-expense-tracker'); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($num_pages > 1): ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        $page_links = paginate_links( [
                            'base' => add_query_arg( 'paged', '%#%' ),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $num_pages,
                            'current' => $paged,
                        ] );
                        echo $page_links;
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php
        }

        public function page_import_export() {
            if (!BET_Utils::current_user_can_manage()) {
                wp_die(__('Unauthorized', 'blog-expense-tracker'));
            }
            ?>
            <div class="wrap">
                <h1><?php _e('Import / Export Expenses', 'blog-expense-tracker'); ?></h1>

                <h2><?php _e('Export CSV', 'blog-expense-tracker'); ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('bet_export_csv_nonce'); ?>
                    <input type="hidden" name="action" value="bet_export_csv">
                    <?php submit_button(__('Export Expenses as CSV', 'blog-expense-tracker')); ?>
                </form>

                <h2><?php _e('Import CSV', 'blog-expense-tracker'); ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('bet_import_csv_nonce'); ?>
                    <input type="hidden" name="action" value="bet_import_csv">
                    <input type="file" name="csv_file" required accept=".csv,text/csv">
                    <?php submit_button(__('Import CSV', 'blog-expense-tracker')); ?>
                </form>
            </div>
            <?php
        }

        public function handle_delete_expense() {
            BET_Utils::require_manage_cap();
            check_admin_referer('bet_delete_expense_nonce');
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id > 0) {
                BET_DB::delete_expense($id);
            }
            wp_redirect(admin_url('admin.php?page=bet-list'));
            exit;
        }

        public function handle_export_csv() {
            BET_Utils::require_manage_cap();
            check_admin_referer('bet_export_csv_nonce');

            $expenses = BET_DB::get_expenses(['page' => 1, 'per_page' => 10000]);
            $filename = 'expenses-export-' . date('Y-m-d') . '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=' . $filename);
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'User ID', 'Date', 'Amount', 'Currency', 'Category', 'Payment Method', 'Notes', 'Created At', 'Updated At']);

            foreach ($expenses['rows'] as $row) {
                fputcsv($output, [
                    $row['id'], $row['user_id'], $row['expense_date'], $row['amount'],
                    $row['currency'], $row['category'], $row['payment_method'], $row['notes'],
                    $row['created_at'], $row['updated_at']
                ]);
            }
            fclose($output);
            exit;
        }

        public function handle_import_csv() {
            BET_Utils::require_manage_cap();
            check_admin_referer('bet_import_csv_nonce');

            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                wp_redirect(admin_url('admin.php?page=bet-import-export&message=error'));
                exit;
            }

            $file = $_FILES['csv_file']['tmp_name'];
            if (($handle = fopen($file, 'r')) !== false) {
                $header = fgetcsv($handle);
                $expected_header = ['ID', 'User ID', 'Date', 'Amount', 'Currency', 'Category', 'Payment Method', 'Notes', 'Created At', 'Updated At'];

                if ($header !== $expected_header) {
                    fclose($handle);
                    wp_redirect(admin_url('admin.php?page=bet-import-export&message=invalid_header'));
                    exit;
                }

                while (($data = fgetcsv($handle)) !== false) {
                    $row_data = array_combine($header, $data);
                    $insert_data = [
                        'user_id' => get_current_user_id(),
                        'expense_date' => BET_Utils::sanitize_date($row_data['Date']),
                        'amount' => BET_Utils::sanitize_money($row_data['Amount']),
                        'currency' => BET_Utils::sanitize_text($row_data['Currency'], 3),
                        'category' => BET_Utils::sanitize_text($row_data['Category']),
                        'payment_method' => BET_Utils::sanitize_text($row_data['Payment Method'], 32),
                        'notes' => BET_Utils::sanitize_text($row_data['Notes'], 1000),
                    ];
                    BET_DB::insert_expense($insert_data);
                }
                fclose($handle);
                wp_redirect(admin_url('admin.php?page=bet-import-export&message=imported'));
                exit;

            } else {
                wp_redirect(admin_url('admin.php?page=bet-import-export&message=error'));
                exit;
            }
        }

        public function page_settings() {
            echo '<h1>' . esc_html(__('Settings', 'blog-expense-tracker')) . '</h1>';
            echo '<p>Coming soon - plugin settings.</p>';
        }
    }

}
