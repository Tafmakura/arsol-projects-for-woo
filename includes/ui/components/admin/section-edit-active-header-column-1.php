<?php
/**
 * Admin Template: Active Project - General Settings (Column 1)
 *
 * This template displays general project settings, dates, customer info, and project management fields.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the current post
global $post;

if (!$post || $post->post_type !== 'arsol-project') {
    return;
}

// Get project data
$project_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$project_status_terms = wp_get_object_terms($project_id, 'arsol-project-status', array('fields' => 'slugs'));
$project_status = !empty($project_status_terms) ? $project_status_terms[0] : 'not-started';
$project_lead = get_post_meta($project_id, '_project_lead', true);
$start_date = get_post_meta($project_id, '_project_start_date', true);
$due_date = get_post_meta($project_id, '_project_due_date', true);

// Get all project statuses
$all_statuses = get_terms(array(
    'taxonomy' => 'arsol-project-status',
    'hide_empty' => false,
));

// Additional project metadata
$project_priority = get_post_meta($project_id, '_project_priority', true);
$project_category = get_post_meta($project_id, '_project_category', true);
$project_tags = get_post_meta($project_id, '_project_tags', true);
?>

<div class="arsol-project-general-settings">

    <!-- Project Timeline -->
    <div class="arsol-section-header">
        <h4><?php _e('Project Timeline', 'arsol-pfw'); ?></h4>
        <small><?php _e('Key dates and deadlines', 'arsol-pfw'); ?></small>
    </div>

    <div class="form-field-row">
        <p class="form-field form-field-half">
            <label for="project_start_date"><?php _e('Start Date:', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="project_start_date" 
                   name="project_start_date" 
                   value="<?php echo esc_attr($start_date); ?>"
                   class="widefat arsol-date-input">
            <small class="arsol-field-help"><?php _e('Actual or planned project start date', 'arsol-pfw'); ?></small>
        </p>
        <p class="form-field form-field-half">
            <label for="project_due_date"><?php _e('Due Date:', 'arsol-pfw'); ?></label>
            <input type="date" 
                   id="project_due_date" 
                   name="project_due_date" 
                   value="<?php echo esc_attr($due_date); ?>"
                   class="widefat arsol-date-input">
            <small class="arsol-field-help"><?php _e('Project completion deadline', 'arsol-pfw'); ?></small>
        </p>
    </div>

    <!-- Customer & Assignment -->
    <div class="arsol-section-header">
        <h4><?php _e('Assignment & Ownership', 'arsol-pfw'); ?></h4>
        <small><?php _e('Customer and team assignment', 'arsol-pfw'); ?></small>
    </div>

    <p class="form-field form-field-wide wc-customer-user">
        <label for="post_author_override">
            <?php _e('Customer:', 'arsol-pfw'); ?>
            <?php if ($customer): ?>
                <div class="arsol-customer-quick-links">
                    <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-project&author=' . $customer_id); ?>" class="arsol-quick-link">
                        <span class="dashicons dashicons-portfolio"></span>
                        <?php _e('View Projects', 'arsol-pfw'); ?>
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
        // Get author dropdown with enhanced styling
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
        <small class="arsol-field-help"><?php _e('The customer this project belongs to', 'arsol-pfw'); ?></small>
    </p>

    <p class="form-field form-field-wide">
        <label for="project_lead"><?php _e('Project Lead:', 'arsol-pfw'); ?></label>
        <?php
        // Get project lead dropdown (all users with edit capabilities)
        $lead_dropdown = wp_dropdown_users(array(
            'name' => 'project_lead',
            'selected' => $project_lead,
            'include_selected' => true,
            'echo' => false,
            'class' => 'wc-enhanced-select arsol-enhanced-select',
            'show_option_none' => __('Select Project Lead', 'arsol-pfw'),
            'option_none_value' => '',
            'capability' => array('edit_posts')
        ));
        echo $lead_dropdown;
        ?>
        <small class="arsol-field-help"><?php _e('Team member responsible for this project', 'arsol-pfw'); ?></small>
    </p>

    <!-- Project Classification -->
    <div class="arsol-section-header">
        <h4><?php _e('Project Classification', 'arsol-pfw'); ?></h4>
        <small><?php _e('Status and categorization', 'arsol-pfw'); ?></small>
    </div>

    <p class="form-field form-field-wide">
        <label for="project_status"><?php _e('Project Status:', 'arsol-pfw'); ?></label>
        <select id="project_status" name="project_status" class="wc-enhanced-select arsol-enhanced-select">
            <?php foreach ($all_statuses as $status) : ?>
                <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($project_status, $status->slug); ?>>
                    <?php echo esc_html($status->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small class="arsol-field-help"><?php _e('Current project status', 'arsol-pfw'); ?></small>
    </p>

    <!-- Priority Level -->
    <p class="form-field form-field-wide">
        <label for="project_priority"><?php _e('Priority Level:', 'arsol-pfw'); ?></label>
        <select id="project_priority" name="project_priority" class="wc-enhanced-select arsol-enhanced-select">
            <option value=""><?php _e('Select Priority', 'arsol-pfw'); ?></option>
            <option value="low" <?php selected($project_priority, 'low'); ?>><?php _e('Low', 'arsol-pfw'); ?></option>
            <option value="normal" <?php selected($project_priority, 'normal'); ?>><?php _e('Normal', 'arsol-pfw'); ?></option>
            <option value="high" <?php selected($project_priority, 'high'); ?>><?php _e('High', 'arsol-pfw'); ?></option>
            <option value="urgent" <?php selected($project_priority, 'urgent'); ?>><?php _e('Urgent', 'arsol-pfw'); ?></option>
        </select>
        <small class="arsol-field-help"><?php _e('Project priority level', 'arsol-pfw'); ?></small>
    </p>

    <!-- Quick Stats -->
    <div class="arsol-section-header">
        <h4><?php _e('Quick Stats', 'arsol-pfw'); ?></h4>
        <small><?php _e('Project metrics at a glance', 'arsol-pfw'); ?></small>
    </div>

    <div class="arsol-quick-stats-grid">
        <?php
        // Get project orders and subscriptions
        $project_orders = get_post_meta($project_id, '_project_orders', true);
        $project_subscriptions = get_post_meta($project_id, '_project_subscriptions', true);
        $order_count = is_array($project_orders) ? count($project_orders) : 0;
        $subscription_count = is_array($project_subscriptions) ? count($project_subscriptions) : 0;
        
        // Calculate project age
        $created_date = get_the_date('Y-m-d H:i:s', $project_id);
        $project_age_days = floor((time() - strtotime($created_date)) / (60 * 60 * 24));
        
        // Calculate days until due date
        $days_until_due = '';
        if (!empty($due_date)) {
            $days_until_due = floor((strtotime($due_date) - time()) / (60 * 60 * 24));
        }
        ?>
        
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo intval($order_count); ?></span>
            <span class="arsol-stat-label"><?php _e('Orders', 'arsol-pfw'); ?></span>
        </div>
        
        <?php if (class_exists('WC_Subscriptions')): ?>
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo intval($subscription_count); ?></span>
            <span class="arsol-stat-label"><?php _e('Subscriptions', 'arsol-pfw'); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="arsol-stat-card">
            <span class="arsol-stat-number"><?php echo intval($project_age_days); ?></span>
            <span class="arsol-stat-label"><?php _e('Days Old', 'arsol-pfw'); ?></span>
        </div>
        
        <?php if (!empty($due_date)): ?>
        <div class="arsol-stat-card <?php echo $days_until_due < 0 ? 'overdue' : ($days_until_due <= 7 ? 'urgent' : ''); ?>">
            <span class="arsol-stat-number">
                <?php 
                if ($days_until_due < 0) {
                    echo abs($days_until_due);
                } else {
                    echo intval($days_until_due);
                }
                ?>
            </span>
            <span class="arsol-stat-label">
                <?php 
                if ($days_until_due < 0) {
                    _e('Days Overdue', 'arsol-pfw');
                } else {
                    _e('Days Left', 'arsol-pfw');
                }
                ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

</div> 