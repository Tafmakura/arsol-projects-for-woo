<?php
namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Status Email
 * 
 * Email sent when project status changes
 */
class Project_Status_Email extends Base_Email {
    
    /**
     * Project ID
     * @var int
     */
    public $project_id;
    
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
        $this->id = 'arsol_project_status';
        $this->title = 'Project Status Update';
        $this->description = 'Email sent when project status changes';
        
        $this->customer_email = true;
        $this->admin_email = false;
        $this->shop_manager_email = false;
        $this->project_lead_email = true;
        
        $this->heading = 'Project Status Update';
        $this->subject = 'Project Update: {project_title} - {new_status}';
        
        // Email templates
        $this->template_html = 'templates/email-project-status.php';
        
        // Triggers
        add_action('arsol_project_status_changed', array($this, 'trigger'), 10, 3);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     * 
     * @param int $project_id
     * @param string $old_status
     * @param string $new_status
     */
    public function trigger($project_id, $old_status, $new_status) {
        $this->setup_locale();
        
        if (!$project_id || !$new_status) {
            return;
        }
        
        $this->project_id = $project_id;
        $this->old_status = $old_status;
        $this->new_status = $new_status;
        
        $project = get_post($project_id);
        $customer_id = get_post_meta($project_id, '_arsol_pfw_project_customer_id', true);
        $customer = get_user_by('id', $customer_id);
        
        if (!$project || !$customer) {
            return;
        }
        
        $this->object = $project;
        
        // Get project lead
        $project_lead_id = get_post_meta($project_id, '_arsol_pfw_project_lead_id', true);
        $project_lead = $project_lead_id ? get_user_by('id', $project_lead_id) : null;
        
        // Set email type based on status
        $email_type = $this->get_email_type_for_status($new_status);
        
        // Replace placeholders
        $this->find_replace = array(
            '{project_id}' => $project_id,
            '{project_title}' => $project->post_title,
            '{customer_name}' => $this->format_user_name($customer),
            '{lead_name}' => $project_lead ? $this->format_user_name($project_lead) : 'Project Team',
            '{old_status}' => $this->get_status_label($old_status, 'arsol-project'),
            '{new_status}' => $this->get_status_label($new_status, 'arsol-project'),
            '{site_name}' => get_bloginfo('name')
        );
        
        if ($this->is_enabled()) {
            // Send to customer
            $this->send_customer_email($email_type, $customer_id);
            
            // Send to project lead
            if ($project_lead) {
                $this->send_project_lead_email($email_type);
            }
            
            // Send to admins
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
            'in-progress' => 'processing',
            'on-hold' => 'error',
            'completed' => 'success',
            'cancelled' => 'error'
        );
        
        return isset($types[$status]) ? $types[$status] : 'update';
    }
    
    /**
     * Send email to customer
     * 
     * @param string $email_type
     * @param int $customer_id
     */
    private function send_customer_email($email_type, $customer_id) {
        $customer = get_user_by('id', $customer_id);
        $this->recipient = $customer->user_email;
        
        // Customize subject and heading based on status
        switch ($this->new_status) {
            case 'in-progress':
                $this->heading = '🔧 Your Project Has Started';
                $this->subject = 'Great News! Your Project Has Started - {project_title}';
                break;
                
            case 'on-hold':
                $this->heading = 'Project Update Required';
                $this->subject = 'Project Update: {project_title} - Action May Be Required';
                break;
                
            case 'completed':
                $this->heading = '🎉 Your Project is Complete!';
                $this->subject = 'Congratulations! Your Project is Complete - {project_title}';
                break;
                
            case 'cancelled':
                $this->heading = 'Project Status Update';
                $this->subject = 'Project Update: {project_title} - Status Changed';
                break;
                
            default:
                $this->heading = 'Project Status Update';
                $this->subject = 'Project Update: {project_title} - {new_status}';
        }
        
        $this->subject = $this->replace_placeholders($this->subject, $this->find_replace);
        
        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content($email_type, $customer_id),
            $this->get_headers(),
            $this->get_attachments()
        );
    }
    
    /**
     * Send email to project lead
     * 
     * @param string $email_type
     */
    private function send_project_lead_email($email_type) {
        $project_lead_id = get_post_meta($this->project_id, '_arsol_pfw_project_lead_id', true);
        $project_lead = get_user_by('id', $project_lead_id);
        
        $this->recipient = $project_lead->user_email;
        $this->heading = 'Project Status Update';
        $this->subject = $this->replace_placeholders(
            'Project Status: {new_status} - {project_title}',
            $this->find_replace
        );
        
        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_project_lead_content($email_type),
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
            $this->heading = 'Project Status Changed';
            $this->subject = $this->replace_placeholders(
                'Project Status: {new_status} - {project_title} (Lead: {lead_name})',
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
     * @param int $customer_id
     * @return string
     */
    public function get_content($email_type = 'update', $customer_id = null) {
        return wc_get_template_html(
            $this->template_html,
            array(
                'project' => $this->object,
                'customer' => get_user_by('id', $customer_id ?: get_post_meta($this->project_id, '_arsol_pfw_project_customer_id', true)),
                'project_lead' => get_user_by('id', get_post_meta($this->project_id, '_arsol_pfw_project_lead_id', true)),
                'old_status' => $this->old_status,
                'new_status' => $this->new_status,
                'status_label' => $this->get_status_label($this->new_status, 'arsol-project'),
                'portal_url' => $this->get_portal_url('project-view-project', $this->project_id),
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
     * Get email content for project leads
     * 
     * @param string $email_type
     * @return string
     */
    public function get_project_lead_content($email_type = 'update') {
        return wc_get_template_html(
            'emails/lead-project-status.php',
            array(
                'project' => $this->object,
                'customer' => get_user_by('id', get_post_meta($this->project_id, '_arsol_pfw_project_customer_id', true)),
                'project_lead' => get_user_by('id', get_post_meta($this->project_id, '_arsol_pfw_project_lead_id', true)),
                'old_status' => $this->old_status,
                'new_status' => $this->new_status,
                'status_label' => $this->get_status_label($this->new_status, 'arsol-project'),
                'admin_url' => $this->get_admin_url($this->project_id),
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
            'emails/admin-project-status.php',
            array(
                'project' => $this->object,
                'customer' => get_user_by('id', get_post_meta($this->project_id, '_arsol_pfw_project_customer_id', true)),
                'project_lead' => get_user_by('id', get_post_meta($this->project_id, '_arsol_pfw_project_lead_id', true)),
                'old_status' => $this->old_status,
                'new_status' => $this->new_status,
                'status_label' => $this->get_status_label($this->new_status, 'arsol-project'),
                'admin_url' => $this->get_admin_url($this->project_id),
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
