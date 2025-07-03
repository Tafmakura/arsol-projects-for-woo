<?php
/**
 * Action Button Component
 * 
 * Variables expected:
 * @var string $url - Button URL
 * @var string $label - Button text
 * @var string $type - Button type (primary, secondary)
 * @var string $icon - Dashicon class (optional)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Set default values
$url = $url ?? '#';
$label = $label ?? __('Action', 'arsol-pfw');
$type = $type ?? 'secondary';
$icon = $icon ?? '';

// Build CSS classes
$button_classes = array('button');
$button_classes[] = $type === 'primary' ? 'button-primary' : 'button-secondary';
$button_classes[] = 'arsol-pfw-action-btn';

// Add WooCommerce theme compatibility if available
if (function_exists('wc_wp_theme_get_element_class_name')) {
    $button_classes[] = wc_wp_theme_get_element_class_name('button');
}

$class_string = implode(' ', $button_classes);
?>

<a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr($class_string); ?>">
    <?php if (!empty($icon)): ?>
        <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
    <?php endif; ?>
    <?php echo esc_html($label); ?>
</a> 