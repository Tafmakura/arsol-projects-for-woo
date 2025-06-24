<?php

namespace Arsol_Projects_For_Woo\Emails;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Setup Class
 * Handles instantiation and registration of all email classes
 */
class Email_Setup {
    
    /**
     * All available email classes
     * 
     * @var array
     */
    private $email_classes = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook into WooCommerce email system
        add_filter('woocommerce_email_classes', array($this, 'register_email_classes'));
        
        // Define all email classes
        $this->define_email_classes();
    }
    
    /**
     * Define all available email classes
     */
    private function define_email_classes() {
        $this->email_classes = array(
            'New_Request_Email' => 'class-email-new-request.php',
            'Request_Status_Email' => 'class-email-request-status.php',
            'New_Proposal_Email' => 'class-email-new-proposal.php',
            'Proposal_Processing_Email' => 'class-email-proposal-processing.php',
            'Proposal_Ready_Email' => 'class-email-proposal-ready.php',
            'Proposal_Decision_Email' => 'class-email-proposal-decision.php',
            'Project_Creation_Email' => 'class-email-project-creation.php',
            'Project_Status_Email' => 'class-email-project-status.php',
            'Project_Completion_Email' => 'class-email-project-completion.php'
        );
    }
    
    /**
     * Register email classes with WooCommerce
     * 
     * @param array $email_classes
     * @return array
     */
    public function register_email_classes($email_classes) {
        // Include base email class first
        require_once ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/class-email-base.php';
        
        // Include and register all email classes
        foreach ($this->email_classes as $class_name => $file_name) {
            $file_path = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/email/' . $file_name;
            
            if (file_exists($file_path)) {
                require_once $file_path;
                
                $full_class_name = '\\Arsol_Projects_For_Woo\\Emails\\' . $class_name;
                $wc_key = 'Arsol_' . $class_name;
                
                if (class_exists($full_class_name)) {
                    $email_classes[$wc_key] = new $full_class_name();
                }
            }
        }
        
        return $email_classes;
    }
    
    /**
     * Get all defined email classes
     * 
     * @return array
     */
    public function get_email_classes() {
        return $this->email_classes;
    }
}
