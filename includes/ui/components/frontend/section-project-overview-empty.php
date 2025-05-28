<?php
/**
 * Project Empty State Template
 *
 * Shows a message when a project has no content.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

do_action('arsol_projects_before_empty_state', $project_id);
?>
<div class="project-empty-state">
    <h3><?php esc_html_e('No Project Overview at the moment', 'arsol-projects-for-woo'); ?></h3>
    <p><?php esc_html_e('This project has no overview information. This is usually the case if it is still being set up, or there is no relevant information to share. Check back soon for more details.', 'arsol-projects-for-woo'); ?></p>
    
    <div class="project-empty-state-actions">
        <a class="bricks-button sm outline bricks-color-primary" href="https://portal.automatedretail.africa/contact-us/support/"><?php esc_html_e('Contact Support', 'arsol-projects-for-woo'); ?></a>
        <a class="bricks-button sm outline bricks-color-primary" href="https://portal.automatedretail.africa/contact-us/sales/"><?php esc_html_e('Contact Sales', 'arsol-projects-for-woo'); ?></a>
    </div>
</div>
<?php do_action('arsol_projects_after_empty_state', $project_id); ?>
