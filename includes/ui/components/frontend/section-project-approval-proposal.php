<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get proposal details
$proposal_budget = get_post_meta($post->ID, '_proposal_budget', true);
$proposal_timeline = get_post_meta($post->ID, '_proposal_timeline', true);
$related_request_id = get_post_meta($post->ID, '_related_request_id', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>
        <div class="project-description">
            <?php echo wp_kses_post($post->post_content); ?>
        </div>
        
        <?php if ($status === 'pending'): ?>
            <div class="proposal-actions-form">
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
    <div class="project-sidebar">
        <div class="project-sidebar-wrapper">
            <div class="project-sidebar-card card">
                <div class="project-details">
                    <h4><?php _e('Proposal Details', 'arsol-pfw'); ?></h4>
                    <div class="project-meta">
                        <p><strong><?php _e('Status:', 'arsol-pfw'); ?></strong> <?php echo esc_html($status); ?></p>
                        <?php if ($proposal_budget): ?>
                            <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo esc_html($proposal_budget); ?></p>
                        <?php endif; ?>
                        <?php if ($proposal_timeline): ?>
                            <p><strong><?php _e('Timeline:', 'arsol-pfw'); ?></strong> <?php echo esc_html($proposal_timeline); ?></p>
                        <?php endif; ?>
                         <p><strong><?php _e('Date:', 'arsol-pfw'); ?></strong> <?php echo get_the_date('', $post->ID); ?></p>
                        <?php if ($related_request_id): ?>
                            <p><strong><?php _e('Related Request:', 'arsol-pfw'); ?></strong> 
                                <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-view-request/' . $related_request_id)); ?>">
                                    <?php echo esc_html(get_the_title($related_request_id)); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
