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
?>

<?php if (!empty($budget_data['amount'])): ?>
<p class="form-field form-field-wide">
    <label><?php _e('Requested Budget:', 'arsol-pfw'); ?></label>
    <span><?php 
    $currency = !empty($budget_data['currency']) ? $budget_data['currency'] : get_woocommerce_currency();
    echo get_woocommerce_currency_symbol($currency) . number_format((float)$budget_data['amount'], 2);
    ?></span>
</p>
<?php endif; ?>

<?php if (!empty($start_date)): ?>
<p class="form-field form-field-wide">
    <label><?php _e('Requested Start Date:', 'arsol-pfw'); ?></label>
    <span><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date))); ?></span>
</p>
<?php endif; ?>

<?php if (!empty($delivery_date)): ?>
<p class="form-field form-field-wide">
    <label><?php _e('Requested Delivery Date:', 'arsol-pfw'); ?></label>
    <span><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))); ?></span>
</p>
<?php endif; ?>

<?php if (!empty($request_content)): ?>
<p class="form-field form-field-wide">
    <label><?php _e('Request Description:', 'arsol-pfw'); ?></label>
    <span><?php echo wp_kses_post(wp_trim_words($request_content, 50)); ?></span>
</p>
<?php endif; ?>

<?php if (!empty($attachments)): ?>
<p class="form-field form-field-wide">
    <label><?php _e('Attachments:', 'arsol-pfw'); ?></label>
    <?php foreach ($attachments as $attachment): ?>
        <a href="<?php echo wp_get_attachment_url($attachment->ID); ?>" target="_blank">
            <?php echo esc_html($attachment->post_title); ?>
        </a><br>
    <?php endforeach; ?>
</p>
<?php endif; ?> 