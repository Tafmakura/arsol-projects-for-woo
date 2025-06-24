<?php
/**
 * Project Lead Proposal Processing Email
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Project Lead Proposal Processing Email Class
 * Sent to project leads when proposal processing starts
 */
class WC_Email_Proposal_Processing extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'proposal_processing';
        $this->title          = __( 'Project Lead: Proposal Processing', 'arsol-pfw' );
        $this->description    = __( 'Project lead notification when proposal processing starts.', 'arsol-pfw' );
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';
        $this->template_html  = 'email-proposal-processing.php';
        $this->placeholders   = array(
            '{proposal_id}' => '',
            '{customer_id}' => '',
            '{project_lead_id}' => '',
        );

        // Listen to main workflow hook
        add_action( 'arsol_proposal_processing_started', array( $this, 'trigger' ), 10, 3 );

        // Call parent constructor
        parent::__construct();

        // Default to admin email, but should be set to project lead
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[Project Lead] Proposal Processing Started #{proposal_id}', 'arsol-pfw' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Proposal Processing Assignment', 'arsol-pfw' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $proposal_id Proposal ID.
     * @param int $customer_id Customer ID.
     * @param int $project_lead_id Project Lead ID.
     */
    public function trigger( $proposal_id, $customer_id, $project_lead_id ) {
        $this->setup_locale();

        if ( $proposal_id && $project_lead_id ) {
            $this->object = get_post( $proposal_id );
            $this->placeholders['{proposal_id}'] = $proposal_id;
            $this->placeholders['{customer_id}'] = $customer_id;
            $this->placeholders['{project_lead_id}'] = $project_lead_id;
            
            // Get project lead email
            $project_lead = get_user_by( 'id', $project_lead_id );
            if ( $project_lead ) {
                $this->recipient = $project_lead->user_email;
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
                'proposal_id'     => $this->placeholders['{proposal_id}'],
                'customer_id'     => $this->placeholders['{customer_id}'],
                'project_lead_id' => $this->placeholders['{project_lead_id}'],
                'email_heading'   => $this->get_heading(),
                'sent_to_admin'   => false,
                'plain_text'      => false,
                'email'           => $this,
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
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{proposal_id}, {customer_id}, {project_lead_id}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __( 'Email heading', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{proposal_id}, {customer_id}, {project_lead_id}</code>' ),
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
