<?php
/**
 * Frontend Notices Handler
 *
 * @package Arsol_Projects_For_Woo
 */

namespace Arsol_Projects_For_Woo\Classes;

// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend Notices class
 */
class Frontend_Notices {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook into WooCommerce account page early to show notices
        add_action('woocommerce_account_content', array($this, 'display_notices'), 1);
        add_action('woocommerce_before_template_part', array($this, 'display_notices_before_template'), 1);
        // Fallback for non-WooCommerce pages
        add_action('wp_footer', array($this, 'display_notices_fallback'));
    }

    /**
     * Display notices using template
     */
    public function display_notices() {
        if (isset($_GET['notice_type']) && isset($_GET['notice_message'])) {
            static $notice_displayed = false;
            if (!$notice_displayed) {
                $this->load_notice_template();
                $notice_displayed = true;
            }
        }
    }

    /**
     * Display notices before specific template parts
     */
    public function display_notices_before_template($template_name) {
        // Only show notices on our plugin's pages
        if (strpos($template_name, 'myaccount/') === 0 || 
            strpos($template_name, 'arsol-') === 0) {
            $this->display_notices();
        }
    }

    /**
     * Fallback display for pages without WooCommerce hooks
     */
    public function display_notices_fallback() {
        if (isset($_GET['notice_type']) && isset($_GET['notice_message'])) {
            // Only display if no previous notice was shown
            static $notice_shown = false;
            if (!$notice_shown) {
                ?>
                <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    // Check if notice already exists
                    if (!document.getElementById('arsol-notice')) {
                        <?php $this->load_notice_template(); ?>
                    }
                });
                </script>
                <?php
                $notice_shown = true;
            }
        }
    }

    /**
     * Load the notice template
     */
    private function load_notice_template() {
        $template_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/notice-display.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
} 