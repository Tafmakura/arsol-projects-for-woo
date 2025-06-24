<?php
namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Proposal Processing Email
 * 
 * Email sent when proposal processing starts
 */
class Proposal_Processing_Email extends Base_Email {
    
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
     * Project Lead ID
     * @var int
     */
    public $project_lead_id;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_proposal_processing';
        $this->title = 'Proposal Processing Started';
        $this->description = 'Email sent when proposal processing begins';
        
        $this->customer_email = true;
        $this->admin_email = false;
        $this->shop_manager_email = false;
        $this->project_lead_email = true;
        
        $this->heading = 'We\'re Working on Your Proposal';
        $this->subject = 'We\'re Working on Your Proposal: {proposal_title} - #{proposal_id}';
        
        // Email templates
        $this->template_html = 'templates/email-proposal-processing.php';
        
        // Triggers
        add_action('arsol_proposal_processing_started', array($this, 'trigger'), 10, 3);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     * 
     * @param int $proposal_id
     * @param int $customer_id
     * @param int $project_lead_id
     */
    public function trigger($proposal_id, $customer_id, $project_lead_id) {
        $this->setup_locale();
        
        if (!$proposal_id || !$customer_id) {
            return;
        }
        
        $this->proposal_id = $proposal_id;
        $this->customer_id = $customer_id;
        $this->project_lead_id = $project_lead_id;
        
        $proposal = get_post($proposal_id);
        $customer = get_user_by('id', $customer_id);
        $project_lead = $project_lead_id ? get_user_by('id', $project_lead_id) : null;
        
        if (!$proposal || !$customer) {
            return;
        }
        
        $this->object = $proposal;
        
        // Replace placeholders
        $this->find_replace = array(
            '{proposal_id}' => $proposal_id,
            '{proposal_title}' => $proposal->post_title,
            '{customer_name}' => $this->format_user_name($customer),
            '{lead_name}' => $project_lead ? $this->format_user_name($project_lead) : 'Project Team',
            '{site_name}' => get_bloginfo('name')
        );
        
        if ($this->is_enabled()) {
            // Send to customer
            $this->send_customer_email();
            
            // Send to project lead
            if ($project_lead) {
                $this->send_project_lead_email();
            }
            
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
        $this->heading = '🔧 We\'re Working on Your Proposal';
        $this->subject = $this->replace_placeholders(
            'We\'re Working on Your Proposal: {proposal_title} - #{proposal_id}',
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
     * Send email to project lead
     */
    private function send_project_lead_email() {
        $project_lead = get_user_by('id', $this->project_lead_id);
        $this->recipient = $project_lead->user_email;
        $this->heading = 'Proposal Assignment Active';
        $this->subject = $this->replace_placeholders(
            'Now Processing: {proposal_title} - #{proposal_id}',
            $this->find_replace
        );
        
        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_project_lead_content(),
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
            $this->heading = 'Proposal Processing Started';
            $this->subject = $this->replace_placeholders(
                'Processing Started: {proposal_title} - Lead: {lead_name}',
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
        return wc_get_template_html(
            $this->template_html,
            array(
                'proposal' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'project_lead' => $this->project_lead_id ? get_user_by('id', $this->project_lead_id) : null,
                'portal_url' => $this->get_portal_url('project-view-proposal', $this->proposal_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('processing'),
                'status_icon' => $this->get_status_icon('processing')
            ),
            '',
            $this->template_base
        );
    }
    
    /**
     * Get email content for project leads
     * 
     * @return string
     */
    public function get_project_lead_content() {
        return wc_get_template_html(
            'emails/lead-proposal-processing.php',
            array(
                'proposal' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'project_lead' => get_user_by('id', $this->project_lead_id),
                'admin_url' => $this->get_admin_url($this->proposal_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('processing'),
                'status_icon' => $this->get_status_icon('processing')
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
            'emails/admin-proposal-processing.php',
            array(
                'proposal' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'project_lead' => $this->project_lead_id ? get_user_by('id', $this->project_lead_id) : null,
                'admin_url' => $this->get_admin_url($this->proposal_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('processing'),
                'status_icon' => $this->get_status_icon('processing')
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
        if (!$this->object) {
            $this->object = (object) array(
                'ID' => 123,
                'post_title' => 'Sample Proposal',
                'post_date' => current_time('mysql'),
                'post_content' => 'Sample content',
                'post_author' => 1
            );
        }
        if (!isset($this->proposal_id)) $this->proposal_id = 123;
        if (!isset($this->customer_id)) $this->customer_id = 1;
        if (!isset($this->project_lead_id)) $this->project_lead_id = 2;
        
        // Call parent admin_options
        parent::admin_options();
    }
}
