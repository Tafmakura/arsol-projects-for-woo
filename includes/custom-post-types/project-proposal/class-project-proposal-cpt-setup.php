<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        // Add project proposal post type
        add_action('init', array($this, 'register_post_type'), 15);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_project_proposals'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('add_meta_boxes', array($this, 'remove_publish_metabox'));
        
        // Setup proposal header container
        add_action('edit_form_after_title', array($this, 'render_proposal_header_container'));
        
        // Hook customer request details into the proposal header
        add_action('arsol_proposal_request_content', array($this, 'render_customer_request_details_section'), 10);
        
        // Save header fields including secondary status
        add_action('save_post', array($this, 'save_proposal_header_fields'));
    }

    public function register_post_type() {
        // Debug logging
        if (function_exists('error_log')) {
            error_log('ARSOL DEBUG: Registering arsol-pfw-proposal post type');
        }

        $labels = array(
            'name'               => __('Project Proposals', 'arsol-pfw'),
            'singular_name'      => __('Project Proposal', 'arsol-pfw'),
            'add_new'           => __('Add New', 'arsol-pfw'),
            'add_new_item'      => __('Add New Project Proposal', 'arsol-pfw'),
            'edit_item'         => __('Edit Project Proposal', 'arsol-pfw'),
            'new_item'          => __('New Project Proposal', 'arsol-pfw'),
            'search_items'      => __('Search Project Proposals', 'arsol-pfw'),
            'not_found'         => __('No project proposals found', 'arsol-pfw'),
            'not_found_in_trash'=> __('No project proposals found in trash', 'arsol-pfw'),
            'menu_name'         => __('Project Proposals', 'arsol-pfw'),
            'all_items'         => __('All Project Proposals', 'arsol-pfw'),
        );

        // Get base supports array
        $supports = array('title');
        
        // Add comments support if enabled
        if (\Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type('arsol-pfw-proposal')) {
            $supports[] = 'comments';
        }

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => false,
            'show_in_admin_bar'  => true,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-portfolio',
            'capability_type'    => array('arsol_project_proposal', 'arsol_project_proposals'),
            'map_meta_cap'       => true,
            'hierarchical'       => false,
            'supports'           => $supports,
            'has_archive'        => false,
            'rewrite'           => false,
            'show_in_rest'      => false,
        );

        $result = register_post_type('arsol-pfw-proposal', $args);
        
        // Debug the result
        if (function_exists('error_log')) {
            if (is_wp_error($result)) {
                error_log('ARSOL DEBUG: Failed to register arsol-pfw-proposal: ' . $result->get_error_message());
            } else {
                error_log('ARSOL DEBUG: Successfully registered arsol-pfw-proposal post type');
            }
        }
    }

    /**
     * Disable Gutenberg for project proposals post type
     */
    public function disable_gutenberg_for_project_proposals($use_block_editor, $post_type) {
        if ($post_type === 'arsol-pfw-proposal') {
            return false;
        }
        return $use_block_editor;
    }

    /**
     * Modify the author dropdown to include only WooCommerce customers
     */
    public function modify_author_dropdown($query_args, $r) {
        if (!is_admin()) {
            return $query_args;
        }

        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'arsol-pfw-proposal') {
            // Get all users who can make purchases
            $query_args['role__in'] = array('customer', 'subscriber');
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Remove the publish metabox
     */
    public function remove_publish_metabox() {
        remove_meta_box('submitdiv', 'arsol-pfw-proposal', 'side');
    }

    /**
     * Render the proposal header container
     */
    public function render_proposal_header_container() {
        global $post;
        
        // Only show for proposals on the edit screen
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
            return;
        }
        
        // Include the header container template
        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/section-edit-proposal-header.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
    
    /**
     * Render Customer Request Details section (hooked into proposal header)
     */
    public function render_customer_request_details_section($post) {
        // Only show if this proposal has original request data
        if (!$this->has_original_request_data($post->ID)) {
            return;
        }

        // Get original request data
        $original_budget = get_post_meta($post->ID, '_arsol_pfw_proposal_request_budget', true);
        $original_start_date = get_post_meta($post->ID, '_arsol_pfw_proposal_request_start_date', true);
        $original_delivery_date = get_post_meta($post->ID, '_arsol_pfw_proposal_request_delivery_date', true);
        $original_request_date = get_post_meta($post->ID, '_arsol_pfw_proposal_request_date', true);
        $original_request_title = get_post_meta($post->ID, '_arsol_pfw_proposal_request_title', true);
        $original_request_content = get_post_meta($post->ID, '_arsol_pfw_proposal_request_details', true);
        $original_request_attachments = get_post_meta($post->ID, '_arsol_pfw_proposal_request_attachments', true);
        
        // Display in WooCommerce order column format
        if ($original_request_title) {
            echo '<p class="form-field form-field-wide">';
            echo '<label><strong>' . __('Original title:', 'arsol-pfw') . '</strong></label>';
            echo '<span>' . esc_html($original_request_title) . '</span>';
            echo '</p>';
        }

        if ($original_budget) {
            echo '<p class="form-field form-field-wide">';
            echo '<label><strong>' . __('Requested budget:', 'arsol-pfw') . '</strong></label>';
            if (is_array($original_budget)) {
                $amount = isset($original_budget['amount']) ? $original_budget['amount'] : '';
                $currency = isset($original_budget['currency']) ? $original_budget['currency'] : get_woocommerce_currency();
                echo '<span>' . wc_price($amount, array('currency' => $currency)) . '</span>';
            } else {
                echo '<span>' . esc_html($original_budget) . '</span>';
            }
            echo '</p>';
        }

        if ($original_start_date) {
            echo '<p class="form-field form-field-wide">';
            echo '<label><strong>' . __('Requested start date:', 'arsol-pfw') . '</strong></label>';
            echo '<span>' . date_i18n(get_option('date_format'), strtotime($original_start_date)) . '</span>';
            echo '</p>';
        }

        if ($original_delivery_date) {
            echo '<p class="form-field form-field-wide">';
            echo '<label><strong>' . __('Requested delivery date:', 'arsol-pfw') . '</strong></label>';
            echo '<span>' . date_i18n(get_option('date_format'), strtotime($original_delivery_date)) . '</span>';
            echo '</p>';
        }

        if ($original_request_date) {
            echo '<p class="form-field form-field-wide">';
            echo '<label><strong>' . __('Request submitted:', 'arsol-pfw') . '</strong></label>';
            echo '<span>' . date_i18n(get_option('date_format'), strtotime($original_request_date)) . '</span>';
            echo '</p>';
        }

        if ($original_request_attachments && is_array($original_request_attachments) && !empty($original_request_attachments)) {
            echo '<p class="form-field form-field-wide">';
            echo '<label><strong>' . __('Attachments:', 'arsol-pfw') . '</strong></label>';
            echo '<span>' . count($original_request_attachments) . ' ' . __('file(s)', 'arsol-pfw') . '</span>';
            echo '</p>';
        }
    }

    /**
     * Check if proposal has original request data
     */
    private function has_original_request_data($post_id) {
        $original_budget = get_post_meta($post_id, '_arsol_pfw_proposal_request_budget', true);
        $original_start_date = get_post_meta($post_id, '_arsol_pfw_proposal_request_start_date', true);
        $original_delivery_date = get_post_meta($post_id, '_arsol_pfw_proposal_request_delivery_date', true);
        $original_request_date = get_post_meta($post_id, '_arsol_pfw_proposal_request_date', true);
        $original_request_attachments = get_post_meta($post_id, '_arsol_pfw_proposal_request_attachments', true);
        
        return !empty($original_budget) || !empty($original_start_date) || !empty($original_delivery_date) || !empty($original_request_date) || !empty($original_request_attachments);
    }

    /**
     * Save proposal header fields including status fields
     */
    public function save_proposal_header_fields($post_id) {
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check if this is the right post type
        if (get_post_type($post_id) !== 'arsol-pfw-proposal') {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save proposal stage
        if (isset($_POST['proposal_stage'])) {
            wp_set_object_terms($post_id, sanitize_text_field($_POST['proposal_stage']), 'arsol-pfw-proposal-stage', false);
        }
        
        // Save project lead
        if (isset($_POST['proposal_project_lead'])) {
            update_post_meta($post_id, '_arsol_pfw_proposal_project_lead', sanitize_text_field($_POST['proposal_project_lead']));
        }
        
        // Save secondary status (keeping existing functionality)
        if (isset($_POST['arsol_pfw_proposal_secondary_status'])) {
            $secondary_status = sanitize_text_field($_POST['arsol_pfw_proposal_secondary_status']);
            // Validate the value is one of the allowed options
            if (in_array($secondary_status, ['ready_for_review', 'processing'])) {
                update_post_meta($post_id, '_arsol_pfw_proposal_secondary_status', $secondary_status);
            }
        }
    }
} 