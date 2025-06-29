<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

$request_id = $post->ID;
// Get the taxonomy term directly and use its name
$request_status_terms = wp_get_post_terms($request_id, 'arsol-pfw-request-status');
$request_status_display = '';
if (!empty($request_status_terms) && !is_wp_error($request_status_terms)) {
    $request_status_display = $request_status_terms[0]->name; // Use taxonomy term name directly
}
$request_status_slug = !empty($request_status_terms) ? $request_status_terms[0]->slug : 'pending-review';
?>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Request Status:', 'arsol-pfw'); ?></strong></label>
    <?php echo esc_html($request_status_display); ?>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Actions:', 'arsol-pfw'); ?></strong></label>
    <a href="#" class="button"><?php _e('Convert to Proposal', 'arsol-pfw'); ?></a>
    <a href="#" class="button"><?php _e('Contact Customer', 'arsol-pfw'); ?></a>
</p> 