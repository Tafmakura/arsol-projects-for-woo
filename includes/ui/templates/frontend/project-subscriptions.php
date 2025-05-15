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

<?php if ($has_subscriptions) : ?>

    <table class="woocommerce-orders-table project-subscriptions-table shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <th scope="col" class="subscription-id"><span class="nobr"><?php esc_html_e('Subscription', 'arsol-projects-for-woo'); ?></span></th>
                <th scope="col" class="subscription-next-payment"><span class="nobr"><?php esc_html_e('Next Payment', 'arsol-projects-for-woo'); ?></span></th>
                <th scope="col" class="subscription-status"><span class="nobr"><?php esc_html_e('Status', 'arsol-projects-for-woo'); ?></span></th>
                <th scope="col" class="subscription-total"><span class="nobr"><?php esc_html_e('Total', 'arsol-projects-for-woo'); ?></span></th>
                <th scope="col" class="subscription-actions"><span class="nobr"><?php esc_html_e('Actions', 'arsol-projects-for-woo'); ?></span></th>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach ($customer_subscriptions->subscriptions as $subscription_id) {
                $subscription = wcs_get_subscription($subscription_id);
                ?>
                <tr class="woocommerce-orders-table__row project-subscriptions-table__row--status-<?php echo esc_attr($subscription->get_status()); ?> subscription">
                    <th class="subscription-id" scope="row">
                        <a href="<?php echo esc_url($subscription->get_view_order_url()); ?>">
                            <?php echo esc_html(_x('#', 'hash before subscription number', 'arsol-projects-for-woo') . $subscription->get_id()); ?>
                        </a>
                    </th>
                    <td class="subscription-next-payment">
                        <?php
                        $next_payment = $subscription->get_date('next_payment');
                        if (!empty($next_payment)) {
                            echo esc_html(date_i18n(get_option('date_format'), strtotime($next_payment)));
                        } else {
                            echo esc_html__('N/A', 'arsol-projects-for-woo');
                        }
                        ?>
                    </td>
                    <td class="subscription-status">
                        <?php echo esc_html(wcs_get_subscription_status_name($subscription->get_status())); ?>
                    </td>
                    <td class="subscription-total">
                        <?php echo wp_kses_post($subscription->get_formatted_order_total()); ?>
                    </td>
                    <td class="subscription-actions">
                        <?php
                        $actions = wcs_get_all_user_actions_for_subscription($subscription, get_current_user_id());
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

    <?php do_action('arsol_projects_before_project_subscriptions_pagination'); ?>

    <?php if (1 < $customer_subscriptions->max_num_pages) : ?>
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
            <?php if (1 !== $current_page) : ?>
                <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(add_query_arg('page', $current_page - 1)); ?>"><?php esc_html_e('Previous', 'arsol-projects-for-woo'); ?></a>
            <?php endif; ?>

            <?php if (intval($customer_subscriptions->max_num_pages) !== $current_page) : ?>
                <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(add_query_arg('page', $current_page + 1)); ?>"><?php esc_html_e('Next', 'arsol-projects-for-woo'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php else : ?>

    <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
        <?php esc_html_e('No subscriptions found for this project.', 'arsol-projects-for-woo'); ?>
    </div>

<?php endif; ?>

<?php do_action('arsol_projects_after_project_subscriptions', $has_subscriptions, $project_id); ?>