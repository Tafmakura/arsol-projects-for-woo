<?php
/**
 * User Projects Template
 *
 * Shows a list of projects associated with the current user.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('arsol_projects_before_user_projects', $has_projects);
?>

<div> 
   <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-projects-create-or-request.php'; ?>
</div>

<div class="woocommerce-MyAccount-content">
    <?php if ($has_items): ?>
        <table class="woocommerce-projects-table shop_table shop_table_responsive my_account_projects account-projects-table">
            <thead>
                <tr>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-title"><?php _e('Project Request', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-date"><?php _e('Date', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-actions"><?php _e('', 'arsol-pfw'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query->have_posts()): $query->the_post(); ?>
                    <tr class="woocommerce-projects-table__row woocommerce-projects-table__row--status-pending project">
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-title" data-title="<?php _e('Project Request', 'arsol-pfw'); ?>">
                            <?php the_title(); ?>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-date" data-title="<?php _e('Date', 'arsol-pfw'); ?>">
                            <?php echo get_the_date(); ?>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-actions" data-title="<?php _e('Actions', 'arsol-pfw'); ?>">
                            <a href="<?php echo esc_url(add_query_arg('id', get_the_ID(), wc_get_account_endpoint_url('project-view-request'))); ?>" class="woocommerce-button button view">
                                <?php _e('Review', 'arsol-pfw'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    
        <?php 
        // Pagination
        if ($total_pages > 1) {
            echo '<div class="woocommerce-pagination">';
            echo paginate_links(array(
                'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'format'    => '?paged=%#%',
                'current'   => $current_page,
                'total'     => $total_pages,
                'prev_text' => '&larr;',
                'next_text' => '&rarr;',
                'type'      => 'list',
            ));
            echo '</div>';
        }
        ?>

    <?php else: ?>
        <div class="woocommerce-info">
            <p><?php _e('No pending project requests to review.', 'arsol-pfw'); ?></p>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>

<?php do_action('arsol_projects_after_user_projects', $has_projects); ?>