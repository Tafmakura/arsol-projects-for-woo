<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-project') {
    return;
}

$project_id = $post->ID;
$original_request_id = get_post_meta($project_id, '_original_request_id', true);
$original_request_title = get_post_meta($project_id, '_original_request_title', true);
$original_request_content = get_post_meta($project_id, '_original_request_content', true);
$original_request_budget = get_post_meta($project_id, '_original_request_budget', true);
$original_request_start_date = get_post_meta($project_id, '_original_request_start_date', true);
$original_request_delivery_date = get_post_meta($project_id, '_original_request_delivery_date', true);

$has_original_data = $original_request_id || $original_request_budget || $original_request_start_date || $original_request_delivery_date;
?>

<?php if ($has_original_data): ?>

    <?php if (!empty($original_request_title)): ?>
    <p class="form-field form-field-wide">
        <strong><?php _e('Original Title:', 'arsol-pfw'); ?></strong>
        <?php echo esc_html($original_request_title); ?>
    </p>
    <?php endif; ?>

    <?php if (!empty($original_request_id)): ?>
    <p class="form-field form-field-wide">
        <strong><?php _e('Request ID:', 'arsol-pfw'); ?></strong>
        <?php echo esc_html($original_request_id); ?>
    </p>
    <?php endif; ?>

    <?php if (!empty($original_request_budget)): ?>
    <p class="form-field form-field-wide">
        <strong><?php _e('Original Budget:', 'arsol-pfw'); ?></strong>
        <?php echo wc_price($original_request_budget['amount']); ?>
    </p>
    <?php endif; ?>

    <?php if (!empty($original_request_start_date)): ?>
    <p class="form-field form-field-wide">
        <strong><?php _e('Requested Start Date:', 'arsol-pfw'); ?></strong>
        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_start_date))); ?>
    </p>
    <?php endif; ?>

    <?php if (!empty($original_request_delivery_date)): ?>
    <p class="form-field form-field-wide">
        <strong><?php _e('Requested Delivery Date:', 'arsol-pfw'); ?></strong>
        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_delivery_date))); ?>
    </p>
    <?php endif; ?>

    <?php if (!empty($original_request_content)): ?>
    <p class="form-field form-field-wide">
        <strong><?php _e('Original Description:', 'arsol-pfw'); ?></strong>
        <?php echo wp_kses_post(wp_trim_words($original_request_content, 30)); ?>
    </p>
    <?php endif; ?>

<?php else: ?>
    <p><?php _e('This project was created directly without an initial customer request.', 'arsol-pfw'); ?></p>
<?php endif; ?> 