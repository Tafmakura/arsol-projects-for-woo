<?php
namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base Email Class
 * 
 * Base class for all Arsol Projects for Woo emails
 * Extends WC_Email with common functionality
 */
abstract class Base_Email extends \WC_Email {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Set default properties
        $this->template_html = 'emails/' . $this->id . '.php';
        $this->template_plain = 'emails/plain/' . $this->id . '.php';
        $this->template_base = ARSOL_PFW_PLUGIN_DIR . 'templates/';
        
        // Call parent constructor
        parent::__construct();
    }
    
    /**
     * Get portal URL for customers
     * 
     * @param string $endpoint
     * @param mixed $id
     * @return string
     */
    protected function get_portal_url($endpoint, $id = null) {
        $url = wc_get_account_endpoint_url($endpoint);
        
        if ($id) {
            $url = rtrim($url, '/') . '/' . $id;
        }
        
        return $url;
    }
    
    /**
     * Get admin edit URL
     * 
     * @param int $post_id
     * @return string
     */
    protected function get_admin_url($post_id) {
        return admin_url('post.php?post=' . $post_id . '&action=edit');
    }
    
    /**
     * Format user name for emails
     * 
     * @param \WP_User $user
     * @return string
     */
    protected function format_user_name($user) {
        if (!$user || !($user instanceof \WP_User)) {
            return 'Customer';
        }
        
        $name = trim($user->first_name . ' ' . $user->last_name);
        return !empty($name) ? $name : $user->display_name;
    }
    
    /**
     * Get email color scheme based on type
     * 
     * @param string $type
     * @return array
     */
    protected function get_color_scheme($type) {
        $schemes = array(
            'processing' => array(
                'background' => '#cce5ff',
                'text' => '#0073aa',
                'cta' => '#0073aa'
            ),
            'success' => array(
                'background' => '#d4edda',
                'text' => '#155724',
                'cta' => '#28a745'
            ),
            'action_required' => array(
                'background' => '#fff3cd',
                'text' => '#856404',
                'cta' => '#ffc107'
            ),
            'error' => array(
                'background' => '#f8d7da',
                'text' => '#721c24',
                'cta' => '#dc3545'
            )
        );
        
        return isset($schemes[$type]) ? $schemes[$type] : $schemes['processing'];
    }
    
    /**
     * Get status icon for emails
     * 
     * @param string $type
     * @return string
     */
    protected function get_status_icon($type) {
        $icons = array(
            'processing' => '🔧',
            'success' => '🎉',
            'action_required' => '⏰',
            'error' => '⚠️',
            'update' => '📋',
            'delivered' => '✅'
        );
        
        return isset($icons[$type]) ? $icons[$type] : '📧';
    }
}
