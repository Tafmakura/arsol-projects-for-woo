<?php
/**
 * Meta Field - Atom
 * 
 * Displays a metadata field with label and formatted value.
 * Supports various data types like text, currency, date, etc.
 * 
 * @package Arsol_Projects_For_Woo
 * @atomic-level atoms
 * 
 * Required Variables:
 * @var string $label         - Field label
 * @var mixed  $value         - Field value
 * 
 * Optional Variables:
 * @var string $type          - Field type: 'text', 'currency', 'date', 'link', 'badge' (default: 'text')
 * @var string $css_class     - Additional CSS classes (default: '')
 * @var string $format        - Date format for date type (default: 'M j, Y')
 * @var string $currency      - Currency symbol for currency type (default: '$')
 * @var bool   $show_empty    - Show field even if value is empty (default: false)
 * 
 * Hooks:
 * @hook arsol_pfw_before_meta_field - Fired before field output
 * @hook arsol_pfw_after_meta_field  - Fired after field output
 * 
 * Usage Example:
 * arsol_load_element('MetaField.php', [
 *     'label' => 'Budget',
 *     'value' => 5000,
 *     'type' => 'currency',
 *     'currency' => '$'
 * ]);
 */

if (!defined('ABSPATH')) exit;

// Set defaults for optional variables
$type = $type ?? 'text';
$css_class = $css_class ?? '';
$format = $format ?? 'M j, Y';
$currency = $currency ?? '$';
$show_empty = $show_empty ?? false;

// Validate required variables
if (empty($label)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('ARSOL: Missing required variable (label) for MetaField');
    }
    return;
}

// Don't show empty values unless explicitly requested
if (empty($value) && !$show_empty) {
    return;
}

// Format value based on type
$formatted_value = '';
switch ($type) {
    case 'currency':
        if (is_array($value)) {
            $formatted_value = $currency . number_format(array_sum($value), 2);
        } else {
            $formatted_value = $currency . number_format((float)$value, 2);
        }
        break;
        
    case 'date':
        if (!empty($value)) {
            $formatted_value = date($format, strtotime($value));
        }
        break;
        
    case 'link':
        if (is_array($value) && isset($value['url'], $value['text'])) {
            $formatted_value = '<a href="' . esc_url($value['url']) . '">' . esc_html($value['text']) . '</a>';
        }
        break;
        
    case 'badge':
        if (is_array($value) && isset($value['status'], $value['label'])) {
            arsol_load_element('StatusBadge.php', [
                'status' => $value['status'],
                'status_label' => $value['label'],
                'css_class' => 'meta-badge'
            ]);
            return; // StatusBadge handles its own output
        }
        break;
        
    case 'list':
        if (is_array($value)) {
            $formatted_value = '<ul><li>' . implode('</li><li>', array_map('esc_html', $value)) . '</li></ul>';
        }
        break;
        
    case 'text':
    default:
        $formatted_value = esc_html($value);
        break;
}

// Generate field CSS classes
$field_classes = ['arsol-meta-field', 'meta-type-' . sanitize_html_class($type)];
if (!empty($css_class)) {
    $field_classes[] = $css_class;
}

// Component-specific hooks
do_action('arsol_pfw_before_meta_field', $label, $value, $type);

?>

<div class="<?php echo esc_attr(implode(' ', $field_classes)); ?>">
    <span class="arsol-meta-label"><?php echo esc_html($label); ?>:</span>
    <span class="arsol-meta-value"><?php echo $formatted_value; ?></span>
</div>

<?php

// After component hook
do_action('arsol_pfw_after_meta_field', $label, $value, $type); 