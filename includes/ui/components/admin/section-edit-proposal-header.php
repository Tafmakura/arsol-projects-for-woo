<?php
/**
 * Admin Template: Edit Proposal Header Container
 *
 * This container appears below the title and above the WYSIWYG editor.
 * Inspired by WooCommerce order data panel structure.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current post
global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

// Get proposal data
$proposal_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$proposal_status = get_post_status($post);
$start_date = get_post_meta($proposal_id, '_proposal_start_date', true);
$delivery_date = get_post_meta($proposal_id, '_proposal_delivery_date', true);
$expiration_date = get_post_meta($proposal_id, '_proposal_expiration_date', true);
$cost_proposal_type = get_post_meta($proposal_id, '_cost_proposal_type', true);

// Check if has original request data
$has_request_data = false;
$original_request_id = get_post_meta($proposal_id, '_original_request_id', true);
if ($original_request_id || 
    get_post_meta($proposal_id, '_original_request_budget', true) ||
    get_post_meta($proposal_id, '_original_request_start_date', true) ||
    get_post_meta($proposal_id, '_original_request_delivery_date', true)) {
    $has_request_data = true;
}
?>

<div id="arsol-pfw-project-data" class="postbox ">
    <div id="proposal_metabox" class="panel-wrap woocommerce">
        <div id="order_data" class="panel woocommerce">
            <h2>
                <?php printf(__('Proposal #%d details', 'arsol-pfw'), $proposal_id); ?>
            </h2>

            <div class="order_data_column_container">
                <div class="order_data_column">
                    <h3><?php _e('General', 'arsol-pfw'); ?></h3>

                    <p class="form-field form-field-wide wc-customer-user">
                        <label for="post_author_override">
                            <?php _e('Customer:', 'arsol-pfw'); ?>
                            <?php if ($customer): ?>
                                <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-pfw-proposal&author=' . $customer_id); ?>">
                                    <?php _e('View other proposals →', 'arsol-pfw'); ?>
                                </a>
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $customer_id); ?>">
                                    <?php _e('Profile →', 'arsol-pfw'); ?>
                                </a>
                            <?php endif; ?>
                        </label>
                        <?php
                        // Get author dropdown
                        $author_dropdown = wp_dropdown_users(array(
                            'name' => 'post_author_override',
                            'selected' => $post->post_author,
                            'include_selected' => true,
                            'echo' => false,
                            'class' => 'wc-customer-search'
                        ));
                        echo $author_dropdown;
                        ?>
                    </p>

                    <p class="form-field form-field-wide">
                        <label><?php _e('Proposal status:', 'arsol-pfw'); ?></label>
                        <span class="proposal-status status-<?php echo esc_attr($proposal_status); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $proposal_status)); ?>
                        </span>
                    </p>

                    <p class="form-field form-field-wide">
                        <label for="cost_proposal_type"><?php _e('Cost proposal type:', 'arsol-pfw'); ?></label>
                        <select id="cost_proposal_type" name="cost_proposal_type" class="wc-enhanced-select">
                            <option value="none" <?php selected($cost_proposal_type, 'none'); ?>><?php _e('None', 'arsol-pfw'); ?></option>
                            <option value="budget_estimates" <?php selected($cost_proposal_type, 'budget_estimates'); ?>><?php _e('Budget Estimates', 'arsol-pfw'); ?></option>
                            <option value="invoice_line_items" <?php selected($cost_proposal_type, 'invoice_line_items'); ?>><?php _e('Invoice Line Items', 'arsol-pfw'); ?></option>
                        </select>
                    </p>

                    <?php if ($start_date): ?>
                    <p class="form-field form-field-wide">
                        <label><?php _e('Proposed start date:', 'arsol-pfw'); ?></label>
                        <span><?php echo date_i18n(get_option('date_format'), strtotime($start_date)); ?></span>
                    </p>
                    <?php endif; ?>

                    <?php if ($delivery_date): ?>
                    <p class="form-field form-field-wide">
                        <label><?php _e('Proposed delivery date:', 'arsol-pfw'); ?></label>
                        <span><?php echo date_i18n(get_option('date_format'), strtotime($delivery_date)); ?></span>
                    </p>
                    <?php endif; ?>

                    <?php if ($expiration_date): ?>
                    <p class="form-field form-field-wide">
                        <label><?php _e('Proposal expiration date:', 'arsol-pfw'); ?></label>
                        <span class="expiration-date"><?php echo date_i18n(get_option('date_format'), strtotime($expiration_date)); ?></span>
                    </p>
                    <?php endif; ?>
                </div>

                <div class="order_double_data_column">
                
                    <?php if ($has_request_data): ?>
                        <h3><?php _e('Original Request', 'arsol-pfw'); ?></h3>
                        
                        <?php
                        // Hook for customer request details
                        do_action('arsol_proposal_header_content', $post);
                        ?>
                    <?php endif; ?>

                    <h3><?php _e('Actions', 'arsol-pfw'); ?></h3>
                    
                    <p class="form-field form-field-wide">
                        <?php
                        $is_disabled = $post->post_status !== 'publish';
                        $convert_url = admin_url('admin-post.php?action=arsol_convert_to_project&proposal_id=' . $post->ID);
                        $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_project_nonce');
                        $confirm_message = esc_js(__('Are you sure you want to convert this proposal to a project?', 'arsol-pfw'));
                        $tooltip_text = $is_disabled
                            ? __('The proposal must be published before it can be converted.', 'arsol-pfw')
                            : __('Converts this proposal into a new project.', 'arsol-pfw');
                        ?>
                        <span title="<?php echo esc_attr($tooltip_text); ?>">
                            <input type="button" 
                                class="button button-primary arsol-confirm-conversion" 
                                value="<?php _e('Convert to Project', 'arsol-pfw'); ?>" 
                                data-url="<?php echo esc_url($convert_url); ?>" 
                                data-message="<?php echo $confirm_message; ?>"
                                <?php disabled($is_disabled, true); ?> />
                        </span>
                    </p>

                </div>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</div>

