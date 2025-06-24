<?php
/**
 * Admin New Request Email
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

/**
 * Admin New Request Email Class
 */
class WC_Email_Admin_New_Request extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'admin_new_request';
        $this->title          = __( 'New Project Request (Admin)', 'arsol-projects-for-woo' );
        $this->description    = __( 'Admin new request emails are sent to shop managers when a new request is submitted.', 'arsol-projects-for-woo' );
        $this->template_html  = 'emails/email-admin-new-request.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';

        // Triggers for this email
        add_action( 'arsol_new_request_admin_notification', array( $this, 'trigger' ), 10, 2 );

        // Call parent constructor
        parent::__construct();

        // Other settings
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_title}] New Project Request: {request_title}', 'arsol-projects-for-woo' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'New Project Request Received', 'arsol-projects-for-woo' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $request_id Request ID.
     * @param int $customer_id Customer ID.
     */
    public function trigger( $request_id, $customer_id ) {
        $this->setup_locale();

        if ( $request_id ) {
            $this->object = get_post( $request_id );
            
            if ( $this->object ) {
                $this->placeholders['{request_id}'] = $request_id;
                $this->placeholders['{request_title}'] = $this->object->post_title;
                $this->placeholders['{site_title}'] = $this->get_blogname();
                
                // Add customer info if available
                if ( $customer_id ) {
                    $customer = get_user_by( 'id', $customer_id );
                    if ( $customer ) {
                        $this->placeholders['{customer_name}'] = $customer->display_name;
                        $this->placeholders['{customer_email}'] = $customer->user_email;
                    }
                }
            }
        }

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

    /**
     * Get content html.
     *
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'request'        => $this->object,
                'customer_name'  => $this->placeholders['{customer_name}'] ?? '',
                'customer_email' => $this->placeholders['{customer_email}'] ?? '',
                'email_heading'  => $this->get_heading(),
                'sent_to_admin'  => true,
                'plain_text'     => false,
                'email'          => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Initialize settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __( 'Enable/Disable', 'arsol-projects-for-woo' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'arsol-projects-for-woo' ),
                'default' => 'yes',
            ),
            'recipient'  => array(
                'title'       => __( 'Recipient(s)', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'arsol-projects-for-woo' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject'    => array(
                'title'       => __( 'Subject', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {request_id}, {request_title}, {customer_name}, {customer_email}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {request_id}, {request_title}, {customer_name}, {customer_email}</code>' ),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'email_type' => array(
                'title'       => __( 'Email type', 'arsol-projects-for-woo' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'arsol-projects-for-woo' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
} 