<?php
namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Status Email
 * 
 * Email sent when a request status changes
 */
class Request_Status_Email extends Base_Email {
    
    /**
     * Request ID
     * @var int
     */
    public $request_id;
    
    /**
     * Old status
     * @var string
     */
    public $old_status;
    
    /**
     * New status
     * @var string
     */
    public $new_status;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_request_status';
        $this->title = 'Project Request Status Change';
        $this->description = 'Email sent when a request status changes';
        
        $this->customer_email = true;
        $this->admin_email = true;
        
        $this->heading = 'Request Status Update';
        $this->subject = 'Status Update: {request_title} - #{request_id}';
        
        // Email templates
        $this->template_html = 'templates/email-request-status.php';
        
        // Triggers
        add_action('arsol_request_status_changed', array($this, 'trigger'), 10, 3);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     * 
     * @param int $request_id
     * @param string $old_status
     * @param string $new_status
     */
    public function trigger($request_id, $old_status, $new_status) {
        $this->setup_locale();
        
        if (!$request_id || !$new_status) {
            return;
        }
        
        $this->request_id = $request_id;
        $this->old_status = $old_status;
        $this->new_status = $new_status;
        
        $request = get_post($request_id);
        $customer = get_user_by('id', $request->post_author);
        
        if (!$request || !$customer) {
            return;
        }
        
        $this->object = $request;
        
        // Set email type based on status
        $email_type = $this->get_email_type_for_status($new_status);
        
        // Replace placeholders
        $this->find_replace = array(
            '{request_id}' => $request_id,
            '{request_title}' => $request->post_title,
            '{customer_name}' => $this->format_user_name($customer),
            '{old_status}' => $this->get_status_label($old_status, 'arsol-pfw-request'),
            '{new_status}' => $this->get_status_label($new_status, 'arsol-pfw-request'),
            '{site_name}' => get_bloginfo('name')
        );
        
        if ($this->is_enabled()) {
            // Send to customer (except for admin-only statuses)
            if ($this->should_notify_customer($new_status)) {
                $this->send_customer_email($email_type);
            }
            
            // Always send to admins
            $this->send_admin_email($email_type);
        }
        
        $this->restore_locale();
    }
    
    /**
     * Get email type based on status
     * 
     * @param string $status
     * @return string
     */
    private function get_email_type_for_status($status) {
        $types = array(
            'under-review' => 'processing',
            'on-hold' => 'error',
            'approved' => 'success'
        );
        
        return isset($types[$status]) ? $types[$status] : 'update';
    }
    
    /**
     * Should notify customer for this status
     * 
     * @param string $status
     * @return bool
     */
    private function should_notify_customer($status) {
        // Customer gets notifications for these statuses
        $customer_statuses = array('under-review', 'on-hold');
        return in_array($status, $customer_statuses);
    }
    
    /**
     * Send email to customer
     * 
     * @param string $email_type
     */
    private function send_customer_email($email_type) {
        $customer = get_user_by('id', $this->object->post_author);
        $this->recipient = $customer->user_email;
        
        // Customize subject and heading based on status
        switch ($this->new_status) {
            case 'under-review':
                $this->heading = 'Your Request is Being Reviewed';
                $this->subject = 'Update: Your Request is Being Reviewed - #{request_id}';
                break;
                
            case 'on-hold':
                $this->heading = 'Request Status Update';
                $this->subject = 'Status Update: Request On Hold - #{request_id}';
                break;
                
            default:
                $this->heading = 'Request Status Update';
                $this->subject = 'Status Update: {request_title} - #{request_id}';
        }
        
        $this->subject = $this->replace_placeholders($this->subject, $this->find_replace);
        
        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content($email_type),
            $this->get_headers(),
            $this->get_attachments()
        );
    }
    
    /**
     * Send email to admins
     * 
     * @param string $email_type
     */
    private function send_admin_email($email_type) {
        $admin_emails = $this->get_admin_emails();
        
        foreach ($admin_emails as $admin_email) {
            $this->recipient = $admin_email;
            $this->heading = 'Request Status Update';
            $this->subject = $this->replace_placeholders(
                'Request Status: {new_status} - {request_title}',
                $this->find_replace
            );
            
            $this->send(
                $this->get_recipient(),
                $this->get_subject(),
                $this->get_admin_content($email_type),
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
        
        $admins = get_users(array(
            'capability' => 'manage_arsol_projects',
            'fields' => 'user_email'
        ));
        
        if (empty($admins)) {
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
     * @param string $email_type
     * @return string
     */
    public function get_content($email_type = 'update') {
        return wc_get_template_html(
            $this->template_html,
            array(
                'request' => $this->object,
                'customer' => get_user_by('id', $this->object->post_author),
                'old_status' => $this->old_status,
                'new_status' => $this->new_status,
                'status_label' => $this->get_status_label($this->new_status, 'arsol-pfw-request'),
                'portal_url' => $this->get_portal_url('project-view-request', $this->request_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme($email_type),
                'status_icon' => $this->get_status_icon($email_type)
            ),
            '',
            $this->template_base
        );
    }
    
    /**
     * Get email content for admins
     * 
     * @param string $email_type
     * @return string
     */
    public function get_admin_content($email_type = 'update') {
        return wc_get_template_html(
            'emails/admin-request-status.php',
            array(
                'request' => $this->object,
                'customer' => get_user_by('id', $this->object->post_author),
                'old_status' => $this->old_status,
                'new_status' => $this->new_status,
                'status_label' => $this->get_status_label($this->new_status, 'arsol-pfw-request'),
                'admin_url' => $this->get_admin_url($this->request_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme($email_type),
                'status_icon' => $this->get_status_icon($email_type)
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
}
