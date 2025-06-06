<?php
/**
 * User Projects Page (Master Template)
 *
 * This template provides the main structure for the projects, proposals,
 * and requests pages, including navigation, content, and sidebar areas.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The following variables are passed from the endpoint function:
// $current_tab, $query, $paged, $total_pages, $wp_button_class

// --- Create/Request Buttons ---
$user_id = get_current_user_id();
$admin_users = new \Arsol_Projects_For_Woo\Admin\Users();
$can_create = $admin_users->can_user_create_projects($user_id);
$can_request = $admin_users->can_user_request_projects($user_id);

$button_url = '';
$button_label = '';
if ($can_create) {
    $button_url = wc_get_account_endpoint_url('project-create');
    $button_label = __('+ Create Project', 'arsol-pfw');
} elseif ($can_request) {
    $button_url = wc_get_account_endpoint_url('project-request');
    $button_label = __('+ Request Project', 'arsol-pfw');
}

if ($button_url && $button_label): ?>
    <div class="arsol-table-header-button">
        <a href="<?php echo esc_url($button_url); ?>" class="arsol-create-or-request-button button">
            <?php echo esc_html($button_label); ?>
        </a>
    </div>
<?php endif;

// --- Navigation ---
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-projects-navigation.php';

// --- Main Content ---
switch ($current_tab) {
    case 'proposals':
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-projects-content-proposals.php';
        break;
    case 'requests':
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-projects-content-requests.php';
        break;
    case 'active':
    default:
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-projects-content-active.php';
        break;
}