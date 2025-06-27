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
    'meta_key' => ARSOL_PROJECT_META_KEY,
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
                    <th class="subscription-id"><span class="nobr"><?php esc_html_e('Subscription', 'woocommerce-subscriptions'); ?></span></th>
                    <th class="subscription-status"><span class="nobr"><?php esc_html_e('Status', 'arsol-pfw'); ?></span></th>
                    <th class="subscription-total"><span class="nobr"><?php esc_html_e('Total', 'arsol-pfw'); ?></span></th>
                    <th class="subscription-actions"><span class="nobr"></span></th>
                </tr>
            </thead>

            <tbody>
                <?php
                while ($customer_subscriptions->have_posts()) {
                    $customer_subscriptions->the_post();
                    $subscription = wcs_get_subscription(get_the_ID());
                    ?>
                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($subscription->get_status()); ?> subscription">
                        <td class="subscription-id" data-title="<?php esc_attr_e('Subscription', 'arsol-pfw'); ?>" scope="row">
                            <a href="<?php echo esc_url($subscription->get_view_order_url()); ?>" aria-label="<?php echo esc_attr(sprintf(__('View subscription number %s', 'arsol-pfw'), $subscription->get_id())); ?>">
                                <?php echo esc_html(_x('#', 'hash before subscription number', 'arsol-pfw') . $subscription->get_id()); ?>
                            </a>
                        </td>
                        <td class="subscription-status" data-title="<?php esc_attr_e('Status', 'arsol-pfw'); ?>">
                            <?php echo esc_html(wcs_get_subscription_status_name($subscription->get_status())); ?>
                        </td>
                        <td class="subscription-total" data-title="<?php esc_attr_e('Total', 'arsol-pfw'); ?>">
                            <?php echo wp_kses_post($subscription->get_formatted_order_total()); ?>
                        </td>
                        <td class="subscription-actions">
                            <a href="<?php echo esc_url($subscription->get_view_order_url()); ?>" class="woocommerce-button button view"><?php esc_html_e('View', 'arsol-pfw'); ?></a>
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