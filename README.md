# Blog Expense Tracker

A WordPress plugin to track blogging expenses with charts, filters, CSV import/export — all from within the WordPress admin dashboard.

## Features

- Add, edit, and delete expense entries
- Categorize and date expenses
- Paginated searchable expense list
- CSV import and export support
- User capability control for management
- REST API endpoint for expenses
- Configurable plugin settings (coming soon)
- Dashboard with summaries and charts (coming soon)

## Installation

1. Upload the plugin folder `blog-expense-tracker` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Expense Tracker** in the WordPress admin menu to start tracking expenses.
4. Use the **Add Expense** page to add new expenses.
5. Use the **All Expenses** page to view, search, and manage your expenses.
6. Use **Import/Export** to bulk upload or download your expense data.

## Usage

- Only users with the `manage_blog_expenses` capability can access or manage expenses.
- Expenses are tied to the current logged-in user by default.
- Expenses can be exported/imported in CSV format with specified columns.

## Requirements

- WordPress 5.9+
- PHP 7.4+
- MySQL with support for custom tables

## Development

- Developed by Mohammad Ashif Iqbal
- Licensed under GPL v2 or later

## Support

If you encounter issues or want to contribute, please open an issue on the GitHub repository.

---

*This plugin is actively developed and functional with plans for further enhancements.*

