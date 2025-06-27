<?php
/**
 * Frontend Template Sidebar Fields
 *
 * Handles sidebar unified form with filterable fields for all project types.
 *
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Template_Sidebar_Fields {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('arsol_pfw_sidebar_form', array($this, 'display_sidebar_form'), 10, 3);
        add_action('wp_loaded', array($this, 'handle_form_submission'));
    }

    /**
     * Display sidebar form
     *
     * @param string $post_type The post type (active, proposal, request)
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_sidebar_form($post_type, $status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        $fields = $this->get_default_fields($post_type, $status, $post_id);

        /**
         * Filter sidebar form fields
         *
         * @param array $fields Array of form field configurations
         * @param string $post_type The post type
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        $fields = apply_filters('arsol_pfw_form_fields', $fields, $post_type, $status, $post_id);

        $submit_button = $this->get_default_submit_button($post_type, $status, $post_id);

        /**
         * Filter sidebar submit button
         *
         * @param array $submit_button Submit button configuration
         * @param string $post_type The post type
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        $submit_button = apply_filters('arsol_pfw_submit_button', $submit_button, $post_type, $status, $post_id);

        /**
         * Filter whether form has fields (to show/hide submit button)
         *
         * @param bool $has_fields Whether form has fields
         * @param string $post_type The post type
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        $has_fields = apply_filters('arsol_pfw_has_form_fields', !empty($fields), $post_type, $status, $post_id);

        if (!$has_fields && empty($fields)) {
            return;
        }

        $this->render_form($fields, $submit_button, $post_type, $status, $post_id);
    }

    /**
     * Get default fields based on post type and status
     *
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     * @return array Array of field configurations
     */
    private function get_default_fields($post_type, $status, $post_id) {
        $fields = array();

        switch ($post_type) {
            case 'active':
                $fields = $this->get_project_fields($post_id, $status);
                break;
            case 'proposal':
                $fields = $this->get_proposal_fields($post_id, $status);
                break;
            case 'request':
                $fields = $this->get_request_fields($post_id, $status);
                break;
        }

        return $this->filter_fields_by_status($fields, $status);
    }

    /**
     * Get project fields
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of field configurations
     */
    private function get_project_fields($post_id, $status) {
        $fields = array();

        // No status change fields for customers on frontend
        // Status is now displayed as metadata instead

        return $fields;
    }

    /**
     * Get proposal fields
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of field configurations
     */
    private function get_proposal_fields($post_id, $status) {
        $fields = array();

        // No status change fields for customers on frontend
        // Customers use action buttons (approve/reject) instead of forms
        // Status is now displayed as metadata instead

        return $fields;
    }

    /**
     * Get request fields
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of field configurations
     */
    private function get_request_fields($post_id, $status) {
        $fields = array();

        // No status change fields for customers on frontend
        // Customers use action buttons (cancel) instead of forms
        // Status is now displayed as metadata instead

        return $fields;
    }

    /**
     * Get default submit button configuration
     *
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     * @return array Submit button configuration
     */
    private function get_default_submit_button($post_type, $status, $post_id) {
        $defaults = array(
            'label' => __('Submit', 'arsol-pfw'),
            'class' => 'button button-primary'
        );

        switch ($post_type) {
            case 'active':
                if (in_array($status, array('active', 'on-hold'))) {
                    $defaults['label'] = __('Update Status', 'arsol-pfw');
                }
                break;
            case 'proposal':
                if ($status === 'pending-approval') {
                    $defaults['label'] = __('Approve Proposal', 'arsol-pfw');
                }
                break;
            case 'request':
                if (in_array($status, array('pending', 'under-review'))) {
                    $defaults['label'] = __('Submit Decision', 'arsol-pfw');
                }
                break;
        }

        return $defaults;
    }

    /**
     * Get project status options
     *
     * @param string $current_status The current status
     * @return array Status options
     */
    private function get_project_status_options($current_status) {
        $all_options = array(
            'active' => __('Active', 'arsol-pfw'),
            'completed' => __('Completed', 'arsol-pfw'),
            'on-hold' => __('On Hold', 'arsol-pfw'),
            'cancelled' => __('Cancelled', 'arsol-pfw')
        );

        // Remove current status from options
        unset($all_options[$current_status]);

        return array('' => __('Select new status...', 'arsol-pfw')) + $all_options;
    }

    /**
     * Filter fields by status conditions
     *
     * @param array $fields Array of field configurations
     * @param string $current_status The current status
     * @return array Filtered fields array
     */
    private function filter_fields_by_status($fields, $current_status) {
        return array_filter($fields, function($field) use ($current_status) {
            if (!isset($field['show_if'])) {
                return true;
            }

            if (isset($field['show_if']['status'])) {
                return in_array($current_status, $field['show_if']['status']);
            }

            return true;
        });
    }

    /**
     * Render the form
     *
     * @param array $fields Array of field configurations
     * @param array $submit_button Submit button configuration
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    private function render_form($fields, $submit_button, $post_type, $status, $post_id) {
        echo '<div class="sidebar-form">';
        echo '<form method="POST" action="' . esc_url(admin_url('admin-post.php')) . '" class="arsol-sidebar-form">';
        
        // Hidden fields
        wp_nonce_field('arsol_pfw_sidebar_form_' . $post_id, 'arsol_pfw_nonce');
        echo '<input type="hidden" name="action" value="arsol_pfw_sidebar_submit">';
        echo '<input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">';
        echo '<input type="hidden" name="post_type" value="' . esc_attr($post_type) . '">';
        echo '<input type="hidden" name="current_status" value="' . esc_attr($status) . '">';

        // Render form fields
        if (!empty($fields)) {
            echo '<div class="form-fields">';
            foreach ($fields as $field_key => $field) {
            $this->render_form_field($field_key, $field);
            }
            echo '</div>';
        }

        // Render submit button
        if (!empty($submit_button)) {
            $button_class = isset($submit_button['class']) ? $submit_button['class'] : 'button button-primary';
            $button_label = isset($submit_button['label']) ? $submit_button['label'] : __('Submit', 'arsol-pfw');
            
            echo '<div class="form-submit">';
            echo '<button type="submit" class="' . esc_attr($button_class) . '">';
            echo esc_html($button_label);
            echo '</button>';
            echo '</div>';
        }

        echo '</form>';
        echo '</div>';
    }

    /**
     * Render a single form field
     *
     * @param string $field_key The field key
     * @param array $field The field configuration
     */
    private function render_form_field($field_key, $field) {
        if (empty($field['type'])) {
            return;
        }

        $type = $field['type'];
        $label = $field['label'] ?? '';
        $required = $field['required'] ?? false;
        $placeholder = $field['placeholder'] ?? '';
        $class = $field['class'] ?? '';

        echo '<div class="form-field field-' . esc_attr($field_key) . '">';

        if (!empty($label) && $type !== 'checkbox') {
            echo '<label for="' . esc_attr($field_key) . '" class="field-label">';
            echo esc_html($label);
            if ($required) echo ' <span class="required">*</span>';
            echo '</label>';
        }

        switch ($type) {
            case 'text':
                echo '<input type="text" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '"';
                if (!empty($placeholder)) echo ' placeholder="' . esc_attr($placeholder) . '"';
                if (!empty($class)) echo ' class="' . esc_attr($class) . '"';
                if ($required) echo ' required';
                echo '>';
                break;

            case 'textarea':
                $rows = $field['rows'] ?? 4;
                echo '<textarea id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '"';
                echo ' rows="' . esc_attr($rows) . '"';
                if (!empty($placeholder)) echo ' placeholder="' . esc_attr($placeholder) . '"';
                if (!empty($class)) echo ' class="' . esc_attr($class) . '"';
                if ($required) echo ' required';
                echo '></textarea>';
                break;

            case 'select':
                $options = $field['options'] ?? array();
                echo '<select id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '"';
                if (!empty($class)) echo ' class="' . esc_attr($class) . '"';
                if ($required) echo ' required';
                echo '>';
                foreach ($options as $value => $option_label) {
                    echo '<option value="' . esc_attr($value) . '">' . esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;

            case 'radio':
                $options = $field['options'] ?? array();
                foreach ($options as $value => $option_label) {
                    echo '<label class="radio-option">';
                    echo '<input type="radio" name="' . esc_attr($field_key) . '" value="' . esc_attr($value) . '"';
                if ($required) echo ' required';
                    echo '> ';
                    echo esc_html($option_label);
                    echo '</label>';
                }
                break;

            case 'checkbox':
                echo '<label class="checkbox-option">';
                echo '<input type="checkbox" id="' . esc_attr($field_key) . '" name="' . esc_attr($field_key) . '" value="1"';
                if ($required) echo ' required';
                echo '> ';
                echo esc_html($label);
                if ($required) echo ' <span class="required">*</span>';
                echo '</label>';
                break;
        }

        echo '</div>';
    }

    /**
     * Handle form submission
     */
    public function handle_form_submission() {
        if (!isset($_POST['action']) || $_POST['action'] !== 'arsol_pfw_sidebar_submit') {
            return;
        }

        $post_id = intval($_POST['post_id'] ?? 0);
        $post_type = sanitize_key($_POST['post_type'] ?? '');
        $current_status = sanitize_key($_POST['current_status'] ?? '');

        // Verify nonce
        if (!wp_verify_nonce($_POST['arsol_pfw_nonce'] ?? '', 'arsol_pfw_sidebar_form_' . $post_id)) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }

        /**
         * Action hook for processing sidebar form submission
         *
         * @param int $post_id The post ID
         * @param string $post_type The post type
         * @param string $current_status The current status
         * @param array $form_data The submitted form data
         */
        do_action('arsol_pfw_process_sidebar_form', $post_id, $post_type, $current_status, $_POST);

        // Redirect back to prevent resubmission
        $redirect_url = wp_get_referer() ?: get_permalink($post_id);
        wp_safe_redirect($redirect_url);
        exit;
    }
}
