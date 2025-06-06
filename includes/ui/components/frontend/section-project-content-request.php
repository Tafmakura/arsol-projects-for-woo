<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get request details
$request_budget = get_post_meta($post->ID, '_request_budget', true);
$request_timeline = get_post_meta($post->ID, '_request_timeline', true);
$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <h3 class="project-title"><?php echo esc_html($post->post_title); ?></h3>
        <div class="project-description">
            <?php echo wp_kses_post($post->post_content); ?>
        </div>
        
        <?php if ($status === 'pending'): ?>
            <div class="request-actions-form">
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
</div>
