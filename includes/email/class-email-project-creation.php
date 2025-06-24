<?php
namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Creation Email
 * 
 * Email sent when project is created (order is ready)
 */
class Project_Creation_Email extends Base_Email {
    
    /**
     * Project ID
     * @var int
     */
    public $project_id;
    
    /**
     * Order ID
     * @var int
     */
    public $order_id;
    
    /**
     * Customer ID
     * @var int
     */
    public $customer_id;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_project_creation';
        $this->title = 'Your Project Order Is Ready';
        $this->description = 'Email sent when project is created and order is ready';
        
        $this->customer_email = true;
        $this->admin_email = false;
        $this->shop_manager_email = false;
        $this->project_lead_email = false;
        
        $this->heading = 'Your Order Is Ready!';
        $this->subject = 'Your Order Is Ready! Complete Your Purchase - #{order_id}';
        
        // Email templates
        $this->template_html = 'templates/email-project-creation.php';
        
        // Triggers
        add_action('arsol_project_created', array($this, 'trigger'), 10, 3);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     * 
     * @param int $project_id
     * @param int $order_id
     * @param int $customer_id
     */
    public function trigger($project_id, $order_id, $customer_id) {
        $this->setup_locale();
        
        if (!$project_id || !$order_id || !$customer_id) {
            return;
        }
        
        $this->project_id = $project_id;
        $this->order_id = $order_id;
        $this->customer_id = $customer_id;
        
        $project = get_post($project_id);
        $order = wc_get_order($order_id);
        $customer = get_user_by('id', $customer_id);
        
        if (!$project || !$order || !$customer) {
            return;
        }
        
        $this->object = $project;
        
        // Get project lead
        $project_lead_id = get_post_meta($project_id, '_arsol_pfw_project_lead_id', true);
        $project_lead = $project_lead_id ? get_user_by('id', $project_lead_id) : null;
        
        // Replace placeholders
        $this->find_replace = array(
            '{project_id}' => $project_id,
            '{order_id}' => $order_id,
            '{project_title}' => $project->post_title,
            '{customer_name}' => $this->format_user_name($customer),
            '{lead_name}' => $project_lead ? $this->format_user_name($project_lead) : 'Project Team',
            '{order_total}' => $order->get_formatted_order_total(),
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
        $this->heading = '🎉 Your Order Is Ready!';
        $this->subject = $this->replace_placeholders(
            'Your Order Is Ready! Complete Your Purchase - #{order_id}',
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
        $project_lead_id = get_post_meta($this->project_id, '_arsol_pfw_project_lead_id', true);
        $project_lead = get_user_by('id', $project_lead_id);
        
        $this->recipient = $project_lead->user_email;
        $this->heading = 'Project Order Created - Awaiting Payment';
        $this->subject = $this->replace_placeholders(
            'Project Order Created: {project_title} - Awaiting Customer Payment',
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
            $this->heading = 'Project Order Created';
            $this->subject = $this->replace_placeholders(
                'Project Order: {project_title} ({order_total}) - Payment Pending',
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
        $order = wc_get_order($this->order_id);
        
        return wc_get_template_html(
            $this->template_html,
            array(
                'project' => $this->object,
                'order' => $order,
                'customer' => get_user_by('id', $this->customer_id),
                'project_lead' => get_user_by('id', get_post_meta($this->project_id, '_arsol_pfw_project_lead_id', true)),
                'checkout_url' => $order->get_checkout_payment_url(),
                'portal_url' => $this->get_portal_url('project-view-project', $this->project_id),
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
     * Get email content for project leads
     * 
     * @return string
     */
    public function get_project_lead_content() {
        $order = wc_get_order($this->order_id);
        
        return wc_get_template_html(
            'emails/lead-project-creation.php',
            array(
                'project' => $this->object,
                'order' => $order,
                'customer' => get_user_by('id', $this->customer_id),
                'project_lead' => get_user_by('id', get_post_meta($this->project_id, '_arsol_pfw_project_lead_id', true)),
                'admin_url' => $this->get_admin_url($this->project_id),
                'order_admin_url' => admin_url('post.php?post=' . $this->order_id . '&action=edit'),
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
        $order = wc_get_order($this->order_id);
        
        return wc_get_template_html(
            'emails/admin-project-creation.php',
            array(
                'project' => $this->object,
                'order' => $order,
                'customer' => get_user_by('id', $this->customer_id),
                'project_lead' => get_user_by('id', get_post_meta($this->project_id, '_arsol_pfw_project_lead_id', true)),
                'admin_url' => $this->get_admin_url($this->project_id),
                'order_admin_url' => admin_url('post.php?post=' . $this->order_id . '&action=edit'),
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
     * Get plain text content
     * 
     * @return string
     */
}
