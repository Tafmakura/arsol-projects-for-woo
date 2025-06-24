<?php
/**
 * Admin New Request Email
 *
 * Email sent to shop managers when a new project request is submitted.
 *
 * @package Arsol_Projects_For_Woo\Emails
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Admin New Request Email Class
 */
class Arsol_Email_Admin_New_Request extends WC_Email {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id             = 'arsol_admin_new_request';
        $this->title          = __('New Project Request (Admin)', 'arsol-projects-for-woo');
        $this->description    = __('Notification sent to shop managers when a new project request is submitted.', 'arsol-projects-for-woo');
        $this->template_html  = 'email-admin-new-request.php';
        $this->template_plain = 'plain/email-admin-new-request.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';
        
        // Recipients - shop managers
        $this->recipient = $this->get_option('recipient', get_option('admin_email'));
        
        // Call parent constructor
        parent::__construct();
    }

    /**
     * Get email subject
     */
    public function get_default_subject() {
        return __('New Project Request: {request_title}', 'arsol-projects-for-woo');
    }

    /**
     * Get email heading
     */
    public function get_default_heading() {
        return __('New Project Request Received', 'arsol-projects-for-woo');
    }

    /**
     * Trigger the sending of this email
     *
     * @param int $request_id Request ID
     * @param int $customer_id Customer ID
     */
    public function trigger($request_id, $customer_id = null) {
        $this->setup_locale();
        
        if ($request_id && !is_a($request_id, 'WP_Post')) {
            $this->object = get_post($request_id);
        }
        
        if (!$this->is_enabled() || !$this->get_recipient() || !$this->object) {
            $this->restore_locale();
            return;
        }
        
        // Set placeholders
        $this->placeholders = array(
            '{request_title}' => $this->object->post_title,
            '{request_id}'    => $this->object->ID,
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

    /**
     * Get content html
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'request'       => $this->object,
                'customer'      => $this->get_customer(),
                'email_heading' => $this->get_heading(),
                'admin_url'     => $this->get_admin_url(),
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
                'request'       => $this->object,
                'customer'      => $this->get_customer(),
                'email_heading' => $this->get_heading(),
                'admin_url'     => $this->get_admin_url(),
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
            'recipient' => array(
                'title'       => __('Recipient(s)', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'description' => sprintf(__('Enter recipients (comma separated) for this email. Defaults to %s.', 'arsol-projects-for-woo'), '<code>' . esc_attr(get_option('admin_email')) . '</code>'),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject' => array(
                'title'       => __('Subject', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'arsol-projects-for-woo'), '<code>{request_title}, {request_id}, {site_title}</code>'),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __('Email heading', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'arsol-projects-for-woo'), '<code>{request_title}, {request_id}, {site_title}</code>'),
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
        
        $customer_id = get_post_meta($this->object->ID, '_arsol_pfw_request_customer_id', true);
        return $customer_id ? get_user_by('id', $customer_id) : null;
    }

    /**
     * Get admin URL for the request
     */
    private function get_admin_url() {
        if (!$this->object) {
            return '';
        }
        
        return admin_url('post.php?post=' . $this->object->ID . '&action=edit');
    }
} 