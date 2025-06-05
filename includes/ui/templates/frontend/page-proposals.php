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
$current_page = max(1, get_query_var('paged'));
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? 
    ' ' . wc_wp_theme_get_element_class_name('button') : '';

do_action('arsol_projects_before_user_proposals', $has_items);
?>

<div class="arsol-project-proposals">
    <?php if ($has_items) : ?>
        <table class="woocommerce-projects-table shop_table shop_table_responsive">
            <thead>
                <tr>
                    <th><?php _e('Title', 'arsol-pfw'); ?></th>
                    <th><?php _e('Status', 'arsol-pfw'); ?></th>
                    <th><?php _e('Budget', 'arsol-pfw'); ?></th>
                    <th><?php _e('Timeline', 'arsol-pfw'); ?></th>
                    <th><?php _e('Date', 'arsol-pfw'); ?></th>
                    <th><?php _e('Actions', 'arsol-pfw'); ?></th>
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
                    <tr>
                        <td>
                            <a href="<?php echo esc_url($view_url); ?>">
                                <?php the_title(); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($status); ?></td>
                        <td><?php echo esc_html($budget); ?></td>
                        <td><?php echo esc_html($timeline); ?></td>
                        <td><?php echo get_the_date(); ?></td>
                        <td>
                            <a href="<?php echo esc_url($view_url); ?>" class="button">
                                <?php _e('View', 'arsol-pfw'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1) : ?>
            <div class="woocommerce-pagination">
                <?php
                echo paginate_links(array(
                    'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                    'format' => '?paged=%#%',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'prev_text' => '&larr;',
                    'next_text' => '&rarr;',
                    'type' => 'list',
                ));
                ?>
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