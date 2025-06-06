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
$customer_orders = wc_get_orders(array(
    'meta_key' => '_arsol_project_id',
    'meta_value' => $project_id,
    'paged' => $paged,
    'customer' => get_current_user_id(),
));
$has_orders = !empty($customer_orders);

do_action('arsol_projects_before_project_orders', $has_orders, $project_id);
?>

<div class="woocommerce">
    <?php if ($has_orders) : ?>
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders project-orders-table shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
                <tr>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-number"><span class="nobr"><?php esc_html_e('Order', 'woocommerce'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-status"><span class="nobr"><?php esc_html_e('Status', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-order-total"><span class="nobr"><?php esc_html_e('Total', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-ars_order_actions"><span class="nobr"></span></th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($customer_orders as $customer_order) {
                    $order = wc_get_order($customer_order);
                    $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                    ?>
                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
                        <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="Order" scope="row">
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

        <?php
        // Manually create pagination links
        $total_pages = wc_get_orders(array('meta_key' => '_arsol_project_id', 'meta_value' => $project_id, 'paginate' => true, 'customer' => get_current_user_id()))->max_num_pages;

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