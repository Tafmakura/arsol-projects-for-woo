<?php
/**
 * Action Button Component
 * 
 * Displays an action button with optional icon and styling.
 * Variables: $url, $label, $type (optional), $icon (optional), $css_class (optional)
 */

if (!defined('ABSPATH')) exit;

// Set defaults
$type = $type ?? 'secondary';
$icon = $icon ?? '';
$css_class = $css_class ?? '';

// Validate required variables
if (empty($url) || empty($label)) {
    return;
}

// Generate button CSS classes
$button_classes = ['arsol-action-button', 'button', 'button-' . sanitize_html_class($type)];
if (!empty($css_class)) {
    $button_classes[] = $css_class;
}

?>

<a href="<?php echo esc_url($url); ?>" class="<?php echo esc_attr(implode(' ', $button_classes)); ?>">
    <?php if (!empty($icon)): ?>
        <span class="arsol-button-icon <?php echo esc_attr($icon); ?>"></span>
    <?php endif; ?>
    <span class="arsol-button-text"><?php echo esc_html($label); ?></span>
</a>
