<?php
/**
 * Project Subscriptions Content
 *
 * Shows subscriptions associated with a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// The $project variable is passed from the page template
$project_id = isset($project['id']) ? $project['id'] : 0;

if (!class_exists('WC_Subscriptions') || !$project_id) {
    if (!$project_id) {
        echo '<p>' . esc_html__('Project ID not found.', 'arsol-pfw') . '</p>';
    } else {
        echo '<p>' . esc_html__('WooCommerce Subscriptions is not active.', 'arsol-pfw') . '</p>';
    }
    return;
}

// Get paginated subscriptions for the project.
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args = array(
    'post_type' => 'shop_subscription',
    'post_status' => 'any',
    'meta_key' => '_arsol_project_id',
    'meta_value' => $project_id,
    'posts_per_page' => 10,
    'paged' => $paged,
    'author' => get_current_user_id(),
);
$customer_subscriptions = new WP_Query($args);
$has_subscriptions = $customer_subscriptions->have_posts();

do_action('arsol_projects_before_project_subscriptions', $has_subscriptions, $project_id);
?>

<div class="woocommerce">
    <?php if ($has_subscriptions) : ?>

        <table class="woocommerce-orders-table woocommerce-MyAccount-orders project-subscriptions-table shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
                <tr>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-subscription-number"><span class="nobr"><?php esc_html_e('Subscription', 'woocommerce-subscriptions'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-subscription-status"><span class="nobr"><?php esc_html_e('Status', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-subscription-total"><span class="nobr"><?php esc_html_e('Total', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-subscription-actions"><span class="nobr"></span></th>
                </tr>
            </thead>

            <tbody>
                <?php
                while ($customer_subscriptions->have_posts()) {
                    $customer_subscriptions->the_post();
                    $subscription = wcs_get_subscription(get_the_ID());
                    ?>
                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($subscription->get_status()); ?> subscription">
                        <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-number" data-title="<?php esc_attr_e('Subscription', 'arsol-projects-for-woo'); ?>" scope="row">
                            <a href="<?php echo esc_url($subscription->get_view_order_url()); ?>" aria-label="<?php echo esc_attr(sprintf(__('View subscription number %s', 'arsol-projects-for-woo'), $subscription->get_id())); ?>">
                                <?php echo esc_html(_x('#', 'hash before subscription number', 'arsol-projects-for-woo') . $subscription->get_id()); ?>
                            </a>
                        </th>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-status" data-title="<?php esc_attr_e('Status', 'arsol-projects-for-woo'); ?>">
                            <?php echo esc_html(wcs_get_subscription_status_name($subscription->get_status())); ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-total" data-title="<?php esc_attr_e('Total', 'arsol-projects-for-woo'); ?>">
                            <?php echo wp_kses_post($subscription->get_formatted_order_total()); ?>
                        </td>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-actions woocommerce-orders-table__cell-ars_order_actions" data-title="">
                            <a href="<?php echo esc_url($subscription->get_view_order_url()); ?>" class="woocommerce-button button view"><?php esc_html_e('View', 'arsol-projects-for-woo'); ?></a>
                        </td>
                    </tr>
                    <?php
                }
                wp_reset_postdata();
                ?>
            </tbody>
        </table>

        <?php do_action('arsol_projects_before_project_subscriptions_pagination'); ?>

        <?php
        // Manually create pagination links
        $total_pages = $customer_subscriptions->max_num_pages;

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
            <?php esc_html_e('No subscriptions found for this project.', 'woocommerce-subscriptions'); ?>
        </div>

    <?php endif; ?>
</div>

<?php do_action('arsol_projects_after_project_subscriptions', $has_subscriptions, $project_id); ?>