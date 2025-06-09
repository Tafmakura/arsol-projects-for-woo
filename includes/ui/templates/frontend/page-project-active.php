<?php
/**
 * Main Project Page Template
 *
 * This template provides the main structure for a single project page,
 * including the header, navigation, and the main content area. It acts
 * as a frame for the different project sections like overview, orders, etc.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.2
 */

if (!defined('ABSPATH')) {
    exit;
}

// The following variables are passed from the render_project_page function:
// $project, $project_id, $tab
$project_title = get_the_title($project_id);
$current_tab = $tab;

// --- Render Page Navigation ---
$tabs = array(
    'overview' => array('label' => __('Overview', 'arsol-pfw'), 'url' => wc_get_account_endpoint_url('project-overview/' . $project_id)),
    'orders' => array('label' => __('Orders', 'woocommerce'), 'url' => wc_get_account_endpoint_url('project-orders/' . $project_id))
);

// Only add subscriptions tab if WooCommerce Subscriptions is active
if (\Arsol_Projects_For_Woo\Woocommerce_Subscriptions::is_plugin_active()) {
    $tabs['subscriptions'] = array('label' => __('Subscriptions', 'woocommerce-subscriptions'), 'url' => wc_get_account_endpoint_url('project-subscriptions/' . $project_id));
}
?>
<div class="arsol-project-intro">
    <p>
        <?php 
        // Create intro text based on available features
        if (\Arsol_Projects_For_Woo\Woocommerce_Subscriptions::is_plugin_active()) {
            // Full intro with subscriptions
            echo sprintf(
                esc_html__('This is your %s project dashboard. The %s tab shows project details, the %s tab displays your project %s, and the %s tab displays all your project %s.', 'arsol-pfw'),
                '<strong>' . esc_html($project_title) . '</strong>',
                '<strong>' . esc_html__('Overview', 'arsol-pfw') . '</strong>',
                '<strong>' . esc_html__('Orders', 'woocommerce') . '</strong>',
                esc_html__('orders', 'woocommerce'),
                '<strong>' . esc_html__('Subscriptions', 'woocommerce-subscriptions') . '</strong>',
                esc_html__('subscriptions', 'woocommerce-subscriptions')
            );
        } else {
            // Simplified intro without subscriptions
            echo sprintf(
                esc_html__('This is your %s project dashboard. The %s tab shows project details and the %s tab displays your project %s.', 'arsol-pfw'),
                '<strong>' . esc_html($project_title) . '</strong>',
                '<strong>' . esc_html__('Overview', 'arsol-pfw') . '</strong>',
                '<strong>' . esc_html__('Orders', 'woocommerce') . '</strong>',
                esc_html__('orders', 'woocommerce')
            );
        }
        ?>
    </p>
</div>
<div class="arsol-project-navigation">
    <div class="arsol-button-container">
        <div class="arsol-button-groups">
            <?php foreach ($tabs as $tab_id => $tab_data) : ?>
                <button class="arsol-btn-secondary arsol-project-btn <?php echo $current_tab === $tab_id ? 'active' : ''; ?>" 
                        onclick="window.location.href='<?php echo esc_url($tab_data['url']); ?>'">
                    <?php echo esc_html($tab_data['label']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
// --- Render Page Content ---
switch ($tab) {
    case 'orders':
        \Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
            'project_orders',
            ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-listing-orders.php',
            compact('project')
        );
        break;
    case 'subscriptions':
        // Only render subscriptions if WooCommerce Subscriptions is active
        if (\Arsol_Projects_For_Woo\Woocommerce_Subscriptions::is_plugin_active()) {
            \Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
                'project_subscriptions',
                ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-listing-subscriptions.php',
                compact('project')
            );
        } else {
            // Redirect to overview if subscriptions plugin is not active
            wp_safe_redirect(wc_get_account_endpoint_url('project-overview/' . $project_id));
            exit;
        }
        break;
    default:
        \Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
            'project_content',
            ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project.php',
            compact('project')
        );
        break;
}
?> 