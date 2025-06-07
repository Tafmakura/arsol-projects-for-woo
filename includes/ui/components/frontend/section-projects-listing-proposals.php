<?php
/**
 * Projects Content: Proposals
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The following variables are passed from the master template:
// $query, $paged, $total_pages, $wp_button_class, $current_tab

$has_items = $query->have_posts();
do_action('arsol_projects_before_user_proposals', $has_items);
?>

<div class="arsol-project-proposals">
    <?php if ($has_items) : ?>
        <table class="woocommerce-projects-table shop_table shop_table_responsive">
            <thead>
                <tr>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-info"><?php _e('Proposal', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-actions">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query->have_posts()) : $query->the_post(); 
                    $proposal_id = get_the_ID();
                    $post_status = get_post_status($proposal_id);
                    $status = '';
                    if ($post_status === 'draft') {
                        $status = __('Draft', 'arsol-pfw');
                    } else {
                        $review_status_terms = wp_get_post_terms($proposal_id, 'arsol-review-status', array('fields' => 'names'));
                        if (!is_wp_error($review_status_terms) && !empty($review_status_terms)) {
                            $status = $review_status_terms[0];
                        } else {
                            $status = __('Published', 'arsol-pfw');
                        }
                    }
                    $view_url = wc_get_account_endpoint_url('project-view-proposal/' . $proposal_id);
                    $excerpt = wp_trim_words(strip_shortcodes(strip_tags(get_the_content())), 40, '...');
                ?>
                    <tr class="woocommerce-projects-table__row">
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-info" data-title="<?php _e('Proposal', 'arsol-pfw'); ?>">
                            <div class="project-title-status-wrapper">
                                <a href="<?php echo esc_url($view_url); ?>" class="project-title-link">
                                    <?php the_title(); ?>
                                </a>
                                <span class="project-status"><?php echo esc_html($status); ?></span>
                            </div>
                            <div class="project-excerpt">
                                <?php echo esc_html($excerpt); ?>
                            </div>
                        </td>
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
                    <a class="woocommerce-button woocommerce-button--previous button" href="<?php echo esc_url(add_query_arg('tab', $current_tab, $prev_url)); ?>"><?php esc_html_e('Previous', 'woocommerce'); ?></a>
                <?php endif; ?>

                <?php if ($paged < $total_pages) :
                    $next_url = trailingslashit(wc_get_account_endpoint_url('projects')) . 'page/' . ($paged + 1) . '/';
                ?>
                    <a class="woocommerce-button woocommerce-button--next button" href="<?php echo esc_url(add_query_arg('tab', $current_tab, $next_url)); ?>"><?php esc_html_e('Next', 'woocommerce'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="woocommerce-info">
            <p><?php _e('No proposals found.', 'arsol-pfw'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php
wp_reset_postdata();
do_action('arsol_projects_after_user_proposals', $has_items);
?>
