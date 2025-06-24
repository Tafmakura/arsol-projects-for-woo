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
        
        $this->customer_email = true;
        $this->admin_email = true;
        
        $this->heading = 'New Project Request Submitted';
        $this->subject = 'Your Project Request Has Been Received - #{request_id}';
        
        // Email templates
        $this->template_html = 'emails/new-request.php';
        $this->template_plain = 'emails/plain/new-request.php';
        
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
            // Send to customer
            $this->send_customer_email();
            
            // Send to admins
            $this->send_admin_email();
        }
        
        $this->restore_locale();
    }
    
    /**
     * Send email to customer
     */
    private function send_customer_email() {
        $this->recipient = get_user_by('id', $this->customer_id)->user_email;
        $this->heading = 'Your Project Request Has Been Received';
        $this->subject = $this->replace_placeholders(
            'Your Project Request Has Been Received - #{request_id}',
            $this->find_replace
        );
        
        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content(),
            $this->get_headers(),
            $this->get_attachments()
        );
    }
    
    /**
     * Send email to admins
     */
    private function send_admin_email() {
        $admin_emails = $this->get_admin_emails();
        
        foreach ($admin_emails as $admin_email) {
            $this->recipient = $admin_email;
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
     * Get admin email addresses
     * 
     * @return array
     */
    private function get_admin_emails() {
        $admin_emails = array();
        
        // Get users with specific capabilities
        $admins = get_users(array(
            'capability' => 'manage_arsol_projects',
            'fields' => 'user_email'
        ));
        
        if (empty($admins)) {
            // Fallback to administrators
            $admins = get_users(array(
                'role' => 'administrator',
                'fields' => 'user_email'
            ));
        }
        
        return $admins;
    }
    
    /**
     * Get email content for customers
     * 
     * @return string
     */
    public function get_content() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'request' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'portal_url' => $this->get_portal_url('project-view-request', $this->request_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('success'),
                'status_icon' => $this->get_status_icon('success')
            ),
            '',
            $this->template_base
        );
    }
    
    /**
     * Get email content for admins
     * 
     * @return string
     */
    public function get_admin_content() {
        return wc_get_template_html(
            'emails/admin-new-request.php',
            array(
                'request' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'admin_url' => $this->get_admin_url($this->request_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('processing'),
                'status_icon' => $this->get_status_icon('update')
            ),
            '',
            $this->template_base
        );
    }
    
    /**
     * Get plain text content
     * 
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'request' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'portal_url' => $this->get_portal_url('project-view-request', $this->request_id),
                'email_heading' => $this->get_heading(),
                'email' => $this
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
}
