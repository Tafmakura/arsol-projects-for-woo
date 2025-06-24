<?php

namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Billing Notification Email Class
 * Sent when billing events occur (payment, subscription, etc.)
 */
class Billing_Notification_Email extends Base_Email {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'arsol_billing_notification';
        $this->title = 'Project Billing Notification';
        $this->description = 'Email sent for project billing events';
        
        $this->customer_email = true;
        $this->admin_email = true;
        
        $this->heading = 'Billing Update';
        $this->subject = 'Billing Update: {project_title} - #{project_id}';
        
        // Email templates
        $this->template_html = 'emails/billing-notification.php';
        $this->template_plain = 'emails/plain/billing-notification.php';
        
        // Triggers
        add_action('arsol_project_order_created', array($this, 'trigger'), 10, 2);
        add_action('woocommerce_payment_complete', array($this, 'trigger_payment'), 10, 1);
        
        parent::__construct();
    }
    
    /**
     * Trigger the email
     */
    public function trigger($project_id, $order_id) {
        $this->setup_locale();
        
        if (!$project_id || !$order_id) {
            return;
        }
        
        $this->project_id = $project_id;
        $this->order_id = $order_id;
        
        $project = get_post($project_id);
        $order = wc_get_order($order_id);
        
        if (!$project || !$order) {
            return;
        }
        
        $this->object = $project;
        $this->recipient = $order->get_billing_email();
        
        if ($this->is_enabled() && $this->get_recipient()) {
            $this->send($this->recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        }
        
        $this->restore_locale();
    }
    
    /**
     * Trigger for payment events
     */
    public function trigger_payment($order_id) {
        $order = wc_get_order($order_id);
        $project_id = $order->get_meta('_arsol_pfw_project_id');
        
        if ($project_id) {
            $this->trigger($project_id, $order_id);
        }
    }
    
    public function get_content() {
        return 'Billing notification content.';
    }
    
    public function init_form_fields() {
        parent::init_form_fields();
    }
}
