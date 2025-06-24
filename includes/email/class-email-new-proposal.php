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
        $this->admin_email = true;
        $this->project_lead_email = true;
        
        $this->heading = 'New Project Proposal Created';
        $this->subject = 'New Proposal: {proposal_title} - #{proposal_id}';
        
        // Email templates
        $this->template_html = 'emails/new-proposal.php';
        $this->template_plain = 'emails/plain/new-proposal.php';
        
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
        return 'New proposal created.';
    }
    
    public function init_form_fields() {
        parent::init_form_fields();
    }
}
