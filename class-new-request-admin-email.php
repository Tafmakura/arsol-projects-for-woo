<?php
/**
 * New Request Admin Email
 *
 * Sent to shop managers when a new project request is received.
 *
 * @package Arsol_Projects_For_Woo\Emails
 * @version 1.0.0
 */

namespace Arsol_Projects_For_Woo\Emails;

defined('ABSPATH') || exit;

/**
 * New Request Admin Email Class
 */
class New_Request_Admin_Email extends \WC_Email {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id             = 'arsol_new_request_admin';
        $this->title          = 'New Project Request (Admin)';
        $this->description    = 'Sent to shop managers when a new project request is received.';
        $this->template_html  = 'emails/admin-new-request.php';
        $this->template_plain = 'emails/plain/admin-new-request.php';
        $this->template_base  = trailingslashit(plugin_dir_path(__FILE__)) . 'templates/';
        
        // Triggers for this email
        add_action('arsol_new_request_admin_notification', array($this, 'trigger'), 10, 1);
        
        // Call parent constructor
        parent::__construct();
        
        // Other settings
        $this->recipient = $this->get_option('recipient', get_option('admin_email'));
    }

    /**
     * Get email subject
     */
    public function get_default_subject() {
        return 'New Project Request: {request_title}';
    }

    /**
     * Get email heading
     */
    public function get_default_heading() {
        return 'New Project Request Received';
    }

    /**
     * Trigger the sending of this email
     *
     * @param int $request_id Request ID
     */
    public function trigger($request_id) {
        $this->setup_locale();
        
        if ($request_id && !$this->is_enabled()) {
            $this->restore_locale();
            return;
        }
        
        $this->object = get_post($request_id);
        
        if (!$this->object) {
            $this->restore_locale();
            return;
        }
        
        $this->placeholders = array(
            '{request_title}' => $this->object->post_title,
            '{request_id}'    => $request_id,
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
                'email_heading' => $this->get_heading(),
                'email'         => $this,
                'sent_to_admin' => true,
                'plain_text'    => false,
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
                'email_heading' => $this->get_heading(),
                'email'         => $this,
                'sent_to_admin' => true,
                'plain_text'    => true,
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
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable this email notification',
                'default' => 'yes',
            ),
            'recipient' => array(
                'title'       => 'Recipient(s)',
                'type'        => 'text',
                'description' => 'Enter recipients (comma separated) for this email. Defaults to admin email.',
                'placeholder' => get_option('admin_email'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject' => array(
                'title'       => 'Subject',
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => 'Available placeholders: {request_title}, {request_id}, {site_title}',
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => 'Email heading',
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => 'Available placeholders: {request_title}, {request_id}, {site_title}',
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'email_type' => array(
                'title'       => 'Email type',
                'type'        => 'select',
                'description' => 'Choose which format of email to send.',
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
} 