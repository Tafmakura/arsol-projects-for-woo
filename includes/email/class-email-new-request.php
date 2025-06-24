<?php
namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * New Request Email
 * 
 * Email sent when a new project request is submitted
 */
class New_Request_Email extends Base_Email {
    
    /**
     * Request ID
     * @var int
     */
    public $request_id;
    
    /**
     * Customer ID
     * @var int
     */
    public $customer_id;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_new_request';
        $this->title = 'New Project Request';
        $this->description = 'Email sent when a new project request is submitted';
        
        $this->customer_email = false;
        $this->admin_email = false;
        $this->shop_manager_email = true;
        
        $this->heading = 'New Project Request Submitted';
        $this->subject = 'Your Project Request Has Been Received - #{request_id}';
        
        // Email templates
        $this->template_html = 'templates/email-new-request.php';
        
        // Triggers
        add_action('arsol_new_request_created', array($this, 'trigger'), 10, 2);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     * 
     * @param int $request_id
     * @param int $customer_id
     */
    public function trigger($request_id, $customer_id) {
        $this->setup_locale();
        
        if (!$request_id || !$customer_id) {
            return;
        }
        
        $this->request_id = $request_id;
        $this->customer_id = $customer_id;
        
        $request = get_post($request_id);
        $customer = get_user_by('id', $customer_id);
        
        if (!$request || !$customer) {
            return;
        }
        
        $this->object = $request;
        $this->recipient = $customer->user_email;
        
        // Replace placeholders in subject and heading
        $this->find_replace = array(
            '{request_id}' => $request_id,
            '{request_title}' => $request->post_title,
            '{customer_name}' => $this->format_user_name($customer),
            '{site_name}' => get_bloginfo('name')
        );
        
        if ($this->is_enabled() && $this->get_recipient()) {
            // Send to shop managers only
            $this->send_shop_manager_email();
        }
        
        $this->restore_locale();
    }
    
    /**
     * Send email to shop managers
     */
    private function send_shop_manager_email() {
        $shop_manager_emails = $this->get_shop_manager_emails();
        
        foreach ($shop_manager_emails as $email) {
            $this->recipient = $email;
            $this->heading = 'New Project Request Submitted';
            $this->subject = $this->replace_placeholders(
                'New Project Request: {request_title}',
                $this->find_replace
            );
            
            $this->send(
                $this->get_recipient(),
                $this->get_subject(),
                $this->get_admin_content(),
                $this->get_headers(),
                $this->get_attachments()
            );
        }
    }
    
    /**
     * Get email content for customers
     * 
     * @return string
     */
    public function get_content() {
        // For preview mode, set up dummy data if needed
        if (!$this->object) {
            $this->setup_preview_data();
        }
        
        // Debug logging
        error_log('New Request Email - get_content called');
        error_log('Template HTML: ' . $this->template_html);
        error_log('Template Base: ' . $this->template_base);
        error_log('Object: ' . print_r($this->object, true));
        
        $template_vars = array(
            'request' => $this->object,
            'customer' => get_user_by('id', $this->customer_id) ?: $this->get_dummy_customer(),
            'portal_url' => $this->get_portal_url('project-view-request', $this->request_id ?: 123),
            'email_heading' => $this->get_heading(),
            'email' => $this,
            'color_scheme' => $this->get_color_scheme('success'),
            'status_icon' => $this->get_status_icon('success')
        );
        
        error_log('Template vars: ' . print_r(array_keys($template_vars), true));
        
        $content = wc_get_template_html(
            $this->template_html,
            $template_vars,
            '',
            $this->template_base
        );
        
        error_log('Generated content length: ' . strlen($content));
        
        return $content;
    }
    
    /**
     * Setup preview data for email preview
     */
    private function setup_preview_data() {
        if (!$this->object) {
            // Create dummy request object for preview
            $this->object = (object) array(
                'ID' => 123,
                'post_title' => 'Sample Project Request',
                'post_date' => current_time('mysql'),
                'post_content' => 'This is a sample project request for preview purposes.'
            );
        }
        
        if (!$this->customer_id) {
            $this->customer_id = 1; // Use admin user for preview
        }
        
        if (!$this->request_id) {
            $this->request_id = 123;
        }
    }
    
    /**
     * Get dummy customer for preview
     */
    private function get_dummy_customer() {
        return (object) array(
            'ID' => 1,
            'user_email' => 'customer@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'display_name' => 'John Doe'
        );
    }
    
    /**
     * Get email content for admins
     * 
     * @return string
     */
    public function get_admin_content() {
        return wc_get_template_html(
            'templates/email-admin-new-request.php',
            array(
                'request' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'admin_url' => $this->get_admin_url($this->request_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'status_icon' => $this->get_status_icon('update')
            ),
            '',
            $this->template_base
        );
    }
    
    /**
     * Initialize settings form fields
     */
    public function init_form_fields() {
        parent::init_form_fields();
        
        $this->form_fields['recipient'] = array(
            'title'       => 'Recipient(s)',
            'type'        => 'text',
            'description' => 'Enter recipients (comma separated) for admin notifications. Leave blank to send to all administrators.',
            'default'     => '',
            'desc_tip'    => true
        );
    }
    
    /**
     * Admin options for WooCommerce email settings
     */
    public function admin_options() {
        // Set up preview data for admin preview
        $this->setup_preview_data();
        
        // Call parent admin_options
        parent::admin_options();
    }
}
