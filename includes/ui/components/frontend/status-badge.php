<?php
/**
 * Status Badge Component
 *
 * Displays a status badge with optional icon.
 *
 * Expected variables:
 * @var string $status - Status slug (required)
 * @var string $status_label - Status display label (required)
 * @var bool $show_icon - Whether to show icon (optional, default true)
 * @var string $css_class - Additional CSS class (optional)
 */

if (!defined('ABSPATH')) exit;

// Ensure required variables are set
if (empty($status) || empty($status_label)) {
    return;
}

// Set defaults
$show_icon = $show_icon ?? true;
$css_class = $css_class ?? 'status-badge';

// Build CSS classes
$classes = array($css_class, 'status-' . sanitize_html_class($status));

// Define status icons
$status_icons = array(
    'active' => 'dashicons-yes-alt',
    'pending' => 'dashicons-clock',
    'completed' => 'dashicons-yes',
    'cancelled' => 'dashicons-dismiss',
    'on-hold' => 'dashicons-pause',
    'under-review' => 'dashicons-visibility',
    'approved' => 'dashicons-thumbs-up',
    'rejected' => 'dashicons-thumbs-down',
    'in-progress' => 'dashicons-update',
    'draft' => 'dashicons-edit',
    'published' => 'dashicons-visibility',
);

$icon_class = $status_icons[$status] ?? 'dashicons-marker';
?>

<span class="<?php echo esc_attr(implode(' ', $classes)); ?>">
    <?php if ($show_icon): ?>
        <span class="dashicons <?php echo esc_attr($icon_class); ?>"></span>
    <?php endif; ?>
    <span class="status-text"><?php echo esc_html($status_label); ?></span>
</span> 