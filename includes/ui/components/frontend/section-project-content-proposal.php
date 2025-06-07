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
        <?php 
        // Check if there's a project overview override for proposals
        if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_project_overview_override('proposal')) {
            echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_project_overview_override('proposal');
        } else {
            // Use default template content
            ?>
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
            <?php
        }
        ?>
    </div>
</div>
