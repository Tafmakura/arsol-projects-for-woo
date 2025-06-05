<?php
/**
 * Project Proposals Template
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$has_items = $query->have_posts();
$total_pages = $query->max_num_pages;
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? 
    ' ' . wc_wp_theme_get_element_class_name('button') : '';

do_action('arsol_projects_before_user_proposals', $has_items);
?>

<div class="arsol-project-proposals">
    <?php if ($has_items) : ?>
        <table class="woocommerce-projects-table shop_table shop_table_responsive">
            <thead>
                <tr>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-title"><?php _e('Title', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-status"><?php _e('Status', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-budget"><?php _e('Budget', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-timeline"><?php _e('Timeline', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-date"><?php _e('Date', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-actions"><?php _e('Actions', 'arsol-pfw'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query->have_posts()) : $query->the_post(); 
                    $proposal_id = get_the_ID();
                    $status = wp_get_post_terms($proposal_id, 'arsol-proposal-status', array('fields' => 'names'));
                    $status = !empty($status) ? $status[0] : '';
                    $budget = get_post_meta($proposal_id, '_proposal_budget', true);
                    $timeline = get_post_meta($proposal_id, '_proposal_timeline', true);
                    $view_url = wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id);
                ?>
                    <tr class="woocommerce-projects-table__row">
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-title" data-title="<?php _e('Title', 'arsol-pfw'); ?>">
                            <a href="<?php echo esc_url($view_url); ?>" class="project-title-link">
                                <?php the_title(); ?>
                            </a>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-status" data-title="<?php _e('Status', 'arsol-pfw'); ?>"><?php echo esc_html($status); ?></td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-budget" data-title="<?php _e('Budget', 'arsol-pfw'); ?>"><?php echo esc_html($budget); ?></td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-timeline" data-title="<?php _e('Timeline', 'arsol-pfw'); ?>"><?php echo esc_html($timeline); ?></td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-date" data-title="<?php _e('Date', 'arsol-pfw'); ?>"><?php echo get_the_date(); ?></td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-actions" data-title="<?php _e('Actions', 'arsol-pfw'); ?>">
                            <a href="<?php echo esc_url($view_url); ?>" class="button<?php echo esc_attr($wp_button_class); ?>">
                                <?php _e('View', 'arsol-pfw'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1) : ?>
            <div class="woocommerce-pagination woocommerce-pagination--without-numbers">
                <?php if ($paged > 1) :
                    $prev_url = ($paged > 2) ? trailingslashit(wc_get_account_endpoint_url('projects')) . 'page/' . ($paged - 1) . '/' : wc_get_account_endpoint_url('projects');
                ?>
                    <a class="woocommerce-button woocommerce-button--previous button" href="<?php echo esc_url( add_query_arg( 'tab', 'proposals', $prev_url ) ); ?>"><?php esc_html_e('Previous', 'woocommerce'); ?></a>
                <?php endif; ?>

                <?php if ($paged < $total_pages) :
                    $next_url = trailingslashit(wc_get_account_endpoint_url('projects')) . 'page/' . ($paged + 1) . '/';
                ?>
                    <a class="woocommerce-button woocommerce-button--next button" href="<?php echo esc_url( add_query_arg( 'tab', 'proposals', $next_url ) ); ?>"><?php esc_html_e('Next', 'woocommerce'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <p class="woocommerce-info"><?php _e('No proposals found.', 'arsol-pfw'); ?></p>
    <?php endif; ?>
</div>

<?php
wp_reset_postdata();
do_action('arsol_projects_after_user_proposals', $has_items);
?> 