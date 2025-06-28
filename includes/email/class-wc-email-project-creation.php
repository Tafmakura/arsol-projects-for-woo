<?php
/**
 * Project Creation Email
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
 * Project Creation Email Class
 */
class WC_Email_Project_Creation extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'project_creation';
        $this->title          = __( 'Project Customer: Your Project Order Is Ready', 'arsol-pfw' );
        $this->description    = __( 'Customer notification when their project order is created and ready.', 'arsol-pfw' );
        $this->template_html  = 'email-project-creation.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';

        // Triggers for this email
        add_action( 'arsol_project_created', array( $this, 'trigger' ), 10, 2 );

        // Call parent constructor
        parent::__construct();

        // This email is sent to the customer
        $this->customer_email = true;
        $this->recipient = __( 'Customer', 'arsol-pfw' );
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_title}] Your Project Order is Ready - #{project_id}', 'arsol-pfw' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Your Project Order is Ready', 'arsol-pfw' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $project_id Project ID.
     * @param int $customer_id Customer ID.
     */
    public function trigger( $project_id, $customer_id ) {
        $this->setup_locale();

        if ( $project_id && $customer_id ) {
            $this->object = get_post( $project_id );
            
            if ( $this->object ) {
                $this->placeholders['{project_id}'] = $project_id;
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
                'project'       => $this->object,
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
                'title'   => __( 'Enable/Disable', 'arsol-pfw' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'arsol-pfw' ),
                'default' => 'yes',
            ),
            'subject'    => array(
                'title'       => __( 'Subject', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{site_title}, {project_id}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{site_title}, {project_id}</code>' ),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'email_type' => array(
                'title'       => __( 'Email type', 'arsol-pfw' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'arsol-pfw' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
}
