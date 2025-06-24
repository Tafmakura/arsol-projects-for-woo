<?php

namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Completion Email Class
 * Sent when a project is marked as completed
 */
class Project_Completion_Email extends Base_Email {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_project_completion';
        $this->title = 'Project Completion Notification';
        $this->description = 'Email sent when a project is completed';
        
        $this->customer_email = true;
        $this->admin_email = false;
        $this->shop_manager_email = false;
        $this->project_lead_email = false;
        
        $this->heading = 'Project Completed Successfully!';
        $this->subject = '🎉 Project Complete: {project_title} - #{project_id}';
        
        // Email templates
        $this->template_html = 'emails/project-completion.php';
        $this->template_plain = 'emails/plain/project-completion.php';
        
        // Triggers
        add_action('arsol_project_completed', array($this, 'trigger'), 10, 2);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     */
    public function trigger($project_id, $customer_id) {
        $this->setup_locale();
        
        if (!$project_id || !$customer_id) {
            return;
        }
        
        $this->project_id = $project_id;
        $this->customer_id = $customer_id;
        
        $project = get_post($project_id);
        $customer = get_user_by('id', $customer_id);
        
        if (!$project || !$customer) {
            return;
        }
        
        $this->object = $project;
        
        // Replace placeholders
        $this->find_replace = array(
            '{project_id}' => $project_id,
            '{project_title}' => $project->post_title,
            '{customer_name}' => $this->format_user_name($customer),
            '{completion_date}' => current_time('F j, Y'),
            '{site_name}' => get_bloginfo('name')
        );
        
        if ($this->is_enabled()) {
            // Send to customer
            $this->send_customer_email();
            
            // Send to project lead
            $project_lead_id = get_post_meta($project_id, '_arsol_pfw_project_lead_id', true);
            if ($project_lead_id) {
                $this->send_project_lead_email($project_lead_id);
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
        $this->heading = '🎉 Your Project is Complete!';
        $this->subject = $this->replace_placeholders(
            'Congratulations! Your Project is Complete - {project_title}',
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
    private function send_project_lead_email($project_lead_id) {
        $project_lead = get_user_by('id', $project_lead_id);
        $this->recipient = $project_lead->user_email;
        $this->heading = 'Project Delivery Confirmed';
        $this->subject = $this->replace_placeholders(
            'Project Delivered: {project_title} - #{project_id}',
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
            $this->heading = 'Project Completed';
            $this->subject = $this->replace_placeholders(
                'Project Completed: {project_title} - #{project_id}',
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
    
    public function get_content() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'project' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'portal_url' => $this->get_portal_url('project-overview', $this->project_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('success'),
                'status_icon' => $this->get_status_icon('success')
            ),
            '',
            $this->template_base
        );
    }
    
    public function get_project_lead_content() {
        return wc_get_template_html(
            'emails/lead-project-completion.php',
            array(
                'project' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'admin_url' => $this->get_admin_url($this->project_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('success'),
                'status_icon' => $this->get_status_icon('success')
            ),
            '',
            $this->template_base
        );
    }
    
    public function get_admin_content() {
        return wc_get_template_html(
            'emails/admin-project-completion.php',
            array(
                'project' => $this->object,
                'customer' => get_user_by('id', $this->customer_id),
                'admin_url' => $this->get_admin_url($this->project_id),
                'email_heading' => $this->get_heading(),
                'email' => $this,
                'color_scheme' => $this->get_color_scheme('success'),
                'status_icon' => $this->get_status_icon('success')
            ),
            '',
            $this->template_base
        );
    }
    
    public function init_form_fields() {
        parent::init_form_fields();
    }
}
