<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$original_request_id = get_post_meta($proposal_id, '_original_request_id', true);
$original_request_title = get_post_meta($proposal_id, '_original_request_title', true);
$original_request_content = get_post_meta($proposal_id, '_original_request_content', true);
$original_request_budget = get_post_meta($proposal_id, '_original_request_budget', true);
$original_request_start_date = get_post_meta($proposal_id, '_original_request_start_date', true);
$original_request_delivery_date = get_post_meta($proposal_id, '_original_request_delivery_date', true);

$has_original_data = $original_request_id || $original_request_budget || $original_request_start_date || $original_request_delivery_date;
?>

<?php if ($has_original_data): ?>

    <?php if (!empty($original_request_title)): ?>
    <div class="form-field-row">
        <p class="form-field form-field-wide">
            <label><strong><?php _e('Original Request Title:', 'arsol-pfw'); ?></strong></label>
            <?php echo esc_html($original_request_title); ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_id)): ?>
    <div class="form-field-row">
        <p class="form-field form-field-wide">
            <label><strong><?php _e('Request ID:', 'arsol-pfw'); ?></strong></label>
            <?php echo esc_html($original_request_id); ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_budget)): ?>
    <div class="form-field-row">
        <p class="form-field form-field-wide">
            <label><strong><?php _e('Original Budget:', 'arsol-pfw'); ?></strong></label>
            <?php echo wc_price($original_request_budget['amount']); ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_start_date)): ?>
    <div class="form-field-row">
        <p class="form-field form-field-wide">
            <label><strong><?php _e('Requested Start Date:', 'arsol-pfw'); ?></strong></label>
            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_start_date))); ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_delivery_date)): ?>
    <div class="form-field-row">
        <p class="form-field form-field-wide">
            <label><strong><?php _e('Requested Delivery Date:', 'arsol-pfw'); ?></strong></label>
            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($original_request_delivery_date))); ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if (!empty($original_request_content)): ?>
    <div class="form-field-row">
        <p class="form-field form-field-wide">
            <label><strong><?php _e('Original Description:', 'arsol-pfw'); ?></strong></label>
            <?php echo wp_kses_post(wp_trim_words($original_request_content, 30)); ?>
        </p>
    </div>
    <?php endif; ?>

<?php else: ?>
    <p><?php _e('This proposal was created directly without an initial customer request.', 'arsol-pfw'); ?></p>
<?php endif; ?>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_status"><?php _e('Proposal Status:', 'arsol-pfw'); ?></label>
        <select id="proposal_status" name="proposal_status" class="wc-enhanced-select">
            <?php foreach ($all_statuses as $status) : ?>
                <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($proposal_status, $status->slug); ?>>
                    <?php echo esc_html($status->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_budget"><?php _e('Proposal Budget:', 'arsol-pfw'); ?></label>
        <input type="number" id="proposal_budget" name="proposal_budget" value="<?php echo esc_attr($proposal_budget); ?>" class="widefat" step="0.01" min="0">
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_currency"><?php _e('Currency:', 'arsol-pfw'); ?></label>
        <select id="proposal_currency" name="proposal_currency" class="wc-enhanced-select">
            <?php foreach ($currencies as $code => $name) : ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected($proposal_currency, $code); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_timeline"><?php _e('Timeline:', 'arsol-pfw'); ?></label>
        <input type="text" id="proposal_timeline" name="proposal_timeline" value="<?php echo esc_attr($timeline); ?>" class="widefat">
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_notes"><?php _e('Notes:', 'arsol-pfw'); ?></label>
        <textarea id="proposal_notes" name="proposal_notes" class="widefat" rows="4"><?php echo esc_textarea($notes); ?></textarea>
    </p>
</div> 