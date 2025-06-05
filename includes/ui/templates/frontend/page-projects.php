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

// Define $has_projects based on whether there are any items
$has_projects = isset($has_items) && $has_items;

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
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-type"><?php _e('Type', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-title"><?php _e('Title', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-status"><?php _e('Status', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-date"><?php _e('Date', 'arsol-pfw'); ?></th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-actions"><?php _e('', 'arsol-pfw'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($query->have_posts()): $query->the_post(); 
                    $post_type = get_post_type();
                    $status = wp_get_object_terms(get_the_ID(), 'arsol-project-status', array('fields' => 'names'));
                    $status = !empty($status) ? $status[0] : 'pending';
                    
                    // Determine the view URL based on post type
                    switch ($post_type) {
                        case 'arsol-project':
                            $view_url = wc_get_account_endpoint_url('project-overview/' . get_the_ID());
                            $type_label = __('Project', 'arsol-pfw');
                            break;
                        case 'arsol-project-proposal':
                            $view_url = add_query_arg('id', get_the_ID(), wc_get_account_endpoint_url('project-view-proposal'));
                            $type_label = __('Proposal', 'arsol-pfw');
                            break;
                        case 'arsol-project-request':
                            $view_url = add_query_arg('id', get_the_ID(), wc_get_account_endpoint_url('project-view-request'));
                            $type_label = __('Request', 'arsol-pfw');
                            break;
                    }
                ?>
                    <tr class="woocommerce-projects-table__row woocommerce-projects-table__row--status-<?php echo esc_attr($status); ?> project">
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-type" data-title="<?php _e('Type', 'arsol-pfw'); ?>">
                            <?php echo esc_html($type_label); ?>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-title" data-title="<?php _e('Title', 'arsol-pfw'); ?>">
                            <a href="<?php echo esc_url($view_url); ?>" class="project-title-link">
                                <?php the_title(); ?>
                            </a>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-status" data-title="<?php _e('Status', 'arsol-pfw'); ?>">
                            <span class="status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst(str_replace('-', ' ', $status))); ?>
                            </span>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-date" data-title="<?php _e('Date', 'arsol-pfw'); ?>">
                            <?php echo get_the_date(); ?>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-actions" data-title="<?php _e('Actions', 'arsol-pfw'); ?>">
                            <a href="<?php echo esc_url($view_url); ?>" class="woocommerce-button button view">
                                <?php _e('View', 'arsol-pfw'); ?>
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
            <p><?php _e('You have no projects, proposals, or requests yet.', 'arsol-pfw'); ?></p>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>

<?php do_action('arsol_projects_after_user_projects', $has_projects); ?>