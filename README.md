# Arsol Projects for Woo

A WordPress plugin to manage projects, orders, and subscriptions with deep WooCommerce integration and a modern, modular admin UI.

---

## Description

**Arsol Projects for Woo** lets you organize, track, and manage WooCommerce orders and subscriptions within projects. It provides a centralized admin interface for project management, project statuses, and settings, with seamless integration into the WordPress and WooCommerce admin experience.

---

## Features

- Custom Post Type: **Project** (with "Arsol Projects for Woo" as the top-level admin menu)
- Project Statuses taxonomy for flexible workflow management
- Assign WooCommerce orders and subscriptions to projects
- Automatic tracking of subscription renewals and child orders
- Modern admin UI with filters for project status, lead, and customer
- "Settings" page for plugin configuration, now a submenu under the main menu
- Shortcodes for displaying project and order information on the frontend
- Modular codebase for easy maintenance and extension

---

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce (active and installed)
- WooCommerce Subscriptions (optional, for subscription tracking)

---

## Installation

1. Download the plugin zip file.
2. Go to your WordPress admin panel.
3. Navigate to **Plugins > Add New**.
4. Click **Upload Plugin** and select the zip file.
5. Click **Install Now** and then **Activate**.

---

## Admin Menu Structure

- **Arsol Projects for Woo** (clipboard icon)
  - **All Projects**: List and manage all projects
  - **Add New Project**: Create a new project
  - **Project Statuses**: Manage project status taxonomy
  - **Settings**: Configure plugin options

---

## Configuration

After activation, visit **Arsol Projects for Woo > Settings** to configure plugin options.  
You can manage projects, assign orders/subscriptions, and define custom statuses from the same menu.

---

## Usage

1. **Create a Project**: Go to **All Projects** or **Add New Project**.
2. **Assign Orders/Subscriptions**: Link WooCommerce orders and subscriptions to projects.
3. **Manage Statuses**: Use the **Project Statuses** submenu to define and organize project workflows.
4. **Configure Settings**: Use the **Settings** submenu for plugin options.
5. **Frontend Shortcodes**: Use the provided shortcodes to display project data on your site.

---

## Shortcodes

- `[arsol_projects]` – Display a grid of projects
- `[arsol_project id="123"]` – Show a single project
- `[arsol_project_categories]` – List project categories
- `[arsol_project_orders project_id="123"]` – Show orders for a project
- `[arsol_project_subscriptions project_id="123"]` – Show subscriptions for a project
- `[arsol_user_projects]` – List projects for the current user
- `[arsol_user_projects_count]` – Number of active projects for the current user
- `[arsol_projects_count]` – Total number of all active projects

See the "Shortcode Examples" section below for usage and arguments.

---

## Support

For support, please visit the [Plugin Support Page](https://your-site.com/arsol-projects-for-woo).

---

## License

This plugin is licensed under the GPL v2 or later.

---

## Author

- **Author:** Taf Makura
- **Website:** [https://your-site.com](https://your-site.com)

---

## Changelog

### Version 0.0.8.1
- Major admin UI refactor: modular classes, WooCommerce-style filters, and settings as submenu
- Improved taxonomy and menu structure
- Bug fixes and code cleanup

### Version 0.0.7
- Initial release

---

## Contributing

Contributions are welcome! Please submit a Pull Request.

---

## Credits

This plugin was developed by Taf Makura. 