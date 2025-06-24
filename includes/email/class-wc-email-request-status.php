<?php
/**
 * Request Status Email
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
 * Request Status Email Class
 */
class WC_Email_Request_Status extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'request_status';
        $this->title          = __( 'Customer Notification: Request Status Update', 'arsol-projects-for-woo' );
        $this->description    = __( 'Customer notification when their request status changes.', 'arsol-projects-for-woo' );
        $this->template_html  = 'email-request-status.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';

        // Triggers for this email
        add_action( 'arsol_request_status_changed', array( $this, 'trigger' ), 10, 4 );

        // Call parent constructor
        parent::__construct();

        // This email is sent to the customer who owns the request
        $this->customer_email = true;
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_title}] Request Status Update - #{request_id}', 'arsol-projects-for-woo' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Request Status Update', 'arsol-projects-for-woo' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int    $request_id Request ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     * @param int    $customer_id Customer ID.
     */
    public function trigger( $request_id, $old_status, $new_status, $customer_id ) {
        $this->setup_locale();

        if ( $request_id && $customer_id ) {
            $this->object = get_post( $request_id );
            
            if ( $this->object ) {
                $this->placeholders['{request_id}'] = $request_id;
                $this->placeholders['{old_status}'] = $old_status;
                $this->placeholders['{new_status}'] = $new_status;
                $this->placeholders['{site_title}'] = $this->get_blogname();
                
                // Get customer email
                $customer = get_user_by( 'id', $customer_id );
                if ( $customer ) {
                    $this->recipient = $customer->user_email;
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
                'request'       => $this->object,
                'old_status'    => $this->placeholders['{old_status}'] ?? '',
                'new_status'    => $this->placeholders['{new_status}'] ?? '',
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => false,
                'email'         => $this,
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
            'subject'    => array(
                'title'       => __( 'Subject', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {request_id}, {old_status}, {new_status}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {request_id}, {old_status}, {new_status}</code>' ),
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