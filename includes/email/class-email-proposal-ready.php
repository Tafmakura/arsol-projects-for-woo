<?php
namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Proposal Ready Email
 * 
 * Email sent when proposal is ready for customer review
 */
class Proposal_Ready_Email extends Base_Email {
    
    /**
     * Proposal ID
     * @var int
     */
    public $proposal_id;
    
    /**
     * Customer ID
     * @var int
     */
    public $customer_id;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_proposal_ready';
        $this->title = 'Project Proposal Ready for Review';
        $this->description = 'Email sent when proposal is ready for customer review';
        
        $this->customer_email = true;
        $this->admin_email = true;
        
        $this->heading = 'Your Proposal is Ready for Review';
        $this->subject = 'Your Proposal is Ready! Review Now - #{proposal_id}';
        
        // Email templates
        $this->template_html = 'templates/email-proposal-ready.php';
        
        // Triggers
        add_action('arsol_proposal_ready_for_review', array($this, 'trigger'), 10, 2);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     * 
     * @param int $proposal_id
     * @param int $customer_id
     */
    public function trigger($proposal_id, $customer_id) {
        $this->setup_locale();
        
        if (!$proposal_id || !$customer_id) {
            return;
        }
        
        $this->proposal_id = $proposal_id;
        $this->customer_id = $customer_id;
        
        $proposal = get_post($proposal_id);
        $customer = get_user_by('id', $customer_id);
        
        if (!$proposal || !$customer) {
            return;
        }
        
        $this->object = $proposal;
        
        // Replace placeholders
        $this->find_replace = array(
            '{proposal_id}' => $proposal_id,
            '{proposal_title}' => $proposal->post_title,
            '{customer_name}' => $this->format_user_name($customer),
            '{site_name}' => get_bloginfo('name')
        );
        
        if ($this->is_enabled()) {
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
        $customer = get_user_by('id', $this->customer_id);
        $this->recipient = $customer->user_email;
        $this->heading = '🎉 Your Proposal is Ready for Review';
        $this->subject = $this->replace_placeholders(
            'Your Proposal is Ready! Review Now - #{proposal_id}',
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
            $this->heading = 'Proposal Ready for Customer Review';
            $this->subject = $this->replace_placeholders(
                'Proposal Ready: {proposal_title} - Awaiting Customer Review',
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
     * @return string
     */
    public function get_content() {
        // For preview mode, set up dummy data if needed
        if (!$this->object) {
            $this->setup_preview_data();
        }
        
        $template_vars = array(
            'proposal' => $this->object,
            'customer' => get_user_by('id', $this->customer_id) ?: $this->get_dummy_customer(),
            'portal_url' => $this->get_portal_url('project-view-proposal', $this->proposal_id ?: 123),
            'email_heading' => $this->get_heading(),
            'email' => $this,
            'color_scheme' => $this->get_color_scheme('action_required'),
            'status_icon' => $this->get_status_icon('action_required')
        );
        
        // Try absolute path first
        $template_file = $this->template_base . $this->template_html;
        
        if (file_exists($template_file)) {
            // Load template directly
            extract($template_vars);
            ob_start();
            include $template_file;
            $content = ob_get_clean();
        } else {
            // Fallback to WooCommerce method
            $content = wc_get_template_html(
                $this->template_html,
                $template_vars,
                '',
                $this->template_base
            );
        }
        
        return $content;
    }
    
    /**
     * Setup preview data for email preview
     */
    private function setup_preview_data() {
        if (!$this->object) {
            // Create dummy proposal object for preview
            $this->object = (object) array(
                'ID' => 123,
                'post_title' => 'Sample Project Proposal',
                'post_date' => current_time('mysql'),
                'post_content' => 'This is a sample project proposal for preview purposes.',
                'post_author' => 1
            );
        }
        
        if (!$this->proposal_id) {
            $this->proposal_id = 123;
        }
        
        if (!$this->customer_id) {
            $this->customer_id = 1;
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
            'emails/admin-proposal-ready.php',
            array(
                'proposal' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'admin_url' => $this->get_admin_url($this->proposal_id),
                'portal_url' => $this->get_portal_url('project-review-proposal', $this->proposal_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('action_required'),
                'status_icon' => $this->get_status_icon('action_required')
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
