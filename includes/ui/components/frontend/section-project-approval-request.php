<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get request details
$request_budget = get_post_meta($post->ID, '_request_budget', true);
$request_currency = get_post_meta($post->ID, '_request_currency', true);
$request_deadline = get_post_meta($post->ID, '_request_deadline', true);
$request_requirements = get_post_meta($post->ID, '_request_requirements', true);
?>

<div class="arsol-request-details">
    <div class="request-content">
        <?php echo wp_kses_post($post->post_content); ?>
    </div>

    <div class="request-meta">
        <?php if ($request_budget): ?>
        <div class="request-budget">
            <h4><?php _e('Budget', 'arsol-pfw'); ?></h4>
            <p><?php echo esc_html(wc_price($request_budget, array('currency' => $request_currency))); ?></p>
        </div>
        <?php endif; ?>

        <?php if ($request_deadline): ?>
        <div class="request-deadline">
            <h4><?php _e('Desired Deadline', 'arsol-pfw'); ?></h4>
            <p><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($request_deadline))); ?></p>
        </div>
        <?php endif; ?>

        <?php if ($request_requirements): ?>
        <div class="request-requirements">
            <h4><?php _e('Requirements', 'arsol-pfw'); ?></h4>
            <p><?php echo wp_kses_post($request_requirements); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($status === 'pending'): ?>
    <div class="request-actions">
        <form method="post" class="arsol-request-form">
            <?php wp_nonce_field('arsol_request_action', 'arsol_request_nonce'); ?>
            <input type="hidden" name="request_id" value="<?php echo esc_attr($post->ID); ?>">
            
            <div class="form-row">
                <label for="request_response"><?php _e('Your Response', 'arsol-pfw'); ?></label>
                <textarea id="request_response" name="request_response" rows="4" required></textarea>
            </div>

            <div class="form-row">
                <button type="submit" name="request_action" value="approve" class="button button-primary">
                    <?php _e('Approve Request', 'arsol-pfw'); ?>
                </button>
                <button type="submit" name="request_action" value="reject" class="button">
                    <?php _e('Reject Request', 'arsol-pfw'); ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
