<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Proposal\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'), 15);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_project_proposals'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('add_meta_boxes', array($this, 'remove_publish_metabox'));
        add_action('edit_form_after_title', array($this, 'render_proposal_header_container'));
        add_action('arsol_proposal_request_content', array($this, 'render_customer_request_details_section'), 10);
        add_action('save_post', array($this, 'save_proposal_header_fields'));
    }

    public function register_post_type() {
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

        $supports = array('title', 'author');
        if (\Arsol_Projects_For_Woo\Admin\Settings\General::is_comments_enabled_for_post_type('arsol-pfw-proposal')) {
            $supports[] = 'comments';
        }

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=arsol-pfw-project',
            'show_in_nav_menus'  => false,
            'show_in_admin_bar'  => true,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-portfolio',
            'hierarchical'       => false,
            'supports'           => $supports,
            'has_archive'        => false,
            'rewrite'           => false,
            'show_in_rest'      => false,
            'taxonomies'         => array('arsol-pfw-proposal-stage'),
            'capability_type'    => 'arsol_pfw_proposal',
            'map_meta_cap'       => true,
        );

        register_post_type('arsol-pfw-proposal', $args);
    }

    public function disable_gutenberg_for_project_proposals($use_block_editor, $post_type) {
        if ($post_type === 'arsol-pfw-proposal') {
            return false;
        }
        return $use_block_editor;
    }

    public function modify_author_dropdown($query_args, $r) {
        if (!is_admin()) {
            return $query_args;
        }

        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'arsol-pfw-proposal') {
            $query_args['role__in'] = array('customer', 'subscriber');
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    public function remove_publish_metabox() {
        remove_meta_box('submitdiv', 'arsol-pfw-proposal', 'side');
    }

    public function render_proposal_header_container() {
        global $post;
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
            return;
        }
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post->ID);
        $template_path = ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/section-edit-proposal-header.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
    
    public function render_customer_request_details_section($post) {
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post->ID);
        if (!$proposal->get_request_id() && !$proposal->get_requested_project_budget() && !$proposal->get_requested_project_start_date() && !$proposal->get_requested_project_due_date()) {
            return;
        }
        
        $requested_budget = $proposal->get_requested_project_budget();
        $requested_start_date = $proposal->get_requested_project_start_date();
        $requested_due_date = $proposal->get_requested_project_due_date();
        $request_date = $proposal->get_request_date();
        $request_title = $proposal->get_request_title();
        $request_content = $proposal->get_request_details();
        $request_attachments = $proposal->get_request_attachments();

        if ($request_title) {
            echo '<div class="arsol-meta-item"><strong>' . __('Request Title:', 'arsol-pfw') . '</strong><span>' . esc_html($request_title) . '</span></div>';
        }

        if ($requested_budget) {
            echo '<div class="arsol-meta-item"><strong>' . __('Request Budget:', 'arsol-pfw') . '</strong>';
            if (is_array($requested_budget)) {
                $amount = isset($requested_budget['amount']) ? $requested_budget['amount'] : '';
                $currency = isset($requested_budget['currency']) ? $requested_budget['currency'] : get_woocommerce_currency();
                echo '<span>' . wc_price($amount, array('currency' => $currency)) . '</span>';
            } else {
                echo '<span>' . esc_html($requested_budget) . '</span>';
            }
            echo '</div>';
        }

        if ($requested_start_date) {
            echo '<div class="arsol-meta-item"><strong>' . __('Request Start Date:', 'arsol-pfw') . '</strong><span>' . date_i18n(get_option('date_format'), strtotime($requested_start_date)) . '</span></div>';
        }

        if ($requested_due_date) {
            echo '<div class="arsol-meta-item"><strong>' . __('Request Due Date:', 'arsol-pfw') . '</strong><span>' . date_i18n(get_option('date_format'), strtotime($requested_due_date)) . '</span></div>';
        }

        if ($request_date) {
            echo '<div class="arsol-meta-item"><strong>' . __('Request Date:', 'arsol-pfw') . '</strong><span>' . date_i18n(get_option('date_format'), strtotime($request_date)) . '</span></div>';
        }

        if ($request_attachments && is_array($request_attachments) && !empty($request_attachments)) {
            echo '<div class="arsol-meta-item"><strong>' . __('Request Attachments:', 'arsol-pfw') . '</strong><span>' . count($request_attachments) . ' ' . __('file(s)', 'arsol-pfw') . '</span></div>';
        }
    }

    public function save_proposal_header_fields($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (get_post_type($post_id) !== 'arsol-pfw-proposal') {
            return;
        }
        if (!arsol_pfw_user_can('edit_arsol_pfw_proposal', $post_id)) {
            return;
        }
        
        if (isset($_POST['proposal_stage'])) {
            wp_set_object_terms($post_id, sanitize_text_field($_POST['proposal_stage']), 'arsol-pfw-proposal-stage', false);
        }
        
        if (isset($_POST['proposal_project_lead'])) {
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
            $proposal->set_proposed_project_lead(sanitize_text_field($_POST['proposal_project_lead']));
        }
        
        if (isset($_POST['arsol_pfw_proposal_secondary_status'])) {
            $secondary_status = sanitize_text_field($_POST['arsol_pfw_proposal_secondary_status']);
            if (in_array($secondary_status, ['ready_for_review', 'processing'])) {
                $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
                $proposal->set_meta('_arsol_pfw_proposal_secondary_status', $secondary_status);
            }
        }
    }
} 
} 
