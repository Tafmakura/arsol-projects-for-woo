<?php
/**
 * Action Button - Atom
 * 
 * Displays an action button with optional icon, custom styling, and confirmation.
 * Used for project actions like approve, reject, cancel, etc.
 * 
 * @package Arsol_Projects_For_Woo
 * @atomic-level atoms
 * 
 * Required Variables:
 * @var string $url           - Button URL/action endpoint
 * @var string $label         - Button text label
 * 
 * Optional Variables:
 * @var string $css_class     - Additional CSS classes (default: '')
 * @var string $icon          - Icon class or HTML (default: '')
 * @var string $type          - Button type: 'primary', 'secondary', 'danger' (default: 'secondary')
 * @var bool   $confirm       - Show confirmation dialog (default: false)
 * @var string $confirm_text  - Confirmation message (default: 'Are you sure?')
 * @var string $method        - HTTP method: 'GET', 'POST' (default: 'GET')
 * @var array  $data_attrs    - Additional data attributes (default: [])
 * 
 * Hooks:
 * @hook arsol_pfw_before_action_button - Fired before button output
 * @hook arsol_pfw_after_action_button  - Fired after button output
 * 
 * Usage Example:
 * arsol_load_element('ActionButton.php', [
 *     'url' => wc_get_account_endpoint_url('project-overview/123'),
 *     'label' => 'View Project',
 *     'type' => 'primary',
 *     'icon' => 'dashicons-visibility'
 * ]);
 */

if (!defined('ABSPATH')) exit;

// Set defaults for optional variables
$css_class = $css_class ?? '';
$icon = $icon ?? '';
$type = $type ?? 'secondary';
$confirm = $confirm ?? false;
$confirm_text = $confirm_text ?? __('Are you sure?', 'arsol-pfw');
$method = $method ?? 'GET';
$data_attrs = $data_attrs ?? [];

// Validate required variables
if (empty($url) || empty($label)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('ARSOL: Missing required variables (url, label) for ActionButton');
    }
    return;
}

// Generate button CSS classes
$button_classes = ['arsol-action-button', 'button', 'button-' . sanitize_html_class($type)];
if (!empty($css_class)) {
    $button_classes[] = $css_class;
}

// Prepare data attributes
$data_attributes = '';
if ($confirm) {
    $data_attributes .= ' data-confirm="' . esc_attr($confirm_text) . '"';
}
if ($method !== 'GET') {
    $data_attributes .= ' data-method="' . esc_attr($method) . '"';
}
foreach ($data_attrs as $key => $value) {
    $data_attributes .= ' data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
}

// Component-specific hooks
do_action('arsol_pfw_before_action_button', $url, $label, $type);

?>

<a href="<?php echo esc_url($url); ?>" 
   class="<?php echo esc_attr(implode(' ', $button_classes)); ?>"
   <?php echo $data_attributes; ?>>
    <?php if (!empty($icon)): ?>
        <span class="arsol-button-icon <?php echo esc_attr($icon); ?>"></span>
    <?php endif; ?>
    <span class="arsol-button-text"><?php echo esc_html($label); ?></span>
</a>

<?php

// After component hook
do_action('arsol_pfw_after_action_button', $url, $label, $type);
