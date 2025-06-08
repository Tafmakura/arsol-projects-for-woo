<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost\Admin;

if (!defined('ABSPATH')) exit;

class Project {
    public function __construct() {
        // Add meta boxes for single project admin screen
        add_action('add_meta_boxes', array($this, 'add_project_details_meta_box'));
        // Save project data
        add_action('save_post_arsol-project', array($this, 'save_project_details'));
    }

    /**
     * Add project details meta box
     */
    public function add_project_details_meta_box() {
        add_meta_box(
            'project_details_meta_box',
            __('Project Details', 'arsol-projects-for-woo'),
            array($this, 'render_project_details_meta_box'),
            'arsol-project',
            'side',
            'default'
        );
    }

    /**
     * Render project details meta box
     */
    public function render_project_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('project_details_meta_box', 'project_details_meta_box_nonce');

        // Get current values
        $current_status = wp_get_object_terms($post->ID, 'arsol-project-status', array('fields' => 'slugs'));
        $current_status = !empty($current_status) ? $current_status[0] : 'not-started';
        $due_date = get_post_meta($post->ID, '_project_due_date', true);
        $start_date = get_post_meta($post->ID, '_project_start_date', true);
        $project_lead = get_post_meta($post->ID, '_project_lead', true);
        $budget = get_post_meta($post->ID, '_proposal_budget', true);
        $recurring_budget = get_post_meta($post->ID, '_proposal_recurring_budget', true);
        
        // Get statuses
        $statuses = get_terms(array(
            'taxonomy' => 'arsol-project-status',
            'hide_empty' => false,
        ));

        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'widefat'
        ));

        // Get project leads (administrators and shop managers)
        $project_leads = get_users(array(
            'role__in' => array('administrator', 'shop_manager'),
            'orderby' => 'display_name',
            'fields' => array('ID', 'display_name')
        ));

        // Check if current user is admin
        $is_admin = current_user_can('administrator');
        ?>
        <p>
            <label for="project_code"><?php _e('Project Code:', 'arsol-projects-for-woo'); ?></label>
            <input type="text" 
                   id="project_code" 
                   value="<?php echo esc_attr($post->ID); ?>"
                   disabled
                   style="width:100%">
        </p>

        <p>
            <label for="post_author_override"><?php _e('Customer:', 'arsol-projects-for-woo'); ?></label>
            <?php echo $author_dropdown; ?>
        </p>

        <p>
            <label for="project_lead"><?php _e('Project Lead:', 'arsol-projects-for-woo'); ?></label>
            <select name="project_lead" id="project_lead" style="width:100%">
                <option value=""><?php _e('Select Project Lead', 'arsol-projects-for-woo'); ?></option>
                <?php foreach ($project_leads as $lead) : ?>
                    <option value="<?php echo esc_attr($lead->ID); ?>" <?php selected($project_lead, $lead->ID); ?>>
                        <?php echo esc_html($lead->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="project_status"><?php _e('Project Status:', 'arsol-projects-for-woo'); ?></label>
            <select name="project_status" id="project_status" style="width:100%">
                <?php foreach ($statuses as $status) : ?>
                    <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($current_status, $status->slug); ?>>
                        <?php echo esc_html($status->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="project_start_date"><?php _e('Start Date:', 'arsol-projects-for-woo'); ?></label>
            <input type="date" 
                   id="project_start_date" 
                   name="project_start_date" 
                   value="<?php echo esc_attr($start_date); ?>"
                   style="width:100%"
                   <?php echo $is_admin ? '' : 'readonly'; ?>>
        </p>

        <p>
            <label for="project_due_date"><?php _e('Due Date:', 'arsol-projects-for-woo'); ?></label>
            <input type="date" 
                   id="project_due_date" 
                   name="project_due_date" 
                   value="<?php echo esc_attr($due_date); ?>"
                   style="width:100%">
        </p>

        <?php if (!empty($budget)) : ?>
            <p>
                <label for="project_budget"><?php _e('Budget:', 'arsol-projects-for-woo'); ?></label>
                <input type="text" 
                       id="project_budget" 
                       value="<?php echo esc_attr(wc_price($budget)); ?>"
                       disabled
                       style="width:100%">
            </p>
        <?php endif; ?>

        <?php if (!empty($recurring_budget)) : ?>
            <p>
                <label for="project_recurring_budget"><?php _e('Recurring Budget:', 'arsol-projects-for-woo'); ?></label>
                <input type="text" 
                       id="project_recurring_budget" 
                       value="<?php echo esc_attr(wc_price($recurring_budget)); ?>"
                       disabled
                       style="width:100%">
            </p>
        <?php endif; ?>
        <?php
    }

    /**
     * Save project details
     */
    public function save_project_details($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['project_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['project_details_meta_box_nonce'], 'project_details_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save project status
        if (isset($_POST['project_status'])) {
            $new_status = sanitize_text_field($_POST['project_status']);
            $current_status = wp_get_object_terms($post_id, 'arsol-project-status', array('fields' => 'slugs'));
            $current_status = !empty($current_status) ? $current_status[0] : '';
            
            // If status is changing to 'in-progress' and there's no start date, set it
            if ($new_status === 'in-progress' && $current_status !== 'in-progress') {
                $start_date = get_post_meta($post_id, '_project_start_date', true);
                if (empty($start_date)) {
                    update_post_meta($post_id, '_project_start_date', current_time('Y-m-d'));
                }
            }
            
            wp_set_object_terms($post_id, $new_status, 'arsol-project-status', false);
        }

        // Save project lead
        if (isset($_POST['project_lead'])) {
            update_post_meta($post_id, '_project_lead', sanitize_text_field($_POST['project_lead']));
        }

        // Save start date (only if user is admin)
        if (isset($_POST['project_start_date']) && current_user_can('administrator')) {
            update_post_meta($post_id, '_project_start_date', sanitize_text_field($_POST['project_start_date']));
        }

        // Save due date
        if (isset($_POST['project_due_date'])) {
            update_post_meta($post_id, '_project_due_date', sanitize_text_field($_POST['project_due_date']));
        }
    }
}