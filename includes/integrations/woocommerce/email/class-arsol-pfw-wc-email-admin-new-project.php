<?php
/**
 * Shop Manager New Project Email
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shop Manager New Project Email Class
 * Sent to shop managers when a new project is created on the frontend
 */
class WC_Email_Admin_New_Project extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'admin_new_project';
        $this->title          = __( 'New Customer Project', 'arsol-pfw' );
        $this->description    = __( 'Shop manager notification when a new project is created on the frontend.', 'arsol-pfw' );
                    $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/integrations/woocommerce/email/templates/';
        $this->template_html  = 'email-admin-new-project.php';
        $this->placeholders   = array(
            '{project_id}' => '',
            '{customer_id}' => '',
        );

        // Listen to project creation hook
        add_action( 'arsol_project_created', array( $this, 'trigger' ), 10, 2 );

        // Call parent constructor
        parent::__construct();

        // Target shop managers
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[Shop Manager] New Project Created #{project_id}', 'arsol-pfw' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'New Project Created', 'arsol-pfw' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $project_id Project ID.
     * @param int $customer_id Customer ID.
     */
    public function trigger( $project_id, $customer_id ) {
        $this->setup_locale();

        if ( $project_id ) {
            $this->object = get_post( $project_id );
            $this->placeholders['{project_id}'] = $project_id;
            $this->placeholders['{customer_id}'] = $customer_id;
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
                'project_id'    => $this->placeholders['{project_id}'],
                'customer_id'   => $this->placeholders['{customer_id}'],
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
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
            'recipient' => array(
                'title'       => __( 'Recipient(s)', 'arsol-pfw' ),
                'type'        => 'text',
                'description' => sprintf( __( 'Enter shop manager emails (comma separated). Defaults to %s.', 'arsol-pfw' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject' => array(
                'title'       => __( 'Subject', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{project_id}, {customer_id}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __( 'Email heading', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{project_id}, {customer_id}</code>' ),
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
