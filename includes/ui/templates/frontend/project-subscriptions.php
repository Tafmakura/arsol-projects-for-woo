<?php
/**
 * Project Subscriptions
 *
 * Shows subscriptions associated with a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_project_subscriptions', $has_subscriptions, $project_id); ?>

<div class="woocommerce">
    <?php if ($has_subscriptions) : ?>

        <table class="woocommerce-orders-table woocommerce-MyAccount-orders project-subscriptions-table shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
                <tr>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-subscription-number"><span class="nobr"><?php esc_html_e('Subscription', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-subscription-status"><span class="nobr"><?php esc_html_e('Status', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-subscription-total"><span class="nobr"><?php esc_html_e('Total', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-subscription-actions"><span class="nobr"></span></th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($customer_subscriptions->subscriptions as $subscription_id) {
                    $subscription = wcs_get_subscription($subscription_id);
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
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-subscription-actions" data-title="">
                            <a href="<?php echo esc_url($subscription->get_view_order_url()); ?>" class="woocommerce-button button view"><?php esc_html_e('View', 'arsol-projects-for-woo'); ?></a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <?php do_action('arsol_projects_before_project_subscriptions_pagination'); ?>

        <?php if (1 < $customer_subscriptions->max_num_pages) : ?>
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

                <?php if (intval($customer_subscriptions->max_num_pages) !== $current_page) : ?>
                    <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(add_query_arg('paged', $current_page + 1, $current_url)); ?>"><?php esc_html_e('Next', 'arsol-projects-for-woo'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else : ?>

        <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
            <?php esc_html_e('No subscriptions found for this project.', 'arsol-projects-for-woo'); ?>
        </div>

    <?php endif; ?>
</div>

<?php do_action('arsol_projects_after_project_subscriptions', $has_subscriptions, $project_id); ?>