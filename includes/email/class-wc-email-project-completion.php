<?php
/**
 * Project Completion Email
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Project Completion Email Class
 */
class WC_Email_Project_Completion extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'project_completion';
        $this->title          = __( 'Project Completion', 'arsol-pfw' );
        $this->description    = __( 'Notification when a project is completed.', 'arsol-pfw' );
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';
        $this->template_html  = 'email-project-completion.php';
        $this->template_plain = 'email-project-completion.php';
        $this->placeholders   = array(
            '{project_id}' => '',
        );

        // Triggers for this email
        add_action( 'arsol_project_completion_notification', array( $this, 'trigger' ), 10, 1 );

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
        return __( 'Project Completed #{project_id}', 'arsol-pfw' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Project Completion', 'arsol-pfw' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $project_id Project ID.
     */
    public function trigger( $project_id ) {
        $this->setup_locale();

        if ( $project_id ) {
            $this->object                    = get_post( $project_id );
            $this->placeholders['{project_id}'] = $project_id;
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
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'project_id'    => $this->object ? $this->object->ID : '',
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => true,
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
                'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'arsol-pfw' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
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