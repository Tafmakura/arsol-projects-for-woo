<?php
/**
 * Project Orders
 *
 * Shows orders associated with a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_project_orders', $has_orders, $project_id); ?>

<div class="woocommerce">
    <?php if ($has_orders) : ?>

        <table class="woocommerce-orders-table woocommerce-MyAccount-orders project-orders-table shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
                <tr>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?php esc_html_e('Invoice', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php esc_html_e('Status', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?php esc_html_e('Total', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-ars_order_actions"><span class="nobr"></span></th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($customer_orders->orders as $customer_order) {
                    $order = wc_get_order($customer_order);
                    $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                    ?>
                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
                        <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Invoice" scope="row">
                            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" aria-label="<?php echo esc_attr(sprintf(__('View order number %s', 'arsol-projects-for-woo'), $order->get_order_number())); ?>">
                                <?php echo esc_html(_x('#', 'hash before order number', 'arsol-projects-for-woo') . $order->get_order_number()); ?>
                            </a>
                        </th>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="Status">
                            <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total" data-title="Total">
                            <?php
                            /* translators: 1: formatted order total 2: total order items */
                            echo wp_kses_post(sprintf(_n('%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'arsol-projects-for-woo'), $order->get_formatted_order_total(), $item_count));
                            ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions woocommerce-orders-table__cell-ars_order_actions" data-title="">
                            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="woocommerce-button button view"><?php esc_html_e('View', 'arsol-projects-for-woo'); ?></a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <?php do_action('arsol_projects_before_project_orders_pagination'); ?>

        <?php if (1 < $customer_orders->max_num_pages) : ?>
            <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
                <?php 
                // Get current URL and preserve existing query args
                $current_url = remove_query_arg('paged');
                
                // Preserve project_id if it exists in shortcode attributes
                if (!empty($atts['project_id'])) {
                    $current_url = add_query_arg('project_id', $atts['project_id'], $current_url);
                }
                ?>
                
                <?php if (1 !== $current_page) : ?>
                    <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(add_query_arg('paged', $current_page - 1, $current_url)); ?>"><?php esc_html_e('Previous', 'arsol-projects-for-woo'); ?></a>
                <?php endif; ?>

                <?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
                    <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(add_query_arg('paged', $current_page + 1, $current_url)); ?>"><?php esc_html_e('Next', 'arsol-projects-for-woo'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else : ?>

        <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
            <?php esc_html_e('No orders found for this project.', 'woocommerce-subscriptions'); ?>
        </div>

    <?php endif; ?>
</div>

<?php do_action('arsol_projects_after_project_orders', $has_orders, $project_id); ?>