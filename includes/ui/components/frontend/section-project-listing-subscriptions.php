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

if (!\Arsol_Projects_For_Woo\Woocommerce_Subscriptions::is_plugin_active() || !$project_id) {
    if (!$project_id) {
        echo '<p>' . esc_html__('Project ID not found.', 'arsol-pfw') . '</p>';
    } else {
        echo '<p>' . esc_html__('WooCommerce Subscriptions is not active.', 'arsol-pfw') . '</p>';
    }
    return;
}

// Get paginated subscriptions for the project using centralized method
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$current_user_id = get_current_user_id();

// Use centralized subscription retrieval
$project_subscriptions = \Arsol_Projects_For_Woo\Woocommerce_Subscriptions::get_project_subscriptions(
    $project_id,
    $current_user_id,
    $paged,
    10
);

$has_subscriptions = !empty($project_subscriptions->subscriptions);

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
                foreach ($project_subscriptions->subscriptions as $subscription_id) {
                    $subscription = wcs_get_subscription($subscription_id);
                    if (!$subscription) continue;
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
                ?>
            </tbody>
        </table>

        <?php do_action('arsol_projects_before_project_subscriptions_pagination'); ?>

        <?php if ($project_subscriptions->max_num_pages > 1) : ?>
            <nav class="woocommerce-pagination">
                <?php
                echo paginate_links(array(
                    'base'      => str_replace(999999, '%#%', esc_url(get_pagenum_link(999999))),
                    'format'    => '?paged=%#%',
                    'current'   => max(1, $paged),
                    'total'     => $project_subscriptions->max_num_pages,
                    'type'      => 'plain',
                    'prev_text' => _x('&larr;', 'previous post', 'arsol-pfw'),
                    'next_text' => _x('&rarr;', 'next post', 'arsol-pfw'),
                ));
                ?>
            </nav>
        <?php endif; ?>

    <?php else : ?>

        <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
            <a class="woocommerce-Button button" href="<?php echo esc_url(apply_filters('arsol_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
                <?php esc_html_e('Browse products', 'arsol-pfw'); ?>
            </a>
            <?php esc_html_e('No subscriptions have been made for this project yet.', 'arsol-pfw'); ?>
        </div>

    <?php endif; ?>

    <?php do_action('arsol_projects_after_project_subscriptions', $has_subscriptions, $project_id); ?>
</div>