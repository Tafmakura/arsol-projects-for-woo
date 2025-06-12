<?php
/**
 * Admin Template: Request - General Settings (Column 1)
 *
 * This template displays general request settings, dates, customer info, and request management fields.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current post
global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

// Get request data
$request_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$request_status_terms = wp_get_object_terms($request_id, 'arsol-request-status', array('fields' => 'slugs'));
$request_status = !empty($request_status_terms) ? $request_status_terms[0] : 'pending';
$budget_data = get_post_meta($request_id, '_request_budget', true);
$start_date = get_post_meta($request_id, '_request_start_date', true);
$delivery_date = get_post_meta($request_id, '_request_delivery_date', true);

// Get all request statuses
$all_statuses = get_terms(array(
    'taxonomy' => 'arsol-request-status',
    'hide_empty' => false,
));

// Additional request metadata
$request_priority = get_post_meta($request_id, '_request_priority', true);
$request_urgency = get_post_meta($request_id, '_request_urgency', true);
$request_category = get_post_meta($request_id, '_request_category', true);
?>

<div class="arsol-request-general-settings">

    <!-- Request Timeline -->
    <div class="arsol-section-header">
        <h4><?php _e('Request Timeline', 'arsol-pfw'); ?></h4>
        <small><?php _e('Customer requirements and deadlines', 'arsol-pfw'); ?></small>
    </div>

    <div class="form-field-row">
        <p class="form-field form-field-half">
            <label for="request_start_date"><?php _e('Required Start Date:', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="request_start_date" 
                   name="request_start_date" 
                   value="<?php echo esc_attr($start_date); ?>"
                   class="widefat arsol-date-input">
            <small class="arsol-field-help"><?php _e('When customer needs project to start', 'arsol-pfw'); ?></small>
        </p>
        <p class="form-field form-field-half">
            <label for="request_delivery_date"><?php _e('Required Delivery Date:', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="request_delivery_date" 
                   name="request_delivery_date" 
                   value="<?php echo esc_attr($delivery_date); ?>"
                   class="widefat arsol-date-input">
            <small class="arsol-field-help"><?php _e('Customer required completion date', 'arsol-pfw'); ?></small>
        </p>
    </div>

    <!-- Customer Information -->
    <div class="arsol-section-header">
        <h4><?php _e('Customer Information', 'arsol-pfw'); ?></h4>
        <small><?php _e('Request submitter and customer details', 'arsol-pfw'); ?></small>
    </div>

    <p class="form-field form-field-wide wc-customer-user">
        <label for="post_author_override">
            <?php _e('Customer:', 'arsol-pfw'); ?>
            <?php if ($customer): ?>
                <div class="arsol-customer-quick-links">
                    <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-pfw-request&author=' . $customer_id); ?>" class="arsol-quick-link">
                        <span class="dashicons dashicons-format-chat"></span>
                        <?php _e('View Requests', 'arsol-pfw'); ?>
                    </a>
                    <a href="<?php echo admin_url('user-edit.php?user_id=' . $customer_id); ?>" class="arsol-quick-link">
                        <span class="dashicons dashicons-admin-users"></span>
                        <?php _e('Profile', 'arsol-pfw'); ?>
                    </a>
                    <a href="<?php echo admin_url('edit.php?post_type=shop_order&_customer_user=' . $customer_id); ?>" class="arsol-quick-link">
                        <span class="dashicons dashicons-cart"></span>
                        <?php _e('Orders', 'arsol-pfw'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </label>
        <?php
        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'wc-customer-search arsol-enhanced-select',
            'show_option_none' => __('Select Customer', 'arsol-pfw'),
            'option_none_value' => ''
        ));
        echo $author_dropdown;
        ?>
        <small class="arsol-field-help"><?php _e('Customer who submitted this request', 'arsol-pfw'); ?></small>
    </p>

    <!-- Request Classification -->
    <div class="arsol-section-header">
        <h4><?php _e('Request Classification', 'arsol-pfw'); ?></h4>
        <small><?php _e('Status and priority management', 'arsol-pfw'); ?></small>
    </div>

    <p class="form-field form-field-wide">
        <label for="request_status"><?php _e('Request Status:', 'arsol-pfw'); ?></label>
        <select id="request_status" name="request_status" class="wc-enhanced-select arsol-enhanced-select">
            <?php foreach ($all_statuses as $status) : ?>
                <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($request_status, $status->slug); ?>>
                    <?php echo esc_html($status->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small class="arsol-field-help"><?php _e('Current request workflow status', 'arsol-pfw'); ?></small>
    </p>

    <!-- Priority & Urgency -->
    <div class="form-field-row">
        <p class="form-field form-field-half">
            <label for="request_priority"><?php _e('Priority Level:', 'arsol-pfw'); ?></label>
            <select id="request_priority" name="request_priority" class="wc-enhanced-select arsol-enhanced-select">
                <option value=""><?php _e('Select Priority', 'arsol-pfw'); ?></option>
                <option value="low" <?php selected($request_priority, 'low'); ?>><?php _e('Low', 'arsol-pfw'); ?></option>
                <option value="normal" <?php selected($request_priority, 'normal'); ?>><?php _e('Normal', 'arsol-pfw'); ?></option>
                <option value="high" <?php selected($request_priority, 'high'); ?>><?php _e('High', 'arsol-pfw'); ?></option>
                <option value="urgent" <?php selected($request_priority, 'urgent'); ?>><?php _e('Urgent', 'arsol-pfw'); ?></option>
            </select>
            <small class="arsol-field-help"><?php _e('Request priority level', 'arsol-pfw'); ?></small>
        </p>
        <p class="form-field form-field-half">
            <label for="request_urgency"><?php _e('Urgency Level:', 'arsol-pfw'); ?></label>
            <select id="request_urgency" name="request_urgency" class="wc-enhanced-select arsol-enhanced-select">
                <option value=""><?php _e('Select Urgency', 'arsol-pfw'); ?></option>
                <option value="low" <?php selected($request_urgency, 'low'); ?>><?php _e('Low', 'arsol-pfw'); ?></option>
                <option value="medium" <?php selected($request_urgency, 'medium'); ?>><?php _e('Medium', 'arsol-pfw'); ?></option>
                <option value="high" <?php selected($request_urgency, 'high'); ?>><?php _e('High', 'arsol-pfw'); ?></option>
                <option value="critical" <?php selected($request_urgency, 'critical'); ?>><?php _e('Critical', 'arsol-pfw'); ?></option>
            </select>
            <small class="arsol-field-help"><?php _e('Customer urgency level', 'arsol-pfw'); ?></small>
        </p>
    </div>

    <!-- Budget Information -->
    <div class="arsol-section-header">
        <h4><?php _e('Budget Information', 'arsol-pfw'); ?></h4>
        <small><?php _e('Customer budget specifications', 'arsol-pfw'); ?></small>
    </div>

    <p class="form-field form-field-wide">
        <label for="request_budget"><?php 
        $currency = !empty($budget_data['currency']) ? $budget_data['currency'] : get_woocommerce_currency();
        echo sprintf(__('Budget (%s):', 'arsol-pfw'), $currency); 
        ?></label>
        <input type="text"
               id="request_budget" 
               name="request_budget" 
               value="<?php echo esc_attr(!empty($budget_data['amount']) ? $budget_data['amount'] : ''); ?>"
               class="widefat arsol-money-input"
               inputmode="decimal"
               placeholder="0.00">
        <small class="arsol-field-help"><?php _e('Customer\'s budget for this project', 'arsol-pfw'); ?></small>
    </p>

    <!-- Request Category -->
    <p class="form-field form-field-wide">
        <label for="request_category"><?php _e('Request Category:', 'arsol-pfw'); ?></label>
        <select id="request_category" name="request_category" class="wc-enhanced-select arsol-enhanced-select">
            <option value=""><?php _e('Select Category', 'arsol-pfw'); ?></option>
            <option value="web_development" <?php selected($request_category, 'web_development'); ?>><?php _e('Web Development', 'arsol-pfw'); ?></option>
            <option value="design" <?php selected($request_category, 'design'); ?>><?php _e('Design', 'arsol-pfw'); ?></option>
            <option value="maintenance" <?php selected($request_category, 'maintenance'); ?>><?php _e('Maintenance', 'arsol-pfw'); ?></option>
            <option value="consulting" <?php selected($request_category, 'consulting'); ?>><?php _e('Consulting', 'arsol-pfw'); ?></option>
            <option value="support" <?php selected($request_category, 'support'); ?>><?php _e('Support', 'arsol-pfw'); ?></option>
            <option value="other" <?php selected($request_category, 'other'); ?>><?php _e('Other', 'arsol-pfw'); ?></option>
        </select>
        <small class="arsol-field-help"><?php _e('Type of work requested', 'arsol-pfw'); ?></small>
    </p>

    <!-- Quick Stats -->
    <div class="arsol-section-header">
        <h4><?php _e('Request Overview', 'arsol-pfw'); ?></h4>
        <small><?php _e('Key metrics and timeline', 'arsol-pfw'); ?></small>
    </div>

    <div class="arsol-quick-stats-grid">
        <?php
        // Calculate request age
        $created_date = get_the_date('Y-m-d H:i:s', $request_id);
        $request_age_days = floor((time() - strtotime($created_date)) / (60 * 60 * 24));
        
        // Calculate days until required start
        $days_until_start = '';
        if (!empty($start_date)) {
            $days_until_start = floor((strtotime($start_date) - time()) / (60 * 60 * 24));
        }
        
        // Calculate days until required delivery
        $days_until_delivery = '';
        if (!empty($delivery_date)) {
            $days_until_delivery = floor((strtotime($delivery_date) - time()) / (60 * 60 * 24));
        }
        
        // Calculate project duration
        $project_duration = 0;
        if (!empty($start_date) && !empty($delivery_date)) {
            $project_duration = floor((strtotime($delivery_date) - strtotime($start_date)) / (60 * 60 * 24));
        }
        
        // Get attachments count
        $attachments = get_attached_media('', $request_id);
        $attachment_count = count($attachments);
        ?>
        
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo intval($request_age_days); ?></span>
            <span class="arsol-stat-label"><?php _e('Days Old', 'arsol-pfw'); ?></span>
        </div>
        
        <?php if (!empty($start_date)): ?>
        <div class="arsol-stat-card <?php echo $days_until_start < 0 ? 'overdue' : ($days_until_start <= 7 ? 'urgent' : ''); ?>">
            <span class="arsol-stat-number">
                <?php echo abs($days_until_start); ?>
            </span>
            <span class="arsol-stat-label">
                <?php echo $days_until_start < 0 ? __('Days Late', 'arsol-pfw') : __('Days Until Start', 'arsol-pfw'); ?>
            </span>
        </div>
        <?php endif; ?>
        
        <?php if ($project_duration > 0): ?>
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo intval($project_duration); ?></span>
            <span class="arsol-stat-label"><?php _e('Duration (Days)', 'arsol-pfw'); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo intval($attachment_count); ?></span>
            <span class="arsol-stat-label"><?php _e('Attachments', 'arsol-pfw'); ?></span>
        </div>
        
        <?php if (!empty($budget_data['amount'])): ?>
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo wc_price($budget_data['amount']); ?></span>
            <span class="arsol-stat-label"><?php _e('Budget', 'arsol-pfw'); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Workflow Actions Section -->
    <div class="arsol-section-header">
        <h4><?php _e('Workflow Actions', 'arsol-pfw'); ?></h4>
        <small><?php _e('Request management tools', 'arsol-pfw'); ?></small>
    </div>

    <div class="arsol-workflow-actions">
        <?php
        $is_disabled = $request_status !== 'under-review';
        $convert_url = admin_url('admin-post.php?action=arsol_convert_to_proposal&request_id=' . $request_id);
        $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_proposal_nonce');
        $confirm_message = esc_js(__('Are you sure you want to convert this request to a proposal? This action cannot be undone and will delete the original request.', 'arsol-pfw'));
        $tooltip_text = $is_disabled 
            ? __('The request status must be "Under Review" to enable conversion.', 'arsol-pfw')
            : __('Converts this request into a new proposal.', 'arsol-pfw');
        ?>
        <p class="form-field form-field-wide">
            <span title="<?php echo esc_attr($tooltip_text); ?>">
                <a href="#" 
                   class="button button-primary arsol-confirm-conversion<?php if ($is_disabled) echo ' disabled'; ?>" 
                   data-url="<?php echo esc_url($convert_url); ?>" 
                   data-message="<?php echo $confirm_message; ?>"
                   <?php disabled($is_disabled, true); ?>>
                   <span class="dashicons dashicons-media-document"></span>
                   <?php _e('Convert to Proposal', 'arsol-pfw'); ?>
                </a>
            </span>
            <small class="arsol-field-help"><?php _e('Create a proposal based on this request', 'arsol-pfw'); ?></small>
        </p>
    </div>

</div>
