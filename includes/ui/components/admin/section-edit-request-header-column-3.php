<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

$request_id = $post->ID;
$request_status_terms = wp_get_object_terms($request_id, 'arsol-pfw-request-status', array('fields' => 'slugs'));
$request_status = !empty($request_status_terms) ? $request_status_terms[0] : 'pending-review';

$request_stage_terms = wp_get_object_terms($request_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
$request_stage = !empty($request_stage_terms) ? $request_stage_terms[0] : 'pending-review';
?>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Request Status:', 'arsol-pfw'); ?></strong></label>
    <?php echo esc_html(ucfirst(str_replace('-', ' ', $request_status))); ?>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Request Stage:', 'arsol-pfw'); ?></strong></label>
    <div class="value">
        <?php echo esc_html(ucfirst(str_replace('-', ' ', $request_stage))); ?>
    </div>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Actions:', 'arsol-pfw'); ?></strong></label>
    <a href="#" class="button"><?php _e('Convert to Proposal', 'arsol-pfw'); ?></a>
    <a href="#" class="button"><?php _e('Contact Customer', 'arsol-pfw'); ?></a>
</p> 