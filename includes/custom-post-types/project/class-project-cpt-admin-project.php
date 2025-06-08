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
        $recurring_budget = get_post_meta($post->ID, '_project_recurring_budget', true);
        
        // Get invoice product settings
        $settings = get_option('arsol_projects_settings', array());
        $invoice_product_id = isset($settings['proposal_invoice_product']) ? $settings['proposal_invoice_product'] : '';
        $recurring_invoice_product_id = isset($settings['proposal_recurring_invoice_product']) ? $settings['proposal_recurring_invoice_product'] : '';

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

        <p>
            <label for="project_recurring_budget"><?php _e('Recurring Budget:', 'arsol-projects-for-woo'); ?></label>
            <input type="number"
                   id="project_recurring_budget"
                   name="project_recurring_budget"
                   value="<?php echo esc_attr($recurring_budget); ?>"
                   style="width:100%"
                   step="0.01"
                   min="0">
        </p>

        <div class="major-actions" style="padding-top:10px; border-top: 1px solid #ddd; margin-top: 10px;">
            <?php if (!empty($invoice_product_id) && get_post_meta($post->ID, '_invoice_created', true) !== 'yes') : ?>
                <p>
                    <label for="create_invoice">
                        <input type="checkbox" id="create_invoice" name="create_invoice">
                        <?php _e('Create invoice for budget', 'arsol-projects-for-woo'); ?>
                    </label>
                </p>
            <?php endif; ?>
            <?php if (!empty($recurring_invoice_product_id) && get_post_meta($post->ID, '_recurring_invoice_created', true) !== 'yes') : ?>
                <p>
                    <label for="create_recurring_invoice">
                        <input type="checkbox" id="create_recurring_invoice" name="create_recurring_invoice">
                        <?php _e('Create invoice for recurring budget', 'arsol-projects-for-woo'); ?>
                    </label>
                </p>
            <?php endif; ?>
        </div>
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
        
        // Save recurring budget
        if (isset($_POST['project_recurring_budget'])) {
            update_post_meta($post_id, '_project_recurring_budget', sanitize_text_field($_POST['project_recurring_budget']));
        }

        // Create invoices if checked
        if (isset($_POST['create_invoice'])) {
            $this->create_invoice($post_id, 'standard');
        }
        if (isset($_POST['create_recurring_invoice'])) {
            $this->create_invoice($post_id, 'recurring');
        }
    }

    private function create_invoice($project_id, $type = 'standard') {
        if (!class_exists('WooCommerce') || get_post_meta($project_id, '_' . $type . '_invoice_created', true) === 'yes') {
            return;
        }

        $project = get_post($project_id);
        $settings = get_option('arsol_projects_settings', array());
        $customer_id = $project->post_author;

        if ($type === 'standard') {
            $product_id = isset($settings['proposal_invoice_product']) ? $settings['proposal_invoice_product'] : '';
            $budget = get_post_meta($project_id, '_request_budget', true);
        } else {
            $product_id = isset($settings['proposal_recurring_invoice_product']) ? $settings['proposal_recurring_invoice_product'] : '';
            $budget = get_post_meta($project_id, '_project_recurring_budget', true);
        }

        if (empty($product_id) || empty($budget) || !is_numeric($budget)) {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        try {
            $order = wc_create_order(array(
                'customer_id' => $customer_id,
                'status' => 'pending'
            ));

            if (is_wp_error($order)) {
                return;
            }

            $order->add_product($product, 1, array('total' => $budget));
            $order->calculate_totals();
            $order->save();
            
            update_post_meta($project_id, '_' . $type . '_invoice_created', 'yes');
            update_post_meta($project_id, '_' . $type . '_order_id', $order->get_id());

        } catch (\Exception $e) {
            // silent fail
        }
    }
}
