<?php
/**
 * Project Requests Template
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

do_action('arsol_projects_before_user_requests', $has_items);
?>

<div class="arsol-project-requests">
<?php if ($has_items) : ?>
    <div class="arsol-pfw-list-layout">
    <?php while ($query->have_posts()) : $query->the_post(); 
        $request_id = get_the_ID();
        $status_terms = wp_get_post_terms($request_id, 'arsol-request-status', array('fields' => 'names'));
        $status = !empty($status_terms) ? $status_terms[0] : '';
        $budget = get_post_meta($request_id, '_request_budget', true);
        $timeline = get_post_meta($request_id, '_request_timeline', true);
        $view_url = wc_get_account_endpoint_url('project-view-request/' . $request_id);
    ?>
        <div class="project-overview-wrapper">
            <div class="project-content">
                <h3 class="project-title">
                    <a href="<?php echo esc_url($view_url); ?>"><?php the_title(); ?></a>
                </h3>
                <div class="project-description">
                    <?php the_content(); ?>
                </div>
            </div>
            <div class="project-sidebar">
                <div class="project-sidebar-wrapper">
                    <div class="project-sidebar-card card">
                        <div class="project-details">
                            <h4><?php _e('Request Details', 'arsol-pfw'); ?></h4>
                            <div class="project-meta">
                                <p><strong><?php _e('Status:', 'arsol-pfw'); ?></strong> <?php echo esc_html($status); ?></p>
                                <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo esc_html($budget); ?></p>
                                <p><strong><?php _e('Timeline:', 'arsol-pfw'); ?></strong> <?php echo esc_html($timeline); ?></p>
                                <p><strong><?php _e('Date:', 'arsol-pfw'); ?></strong> <?php echo get_the_date(); ?></p>
                            </div>
                            <a href="<?php echo esc_url($view_url); ?>" class="button<?php echo esc_attr($wp_button_class); ?> view-button">
                                <?php _e('View Request', 'arsol-pfw'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>

    <?php if ($total_pages > 1) : ?>
        <div class="woocommerce-pagination woocommerce-pagination--without-numbers">
            <?php if ($paged > 1) :
                $prev_url = ($paged > 2) ? trailingslashit(wc_get_account_endpoint_url('projects')) . 'page/' . ($paged - 1) . '/' : wc_get_account_endpoint_url('projects');
            ?>
                <a class="woocommerce-button woocommerce-button--previous button" href="<?php echo esc_url( add_query_arg( 'tab', 'requests', $prev_url ) ); ?>"><?php esc_html_e('Previous', 'woocommerce'); ?></a>
            <?php endif; ?>

            <?php if ($paged < $total_pages) :
                $next_url = trailingslashit(wc_get_account_endpoint_url('projects')) . 'page/' . ($paged + 1) . '/';
            ?>
                <a class="woocommerce-button woocommerce-button--next button" href="<?php echo esc_url( add_query_arg( 'tab', 'requests', $next_url ) ); ?>"><?php esc_html_e('Next', 'woocommerce'); ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php else : ?>
    <p class="woocommerce-info"><?php _e('No requests found.', 'arsol-pfw'); ?></p>
<?php endif; ?>
</div>

<?php
wp_reset_postdata();
do_action('arsol_projects_after_user_requests', $has_items);
?> 