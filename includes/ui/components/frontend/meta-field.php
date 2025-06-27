<?php
/**
 * Meta Field Component
 * 
 * Displays a metadata field with label and formatted value.
 * Variables: $label, $value, $type (optional), $format (optional), $currency (optional)
 */

if (!defined('ABSPATH')) exit;

// Set defaults
$type = $type ?? 'text';
$format = $format ?? 'M j, Y';
$currency = $currency ?? '$';

// Validate required variables
if (empty($label) || empty($value)) {
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
        $formatted_value = date($format, strtotime($value));
        break;
        
    case 'text':
    default:
        $formatted_value = esc_html($value);
        break;
}

?>

<div class="arsol-meta-field meta-type-<?php echo esc_attr($type); ?>">
    <span class="arsol-meta-label"><?php echo esc_html($label); ?>:</span>
    <span class="arsol-meta-value"><?php echo $formatted_value; ?></span>
</div>
