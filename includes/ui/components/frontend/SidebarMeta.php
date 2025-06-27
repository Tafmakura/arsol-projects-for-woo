<?php
/**
 * Sidebar Meta - Molecule
 * 
 * Displays a collection of metadata fields in a sidebar format.
 * Combines multiple MetaField atoms into a cohesive metadata display.
 * 
 * @package Arsol_Projects_For_Woo
 * @atomic-level molecules
 * 
 * Required Variables:
 * @var int    $post_id       - Post ID (project, proposal, or request)
 * @var string $post_type     - Post type ('arsol-project', 'arsol-proposal', 'arsol-request')
 * 
 * Optional Variables:
 * @var string $css_class     - Additional CSS classes (default: '')
 * @var array  $fields        - Custom field configuration (default: auto-detect)
 * @var bool   $show_title    - Show section title (default: true)
 * @var string $title         - Custom section title (default: auto-generated)
 * 
 * Hooks:
 * @hook arsol_pfw_before_sidebar_meta - Fired before meta section output
 * @hook arsol_pfw_after_sidebar_meta  - Fired after meta section output
 * 
 * Usage Example:
 * arsol_load_molecule('SidebarMeta.php', [
 *     'post_id' => 123,
 *     'post_type' => 'arsol-project',
 *     'show_title' => true
 * ]);
 */

if (!defined('ABSPATH')) exit;

// Set defaults for optional variables
$css_class = $css_class ?? '';
$fields = $fields ?? [];
$show_title = $show_title ?? true;
$title = $title ?? '';

// Validate required variables
if (empty($post_id) || empty($post_type)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('ARSOL: Missing required variables (post_id, post_type) for SidebarMeta');
    }
    return;
}

// Auto-detect fields if not provided
if (empty($fields)) {
    switch ($post_type) {
        case 'arsol-project':
            $fields = [
                'budget' => ['label' => 'Budget', 'type' => 'currency'],
                'start_date' => ['label' => 'Start Date', 'type' => 'date'],
                'due_date' => ['label' => 'Due Date', 'type' => 'date'],
                'client_name' => ['label' => 'Client', 'type' => 'text'],
                'project_type' => ['label' => 'Type', 'type' => 'text']
            ];
            $title = $title ?: __('Project Details', 'arsol-pfw');
            break;
            
        case 'arsol-proposal':
            $fields = [
                'proposal_amount' => ['label' => 'Amount', 'type' => 'currency'],
                'valid_until' => ['label' => 'Valid Until', 'type' => 'date'],
                'client_name' => ['label' => 'Client', 'type' => 'text'],
                'proposal_type' => ['label' => 'Type', 'type' => 'text']
            ];
            $title = $title ?: __('Proposal Details', 'arsol-pfw');
            break;
            
        case 'arsol-request':
            $fields = [
                'requested_budget' => ['label' => 'Budget', 'type' => 'currency'],
                'requested_date' => ['label' => 'Requested Date', 'type' => 'date'],
                'client_name' => ['label' => 'Client', 'type' => 'text'],
                'request_type' => ['label' => 'Type', 'type' => 'text']
            ];
            $title = $title ?: __('Request Details', 'arsol-pfw');
            break;
    }
}

// Get metadata values
$meta_values = [];
foreach ($fields as $field_key => $field_config) {
    $value = get_post_meta($post_id, $field_key, true);
    if (!empty($value) || (!empty($field_config['show_empty']) && $field_config['show_empty'])) {
        $meta_values[$field_key] = [
            'label' => $field_config['label'],
            'value' => $value,
            'type' => $field_config['type'] ?? 'text',
            'format' => $field_config['format'] ?? null,
            'currency' => $field_config['currency'] ?? '$'
        ];
    }
}

// Don't show section if no metadata
if (empty($meta_values)) {
    return;
}

// Generate section CSS classes
$section_classes = ['arsol-sidebar-meta'];
if (!empty($css_class)) {
    $section_classes[] = $css_class;
}

// Component-specific hooks
do_action('arsol_pfw_before_sidebar_meta', $post_id, $post_type, $meta_values);

?>

<div class="<?php echo esc_attr(implode(' ', $section_classes)); ?>">
    <?php if ($show_title && !empty($title)): ?>
        <h4 class="arsol-sidebar-title"><?php echo esc_html($title); ?></h4>
    <?php endif; ?>
    
    <div class="arsol-meta-fields">
        <?php foreach ($meta_values as $field_key => $field_data): ?>
            <?php
            arsol_load_element('MetaField.php', [
                'label' => $field_data['label'],
                'value' => $field_data['value'],
                'type' => $field_data['type'],
                'format' => $field_data['format'],
                'currency' => $field_data['currency'],
                'css_class' => 'meta-field-' . sanitize_html_class($field_key)
            ]);
            ?>
        <?php endforeach; ?>
    </div>
</div>

<?php

// After component hook
do_action('arsol_pfw_after_sidebar_meta', $post_id, $post_type, $meta_values); 