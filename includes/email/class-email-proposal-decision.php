<?php
namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Proposal Decision Email
 * 
 * Email sent when proposal is approved or rejected
 */
class Proposal_Decision_Email extends Base_Email {
    
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
     * Decision (approved/rejected)
     * @var string
     */
    public $decision;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_proposal_decision';
        $this->title = 'Project Proposal Decision';
        $this->description = 'Email sent when proposal is approved or rejected';
        
        $this->customer_email = false; // Only send to lead/admin
        $this->admin_email = true;
        
        $this->heading = 'Proposal Decision Received';
        $this->subject = 'Proposal {decision}: {proposal_title} - #{proposal_id}';
        
        // Email templates
        $this->template_html = 'emails/proposal-decision.php';
        $this->template_plain = 'emails/plain/proposal-decision.php';
        
        // Triggers
        add_action('arsol_proposal_approved', array($this, 'trigger_approved'), 10, 2);
        add_action('arsol_proposal_rejected', array($this, 'trigger_rejected'), 10, 2);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email for approved proposal
     * 
     * @param int $proposal_id
     * @param int $customer_id
     */
    public function trigger_approved($proposal_id, $customer_id) {
        $this->trigger($proposal_id, $customer_id, 'approved');
    }
    
    /**
     * Trigger the email for rejected proposal
     * 
     * @param int $proposal_id
     * @param int $customer_id
     */
    public function trigger_rejected($proposal_id, $customer_id) {
        $this->trigger($proposal_id, $customer_id, 'rejected');
    }
    
    /**
     * Trigger the email
     * 
     * @param int $proposal_id
     * @param int $customer_id
     * @param string $decision
     */
    public function trigger($proposal_id, $customer_id, $decision) {
        $this->setup_locale();
        
        if (!$proposal_id || !$customer_id || !$decision) {
            return;
        }
        
        $this->proposal_id = $proposal_id;
        $this->customer_id = $customer_id;
        $this->decision = $decision;
        
        $proposal = get_post($proposal_id);
        $customer = get_user_by('id', $customer_id);
        
        if (!$proposal || !$customer) {
            return;
        }
        
        $this->object = $proposal;
        
        // Get project lead
        $project_lead_id = get_post_meta($proposal_id, '_arsol_pfw_proposal_project_lead_id', true);
        $project_lead = $project_lead_id ? get_user_by('id', $project_lead_id) : null;
        
        // Replace placeholders
        $this->find_replace = array(
            '{proposal_id}' => $proposal_id,
            '{proposal_title}' => $proposal->post_title,
            '{customer_name}' => $this->format_user_name($customer),
            '{decision}' => ucfirst($decision),
            '{site_name}' => get_bloginfo('name')
        );
        
        if ($this->is_enabled()) {
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
     * Send email to project lead
     */
    private function send_project_lead_email() {
        $project_lead_id = get_post_meta($this->proposal_id, '_arsol_pfw_proposal_project_lead_id', true);
        $project_lead = get_user_by('id', $project_lead_id);
        
        $this->recipient = $project_lead->user_email;
        
        if ($this->decision === 'approved') {
            $this->heading = '🎉 Proposal Approved - Project Creation Next';
            $this->subject = $this->replace_placeholders(
                'Proposal Approved: {proposal_title} - Ready for Project Creation',
                $this->find_replace
            );
        } else {
            $this->heading = 'Proposal Declined';
            $this->subject = $this->replace_placeholders(
                'Proposal Declined: {proposal_title} - No Action Required',
                $this->find_replace
            );
        }
        
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
            
            if ($this->decision === 'approved') {
                $this->heading = 'Proposal Approved - Conversion Pending';
                $this->subject = $this->replace_placeholders(
                    'Approved: {proposal_title} - Conversion to Project/Order Pending',
                    $this->find_replace
                );
            } else {
                $this->heading = 'Proposal Declined by Customer';
                $this->subject = $this->replace_placeholders(
                    'Declined: {proposal_title} - Customer Rejection',
                    $this->find_replace
                );
            }
            
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
     * Get email content for project leads
     * 
     * @return string
     */
    public function get_project_lead_content() {
        $email_type = $this->decision === 'approved' ? 'success' : 'error';
        
        return wc_get_template_html(
            'emails/lead-proposal-decision.php',
            array(
                'proposal' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'project_lead' => get_user_by('id', get_post_meta($this->proposal_id, '_arsol_pfw_proposal_project_lead_id', true)),
                'decision' => $this->decision,
                'admin_url' => $this->get_admin_url($this->proposal_id),
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
     * @return string
     */
    public function get_admin_content() {
        $email_type = $this->decision === 'approved' ? 'action_required' : 'error';
        
        return wc_get_template_html(
            'emails/admin-proposal-decision.php',
            array(
                'proposal' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'decision' => $this->decision,
                'admin_url' => $this->get_admin_url($this->proposal_id),
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
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'proposal' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'decision' => $this->decision,
                'admin_url' => $this->get_admin_url($this->proposal_id),
                'email_heading' => $this->get_heading(),
                'email' => $this
            ),
            '',
            $this->template_base
        );
    }
}
