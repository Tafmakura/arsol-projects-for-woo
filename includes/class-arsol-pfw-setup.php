<?php
/**
 * Root Setup Class for Arsol Projects for WooCommerce
 *
 * This is the main entry point for the plugin that initializes all subsystems.
 * It follows the new architecture where each directory has its own setup class.
 *
 * @package Arsol_PFW
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Setup Class
 *
 * Initializes all plugin subsystems through their respective setup classes.
 * This class acts as the central orchestrator for the entire plugin.
 */
class Setup {
    
    /**
     * Instance of this class
     *
     * @var Setup
     */
    private static $instance;
    
    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.0';
    
    /**
     * Plugin path
     *
     * @var string
     */
    public $plugin_path;
    
    /**
     * Plugin URL
     *
     * @var string
     */
    public $plugin_url;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->define_constants();
        $this->init_hooks();
        $this->init_subsystems();
    }
    
    /**
     * Get instance
     *
     * @return Setup
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Define plugin constants
     */
    private function define_constants() {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__));
        $this->plugin_url = plugin_dir_url(dirname(__FILE__));
        
        if (!defined('ARSOL_PFW_VERSION')) {
            define('ARSOL_PFW_VERSION', $this->version);
        }
        
        if (!defined('ARSOL_PFW_PLUGIN_PATH')) {
            define('ARSOL_PFW_PLUGIN_PATH', $this->plugin_path);
        }
        
        if (!defined('ARSOL_PFW_PLUGIN_URL')) {
            define('ARSOL_PFW_PLUGIN_URL', $this->plugin_url);
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'), 0);
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
        
        // Register activation/deactivation hooks
        register_activation_hook(ARSOL_PFW_PLUGIN_PATH . 'arsol-projects-for-woo.php', array($this, 'activate'));
        register_deactivation_hook(ARSOL_PFW_PLUGIN_PATH . 'arsol-projects-for-woo.php', array($this, 'deactivate'));
    }
    
    /**
     * Initialize subsystems
     *
     * Each subsystem is initialized through its own setup class.
     * This creates a clean separation of concerns.
     */
    private function init_subsystems() {
        // Include directory setup classes
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/core/class-arsol-pfw-setup-core.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/admin/class-arsol-pfw-setup-admin.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/frontend/class-arsol-pfw-setup-frontend.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/custom-post-types/class-arsol-pfw-setup-custom-post-types.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/taxonomies/class-arsol-pfw-setup-taxonomies.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/data-stores/class-arsol-pfw-setup-data-stores.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/workflows/class-arsol-pfw-setup-workflows.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/integrations/class-arsol-pfw-setup-integrations.php';
        require_once ARSOL_PFW_PLUGIN_PATH . 'includes/email/class-arsol-pfw-setup-email.php';
        
        // Initialize directory setup classes in dependency order
        new \Arsol_PFW\Setup\Core();
        new \Arsol_PFW\Setup\Admin();
        new \Arsol_PFW\Setup\Frontend();
        new \Arsol_PFW\Setup\Custom_Post_Types();
        new \Arsol_PFW\Setup\Taxonomies();
        new \Arsol_PFW\Setup\Data_Stores();
        new \Arsol_PFW\Setup\Workflows();
        new \Arsol_PFW\Setup\Integrations();
        new \Arsol_PFW\Setup\Email();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Initialize localization
        $this->load_plugin_textdomain();
        
        // Fire init action
        do_action('arsol_pfw_init');
    }
    
    /**
     * Plugins loaded
     */
    public function plugins_loaded() {
        do_action('arsol_pfw_plugins_loaded');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            wp_die(__('Arsol Projects for WooCommerce requires PHP 7.4 or later.', 'arsol-pfw'));
        }
        
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            wp_die(__('Arsol Projects for WooCommerce requires WooCommerce to be installed and activated.', 'arsol-pfw'));
        }
        
        // Fire activation action
        do_action('arsol_pfw_activate');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Fire deactivation action
        do_action('arsol_pfw_deactivate');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>';
        echo __('Arsol Projects for WooCommerce requires WooCommerce to be installed and activated.', 'arsol-pfw');
        echo '</strong></p></div>';
    }
    
    /**
     * Load plugin textdomain
     */
    private function load_plugin_textdomain() {
        $locale = apply_filters('plugin_locale', get_locale(), 'arsol-pfw');
        
        load_textdomain('arsol-pfw', WP_LANG_DIR . '/arsol-pfw/arsol-pfw-' . $locale . '.mo');
        load_plugin_textdomain('arsol-pfw', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    /**
     * Get plugin path
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }
    
    /**
     * Get plugin URL
     *
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit(plugin_dir_url(__FILE__));
    }
} 