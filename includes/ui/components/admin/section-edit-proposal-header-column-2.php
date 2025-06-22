<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$original_request_id = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_id', true);
$original_request_title = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_title', true);
$original_request_content = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_details', true);
$original_request_budget = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_budget', true);
$original_request_start_date = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_start_date', true);
$original_request_delivery_date = get_post_meta($proposal_id, '_arsol_pfw_proposal_request_delivery_date', true);

$has_original_data = $original_request_id || $original_request_budget || $original_request_start_date || $original_request_delivery_date;
?>

<?php if ($has_original_data): ?>

    <p class="form-field form-field-wide">
        <label><strong><?php _e('Available Budget:', 'arsol-pfw'); ?></strong></label>
        <?php 
        if (!empty($original_request_budget)) {
            if (is_array($original_request_budget) && isset($original_request_budget['amount'])) {
                $currency = isset($original_request_budget['currency']) ? $original_request_budget['currency'] : get_woocommerce_currency();
                echo wc_price($original_request_budget['amount'], array('currency' => $currency));
            } else {
                // Fallback for legacy or malformed data
                echo wc_price($original_request_budget);
            }
        } else {
            echo '<em>' . __('Not provided', 'arsol-pfw') . '</em>';
        }
        ?>
    </p>

    <p class="form-field form-field-wide">
        <label><strong><?php _e('Requested Start Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo !empty($original_request_start_date) ? esc_html(date_i18n(get_option('date_format'), strtotime($original_request_start_date))) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?>
    </p>

    <p class="form-field form-field-wide">
        <label><strong><?php _e('Requested Delivery Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo !empty($original_request_delivery_date) ? esc_html(date_i18n(get_option('date_format'), strtotime($original_request_delivery_date))) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?>
    </p>

    <p class="form-field form-field-wide">
        <label><strong><?php _e('Project Request Details:', 'arsol-pfw'); ?></strong></label>
        <?php echo !empty($original_request_content) ? wp_kses_post(wp_trim_words($original_request_content, 30)) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?>
    </p>

<?php else: ?>
    <p><?php _e('This proposal was created directly without an initial customer request.', 'arsol-pfw'); ?></p>
<?php endif; ?> 