<?php
/**
 * Status Badge Component
 * 
 * Displays a status badge with optional icon.
 * Variables: $status, $status_label, $show_icon (optional), $css_class (optional)
 */

if (!defined('ABSPATH')) exit;

// Set defaults
$show_icon = $show_icon ?? true;
$css_class = $css_class ?? '';

// Validate required variables
if (empty($status) || empty($status_label)) {
    return;
}

// Generate badge CSS classes
$badge_classes = ['arsol-status-badge', 'status-' . sanitize_html_class($status)];
if (!empty($css_class)) {
    $badge_classes[] = $css_class;
}

?>

<span class="<?php echo esc_attr(implode(' ', $badge_classes)); ?>" data-status="<?php echo esc_attr($status); ?>">
    <?php if ($show_icon): ?>
        <span class="arsol-status-icon status-icon-<?php echo esc_attr($status); ?>"></span>
    <?php endif; ?>
    <span class="arsol-status-text"><?php echo esc_html($status_label); ?></span>
</span>
