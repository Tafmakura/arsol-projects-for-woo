<?php
/**
 * Project Navigation Component
 *
 * This template displays the navigation tabs for a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 *
 * @var string $project_title The title of the project
 * @var array $tabs The navigation tabs
 * @var string $current_tab The current active tab
 */

defined('ABSPATH') || exit;

 // Define tabs
 $tabs = array(
    'overview' => array(
        'label' => __('Overview', 'arsol-pfw'),
        'url' => wc_get_account_endpoint_url('project-overview/' . $project_id)
    ),
    'orders' => array(
        'label' => __('Orders', 'woocommerce'), // Using WooCommerce translation domain
        'url' => wc_get_account_endpoint_url('project-orders/' . $project_id)
    ),
    'subscriptions' => array(
        'label' => __('Subscriptions', 'woocommerce-subscriptions'), // Using WooCommerce Subscriptions translation domain
        'url' => wc_get_account_endpoint_url('project-subscriptions/' . $project_id)
    )
);

// Start output buffer
ob_start();

?>


<div class="arsol-project-intro">
    <p>
        <?php 
            echo sprintf(
                esc_html__('Welcome to your %s project dashboard. The %s tab shows project details, %s tab displays your %s, and %s tab displays all your ongoing %s plans.', 'arsol-pfw'),
                '<strong>' . esc_html($project_title) . '</strong>',
                '<strong>' . esc_html__('Overview', 'arsol-pfw') . '</strong>',
                '<strong>' . esc_html__('Orders', 'woocommerce') . '</strong>',
                esc_html__('orders', 'woocommerce'),
                '<strong>' . esc_html__('Subscriptions', 'woocommerce-subscriptions') . '</strong>',
                . esc_html__('subscription', 'woocommerce-subscriptions')
            );
        ?>
    </p>
</div>
<div class="arsol-project-navigation">
    <div class="arsol-button-container">
        <div class="arsol-button-groups">
            <?php foreach ($tabs as $tab_id => $tab) : ?>
                <button class="arsol-btn-secondary arsol-project-btn <?php echo $current_tab === $tab_id ? 'active' : ''; ?>" 
                        onclick="window.location.href='<?php echo esc_url($tab['url']); ?>'">
                    <?php echo esc_html($tab['label']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>