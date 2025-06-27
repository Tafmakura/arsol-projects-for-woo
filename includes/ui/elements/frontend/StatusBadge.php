<?php
/**
 * Status Badge - Atom
 * 
 * Displays a status badge with optional icon and custom styling.
 * Used throughout the plugin to show project, proposal, and request statuses.
 * 
 * @package Arsol_Projects_For_Woo
 * @atomic-level atoms
 * 
 * Required Variables:
 * @var string $status        - Status slug (e.g., 'in-progress', 'completed')
 * @var string $status_label  - Human-readable status label
 * 
 * Optional Variables:
 * @var string $css_class     - Additional CSS classes (default: '')
 * @var bool   $show_icon     - Display status icon (default: true)
 * @var string $context       - Context where badge is used (default: 'default')
 * @var string $taxonomy      - Taxonomy name for status (default: '')
 * 
 * Hooks:
 * @hook arsol_pfw_before_status_badge - Fired before badge output
 * @hook arsol_pfw_after_status_badge  - Fired after badge output
 * 
 * Usage Example:
 * arsol_load_element('StatusBadge.php', [
 *     'status' => 'in-progress',
 *     'status_label' => 'In Progress',
 *     'css_class' => 'sidebar-badge',
 *     'show_icon' => true
 * ]);
 */

if (!defined('ABSPATH')) exit;

// Set defaults for optional variables
$css_class = $css_class ?? '';
$show_icon = $show_icon ?? true;
$context = $context ?? 'default';
$taxonomy = $taxonomy ?? '';

// Validate required variables
if (empty($status) || empty($status_label)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('ARSOL: Missing required variables (status, status_label) for StatusBadge');
    }
    return;
}

// Generate badge CSS classes
$badge_classes = ['arsol-status-badge', 'status-' . sanitize_html_class($status)];
if (!empty($css_class)) {
    $badge_classes[] = $css_class;
}

// Component-specific hooks
do_action('arsol_pfw_before_status_badge', $status, $status_label, $context);

?>

<span class="<?php echo esc_attr(implode(' ', $badge_classes)); ?>" data-status="<?php echo esc_attr($status); ?>">
    <?php if ($show_icon): ?>
        <span class="arsol-status-icon status-icon-<?php echo esc_attr($status); ?>"></span>
    <?php endif; ?>
    <span class="arsol-status-text"><?php echo esc_html($status_label); ?></span>
</span>

<?php

// After component hook
do_action('arsol_pfw_after_status_badge', $status, $status_label, $context); 