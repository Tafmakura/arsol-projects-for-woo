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
$proposal_project_lead = get_post_meta($proposal_id, '_arsol_pfw_proposal_project_lead', true);

// Check for project-tied proposal - URL parameter first, then meta data
$is_project_tied = false;
$parent_project_data = false;

// ALWAYS check URL parameter first (for new proposals)
if (isset($_GET['parent_project']) && !empty($_GET['parent_project'])) {
    $parent_project_id = intval($_GET['parent_project']);
    $parent_project = get_post($parent_project_id);
    
    if ($parent_project && $parent_project->post_type === 'arsol-project') {
        $is_project_tied = true;
        
        // Get parent project data
        $parent_customer_id = get_post_meta($parent_project_id, '_arsol_pfw_project_customer_id', true);
        $parent_lead_id = get_post_meta($parent_project_id, '_arsol_pfw_project_lead_id', true);
        
        $parent_project_data = array(
            'id' => $parent_project_id,
            'title' => $parent_project->post_title,
            'customer_id' => $parent_customer_id,
            'lead_id' => $parent_lead_id
        );
        
        // Override values with parent project data
        if ($parent_customer_id) {
            $customer_id = $parent_customer_id;
            $customer = get_userdata($customer_id);
        }
        if ($parent_lead_id) {
            $proposal_project_lead = $parent_lead_id;
        }
        $cost_proposal_type = 'quotation'; // Always quotation for project-tied proposals
    }
} 
// Fallback to meta data check (for existing proposals)
elseif ($proposal_id > 0) {
    $parent_project_id = get_post_meta($proposal_id, '_arsol_pfw_parent_project_id', true);
    if (!empty($parent_project_id)) {
        $parent_project = get_post($parent_project_id);
        if ($parent_project && $parent_project->post_type === 'arsol-project') {
            $is_project_tied = true;
            
            // Get parent project data
            $parent_customer_id = get_post_meta($parent_project_id, '_arsol_pfw_project_customer_id', true);
            $parent_lead_id = get_post_meta($parent_project_id, '_arsol_pfw_project_lead_id', true);
            
            $parent_project_data = array(
                'id' => $parent_project_id,
                'title' => $parent_project->post_title,
                'customer_id' => $parent_customer_id,
                'lead_id' => $parent_lead_id
            );
            
            // Override values with parent project data
            if ($parent_customer_id) {
                $customer_id = $parent_customer_id;
                $customer = get_userdata($customer_id);
            }
            if ($parent_lead_id) {
                $proposal_project_lead = $parent_lead_id;
            }
            $cost_proposal_type = 'quotation'; // Always quotation for project-tied proposals
        }
    }
}

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
        
        <?php if ($is_project_tied && $customer): ?>
            <!-- Locked customer field for project-tied proposals -->
            <select class="arsol-disabled-select" disabled>
                <option selected><?php echo esc_html(\Arsol_Projects_For_Woo\Woocommerce::format_customer_admin_display($customer)); ?></option>
            </select>
            <input type="hidden" name="post_author_override" value="<?php echo esc_attr($customer_id); ?>">
        <?php else: ?>
            <!-- Regular customer search field -->
            <select class="wc-customer-search" name="post_author_override" data-placeholder="<?php esc_attr_e('Search for customer...', 'arsol-pfw'); ?>" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="<?php echo esc_attr(wp_create_nonce('search-customers')); ?>" required>
                <?php if ($post->post_author): ?>
                    <?php 
                    $customer_user = get_userdata($post->post_author);
                    if ($customer_user) {
                        $customer_display = \Arsol_Projects_For_Woo\Woocommerce::format_customer_admin_display($customer_user);
                        
                        printf(
                            '<option value="%s" selected="selected">%s</option>',
                            esc_attr($customer_user->ID),
                            esc_html($customer_display)
                        );
                    }
                    ?>
                <?php endif; ?>
            </select>
        <?php endif; ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_project_lead"><?php _e('Project Lead:', 'arsol-pfw'); ?></label>
        <?php if ($is_project_tied): ?>
            <!-- Locked project lead field for project-tied proposals -->
            <?php 
            $lead_user = get_userdata($proposal_project_lead);
            if ($lead_user): ?>
                <select class="arsol-disabled-select" disabled>
                    <option selected><?php echo esc_html($lead_user->display_name . ' (' . $lead_user->user_email . ')'); ?></option>
                </select>
                <input type="hidden" name="proposal_project_lead" value="<?php echo esc_attr($proposal_project_lead); ?>">
            <?php else: ?>
                <!-- No project lead assigned to parent project -->
                <select class="arsol-disabled-select" disabled>
                    <option selected><?php _e('No project lead assigned', 'arsol-pfw'); ?></option>
                </select>
                <input type="hidden" name="proposal_project_lead" value="">
            <?php endif; ?>
        <?php else: ?>
            <!-- Regular project lead search field -->
            <?php
            \Arsol_Projects_For_Woo\Admin\Users::render_project_lead_search_field(array(
                'name' => 'proposal_project_lead',
                'id' => 'proposal_project_lead',
                'selected' => $proposal_project_lead,
                'placeholder' => __('Search for project lead...', 'arsol-pfw')
            ));
            ?>
        <?php endif; ?>
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
        <?php if ($is_project_tied): ?>
            <!-- Locked cost type field for project-tied proposals -->
            <select class="arsol-disabled-select" disabled>
                <option selected><?php _e('Quotation', 'arsol-pfw'); ?></option>
            </select>
            <input type="hidden" name="arsol_pfw_proposal_costing_type" value="quotation">
        <?php else: ?>
            <!-- Regular cost type field -->
            <select id="arsol_pfw_proposal_costing_type" name="arsol_pfw_proposal_costing_type" class="wc-enhanced-select">
                <option value="none" <?php selected($cost_proposal_type, 'none'); ?>><?php _e('None', 'arsol-pfw'); ?></option>
                <option value="budget" <?php selected($cost_proposal_type, 'budget'); ?>><?php _e('Budget', 'arsol-pfw'); ?></option>
                <option value="quotation" <?php selected($cost_proposal_type, 'quotation'); ?>><?php _e('Quotation', 'arsol-pfw'); ?></option>
            </select>
        <?php endif; ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label for="arsol_pfw_proposal_expiration_date"><?php _e('Proposal Expiration Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="arsol_pfw_proposal_expiration_date" name="arsol_pfw_proposal_expiration_date" value="<?php echo esc_attr($expiration_date); ?>" class="widefat">
    </p>
</div>
