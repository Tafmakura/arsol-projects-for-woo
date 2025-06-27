<?php
/**
 * Projects endpoint template
 * 
 * Handles the main /my-account/projects/ endpoint with tabs
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from the endpoint handler
// $current_tab, $query, $total_pages, $wp_button_class, $paged, $user_id should be available

// This template contains the projects listing content directly

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
// Get current tab from URL parameter
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'active';

// Get current user's account URL
$account_url = wc_get_account_endpoint_url('projects');

// Define tabs
$tabs = array(
    'active' => array(
        'label' => __('Active', 'arsol-pfw'),
        'url' => add_query_arg('tab', 'active', $account_url)
    ),
    'proposals' => array(
        'label' => __('Proposals', 'arsol-pfw'),
        'url' => add_query_arg('tab', 'proposals', $account_url)
    ),
    'requests' => array(
        'label' => __('Requests', 'arsol-pfw'),
        'url' => add_query_arg('tab', 'requests', $account_url)
    )
);
?>

<div class="arsol-project-navigation">
    <div class="arsol-button-container">
        <div class="arsol-button-groups">
            <?php foreach ($tabs as $tab_id => $tab) : ?>
                <button 
                    class="arsol-btn-secondary <?php echo $current_tab === $tab_id ? 'active' : ''; ?>" 
                    onclick="window.location.href='<?php echo esc_url($tab['url']); ?>'"
                >
                    <?php echo esc_html($tab['label']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php

// --- Main Content ---

?>
<div class="woocommerce-MyAccount-content">
    <?php
    switch ($current_tab) {
        case 'proposals':
            if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_template_override('project_proposal_listings')) {
                echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_template_override('project_proposal_listings');
            } else {
                echo do_shortcode('[arsol_pfw_projects_listing_proposals]');
            }
            break;
        case 'requests':
            if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_template_override('project_requests_listings')) {
                echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_template_override('project_requests_listings');
            } else {
                echo do_shortcode('[arsol_pfw_projects_listing_requests]');
            }
            break;
        case 'active':
        default:
            if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_template_override('projects_listing')) {
                echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_template_override('projects_listing');
            } else {
                echo do_shortcode('[arsol_pfw_projects_listing_active]');
            }
            break;
    }
    ?>
</div> 