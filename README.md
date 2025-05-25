# Arsol Projects for Woo

A WordPress plugin that enables you to organize and track WooCommerce orders and subscriptions within projects, with automatic tracking of subscription renewals and child orders.

## Description

Arsol Projects for Woo is a specialized WordPress plugin that helps you manage and track WooCommerce orders and subscriptions within project contexts. It allows you to assign multiple orders and subscriptions to projects, providing a centralized view of all related transactions. The plugin automatically tracks subscription renewals and child orders, making it perfect for businesses that need to manage recurring revenue streams and project-based billing.

## Features

- Create and manage projects with associated WooCommerce orders and subscriptions
- Assign multiple orders and subscriptions to a single project
- Automatic tracking of subscription renewals and child orders
- Dedicated project pages showing all related transactions
- WooCommerce Subscriptions integration for recurring revenue tracking
- Project-based organization of customer orders and subscriptions
- Easy project overview with all related financial transactions
- Seamless integration with existing WooCommerce functionality
- Shortcodes for displaying project information:
  - `[arsol_projects]` - Display a grid of projects
  - `[arsol_project]` - Show a single project
  - `[arsol_project_categories]` - List project categories
  - `[arsol_project_orders]` - Display orders for a specific project
  - `[arsol_project_subscriptions]` - Show subscriptions for a project
  - `[arsol_user_projects]` - List projects for the current user
  - `[arsol_user_projects_count]` - Display the number of projects for the current user
  - `[arsol_projects_count]` - Display the total number of all projects

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce (active and installed)
- WooCommerce Subscriptions (optional, for subscription tracking)

## Installation

1. Download the plugin zip file
2. Go to your WordPress admin panel
3. Navigate to Plugins > Add New
4. Click on "Upload Plugin" and select the downloaded zip file
5. Click "Install Now"
6. After installation, click "Activate"

## Configuration

After activation, you can find the plugin settings in your WordPress admin panel under the "Projects" menu. Configure your project settings and ensure WooCommerce integration is properly set up.

## Usage

1. Create a new project through the WordPress admin panel
2. Assign WooCommerce orders to your project
3. Link WooCommerce subscriptions to your project (if using WooCommerce Subscriptions)
4. View all related orders and subscriptions on the project's dedicated page
5. Track subscription renewals and child orders automatically
6. Monitor project revenue through the integrated WooCommerce reporting

### Shortcode Examples

Display a grid of projects:
```
[arsol_projects limit="10" columns="3" orderby="date" order="DESC" pagination="yes" category=""]
```
Arguments:
- `limit` (default: 10) - Number of projects to display
- `columns` (default: 3) - Number of columns in the grid
- `orderby` (default: "date") - Order by field (date, title, etc.)
- `order` (default: "DESC") - Sort order (ASC or DESC)
- `pagination` (default: "yes") - Whether to show pagination
- `category` (default: "") - Category slug to filter projects

Show a single project:
```
[arsol_project id="123"]
```
Arguments:
- `id` (required) - The ID of the project to display

Display project categories:
```
[arsol_project_categories limit="-1" orderby="name" order="ASC" parent="" hide_empty="no"]
```
Arguments:
- `limit` (default: -1) - Number of categories to display (-1 for all)
- `orderby` (default: "name") - Order by field (name, count, etc.)
- `order` (default: "ASC") - Sort order (ASC or DESC)
- `parent` (default: "") - Parent category ID
- `hide_empty` (default: "no") - Whether to hide empty categories

Show orders for a specific project:
```
[arsol_project_orders project_id="123" per_page="10" paged="1"]
```
Arguments:
- `project_id` (default: 0) - The ID of the project (0 to use current page)
- `per_page` (default: 10) - Number of orders per page
- `paged` (default: 1) - Current page number

Display subscriptions for a project:
```
[arsol_project_subscriptions project_id="123" per_page="10" paged="1"]
```
Arguments:
- `project_id` (default: 0) - The ID of the project (0 to use current page)
- `per_page` (default: 10) - Number of subscriptions per page
- `paged` (default: 1) - Current page number

Show the number of projects for the current user:
```
[arsol_user_projects_count]
```
No arguments required.

Show the total number of all projects:
```
[arsol_projects_count]
```
No arguments required.

## Support

For support, please visit [Plugin Support Page](https://your-site.com/arsol-projects-for-woo)

## License

This plugin is licensed under the GPL v2 or later.

## Author

- **Author:** Taf Makura
- **Website:** [https://your-site.com](https://your-site.com)

## Changelog

### Version 0.0.7
- Initial release

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

This plugin was developed by Taf Makura. 