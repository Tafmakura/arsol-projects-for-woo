<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin;

if (!defined('ABSPATH')) exit;

class Single_Controller {
    public function __construct() {
        // Add meta boxes for single project admin screen
        add_action('add_meta_boxes', array($this, 'add_project_details_meta_box'));
        // Save project data
        add_action('save_post_arsol-pfw-project', array($this, 'save_project_details'));
        // Prevent project deletion if tied proposals exist
        add_action('before_delete_post', array($this, 'prevent_project_deletion_with_proposals'));
    }

    /**
     * Add project actions meta box
     */
    public function add_project_details_meta_box() {
        add_meta_box(
            'arsol-pfw-project-actions-metabox',
            __('Project Actions', 'arsol-pfw'),
            array($this, 'render_project_actions_metabox'),
            'arsol-pfw-project',
            'side',
            'high'
        );

        // Customer Notice metabox
        add_meta_box(
            'arsol-pfw-project-customer-notice-metabox',
            __('Customer Notice', 'arsol-pfw'),
            array($this, 'render_customer_notice_metabox'),
            'arsol-pfw-project',
            'normal',
            'high'
        );
    }

    /**
     * Render project actions metabox
     */
    public function render_project_actions_metabox($post) {
        // Add nonce for security
        wp_nonce_field('arsol-pfw-project-actions-metabox', 'arsol_pfw_project_actions_metabox_nonce');
        
        // Check if project is published for conversion eligibility
        $is_disabled = $post->post_status !== 'publish';
        
        // Prepare Create Proposal button data
        $create_url = admin_url('post-new.php?post_type=arsol-pfw-proposal&parent_project=' . $post->ID);
        $create_url = wp_nonce_url($create_url, 'arsol_create_proposal_nonce');
        $confirm_message = esc_js(__('This will save the current project and create a new proposal based on this project. Continue?', 'arsol-pfw'));
        
        $tooltip_text = $is_disabled
            ? __('The project must be published before you can create a proposal.', 'arsol-pfw')
            : __('Creates a new proposal based on this project.', 'arsol-pfw');
        ?>
        <div class="project-details">
            <!-- Main content area for any future project-specific content -->
        </div>
        
        <div class="major-actions">
            <?php if ($post->post_status === 'publish'): ?>
                <input type="submit" id="save-post" name="save" class="button button-primary" value="<?php _e('Update', 'arsol-pfw'); ?>">
            <?php else: ?>
                <input type="submit" id="publish" name="publish" class="button button-primary" value="<?php _e('Publish', 'arsol-pfw'); ?>">
            <?php endif; ?>
            
            <!-- Secondary Action Button -->
            <span title="<?php echo esc_attr($tooltip_text); ?>">
            <input type="button" 
                   id="create-proposal" 
                   name="create_proposal" 
                   class="button button-secondary arsol-confirm-conversion" 
                   value="<?php _e('Create Proposal', 'arsol-pfw'); ?>" 
                   data-url="<?php echo esc_url($create_url); ?>" 
                       data-message="<?php echo $confirm_message; ?>"
                       <?php disabled($is_disabled, true); ?> />
            </span>
        </div>
        <?php
    }

    /**
     * Render customer notice metabox
     */
    public function render_customer_notice_metabox($post) {
        $this->render_customer_notice_content($post);
    }

    /**
     * Render customer notice content
     */
    public function render_customer_notice_content($post) {
        // Add nonce for security
        wp_nonce_field('project_customer_notice_section', 'project_customer_notice_section_nonce');

        // Get current values using Project entity
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post->ID);
        $notice = $project->get_project_customer_notice();
        ?>
        <div>
            <p class="description">
                <?php _e('Add any important notices or updates that should be communicated to the customer regarding this project.', 'arsol-pfw'); ?>
            </p>
            
            <div class="arsol-request-feedback-editor">
                <?php
                $editor_settings = array(
                    'textarea_name' => 'arsol_pfw_project_customer_notice',
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'teeny' => false,
                    'quicktags' => array(
                        'buttons' => 'strong,em,ul,ol,li,link,close'
                    ),
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                        'toolbar2' => '',
                        'toolbar3' => ''
                    )
                );
                
                wp_editor($notice, 'arsol_pfw_project_customer_notice', $editor_settings);
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Save project details
     */
    public function save_project_details($post_id) {
        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get project entity
        $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post_id);

        // Handle project stage change
        if (isset($_POST['project_stage'])) {
            $new_status = sanitize_text_field($_POST['project_stage']);
            
            // Get the status before this save
            $current_status_terms = wp_get_object_terms($post_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
            $old_status = !empty($current_status_terms) ? $current_status_terms[0] : 'not-started';

            // Set start date on the first transition to 'in-progress'
            if ($new_status === 'in-progress' && $old_status !== 'in-progress') {
                if (empty($project->get_project_start_date())) {
                    $project->set_project_start_date(current_time('mysql'));
                }
            }
            
            wp_set_object_terms($post_id, $new_status, 'arsol-pfw-project-stage', false);
        }

        // Set default start date if not already set
        if (empty($project->get_project_start_date())) {
            $project->set_project_start_date(current_time('mysql'));
        }

        // Save project lead
        if (isset($_POST['project_lead'])) {
            $project->set_project_lead(sanitize_text_field($_POST['project_lead']));
        }

        // Save project start date
        if (isset($_POST['project_start_date'])) {
            $project->set_project_start_date(sanitize_text_field($_POST['project_start_date']));
        }

        // Save project due date
        if (isset($_POST['project_due_date'])) {
            $project->set_project_due_date(sanitize_text_field($_POST['project_due_date']));
        }
        
        // Save customer notice
        if (isset($_POST['project_customer_notice_section_nonce']) && wp_verify_nonce($_POST['project_customer_notice_section_nonce'], 'project_customer_notice_section')) {
            if (isset($_POST['arsol_pfw_project_customer_notice'])) {
                $project->set_project_customer_notice(wp_kses_post($_POST['arsol_pfw_project_customer_notice']));
            }
        }
        
        // Save customer ID from customer_id field
        if (isset($_POST['customer_id']) && !empty($_POST['customer_id'])) {
            $project->set_customer_id(intval($_POST['customer_id']));
        }
        
        // Save all changes
        $project->save();
        
        // Handle create proposal after save
        if (isset($_POST['arsol_create_after_save']) && !empty($_POST['arsol_create_after_save'])) {
            // Direct PHP redirect to create new proposal
            $create_url = esc_url_raw($_POST['arsol_create_after_save']);
            wp_redirect($create_url);
            exit;
        }
    }

    /**
     * Prevent project deletion if tied proposals exist
     */
    public function prevent_project_deletion_with_proposals($post_id) {
        if (get_post_type($post_id) !== 'arsol-pfw-project') {
            return;
        }
        
        // Check for tied proposals
        $tied_proposals = get_posts(array(
            'post_type' => 'arsol-pfw-proposal',
            'meta_key' => '_arsol_pfw_parent_project_id',
            'meta_value' => $post_id,
            'post_status' => 'any',
            'numberposts' => 1,
            'fields' => 'ids'
        ));
        
        if (!empty($tied_proposals)) {
            wp_die(
                __('Cannot delete project with tied proposals. Please delete or untie proposals first.', 'arsol-pfw'),
                __('Project Deletion Prevented', 'arsol-pfw'),
                array('back_link' => true)
            );
        }
    }
}
