<?php
/**
 * Project Status Email
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Project Status Email Class
 */
class WC_Email_Project_Status extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'project_status';
        $this->title          = __( 'Project Customer: Project Status Update', 'arsol-pfw' );
        $this->description    = __( 'Customer notification when their project status changes.', 'arsol-pfw' );
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';
        $this->template_html  = 'email-project-status.php';
        $this->placeholders   = array(
            '{project_id}' => '',
        );

        // Listen to main workflow hook
        add_action( 'arsol_project_status_changed', array( $this, 'trigger' ), 10, 4 );

        // Call parent constructor
        parent::__construct();

        // This email is sent to the customer who owns the project
        $this->customer_email = true;
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( 'Project Status Update #{project_id}', 'arsol-pfw' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Project Status Update', 'arsol-pfw' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int    $project_id Project ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     * @param int    $customer_id Customer ID.
     */
    public function trigger( $project_id, $old_status, $new_status, $customer_id ) {
        $this->setup_locale();

        if ( $project_id && $customer_id ) {
            $this->object = get_post( $project_id );
            $this->placeholders['{project_id}'] = $project_id;
            
            // Get customer email
            $customer = get_user_by( 'id', $customer_id );
            if ( $customer ) {
                $this->recipient = $customer->user_email;
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
                'project_id'    => $this->object ? $this->object->ID : '',
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
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'arsol-pfw' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'arsol-pfw' ),
                'default' => 'yes',
            ),
            'subject' => array(
                'title'       => __( 'Subject', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{project_id}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __( 'Email heading', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{project_id}</code>' ),
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