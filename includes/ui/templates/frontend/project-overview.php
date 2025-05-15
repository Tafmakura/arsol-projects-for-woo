<?php
/**
 * Project Overview
 *
 * Shows overview information about a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_project_overview', $project_id);
?>

<div class="woocommerce">
    <!-- Project Navigation Tabs -->
    <div class="project-navigation">
        <nav class="woocommerce-MyAccount-navigation">
            <ul>
                <li class="woocommerce-MyAccount-navigation-link is-active">
                    <a href="<?php echo esc_url(get_permalink($project_id) . 'overview/'); ?>"><?php esc_html_e('Overview', 'arsol-projects-for-woo'); ?></a>
                </li>
                <li class="woocommerce-MyAccount-navigation-link">
                    <a href="<?php echo esc_url(get_permalink($project_id) . 'invoices/'); ?>"><?php esc_html_e('Invoices', 'arsol-projects-for-woo'); ?> <span class="count">(<?php echo esc_html($project_orders_count); ?>)</span></a>
                </li>
                <li class="woocommerce-MyAccount-navigation-link">
                    <a href="<?php echo esc_url(get_permalink($project_id) . 'subscriptions/'); ?>"><?php esc_html_e('Subscriptions', 'arsol-projects-for-woo'); ?> <span class="count">(<?php echo esc_html($project_subscriptions_count); ?>)</span></a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="project-content">
        <h2><?php echo esc_html($project_title); ?></h2>
        
        <div class="project-description">
            <?php echo $project_content; ?>
        </div>
        
        <div class="project-meta">
            <?php if (!empty($project_meta['_project_start_date'][0])) : ?>
                <p><strong><?php esc_html_e('Start Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_start_date'][0]))); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($project_meta['_project_end_date'][0])) : ?>
                <p><strong><?php esc_html_e('End Date:', 'arsol-projects-for-woo'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($project_meta['_project_end_date'][0]))); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php do_action('arsol_projects_after_project_overview', $project_id); ?>