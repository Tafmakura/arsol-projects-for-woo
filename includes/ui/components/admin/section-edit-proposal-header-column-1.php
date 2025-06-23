<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$proposal_status = get_post_status($post);
$start_date = get_post_meta($proposal_id, '_arsol_pfw_proposal_start_date', true);
$delivery_date = get_post_meta($proposal_id, '_arsol_pfw_proposal_delivery_date', true);
$expiration_date = get_post_meta($proposal_id, '_arsol_pfw_proposal_expiration_date', true);
$cost_proposal_type = get_post_meta($proposal_id, '_arsol_pfw_proposal_costing_type', true);

// Get status terms
$proposal_status_terms = wp_get_object_terms($proposal_id, 'arsol-proposal-status', array('fields' => 'slugs'));
$current_proposal_status = !empty($proposal_status_terms) ? $proposal_status_terms[0] : 'processing';

// Get all available statuses
$all_proposal_statuses = get_terms(array(
    'taxonomy' => 'arsol-proposal-status',
    'hide_empty' => false,
));
?>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label for="arsol_pfw_proposal_start_date"><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="arsol_pfw_proposal_start_date" name="arsol_pfw_proposal_start_date" value="<?php echo esc_attr($start_date); ?>" class="widefat">
    </p>
    <p class="form-field form-field-half">
        <label for="arsol_pfw_proposal_delivery_date"><?php _e('Proposed Delivery Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="arsol_pfw_proposal_delivery_date" name="arsol_pfw_proposal_delivery_date" value="<?php echo esc_attr($delivery_date); ?>" class="widefat">
    </p>
</div>

<div class="form-field-row">
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
        <select class="wc-customer-search" name="post_author_override" data-placeholder="<?php esc_attr_e('Search for customer...', 'arsol-pfw'); ?>" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="<?php echo esc_attr(wp_create_nonce('search-customers')); ?>" required>
            <?php if ($post->post_author): ?>
                <?php 
                $customer_user = get_userdata($post->post_author);
                if ($customer_user) {
                    // Format customer display like WooCommerce: "First Last (#ID – email)" or fallback to "Display Name (#ID – email)"
                    $customer_name = trim($customer_user->first_name . ' ' . $customer_user->last_name);
                    if (empty($customer_name)) {
                        $customer_name = $customer_user->display_name;
                    }
                    
                    printf(
                        '<option value="%s" selected="selected">%s (#%s &ndash; %s)</option>',
                        esc_attr($customer_user->ID),
                        esc_html($customer_name),
                        esc_html($customer_user->ID),
                        esc_html($customer_user->user_email)
                    );
                }
                ?>
            <?php endif; ?>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_project_lead"><?php _e('Project Lead:', 'arsol-pfw'); ?></label>
        <?php
        // Get current project lead
        $proposal_project_lead = get_post_meta($proposal_id, '_arsol_pfw_proposal_project_lead', true);
        
        // Get users who can create projects based on Project Manager Roles setting
        $admin_users_helper = new \Arsol_Projects_For_Woo\Admin\Users();
        $project_lead_users = get_users(array(
            'fields' => array('ID', 'display_name'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'manage_projects',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities', 
                    'value' => 'create_projects',
                    'compare' => 'LIKE'
                )
            )
        ));
        
        // Filter to only users who can actually create projects
        $valid_user_ids = array();
        foreach ($project_lead_users as $user) {
            if ($admin_users_helper->can_user_create_projects($user->ID)) {
                $valid_user_ids[] = $user->ID;
            }
        }
        
        // Use WordPress native dropdown
        wp_dropdown_users(array(
            'name' => 'proposal_project_lead',
            'id' => 'proposal_project_lead',
            'class' => 'arsol-user-select2',
            'selected' => $proposal_project_lead,
            'include' => $valid_user_ids,
            'show_option_none' => __('Search for project lead...', 'arsol-pfw'),
            'option_none_value' => ''
        ));
        ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_status"><?php _e('Proposal Status:', 'arsol-pfw'); ?></label>
        <select id="proposal_status" name="proposal_status" class="wc-enhanced-select">
            <?php if (!empty($all_proposal_statuses) && !is_wp_error($all_proposal_statuses)) : ?>
                <?php foreach ($all_proposal_statuses as $status) : ?>
                    <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($current_proposal_status, $status->slug); ?>>
                        <?php echo esc_html($status->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="arsol_pfw_proposal_costing_type"><?php _e('Cost Proposal Type:', 'arsol-pfw'); ?></label>
                        <select id="arsol_pfw_proposal_costing_type" name="arsol_pfw_proposal_costing_type" class="wc-enhanced-select">
                    <option value="none" <?php selected($cost_proposal_type, 'none'); ?>><?php _e('None', 'arsol-pfw'); ?></option>
                    <option value="budget" <?php selected($cost_proposal_type, 'budget'); ?>><?php _e('Budget', 'arsol-pfw'); ?></option>
                    <option value="quotation" <?php selected($cost_proposal_type, 'quotation'); ?>><?php _e('Quotation', 'arsol-pfw'); ?></option>
                </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label for="arsol_pfw_proposal_expiration_date"><?php _e('Proposal Expiration Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="arsol_pfw_proposal_expiration_date" name="arsol_pfw_proposal_expiration_date" value="<?php echo esc_attr($expiration_date); ?>" class="widefat">
    </p>
</div> 