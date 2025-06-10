<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        $this->require_files();
        $this->instantiate_classes();
        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        
        // Register activation and deactivation hooks
        register_activation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'deactivate'));
    }

    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_notice'));
            return;
        }
        
        // Check if we need to flush rewrite rules
        if (get_option('arsol_projects_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('arsol_projects_flush_rewrite_rules');
        }
        
        // Handle manual rewrite flush
        if (isset($_GET['arsol_flush_rewrite_rules']) && current_user_can('manage_options')) {
            flush_rewrite_rules();
            wp_redirect(admin_url('admin.php?page=arsol-projects-settings&flushed=1'));
            exit;
        }
        
        // Add debug admin notice to help troubleshoot
        add_action('admin_notices', array($this, 'debug_status_notice'));
    }

    public function load_textdomain() {
        // Load plugin text domain with updated domain name
        load_plugin_textdomain('arsol-pfw', false, dirname(ARSOL_PROJECTS_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Include necessary files.
     */
    private function require_files() {
        // Core Classes
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/class-setup-custom-post-types.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-shortcodes.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-woocommerce.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-woocommerce-subscriptions.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-assets.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-frontend-woocommerce-endpoints.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-frontend-woocommerce-checkout.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-general.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-advanced.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-settings-integrations.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-frontend-template-overrides.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-users.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/classes/class-admin-capabilities.php';

        // Frontend Handlers
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php';
    }

    /**
     * Instantiate plugin classes.
     */
    private function instantiate_classes() {
        // Initialize capabilities first
        new Admin\Admin_Capabilities();
        
        // Initialize other classes
        new Custom_Post_Types\Setup();
        new Shortcodes();
        new Woocommerce();
        new Woocommerce_Subscriptions();
        new Assets();
        new Woocommerce\Frontend_Endpoints();
        new Frontend_Woocommerce_Checkout();
        new Admin\Settings_General();
        new Admin\Settings_Advanced();
        new Admin\Settings_Integrations();
        new Admin\Setup();
        new Admin\Users();

        // Frontend Handlers
        new Custom_Post_Types\Project\Frontend_Handler();
        new Custom_Post_Types\ProjectRequest\Frontend_Handler();
        new Custom_Post_Types\ProjectProposal\Frontend_Handler();
    }

    public function woocommerce_notice() {
        echo '<div class="error"><p>';
        echo esc_html__('Arsol Projects for WooCommerce requires WooCommerce to be installed and active.', 'arsol-pfw');
        echo '</p></div>';
    }

    /**
     * Debug status notice to help troubleshoot setup issues
     */
    public function debug_status_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $post_types = get_post_types();
        $arsol_project_exists = in_array('arsol-project', $post_types);
        $arsol_request_exists = in_array('arsol-pfw-request', $post_types);
        $arsol_proposal_exists = in_array('arsol-pfw-proposal', $post_types);
        
        $query_vars = get_option('rewrite_rules');
        $projects_endpoint = false;
        if ($query_vars) {
            foreach ($query_vars as $rule => $rewrite) {
                if (strpos($rule, 'projects') !== false) {
                    $projects_endpoint = true;
                    break;
                }
            }
        }
        
        $notice_class = 'notice-info';
        $all_working = $arsol_project_exists && $arsol_request_exists && $arsol_proposal_exists && $projects_endpoint;
        if (!$all_working) {
            $notice_class = 'notice-warning';
        }
        
        echo '<div class="notice ' . $notice_class . '"><p>';
        echo '<strong>Arsol Projects Debug Status:</strong><br>';
        echo 'Project CPT: ' . ($arsol_project_exists ? '✓ Registered' : '✗ Missing') . '<br>';
        echo 'Request CPT: ' . ($arsol_request_exists ? '✓ Registered' : '✗ Missing') . '<br>';
        echo 'Proposal CPT: ' . ($arsol_proposal_exists ? '✓ Registered' : '✗ Missing') . '<br>';
        echo 'Projects Endpoint: ' . ($projects_endpoint ? '✓ Found' : '✗ Missing') . '<br>';
        echo 'WooCommerce: ' . (class_exists('WooCommerce') ? '✓ Active' : '✗ Inactive') . '<br>';
        
        if (isset($_GET['flushed'])) {
            echo '<strong style="color: green;">✓ Rewrite rules flushed successfully!</strong><br>';
        }
        
        if (!$all_working) {
            echo '<br><a href="' . admin_url('admin.php?arsol_flush_rewrite_rules=1') . '" class="button button-secondary">Flush Rewrite Rules</a>';
        }
        
        echo '</p></div>';
    }

    /**
     * Plugin activation callback
     * 
     * @return void
     */
    public function activate() {
        // Flush rewrite rules to ensure our custom post types and endpoints are registered
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation callback
     * 
     * @return void
     */
    public function deactivate() {
        // Flush rewrite rules on deactivation to clean up
        flush_rewrite_rules();
    }
}
