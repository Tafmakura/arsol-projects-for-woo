<?php
/**
 * User Projects Listing
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
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-info">
                        <?php _e('Project', 'arsol-pfw'); ?>
                    </th>
                    <th class="woocommerce-projects-table__header woocommerce-projects-table__header-project-actions">
                        &nbsp;
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_projects as $project) : 
                    $project_id = $project->ID;
                    $status_terms = wp_get_post_terms($project_id, 'arsol-project-status', array('fields' => 'names'));
                    $status = !empty($status_terms) ? $status_terms[0] : '';
                    $view_url = wc_get_account_endpoint_url('project-overview/' . $project_id);
                    $excerpt = wp_trim_words(strip_shortcodes(strip_tags($project->post_content)), 40, '...');
                ?>
                    <tr class="woocommerce-projects-table__row">
                        <td class="woocommerce-projects-table__cell woocommerce-projects-table__cell-project-info" data-title="<?php _e('Project', 'arsol-pfw'); ?>">
                            <div class="project-title-status-wrapper">
                            <a href="<?php echo esc_url($view_url); ?>" class="project-title-link">
                                <?php echo esc_html($project->post_title); ?>
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
                <?php endforeach; ?>
            </tbody>
        </table>
    
        <?php if ($total_pages > 1) : ?>
            <div class="woocommerce-pagination woocommerce-pagination--without-numbers">
                <?php if ($paged > 1) :
                    $prev_url = ($paged > 2) ? trailingslashit(wc_get_account_endpoint_url('projects')) . 'page/' . ($paged - 1) . '/' : wc_get_account_endpoint_url('projects');
                ?>
                    <a class="woocommerce-button woocommerce-button--previous button" href="<?php echo esc_url($prev_url); ?>"><?php esc_html_e('Previous', 'woocommerce'); ?></a>
                <?php endif; ?>

                <?php if ($paged < $total_pages) :
                    $next_url = trailingslashit(wc_get_account_endpoint_url('projects')) . 'page/' . ($paged + 1) . '/';
                ?>
                    <a class="woocommerce-button woocommerce-button--next button" href="<?php echo esc_url($next_url); ?>"><?php esc_html_e('Next', 'woocommerce'); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="woocommerce-info">
            <p><?php _e('You have no projects, proposals, or requests yet.', 'arsol-pfw'); ?></p>
        </div>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</div>

<?php do_action('arsol_projects_after_user_projects', $has_projects); ?> 