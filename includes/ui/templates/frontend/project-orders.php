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

<?php if ($has_orders) : ?>

    <table class="woocommerce-orders-table project-orders-table shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <th scope="col" class="order-number"><span class="nobr"><?php esc_html_e('Order', 'arsol-projects-for-woo'); ?></span></th>
                <th scope="col" class="order-date"><span class="nobr"><?php esc_html_e('Date', 'arsol-projects-for-woo'); ?></span></th>
                <th scope="col" class="order-status"><span class="nobr"><?php esc_html_e('Status', 'arsol-projects-for-woo'); ?></span></th>
                <th scope="col" class="order-total"><span class="nobr"><?php esc_html_e('Total', 'arsol-projects-for-woo'); ?></span></th>
                <th scope="col" class="order-actions"><span class="nobr"><?php esc_html_e('Actions', 'arsol-projects-for-woo'); ?></span></th>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach ($customer_orders->orders as $customer_order) {
                $order = wc_get_order($customer_order);
                $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                ?>
                <tr class="woocommerce-orders-table__row project-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
                    <th class="order-number" scope="row">
                        <a href="<?php echo esc_url($order->get_view_order_url()); ?>">
                            <?php echo esc_html(_x('#', 'hash before order number', 'arsol-projects-for-woo') . $order->get_order_number()); ?>
                        </a>
                    </th>
                    <td class="order-date">
                        <time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></time>
                    </td>
                    <td class="order-status">
                        <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                    </td>
                    <td class="order-total">
                        <?php
                        /* translators: 1: formatted order total 2: total order items */
                        echo wp_kses_post(sprintf(_n('%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'arsol-projects-for-woo'), $order->get_formatted_order_total(), $item_count));
                        ?>
                    </td>
                    <td class="order-actions">
                        <?php
                        $actions = wc_get_account_orders_actions($order);

                        if (!empty($actions)) {
                            foreach ($actions as $key => $action) {
                                echo '<a href="' . esc_url($action['url']) . '" class="woocommerce-button' . esc_attr($wp_button_class) . ' button ' . sanitize_html_class($key) . '">' . esc_html($action['name']) . '</a>';
                            }
                        }
                        ?>
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
        <?php esc_html_e('No orders found for this project.', 'arsol-projects-for-woo'); ?>
    </div>

<?php endif; ?>

<?php do_action('arsol_projects_after_project_orders', $has_orders, $project_id); ?>