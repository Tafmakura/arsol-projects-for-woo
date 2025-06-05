<?php
/**
 * Plugin Name: Arsol Projects for Woo
 * Plugin URI: https://your-site.com/arsol-projects-for-woo
 * Description: A WordPress plugin to manage projects with WooCommerce integration
 * Version: 0.0.9.2
 * Requires at least: 5.8
 * Requires PHP: 7.4.1
 * Requires Plugins: woocommerce
 * Author: Taf Makura
 * Author URI: https://your-site.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: arsol-pfw
 * Domain Path: /languages
 * 
 * @package Arsol_Projects_For_Woo
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ARSOL_PROJECTS_PLUGIN_FILE', __FILE__);
define('ARSOL_PROJECTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ARSOL_PROJECTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ARSOL_PROJECTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Use correct namespace
use Arsol_Projects_For_Woo\Setup;

// Include the Setup class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-setup.php';

// Include the admin settings class
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-general.php';

// Initialize Project Request Handler
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-project-request-handler.php';
new \Arsol_Projects_For_Woo\Project_Request_Handler();

// Initialize Project Proposal Handler
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-project-proposal-handler.php';
new \Arsol_Projects_For_Woo\Project_Proposal_Handler();

// Initialize Project Handler
require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-project-handler.php';
new \Arsol_Projects_For_Woo\Project_Handler();

// Register activation hook
register_activation_hook(__FILE__, 'arsol_projects_activate');

/**
 * Plugin activation function
 */
function arsol_projects_activate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Add form submission handlers
add_action('admin_post_arsol_create_project_request', 'arsol_handle_project_request_submission');
add_action('admin_post_nopriv_arsol_create_project_request', 'arsol_handle_project_request_submission');

/**
 * Handle project request form submission
 */
function arsol_handle_project_request_submission() {
    // Verify nonce
    if (!isset($_POST['arsol_project_request_nonce']) || !wp_verify_nonce($_POST['arsol_project_request_nonce'], 'arsol_create_project_request')) {
        wp_die(__('Security check failed', 'arsol-pfw'));
    }

    // Get current user
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_die(__('You must be logged in to submit a project request', 'arsol-pfw'));
    }

    // Check permissions
    if (!\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_requests($user_id)) {
        wp_die(__('You do not have permission to create project requests', 'arsol-pfw'));
    }

    // Get and sanitize form data
    $title = isset($_POST['request_title']) ? sanitize_text_field($_POST['request_title']) : '';
    $description = isset($_POST['request_description']) ? wp_kses_post($_POST['request_description']) : '';
    $budget = isset($_POST['request_budget']) ? sanitize_text_field($_POST['request_budget']) : '';
    $timeline = isset($_POST['request_timeline']) ? sanitize_text_field($_POST['request_timeline']) : '';

    // Validate required fields
    if (empty($title) || empty($description)) {
        wc_add_notice(__('Please fill in all required fields', 'arsol-pfw'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('project-request'));
        exit;
    }

    // Create the project request
    $request_data = array(
        'post_title'    => $title,
        'post_content'  => $description,
        'post_status'   => 'pending',
        'post_type'     => 'arsol-pfw-request',
        'post_author'   => $user_id
    );

    $request_id = wp_insert_post($request_data);

    if (is_wp_error($request_id)) {
        wc_add_notice(__('Error creating project request. Please try again.', 'arsol-pfw'), 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('project-request'));
        exit;
    }

    // Add meta data
    if (!empty($budget)) {
        update_post_meta($request_id, '_arsol_request_budget', $budget);
    }
    if (!empty($timeline)) {
        update_post_meta($request_id, '_arsol_request_timeline', $timeline);
    }

    // Add success message
    wc_add_notice(__('Project request submitted successfully!', 'arsol-pfw'), 'success');

    // Redirect to the request view page
    wp_safe_redirect(wc_get_account_endpoint_url('project-view-request/' . $request_id));
    exit;
}

// Instantiate the Setup class
new Setup(); 