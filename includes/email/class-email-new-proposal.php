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
        $this->template_html = 'emails/new-proposal.php';
        
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
    
    public function get_content() {
        $proposal = $this->object;
        $customer = get_user_by('id', $this->customer_id);
        $project_lead_id = get_post_meta($this->proposal_id, '_arsol_pfw_proposal_project_lead_id', true);
        $project_lead = $project_lead_id ? get_user_by('id', $project_lead_id) : null;

        $content = '<h2>' . sprintf(__('New Project Proposal #%d', 'arsol-pfw'), $this->proposal_id) . '</h2>';
        $content .= '<p>' . sprintf(__('A new proposal has been created for project: %s', 'arsol-pfw'), '<strong>' . esc_html($proposal->post_title) . '</strong>') . '</p>';
        
        $content .= '<h3>' . __('Details', 'arsol-pfw') . '</h3>';
        $content .= '<ul>';
        $content .= '<li><strong>' . __('Customer:', 'arsol-pfw') . '</strong> ' . esc_html($customer->display_name) . ' (' . esc_html($customer->user_email) . ')</li>';
        $content .= '<li><strong>' . __('Created:', 'arsol-pfw') . '</strong> ' . esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($proposal->post_date))) . '</li>';
        
        if ($project_lead) {
            $content .= '<li><strong>' . __('Assigned to:', 'arsol-pfw') . '</strong> ' . esc_html($project_lead->display_name) . '</li>';
        }
        $content .= '</ul>';
        
        $content .= '<h3>' . __('Next Steps', 'arsol-pfw') . '</h3>';
        $content .= '<ul>';
        $content .= '<li>' . __('Complete the proposal details', 'arsol-pfw') . '</li>';
        $content .= '<li>' . __('Add pricing and timeline', 'arsol-pfw') . '</li>';
        $content .= '<li>' . __('Send to customer for review', 'arsol-pfw') . '</li>';
        $content .= '</ul>';
        
        return $content;
    }
    
    public function init_form_fields() {
        parent::init_form_fields();
    }
}
