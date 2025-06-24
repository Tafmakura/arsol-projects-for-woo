<?php
/**
 * Customer Project Creation Email
 *
 * Email sent to customers when their project order is ready.
 *
 * @package Arsol_Projects_For_Woo\Emails
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Customer Project Creation Email Class
 */
class Arsol_Email_Customer_Project_Creation extends WC_Email {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id             = 'arsol_customer_project_creation';
        $this->title          = __('Your Project Order Is Ready (Customer)', 'arsol-projects-for-woo');
        $this->description    = __('Email sent to customers when their project order is ready for purchase.', 'arsol-projects-for-woo');
        $this->template_html  = 'email-project-creation.php';
        $this->template_plain = 'plain/email-project-creation.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';
        
        $this->customer_email = true;
        
        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get email subject
     */
    public function get_default_subject() {
        return __('Your Project Order Is Ready: {project_title}', 'arsol-projects-for-woo');
    }

    /**
     * Get email heading
     */
    public function get_default_heading() {
        return __('Your Project Order Is Ready', 'arsol-projects-for-woo');
    }

    /**
     * Trigger the sending of this email
     *
     * @param int $project_id Project ID
     * @param int $order_id Order ID
     * @param int $customer_id Customer ID
     */
    public function trigger($project_id, $order_id = null, $customer_id = null) {
        $this->setup_locale();
        
        if ($project_id && !is_a($project_id, 'WP_Post')) {
            $this->object = get_post($project_id);
        }
        
        if (!$this->is_enabled() || !$this->object) {
            $this->restore_locale();
            return;
        }
        
        // Get customer
        if (!$customer_id) {
            $customer_id = get_post_meta($this->object->ID, '_arsol_pfw_project_customer_id', true);
        }
        
        $customer = get_user_by('id', $customer_id);
        if (!$customer) {
            $this->restore_locale();
            return;
        }
        
        $this->recipient = $customer->user_email;
        
        // Get order if provided
        $order = null;
        if ($order_id) {
            $order = wc_get_order($order_id);
        }
        
        // Set placeholders
        $this->placeholders = array(
            '{project_title}'  => $this->object->post_title,
            '{project_id}'     => $this->object->ID,
            '{customer_name}'  => $customer->display_name,
            '{order_number}'   => $order ? $order->get_order_number() : '',
            '{site_title}'     => $this->get_blogname(),
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

    /**
     * Get content html
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'project'       => $this->object,
                'customer'      => $this->get_customer(),
                'order'         => $this->get_order(),
                'email_heading' => $this->get_heading(),
                'portal_url'    => $this->get_portal_url(),
                'email'         => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Get content plain
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'project'       => $this->object,
                'customer'      => $this->get_customer(),
                'order'         => $this->get_order(),
                'email_heading' => $this->get_heading(),
                'portal_url'    => $this->get_portal_url(),
                'email'         => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Initialize settings form fields
     */
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
                'description' => sprintf(__('Available placeholders: %s', 'arsol-projects-for-woo'), '<code>{project_title}, {project_id}, {customer_name}, {order_number}, {site_title}</code>'),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __('Email heading', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'arsol-projects-for-woo'), '<code>{project_title}, {project_id}, {customer_name}, {order_number}, {site_title}</code>'),
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

    /**
     * Get customer object
     */
    private function get_customer() {
        if (!$this->object) {
            return null;
        }
        
        $customer_id = get_post_meta($this->object->ID, '_arsol_pfw_project_customer_id', true);
        return $customer_id ? get_user_by('id', $customer_id) : null;
    }

    /**
     * Get order object
     */
    private function get_order() {
        if (!$this->object) {
            return null;
        }
        
        $order_id = get_post_meta($this->object->ID, '_arsol_pfw_project_order_id', true);
        return $order_id ? wc_get_order($order_id) : null;
    }

    /**
     * Get customer portal URL for the project
     */
    private function get_portal_url() {
        if (!$this->object) {
            return '';
        }
        
        return home_url('/my-account/project-view-project/' . $this->object->ID);
    }
} 