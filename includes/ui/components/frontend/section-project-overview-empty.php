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
    <h3><?php esc_html_e('Project Overview', 'arsol-projects-for-woo'); ?></h3>
    <p><?php esc_html_e('This project is currently being set up. Check back soon for more details.', 'arsol-projects-for-woo'); ?></p>
</div>
<?php do_action('arsol_projects_after_empty_state', $project_id); ?>
