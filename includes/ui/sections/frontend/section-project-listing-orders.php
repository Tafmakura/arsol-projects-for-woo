<?php
/**
 * Project Orders Content
 *
 * Shows orders associated with a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// The $project variable is passed from the page template
$project_id = isset($project['id']) ? $project['id'] : 0;

if (!$project_id) {
    echo '<p>' . esc_html__('Project ID not found.', 'arsol-pfw') . '</p>';
    return;
}

// Get paginated orders for the project.
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$orders = wc_get_orders(array(
    'meta_key' => ARSOL_PROJECT_META_KEY,
    'meta_value' => $project_id,
    'limit' => $per_page,
    'offset' => ($current_page - 1) * $per_page,
    'customer' => get_current_user_id(),
    'orderby' => 'date',
    'order' => 'DESC'
));
$has_orders = !empty($orders);

do_action('arsol_projects_before_project_orders', $has_orders, $project_id);
?>

<div class="woocommerce">
    <?php if ($has_orders) : ?>
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
                <tr>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?php esc_html_e('Order', 'woocommerce'); ?></span></th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-date"><span class="nobr"><?php esc_html_e('Date', 'woocommerce'); ?></span></th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php esc_html_e('Status', 'arsol-pfw'); ?></span></th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?php esc_html_e('Total', 'arsol-pfw'); ?></span></th>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-order-actions"><span class="nobr"><?php esc_html_e('Actions', 'woocommerce'); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($orders as $order) {
                    $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                    ?>
                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="<?php esc_attr_e('Order', 'woocommerce'); ?>">
                            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" title="<?php esc_attr(sprintf(__('View order number %s', 'arsol-pfw'), $order->get_order_number())); ?>">
                                <?php echo esc_html(_x('#', 'hash before order number', 'arsol-pfw') . $order->get_order_number()); ?>
                            </a>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date" data-title="<?php esc_attr_e('Date', 'woocommerce'); ?>">
                            <time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></time>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="<?php esc_attr_e('Status', 'woocommerce'); ?>">
                            <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total" data-title="<?php esc_attr_e('Total', 'woocommerce'); ?>">
                            <?php
                            /* translators: 1: formatted order total 2: total order items */
                            echo wp_kses_post(sprintf(_n('%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'arsol-pfw'), $order->get_formatted_order_total(), $item_count));
                            ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="<?php esc_attr_e('Actions', 'woocommerce'); ?>">
                            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="woocommerce-button button view"><?php esc_html_e('View', 'arsol-pfw'); ?></a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <?php do_action('arsol_projects_before_project_orders_pagination'); ?>

        <?php
        // Manually create pagination links
        $total_pages = wc_get_orders(array('meta_key' => ARSOL_PROJECT_META_KEY, 'meta_value' => $project_id, 'paginate' => true, 'customer' => get_current_user_id()))->max_num_pages;

        if ($total_pages > 1) {
            echo '<nav class="woocommerce-pagination">';
            echo paginate_links(array(
                'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'format' => '?paged=%#%',
                'current' => max(1, $paged),
                'total' => $total_pages,
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type' => 'list',
            ));
            echo '</nav>';
        }
        ?>

    <?php else : ?>

        <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
            <?php esc_html_e('No orders found for this project.', 'woocommerce'); ?>
        </div>

    <?php endif; ?>
</div>

<?php do_action('arsol_projects_after_project_orders', $has_orders, $project_id); ?>