<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

$request_id = $post->ID;
$request_status_terms = wp_get_object_terms($request_id, 'arsol-request-status', array('fields' => 'slugs'));
$request_status = !empty($request_status_terms) ? $request_status_terms[0] : 'pending';
?>

<p class="form-field form-field-wide">
    <label><?php _e('Request Status:', 'arsol-pfw'); ?></label>
    <span><?php echo esc_html(ucfirst(str_replace('-', ' ', $request_status))); ?></span>
</p>

<p class="form-field form-field-wide">
    <label><?php _e('Actions:', 'arsol-pfw'); ?></label>
    <a href="#" class="button"><?php _e('Convert to Proposal', 'arsol-pfw'); ?></a>
    <a href="#" class="button"><?php _e('Contact Customer', 'arsol-pfw'); ?></a>
</p> 