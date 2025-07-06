<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Master Includes Setup Class
 * 
 * Coordinates all plugin includes and their setup classes
 */
class Includes_Setup {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->require_includes_files();
        $this->initialize_includes_classes();
        $this->setup_hooks();
    }

    /**
     * Require all includes files
     */
    private function require_includes_files() {
        // Core Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-assets.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-shortcodes.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-stages-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/core/class-pfw-core-setup.php';
        
        // Admin Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-capabilities.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-advanced.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-display.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-files.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-general.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-integrations.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-settings-tools.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-setup-defaults.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-admin-users.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-menu-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/admin/class-arsol-pfw-admin-setup.php';
        
        // Frontend Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-frontend-comments.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-frontend-template-overrides.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-frontend-template-sidebar-buttons.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-frontend-template-sidebar-meta.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-frontend-woocommerce-checkout.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-frontend-woocommerce-endpoints.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/frontend/class-arsol-pfw-frontend-setup.php';
        
        // Custom Post Types Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/class-arsol-pfw-custom-post-types-setup.php';
        
        // Project CPT Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-project.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-admin-projects.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-project-cpt.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project/class-projects-cpt.php';
        
        // Project Request CPT Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-admin-requests.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-request-cpt.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-request/class-project-requests-cpt.php';
        
        // Project Proposal CPT Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-budget.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal-quotation.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposals.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposal-cpt.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/custom-post-types/project-proposal/class-project-proposals-cpt.php';
        
        // Taxonomies Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/class-arsol-pfw-taxonomies-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/request-stage/class-taxonomies-request-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/request-stage/class-taxonomies-request-stage-admin.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/proposal-stage/class-taxonomies-proposal-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/proposal-stage/class-taxonomies-proposal-stage-admin.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/project-stage/class-taxonomies-project-stage-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/taxonomies/project-stage/class-taxonomies-project-stage-admin.php';
        
        // Workflows Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/workflows/class-arsol-pfw-workflows-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/workflows/standard/class-arsol-pfw-workflow-standard-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/workflows/standard/class-arsol-pfw-workflow-standard.php';
        
        // Integrations Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/class-arsol-pfw-integrations-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-woocommerce.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-woocommerce-logs.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce/class-woocommerce-biller-invoice.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/integrations/woocommerce-subscriptions/class-woocommerce-subscriptions.php';
        
        // Phases Files
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/phases/class-arsol-pfw-phases-setup.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/phases/class-arsol-pfw-phases-conversion.php';
        
        // Email Files (excluding templates)
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-email-manager.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-admin-new-project.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-admin-new-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-new-request.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-project-completion.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-project-creation.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-project-stage.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-proposal-decision.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-proposal-processing.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-proposal-ready.php';
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-wc-email-request-stage.php';
    }

    /**
     * Initialize all includes classes
     */
    private function initialize_includes_classes() {
        // Initialize Core Classes
        new Assets();
        new Shortcodes();
        new Stages_Handler();
        new Core\Setup();
        
        // Initialize Admin Classes
        new Admin\Admin_Capabilities();
        new Admin\Settings_Advanced();
        new Admin\Settings_Display();
        new Admin\Settings_Files();
        new Admin\Settings_General();
        new Admin\Settings_Integrations();
        new Admin\Settings_Tools();
        new Admin\Setup_Defaults();
        new Admin\Users();
        new \Arsol_Projects_For_Woo\Admin\Menu\Setup();
        Admin\Setup::get_instance();
        
        // Initialize Frontend Classes
        new Frontend\Comments();
        new Frontend\Template_Overrides();
        new Frontend\Template_Sidebar_Buttons();
        new Frontend\Template_Sidebar_Meta();
        new Frontend\WooCommerce_Checkout();
        new Frontend\WooCommerce_Endpoints();
        Frontend\Setup::get_instance();
        
        // Initialize Custom Post Types Setup
        new Custom_Post_Types\Setup();
        
        // Initialize Project CPT Classes
        new Custom_Post_Types\Project\Setup();
        new Custom_Post_Types\Project\Admin_Project();
        new Custom_Post_Types\Project\Admin_Projects();
        new Custom_Post_Types\Project\Frontend_Handler();
        new Custom_Post_Types\Project\Project_CPT();
        new Custom_Post_Types\Project\Projects_CPT();
        
        // Initialize Project Request CPT Classes
        new Custom_Post_Types\ProjectRequest\Setup();
        new Custom_Post_Types\ProjectRequest\Admin_Request();
        new Custom_Post_Types\ProjectRequest\Admin_Requests();
        new Custom_Post_Types\ProjectRequest\Frontend_Handler();
        new Custom_Post_Types\ProjectRequest\Project_Request_CPT();
        new Custom_Post_Types\ProjectRequest\Project_Requests_CPT();
        
        // Initialize Project Proposal CPT Classes
        new Custom_Post_Types\ProjectProposal\Setup();
        new Custom_Post_Types\ProjectProposal\Admin_Proposal();
        new Custom_Post_Types\ProjectProposal\Admin_Proposal_Budget();
        new Custom_Post_Types\ProjectProposal\Admin_Proposal_Quotation();
        new Custom_Post_Types\ProjectProposal\Admin_Proposals();
        new Custom_Post_Types\ProjectProposal\Frontend_Handler();
        new Custom_Post_Types\ProjectProposal\Project_Proposal_CPT();
        new Custom_Post_Types\ProjectProposal\Project_Proposals_CPT();
        
        // Initialize Taxonomies Classes
        new Taxonomies\Setup();
        new Taxonomies\Request_Stage\Setup();
        new Taxonomies\Request_Stage\Admin();
        new Taxonomies\Proposal_Stage\Setup();
        new Taxonomies\Proposal_Stage\Admin();
        new Taxonomies\Project_Stage\Setup();
        new Taxonomies\Project_Stage\Admin();
        
        // Initialize Workflows Classes
        Workflows\Setup::get_instance();
        new Workflows\Standard\Setup();
        new Workflows\Standard\Standard();
        
        // Initialize Integrations Classes
        Integrations\Setup::get_instance();
        new Integrations\WooCommerce\WooCommerce();
        new Integrations\WooCommerce\Logs();
        new Integrations\WooCommerce\Biller_Invoice();
        new Integrations\WooCommerce_Subscriptions\Subscriptions();
        
        // Initialize Phases Classes
        Phases\Setup::get_instance();
        Phases\Conversion::get_instance();
        
        // Initialize Email Classes
        \Arsol_Email_Manager::init();
        new WC_Email_Admin_New_Project();
        new WC_Email_Admin_New_Request();
        new WC_Email_New_Request();
        new WC_Email_Project_Completion();
        new WC_Email_Project_Creation();
        new WC_Email_Project_Stage();
        new WC_Email_Proposal_Decision();
        new WC_Email_Proposal_Processing();
        new WC_Email_Proposal_Ready();
        new WC_Email_Request_Stage();
    }

    /**
     * Setup plugin hooks
     */
    private function setup_hooks() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        
        // Register activation and deactivation hooks
        register_activation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(ARSOL_PROJECTS_PLUGIN_FILE, array($this, 'deactivate'));
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_notice'));
            return;
        }
        
        // Plugin is fully loaded and WooCommerce is active
        do_action('arsol_pfw_includes_loaded');
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('arsol-pfw', false, dirname(ARSOL_PROJECTS_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * WooCommerce dependency notice
     */
    public function woocommerce_notice() {
        echo '<div class="error"><p>';
        echo esc_html__('Arsol Projects for WooCommerce requires WooCommerce to be installed and active.', 'arsol-pfw');
        echo '</p></div>';
    }

    /**
     * Plugin activation callback
     */
    public function activate() {
        // Flush rewrite rules to ensure our custom post types and endpoints are registered
        flush_rewrite_rules();
        
        // Trigger activation hook for all setup classes
        do_action('arsol_pfw_includes_activate');
    }

    /**
     * Plugin deactivation callback
     */
    public function deactivation() {
        // Trigger deactivation hook for all setup classes
        do_action('arsol_pfw_includes_deactivate');
        
        // Flush rewrite rules on deactivation to clean up
        flush_rewrite_rules();
    }

    /**
     * Get setup status for all includes
     */
    public function get_includes_status() {
        return [
            'core' => class_exists('Arsol_Projects_For_Woo\Core\Setup'),
            'custom_post_types' => class_exists('Arsol_Projects_For_Woo\Custom_Post_Types\Setup'),
            'taxonomies' => class_exists('Arsol_Projects_For_Woo\Taxonomies\Setup'),
            'email_manager' => class_exists('Arsol_Email_Manager'),
            'admin' => class_exists('Arsol_Projects_For_Woo\Admin\Setup'),
            'frontend' => class_exists('Arsol_Projects_For_Woo\Frontend\Setup'),
            'integrations' => class_exists('Arsol_Projects_For_Woo\Integrations\Setup'),
            'phases' => class_exists('Arsol_Projects_For_Woo\Phases\Setup'),
            'workflows' => class_exists('Arsol_Projects_For_Woo\Workflows\Setup'),
            'woocommerce_active' => class_exists('WooCommerce'),
        ];
    }

    /**
     * Get all setup class instances
     */
    public function get_setup_instances() {
        return [
            'core' => new Core\Setup(),
            'custom_post_types' => new Custom_Post_Types\Setup(),
            'taxonomies' => new Taxonomies\Setup(),
            'admin' => Admin\Setup::get_instance(),
            'frontend' => Frontend\Setup::get_instance(),
            'integrations' => Integrations\Setup::get_instance(),
            'phases' => Phases\Setup::get_instance(),
            'workflows' => Workflows\Setup::get_instance(),
        ];
    }
} 