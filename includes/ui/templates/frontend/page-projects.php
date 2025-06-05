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

<div class="woocommerce-MyAccount-content">
    <?php if ($has_items): ?>
        <table class="woocommerce-projects-table shop_table shop_table_responsive">
            <thead>
                <tr>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-title">
                        <?php _e('Title', 'arsol-pfw'); ?>
                    </th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-status">
                        <?php _e('Status', 'arsol-pfw'); ?>
                    </th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-due-date">
                        <?php _e('Due Date', 'arsol-pfw'); ?>
                    </th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-date">
                        <?php _e('Date', 'arsol-pfw'); ?>
                    </th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-actions">
                        <?php _e('Actions', 'arsol-pfw'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_projects as $project) : 
                    $project_id = $project->ID;
                    $status = wp_get_post_terms($project_id, 'arsol-project-status', array('fields' => 'names'));
                    $status = !empty($status) ? $status[0] : '';
                    $due_date = get_post_meta($project_id, '_project_due_date', true);
                    $view_url = wc_get_account_endpoint_url('project-overview/' . $project_id);
                ?>
                    <tr class="woocommerce-projects-table__row">
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-title" data-title="<?php _e('Title', 'arsol-pfw'); ?>">
                            <a href="<?php echo esc_url($view_url); ?>" class="project-title-link">
                                <?php echo esc_html($project->post_title); ?>
                            </a>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-status" data-title="<?php _e('Status', 'arsol-pfw'); ?>">
                            <?php echo esc_html($status); ?>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-due-date" data-title="<?php _e('Due Date', 'arsol-pfw'); ?>">
                            <?php echo esc_html($due_date); ?>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-date" data-title="<?php _e('Date', 'arsol-pfw'); ?>">
                            <?php echo get_the_date('', $project_id); ?>
                        </td>
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-actions" data-title="<?php _e('Actions', 'arsol-pfw'); ?>">
                            <a href="<?php echo esc_url($view_url); ?>" class="button<?php echo esc_attr($wp_button_class); ?>">
                                <?php _e('View', 'arsol-pfw'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    
        <?php if ($total_pages > 1) : ?>
            <nav class="woocommerce-pagination">
                <?php
                $links = paginate_links(
                    array(
                        'base'      => trailingslashit(wc_get_account_endpoint_url('projects')) . 'page/%#%/',
                        'format'    => '',
                        'current'   => max(1, $paged),
                        'total'     => $total_pages,
                        'prev_text' => esc_html__('Previous', 'woocommerce'),
                        'next_text' => esc_html__('Next', 'woocommerce'),
                        'type'      => 'list',
                        'end_size'  => 0,
                        'mid_size'  => 0,
                    )
                );
                
                if ($links) {
                    echo $links;
                }
                ?>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="woocommerce-info">
            <p><?php _e('You have no projects, proposals, or requests yet.', 'arsol-pfw'); ?></p>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>

<?php do_action('arsol_projects_after_user_projects', $has_projects); ?>