<?php
/**
 * Customer Project Status Email
 */

defined('ABSPATH') || exit;

class Arsol_Email_Customer_Project_Status extends WC_Email {

    public $status;

    public function __construct() {
        $this->id             = 'arsol_customer_project_status';
        $this->title          = __('Project Status Update (Customer)', 'arsol-projects-for-woo');
        $this->description    = __('Email sent to customers when project status updates.', 'arsol-projects-for-woo');
        $this->template_html  = 'email-project-status.php';
        $this->template_plain = 'plain/email-project-status.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';
        
        $this->customer_email = true;
        
        parent::__construct();
    }

    public function get_default_subject() {
        return __('Project Update: {project_title} - {status}', 'arsol-projects-for-woo');
    }

    public function get_default_heading() {
        return __('Project Status Update', 'arsol-projects-for-woo');
    }

    public function trigger($project_id, $status) {
        $this->setup_locale();
        
        if ($project_id && !is_a($project_id, 'WP_Post')) {
            $this->object = get_post($project_id);
        }
        
        if (!$this->is_enabled() || !$this->object) {
            $this->restore_locale();
            return;
        }
        
        $customer_id = get_post_meta($this->object->ID, '_arsol_pfw_project_customer_id', true);
        $customer = get_user_by('id', $customer_id);
        
        if (!$customer) {
            $this->restore_locale();
            return;
        }
        
        $this->recipient = $customer->user_email;
        $this->status = $status;
        
        $this->placeholders = array(
            '{project_title}' => $this->object->post_title,
            '{project_id}'    => $this->object->ID,
            '{status}'        => ucwords(str_replace('_', ' ', $status)),
            '{customer_name}' => $customer->display_name,
            '{site_title}'    => $this->get_blogname(),
        );
        
        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content(),
            $this->get_headers(),
            $this->get_attachments()
        );
        
        $this->restore_locale();
    }

    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'project'       => $this->object,
                'customer'      => $this->get_customer(),
                'project_lead'  => $this->get_project_lead(),
                'status'        => $this->status,
                'email_heading' => $this->get_heading(),
                'portal_url'    => $this->get_portal_url(),
                'email'         => $this,
            ),
            '',
            $this->template_base
        );
    }

    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'project'       => $this->object,
                'customer'      => $this->get_customer(),
                'project_lead'  => $this->get_project_lead(),
                'status'        => $this->status,
                'email_heading' => $this->get_heading(),
                'portal_url'    => $this->get_portal_url(),
                'email'         => $this,
            ),
            '',
            $this->template_base
        );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'arsol-projects-for-woo'),
                'type'    => 'checkbox',
                'label'   => __('Enable this email notification', 'arsol-projects-for-woo'),
                'default' => 'yes',
            ),
            'subject' => array(
                'title'       => __('Subject', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'arsol-projects-for-woo'), '<code>{project_title}, {project_id}, {status}, {customer_name}, {site_title}</code>'),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __('Email heading', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'arsol-projects-for-woo'), '<code>{project_title}, {project_id}, {status}, {customer_name}, {site_title}</code>'),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __('Additional content', 'arsol-projects-for-woo'),
                'description' => __('Text to appear below the main email content.', 'arsol-projects-for-woo'),
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __('N/A', 'arsol-projects-for-woo'),
                'type'        => 'textarea',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'email_type' => array(
                'title'       => __('Email type', 'arsol-projects-for-woo'),
                'type'        => 'select',
                'description' => __('Choose which format of email to send.', 'arsol-projects-for-woo'),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }

    private function get_customer() {
        if (!$this->object) return null;
        $customer_id = get_post_meta($this->object->ID, '_arsol_pfw_project_customer_id', true);
        return $customer_id ? get_user_by('id', $customer_id) : null;
    }

    private function get_project_lead() {
        if (!$this->object) return null;
        $lead_id = get_post_meta($this->object->ID, '_arsol_pfw_project_project_lead_id', true);
        return $lead_id ? get_user_by('id', $lead_id) : null;
    }

    private function get_portal_url() {
        if (!$this->object) return '';
        return home_url('/my-account/project-view-project/' . $this->object->ID);
    }
} 