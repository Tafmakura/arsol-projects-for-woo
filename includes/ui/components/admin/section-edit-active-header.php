<?php
/**
 * Admin Template: Edit Project Header Container
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

// Check if has original proposal data
$has_proposal_data = false;
$original_proposal_id = get_post_meta($project_id, '_original_proposal_id', true);
if ($original_proposal_id || 
    get_post_meta($project_id, '_original_proposal_budget', true) ||
    get_post_meta($project_id, '_original_proposal_start_date', true) ||
    get_post_meta($project_id, '_original_proposal_delivery_date', true)) {
    $has_proposal_data = true;
}
?>

<div id="arsol-pfw-project-data" class="postbox ">
    <div id="proposal_metabox" class="panel-wrap woocommerce">
        <div id="order_data" class="panel woocommerce">
            <h2>
                <?php printf(__('Project #%d details', 'arsol-pfw'), $project_id); ?>
            </h2>

            <div class="order_data_column_container">
                <div class="order_data_column">
                    <h3><?php _e('General', 'arsol-pfw'); ?></h3>

                    <div class="form-field-row">
                        <p class="form-field form-field-half">
                            <label for="project_start_date"><?php _e('Start Date:', 'arsol-pfw'); ?></label>
                            <input type="date" 
                                   id="project_start_date" 
                                   name="project_start_date" 
                                   value="<?php echo esc_attr($start_date); ?>"
                                   class="widefat">
                        </p>
                        <p class="form-field form-field-half">
                            <label for="project_due_date"><?php _e('Due Date:', 'arsol-pfw'); ?></label>
                            <input type="date" 
                                   id="project_due_date" 
                                   name="project_due_date" 
                                   value="<?php echo esc_attr($due_date); ?>"
                                   class="widefat">
                        </p>
                    </div>

                    <p class="form-field form-field-wide wc-customer-user">
                        <label for="post_author_override">
                            <?php _e('Customer:', 'arsol-pfw'); ?>
                            <?php if ($customer): ?>
                                <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-project&author=' . $customer_id); ?>">
                                    <?php _e('View other projects →', 'arsol-pfw'); ?>
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
                        <label for="project_status"><?php _e('Project Status:', 'arsol-pfw'); ?></label>
                        <select id="project_status" name="project_status" class="wc-enhanced-select">
                            <?php foreach ($all_statuses as $status) : ?>
                                <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($project_status, $status->slug); ?>>
                                    <?php echo esc_html($status->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p class="form-field form-field-wide">
                        <label for="project_lead"><?php _e('Project Lead:', 'arsol-pfw'); ?></label>
                        <?php
                        // Get project lead dropdown (all users)
                        $lead_dropdown = wp_dropdown_users(array(
                            'name' => 'project_lead',
                            'selected' => $project_lead,
                            'include_selected' => true,
                            'echo' => false,
                            'class' => 'wc-enhanced-select',
                            'show_option_none' => __('Select Project Lead', 'arsol-pfw'),
                            'option_none_value' => ''
                        ));
                        echo $lead_dropdown;
                        ?>
                    </p>
                </div>

                <div class="order_data_column">
                    
                    <h3><?php _e('Project Details', 'arsol-pfw'); ?></h3>
                    
                    <?php
                    // Hook for project details content
                    do_action('arsol_project_details_content', $post);
                    ?>

                </div>
                <div class="order_data_column">
                        
                    <h3><?php _e('Project Proposal Details', 'arsol-pfw'); ?></h3>
                    <?php if ($has_proposal_data): ?>
                    
                        <?php
                        // Hook for proposal details
                        do_action('arsol_project_proposal_content', $post);
                        ?>
                    <?php endif; ?>

                </div>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</div>
