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
[arsol_projects limit="10" columns="3" pagination="yes"]
```

Show orders for a specific project:
```
[arsol_project_orders project_id="123" per_page="10"]
```

Display subscriptions for a project:
```
[arsol_project_subscriptions project_id="123" per_page="10"]
```

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