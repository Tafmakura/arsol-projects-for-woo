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

// Get the default message for active projects (empty content)
$default_message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('arsol_pfw_project_default_empty_content');

do_action('arsol_projects_before_empty_state', $project_id);

?>
<div class="arsol-pfw-project-overview-empty">
    <div class="arsol-pfw-empty-state">
        <div class="arsol-pfw-empty-state__content">
            <?php echo wp_kses_post(wpautop($default_message)); ?>
        </div>
    </div>
</div>
<?php do_action('arsol_projects_after_empty_state', $project_id); ?>
