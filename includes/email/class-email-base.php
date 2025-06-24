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
     * Email properties
     */
    protected $customer_email = false;
    protected $admin_email = false;
    protected $template_base;
    protected $request_id;
    protected $customer_id;
    protected $proposal_id;
    protected $project_id;
    protected $project_lead_id;
    protected $order_id;
    protected $find_replace = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        // Set template paths
        $this->template_html = 'templates/' . $this->id . '.php';
        $this->template_plain = 'templates/plain/' . $this->id . '.php';
        $this->template_base = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/';
        
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
    
    /**
     * Get status label for different post types
     * 
     * @param string $status
     * @param string $post_type
     * @return string
     */
    protected function get_status_label($status, $post_type) {
        $labels = array();
        
        switch ($post_type) {
            case 'arsol-pfw-request':
                $labels = array(
                    'pending-review' => 'Pending Review',
                    'under-review' => 'Under Review',
                    'on-hold' => 'On Hold',
                    'approved' => 'Approved'
                );
                break;
                
            case 'arsol-pfw-proposal':
                $labels = array(
                    'processing' => 'Processing',
                    'pending-approval' => 'Pending Approval',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected'
                );
                break;
                
            case 'arsol-project':
                $labels = array(
                    'not-started' => 'Not Started',
                    'in-progress' => 'In Progress',
                    'on-hold' => 'On Hold',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled'
                );
                break;
        }
        
        return isset($labels[$status]) ? $labels[$status] : ucfirst(str_replace('-', ' ', $status));
    }
    
    /**
     * Replace email placeholders
     * 
     * @param string $content
     * @param array $replacements
     * @return string
     */
    protected function replace_placeholders($content, $replacements) {
        foreach ($replacements as $placeholder => $value) {
            $content = str_replace('{' . $placeholder . '}', $value, $content);
        }
        
        return $content;
    }
    
    /**
     * Initialize settings form fields for WooCommerce email settings
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable this email notification',
                'default' => 'yes'
            ),
            'subject' => array(
                'title'       => 'Subject',
                'type'        => 'text',
                'description' => sprintf('This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->get_default_subject()),
                'placeholder' => $this->get_default_subject(),
                'default'     => ''
            ),
            'heading' => array(
                'title'       => 'Email Heading',
                'type'        => 'text',
                'description' => sprintf('This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', $this->get_default_heading()),
                'placeholder' => $this->get_default_heading(),
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => 'Email type',
                'type'        => 'select',
                'description' => 'Choose which format of email to send.',
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options()
            )
        );
    }
    
    /**
     * Get default subject for settings
     * 
     * @return string
     */
    public function get_default_subject() {
        return $this->subject;
    }
    
    /**
     * Get default heading for settings
     * 
     * @return string
     */
    public function get_default_heading() {
        return $this->heading;
    }
}
