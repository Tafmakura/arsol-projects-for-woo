<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get proposal details
$proposal_budget = get_post_meta($post->ID, '_proposal_budget', true);
$proposal_timeline = get_post_meta($post->ID, '_proposal_timeline', true);
$related_request_id = get_post_meta($post->ID, '_related_request_id', true);
?>

<div class="arsol-proposal-details">
    <div class="proposal-content">
        <?php echo wp_kses_post($post->post_content); ?>
    </div>

    <div class="proposal-meta">
        <?php if ($proposal_budget): ?>
        <div class="proposal-budget">
            <h4><?php _e('Budget', 'arsol-pfw'); ?></h4>
            <p><?php echo esc_html($proposal_budget); ?></p>
        </div>
        <?php endif; ?>

        <?php if ($proposal_timeline): ?>
        <div class="proposal-timeline">
            <h4><?php _e('Timeline', 'arsol-pfw'); ?></h4>
            <p><?php echo esc_html($proposal_timeline); ?></p>
        </div>
        <?php endif; ?>

        <?php if ($related_request_id): ?>
        <div class="proposal-request">
            <h4><?php _e('Related Request', 'arsol-pfw'); ?></h4>
            <p>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-view-request/' . $related_request_id)); ?>">
                    <?php echo esc_html(get_the_title($related_request_id)); ?>
                </a>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($status === 'pending'): ?>
    <div class="proposal-actions">
        <form method="post" class="arsol-proposal-form">
            <?php wp_nonce_field('arsol_proposal_action', 'arsol_proposal_nonce'); ?>
            <input type="hidden" name="proposal_id" value="<?php echo esc_attr($post->ID); ?>">
            
            <div class="form-row">
                <label for="proposal_response"><?php _e('Your Response', 'arsol-pfw'); ?></label>
                <textarea id="proposal_response" name="proposal_response" rows="4" required></textarea>
            </div>

            <div class="form-row">
                <button type="submit" name="proposal_action" value="approve" class="button button-primary">
                    <?php _e('Approve Proposal', 'arsol-pfw'); ?>
                </button>
                <button type="submit" name="proposal_action" value="reject" class="button">
                    <?php _e('Reject Proposal', 'arsol-pfw'); ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
