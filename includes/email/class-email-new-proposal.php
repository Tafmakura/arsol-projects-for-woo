<?php

namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * New Proposal Email Class
 * Sent when a new proposal is created internally (Admin + Project Lead only)
 */
class New_Proposal_Email extends Base_Email {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_new_proposal';
        $this->title = 'New Project Proposal Created';
        $this->description = 'Email sent when a new proposal is created internally';
        
        $this->customer_email = false; // Internal only
        $this->admin_email = false;
        $this->shop_manager_email = false;
        $this->project_lead_email = true;
        
        $this->heading = 'New Project Proposal Created';
        $this->subject = 'New Proposal: {proposal_title} - #{proposal_id}';
        
        // Email templates
        $this->template_html = 'templates/email-new-proposal.php';
        
        // Triggers
        add_action('arsol_new_proposal_created', array($this, 'trigger'), 10, 3);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     */
    public function trigger($proposal_id, $customer_id, $project_lead_id = null) {
        $this->setup_locale();
        
        if (!$proposal_id || !$customer_id) {
            return;
        }
        
        $this->proposal_id = $proposal_id;
        $this->customer_id = $customer_id;
        $this->project_lead_id = $project_lead_id;
        
        $proposal = get_post($proposal_id);
        $customer = get_user_by('id', $customer_id);
        
        if (!$proposal || !$customer) {
            return;
        }
        
        $this->object = $proposal;
        $this->recipient = '';
        
        if ($this->is_enabled()) {
            $this->send($this->recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        }
        
        $this->restore_locale();
    }
    
    /**
     * Get email content
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
            'color_scheme' => $this->get_color_scheme('success'),
            'status_icon' => $this->get_status_icon('success')
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
     * Admin options for WooCommerce email settings
     */
    public function admin_options() {
        // Set up preview data for admin preview
        $this->setup_preview_data();
        
        // Call parent admin_options
        parent::admin_options();
    }
    
    public function init_form_fields() {
        parent::init_form_fields();
    }
}
