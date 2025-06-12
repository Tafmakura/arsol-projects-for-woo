<?php
/**
 * Admin Template: Proposal - General Settings (Column 1)
 *
 * This template displays general proposal settings, dates, customer info, and proposal management fields.
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

// Additional proposal metadata
$proposal_priority = get_post_meta($proposal_id, '_proposal_priority', true);
$proposal_category = get_post_meta($proposal_id, '_proposal_category', true);
$proposal_version = get_post_meta($proposal_id, '_proposal_version', true);
?>

<div class="arsol-proposal-general-settings">

    <!-- Proposal Timeline -->
    <div class="arsol-section-header">
        <h4><?php _e('Proposal Timeline', 'arsol-pfw'); ?></h4>
        <small><?php _e('Proposed dates and deadlines', 'arsol-pfw'); ?></small>
    </div>

    <div class="form-field-row">
        <p class="form-field form-field-half">
            <label for="proposal_start_date"><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="proposal_start_date" 
                   name="proposal_start_date" 
                   value="<?php echo esc_attr($start_date); ?>"
                   class="widefat arsol-date-input">
            <small class="arsol-field-help"><?php _e('When the project would begin', 'arsol-pfw'); ?></small>
        </p>
        <p class="form-field form-field-half">
            <label for="proposal_delivery_date"><?php _e('Proposed Delivery Date:', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="proposal_delivery_date" 
                   name="proposal_delivery_date" 
                   value="<?php echo esc_attr($delivery_date); ?>"
                   class="widefat arsol-date-input">
            <small class="arsol-field-help"><?php _e('Expected project completion', 'arsol-pfw'); ?></small>
        </p>
    </div>

    <p class="form-field form-field-half">
        <label for="proposal_expiration_date"><?php _e('Proposal Expiration Date:', 'arsol-pfw'); ?></label>
        <input type="date" 
               id="proposal_expiration_date" 
               name="proposal_expiration_date" 
               value="<?php echo esc_attr($expiration_date); ?>"
               class="widefat arsol-date-input">
        <small class="arsol-field-help"><?php _e('When this proposal expires', 'arsol-pfw'); ?></small>
    </p>

    <!-- Customer & Assignment -->
    <div class="arsol-section-header">
        <h4><?php _e('Customer & Ownership', 'arsol-pfw'); ?></h4>
        <small><?php _e('Customer and proposal management', 'arsol-pfw'); ?></small>
    </div>

    <p class="form-field form-field-wide wc-customer-user">
        <label for="post_author_override">
            <?php _e('Customer:', 'arsol-pfw'); ?>
            <?php if ($customer): ?>
                <div class="arsol-customer-quick-links">
                    <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-pfw-proposal&author=' . $customer_id); ?>" class="arsol-quick-link">
                        <span class="dashicons dashicons-media-document"></span>
                        <?php _e('View Proposals', 'arsol-pfw'); ?>
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
        <small class="arsol-field-help"><?php _e('The customer this proposal is for', 'arsol-pfw'); ?></small>
    </p>

    <!-- Proposal Status & Type -->
    <div class="arsol-section-header">
        <h4><?php _e('Proposal Classification', 'arsol-pfw'); ?></h4>
        <small><?php _e('Status and proposal configuration', 'arsol-pfw'); ?></small>
    </div>

    <p class="form-field form-field-wide">
        <label for="proposal_secondary_status"><?php _e('Internal Status:', 'arsol-pfw'); ?></label>
        <?php
        $secondary_status = get_post_meta($proposal_id, '_proposal_secondary_status', true);
        if (empty($secondary_status)) {
            $secondary_status = 'processing'; // Default value
        }
        ?>
        <select id="proposal_secondary_status" name="proposal_secondary_status" class="wc-enhanced-select arsol-enhanced-select">
            <option value="draft" <?php selected($secondary_status, 'draft'); ?>><?php _e('Draft', 'arsol-pfw'); ?></option>
            <option value="ready_for_review" <?php selected($secondary_status, 'ready_for_review'); ?>><?php _e('Ready for Review', 'arsol-pfw'); ?></option>
            <option value="processing" <?php selected($secondary_status, 'processing'); ?>><?php _e('Processing', 'arsol-pfw'); ?></option>
            <option value="sent_to_customer" <?php selected($secondary_status, 'sent_to_customer'); ?>><?php _e('Sent to Customer', 'arsol-pfw'); ?></option>
        </select>
        <small class="arsol-field-help"><?php _e('Internal proposal workflow status', 'arsol-pfw'); ?></small>
    </p>

    <p class="form-field form-field-wide">
        <label for="cost_proposal_type"><?php _e('Cost Proposal Type:', 'arsol-pfw'); ?></label>
        <select id="cost_proposal_type" name="cost_proposal_type" class="wc-enhanced-select arsol-enhanced-select">
            <option value="none" <?php selected($cost_proposal_type, 'none'); ?>><?php _e('None', 'arsol-pfw'); ?></option>
            <option value="budget_estimates" <?php selected($cost_proposal_type, 'budget_estimates'); ?>><?php _e('Budget Estimates', 'arsol-pfw'); ?></option>
            <option value="invoice_line_items" <?php selected($cost_proposal_type, 'invoice_line_items'); ?>><?php _e('Invoice Line Items', 'arsol-pfw'); ?></option>
        </select>
        <small class="arsol-field-help"><?php _e('How costs are presented to the customer', 'arsol-pfw'); ?></small>
    </p>

    <!-- Priority Level -->
    <p class="form-field form-field-wide">
        <label for="proposal_priority"><?php _e('Priority Level:', 'arsol-pfw'); ?></label>
        <select id="proposal_priority" name="proposal_priority" class="wc-enhanced-select arsol-enhanced-select">
            <option value=""><?php _e('Select Priority', 'arsol-pfw'); ?></option>
            <option value="low" <?php selected($proposal_priority, 'low'); ?>><?php _e('Low', 'arsol-pfw'); ?></option>
            <option value="normal" <?php selected($proposal_priority, 'normal'); ?>><?php _e('Normal', 'arsol-pfw'); ?></option>
            <option value="high" <?php selected($proposal_priority, 'high'); ?>><?php _e('High', 'arsol-pfw'); ?></option>
            <option value="urgent" <?php selected($proposal_priority, 'urgent'); ?>><?php _e('Urgent', 'arsol-pfw'); ?></option>
        </select>
        <small class="arsol-field-help"><?php _e('Proposal priority level', 'arsol-pfw'); ?></small>
    </p>

    <!-- Version Control -->
    <p class="form-field form-field-wide">
        <label for="proposal_version"><?php _e('Proposal Version:', 'arsol-pfw'); ?></label>
        <input type="text"
               id="proposal_version" 
               name="proposal_version" 
               value="<?php echo esc_attr(!empty($proposal_version) ? $proposal_version : '1.0'); ?>"
               class="widefat arsol-version-input">
        <small class="arsol-field-help"><?php _e('Version number for tracking revisions', 'arsol-pfw'); ?></small>
    </p>

    <!-- Quick Stats -->
    <div class="arsol-section-header">
        <h4><?php _e('Proposal Overview', 'arsol-pfw'); ?></h4>
        <small><?php _e('Key metrics and status', 'arsol-pfw'); ?></small>
    </div>

    <div class="arsol-quick-stats-grid">
        <?php
        // Calculate proposal age
        $created_date = get_the_date('Y-m-d H:i:s', $proposal_id);
        $proposal_age_days = floor((time() - strtotime($created_date)) / (60 * 60 * 24));
        
        // Calculate days until expiration
        $days_until_expiration = '';
        $is_expired = false;
        if (!empty($expiration_date)) {
            $days_until_expiration = floor((strtotime($expiration_date) - time()) / (60 * 60 * 24));
            $is_expired = $days_until_expiration < 0;
        }
        
        // Calculate project duration
        $project_duration = 0;
        if (!empty($start_date) && !empty($delivery_date)) {
            $project_duration = floor((strtotime($delivery_date) - strtotime($start_date)) / (60 * 60 * 24));
        }
        
        // Get invoice data for totals
        $invoice_data = get_post_meta($proposal_id, '_proposal_invoice_data', true);
        $total_amount = 0;
        if (!empty($invoice_data['products'])) {
            foreach ($invoice_data['products'] as $product) {
                $total_amount += floatval($product['total']);
            }
        }
        if (!empty($invoice_data['recurring_fees'])) {
            foreach ($invoice_data['recurring_fees'] as $fee) {
                $total_amount += floatval($fee['amount']);
            }
        }
        ?>
        
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo intval($proposal_age_days); ?></span>
            <span class="arsol-stat-label"><?php _e('Days Old', 'arsol-pfw'); ?></span>
        </div>
        
        <?php if (!empty($expiration_date)): ?>
        <div class="arsol-stat-card <?php echo $is_expired ? 'expired' : ($days_until_expiration <= 7 ? 'urgent' : ''); ?>">
            <span class="arsol-stat-number">
                <?php echo abs($days_until_expiration); ?>
            </span>
            <span class="arsol-stat-label">
                <?php echo $is_expired ? __('Days Expired', 'arsol-pfw') : __('Days Left', 'arsol-pfw'); ?>
            </span>
        </div>
        <?php endif; ?>
        
        <?php if ($project_duration > 0): ?>
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo intval($project_duration); ?></span>
            <span class="arsol-stat-label"><?php _e('Duration (Days)', 'arsol-pfw'); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($total_amount > 0): ?>
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo wc_price($total_amount); ?></span>
            <span class="arsol-stat-label"><?php _e('Total Value', 'arsol-pfw'); ?></span>
        </div>
        <?php endif; ?>
    </div>

</div>
