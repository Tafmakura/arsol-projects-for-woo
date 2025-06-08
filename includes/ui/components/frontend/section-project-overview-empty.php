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
<div class="arsol-pfw-project-overview-empty">
    <div class="arsol-pfw-empty-state">
        <div class="arsol-pfw-empty-state__icon">
            <span class="dashicons dashicons-portfolio"></span>
        </div>
        <div class="arsol-pfw-empty-state__content">
            <h1><?php esc_html_e('No Project Overview at the moment.', 'arsol-pfw'); ?></h1>
            <p><?php esc_html_e('The project details aren\'t available at this time. Check back soon for more details.', 'arsol-pfw'); ?></p>
            <div class="arsol-pfw-empty-state__actions">
                <a href="/contact-us/" class="button button-primary"><?php esc_html_e('Contact Support', 'arsol-pfw'); ?></a>
                <a href="/services/" class="button"><?php esc_html_e('Contact Sales', 'arsol-pfw'); ?></a>
            </div>
        </div>
    </div>
</div>
<?php do_action('arsol_projects_after_empty_state', $project_id); ?>
