<?php
/**
 * Project Overview endpoint template
 *
 * Handles /my-account/project-overview/{project_id}/ endpoint
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The following variables are passed from the endpoint function:
// $project
$project_id = $project['id'];
$project_title = $project['title'];
$current_tab = 'overview';

// --- Render Page Navigation ---
$tabs = array(
    'overview' => array('label' => __('Overview', 'arsol-pfw'), 'url' => wc_get_account_endpoint_url('project-overview/' . $project_id)),
    'orders' => array('label' => __('Orders', 'woocommerce'), 'url' => wc_get_account_endpoint_url('project-orders/' . $project_id))
);

// Only add subscriptions tab if WooCommerce Subscriptions is active
if (class_exists('WC_Subscriptions')) {
    $tabs['subscriptions'] = array('label' => __('Subscriptions', 'woocommerce-subscriptions'), 'url' => wc_get_account_endpoint_url('project-subscriptions/' . $project_id));
}
?>
<div class="arsol-project-intro">
    <p>
        <?php 
        // Create intro text based on available features
        if (class_exists('WC_Subscriptions')) {
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
// Always render overview content for this template
\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'project_content',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project.php',
    compact('project')
);
?>
