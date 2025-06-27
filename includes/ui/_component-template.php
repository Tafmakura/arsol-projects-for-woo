<?php
/**
 * [Component Name] - [Atomic Level]
 * 
 * Brief description of what this component does and when to use it.
 * 
 * @package Arsol_Projects_For_Woo
 * @atomic-level [atoms|molecules|organisms]
 * 
 * Required Variables:
 * @var int    $project_id    - Project post ID
 * @var string $status        - Current project status
 * 
 * Optional Variables:
 * @var string $css_class     - Additional CSS classes (default: '')
 * @var bool   $show_icon     - Display status icon (default: true)
 * @var string $context       - Context where component is used (default: 'default')
 * 
 * Hooks:
 * @hook arsol_pfw_before_[component_name] - Fired before component output
 * @hook arsol_pfw_after_[component_name]  - Fired after component output
 * 
 * Usage Example:
 * arsol_load_element('StatusBadge.php', [
 *     'project_id' => 123,
 *     'status' => 'in-progress',
 *     'css_class' => 'custom-class',
 *     'show_icon' => true
 * ]);
 */

if (!defined('ABSPATH')) exit;

// Set defaults for optional variables
$css_class = $css_class ?? '';
$show_icon = $show_icon ?? true;
$context = $context ?? 'default';

// Validate required variables
if (empty($project_id) || empty($status)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('ARSOL: Missing required variables for [Component Name]');
    }
    return;
}

// Component-specific hooks
do_action('arsol_pfw_before_[component_name]', $project_id, $status, $context);

?>

<!-- Component HTML output here -->
<div class="arsol-component <?php echo esc_attr($css_class); ?>">
    <!-- Component content -->
</div>

<?php

// After component hook
do_action('arsol_pfw_after_[component_name]', $project_id, $status, $context); 