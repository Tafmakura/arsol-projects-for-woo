<?php
/**
 * Template for displaying related project details on order and subscription pages
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Variables that should be passed to this template:
 * 
 * @var string $project_name The project name
 * @var bool $has_link Whether the project has a valid link
 * @var int $project_id The project ID (if available)
 * @var bool $is_from_parent Whether this project is from a parent order
 * @var string $order_url URL to parent order (if applicable)
 * @var string $parent_order_number Parent order number (if applicable)
 */
?>
<header class="arsol-pfw-header">
    <h2><?php esc_html_e('Related Project', 'arsol-pfw'); ?></h2>
</header>
<table class="shop_table shop_table_responsive my_account_orders woocommerce-orders-table woocommerce-MyAccount-subscriptions woocommerce-orders-table--subscriptions arsol-pfw-projects-row">
    <thead>
        <tr>
            <th class="arsol-pfw-project-name woocommerce-orders-table__header woocommerce-orders-table__header-project-name"><span class="nobr"><?php esc_html_e('Project name', 'arsol-pfw'); ?></span></th>
            <th class="arsol-pfw-project-actions order-actions woocommerce-orders-table__header woocommerce-orders-table__header-order-actions woocommerce-orders-table__header-project-actions">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr class="woocommerce-orders-table__row <?php echo $has_link ? 'arsol-pfw-status-active' : 'arsol-pfw-status-inactive'; ?>">
            <td class="woocommerce-orders-table__cell arsol-pfw-project-cell" data-title="<?php esc_attr_e('Project', 'arsol-pfw'); ?>">
                <?php if ($has_link) : ?>
                    <a href="<?php echo esc_url(get_permalink($project_id)); ?>" class="arsol-pfw-project-link">
                        <?php echo esc_html($project_name); ?>
                    </a>
                <?php else : ?>
                    <span class="arsol-pfw-project-name-text"><?php echo esc_html($project_name); ?></span>
                <?php endif; ?>
                
                <?php if ($is_from_parent): ?>
                    <span class="arsol-pfw-parent-info"><?php esc_html_e('From parent order', 'arsol-pfw'); ?> 
                    <a href="<?php echo esc_url($order_url); ?>" class="arsol-pfw-parent-link">#<?php echo esc_html($parent_order_number); ?></a></span>          
                <?php endif; ?>
                <br>
                <span>dsfsdf<?php echo wp_kses_post(get_the_excerpt()); ?></span>
            </td>
            <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions arsol-pfw-actions-cell">
                <?php if ($has_link) : ?>
                    <a href="<?php echo esc_url(get_permalink($project_id)); ?>" class="woocommerce-button button view arsol-pfw-view-button">
                        <?php esc_html_e('View', 'arsol-pfw'); ?>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>