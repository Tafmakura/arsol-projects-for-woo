<?php
/**
 * User Projects Template
 *
 * Shows a list of projects associated with the current user.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_user_projects', $has_projects);
?>

<div class="woocommerce">
    <?php if ($has_projects) : ?>
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table user-projects-table">
            <thead>
                <tr>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-project-name"><span class="nobr"><?php esc_html_e('Name', 'arsol-projects-for-woo'); ?></span></th>
                    <th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-project-actions"><span class="nobr"></span></th>
                </tr>
            </thead>

            <tbody>
                <?php
                while ($projects_query->have_posts()) {
                    $projects_query->the_post();
                    $project_id = get_the_ID();
                    ?>
                    <tr class="woocommerce-orders-table__row project">
                        <th class="woocommerce-orders-table__cell woocommerce-orders-table__cell-project-name" data-title="<?php esc_attr_e('Name', 'arsol-projects-for-woo'); ?>" scope="row">
                            <a href="<?php echo esc_url(get_permalink($project_id)); ?>" aria-label="<?php echo esc_attr(sprintf(__('View project %s', 'arsol-projects-for-woo'), get_the_title())); ?>">
                                <?php echo esc_html(get_the_title()); ?>
                            </a>
                        </th>
                        <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-project-actions" data-title="">
                            <a href="<?php echo esc_url(get_permalink($project_id)); ?>" class="woocommerce-button button view<?php echo esc_attr($wp_button_class); ?>"><?php esc_html_e('View', 'arsol-projects-for-woo'); ?></a>
                        </td>
                    </tr>
                    <?php
                }
                wp_reset_postdata();
                ?>
            </tbody>
        </table>

        <?php do_action('arsol_projects_before_user_projects_pagination'); ?>

        <?php if ($total_pages > 1) : ?>
            <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
                <?php 
                // Get current URL and preserve existing query args
                $current_url = remove_query_arg('paged');
                ?>
                
                <?php if (1 !== $current_page) : ?>
                    <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(add_query_arg('paged', $current_page - 1, $current_url)); ?>"><?php esc_html_e('Previous', 'arsol-projects-for-woo'); ?></a>
                <?php endif; ?>

                <?php if ($total_pages !== $current_page) : ?>
                    <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr($wp_button_class); ?>" href="<?php echo esc_url(add_query_arg('paged', $current_page + 1, $current_url)); ?>"><?php esc_html_e('Next', 'arsol-projects-for-woo'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else : ?>

        <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
            <?php esc_html_e('No projects found.', 'arsol-projects-for-woo'); ?>
        </div>

    <?php endif; ?>
</div>

<?php do_action('arsol_projects_after_user_projects', $has_projects); ?>