<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

$request_id = $post->ID;
$request_content = get_post_field('post_content', $request_id);
$attachments = get_attached_media('', $request_id);
$budget_data = get_post_meta($request_id, '_request_budget', true);
$start_date = get_post_meta($request_id, '_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_request_delivery_date', true);
$request_status_terms = wp_get_object_terms($request_id, 'arsol-request-status', array('fields' => 'names'));
$request_status = !empty($request_status_terms) ? $request_status_terms[0] : '';
$customer = get_userdata($post->post_author);
$submission_date = get_the_time('l j F \a\t g:ia', $post);
?>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Submission Date:', 'arsol-pfw'); ?></strong></label>
    <span><?php echo $submission_date ? esc_html($submission_date) : __('N/A', 'arsol-pfw'); ?></span>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Requested Budget:', 'arsol-pfw'); ?></strong></label>
    <span><?php 
    if (!empty($budget_data['amount'])) {
        $currency = !empty($budget_data['currency']) ? $budget_data['currency'] : get_woocommerce_currency();
        echo get_woocommerce_currency_symbol($currency) . number_format((float)$budget_data['amount'], 2);
    } else {
        echo __('N/A', 'arsol-pfw');
    }
    ?></span>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Requested Start Date:', 'arsol-pfw'); ?></strong></label>
    <span><?php echo $start_date ? esc_html(date_i18n(get_option('date_format'), strtotime($start_date))) : __('N/A', 'arsol-pfw'); ?></span>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Requested Delivery Date:', 'arsol-pfw'); ?></strong></label>
    <span><?php echo $delivery_date ? esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))) : __('N/A', 'arsol-pfw'); ?></span>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Project Description:', 'arsol-pfw'); ?></strong></label>
    <span><?php echo $request_content ? wp_kses_post(wp_trim_words($request_content, 50)) : __('N/A', 'arsol-pfw'); ?></span>
</p>

<p class="form-field form-field-wide">
    <label><strong><?php _e('Attachments:', 'arsol-pfw'); ?></strong></label>
    <?php if (!empty($attachments)): ?>
        <span>
            <?php foreach ($attachments as $attachment): ?>
                <a href="<?php echo esc_url(wp_get_attachment_url($attachment->ID)); ?>" target="_blank">
                    <?php echo esc_html($attachment->post_title); ?>
                </a><br>
            <?php endforeach; ?>
        </span>
    <?php else: ?>
        <span><?php echo __('N/A', 'arsol-pfw'); ?></span>
    <?php endif; ?>
</p> 