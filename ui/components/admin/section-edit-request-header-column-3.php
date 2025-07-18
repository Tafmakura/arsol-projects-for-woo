<?php
/**
 * Admin Template: Edit Request Header - Status & Actions Column
 *
 * Variables passed from parent template:
 * $request (Arsol_PFW_Request object) - The request entity instance
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure we have the request entity instance
if (!isset($request) || !is_object($request)) {
    return;
}

$request_id = $request->get_id();
$request_stage = $request->get_stage();
$request_stage_label = $request->get_stage_label();
?>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Request Stage:', 'arsol-pfw'); ?></strong></label>
    <?php echo esc_html($request_stage_label); ?>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Request Stage:', 'arsol-pfw'); ?></strong></label>
    <div class="value">
        <?php echo esc_html($request_stage_label); ?>
    </div>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Actions:', 'arsol-pfw'); ?></strong></label>
    <a href="#" class="button"><?php _e('Convert to Proposal', 'arsol-pfw'); ?></a>
    <a href="#" class="button"><?php _e('Contact Customer', 'arsol-pfw'); ?></a>
</p> 