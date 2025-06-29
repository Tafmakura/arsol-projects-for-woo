<?php
/**
 * Project Lead Proposal Decision Email
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Project Lead Proposal Decision Email Class
 * Sent to project leads when a proposal decision is made
 */
class WC_Email_Proposal_Decision extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'proposal_decision';
        $this->title          = __( 'Project Lead: Proposal Decision', 'arsol-pfw' );
        $this->description    = __( 'Project lead notification when a proposal decision is made.', 'arsol-pfw' );
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';
        $this->template_html  = 'email-proposal-decision.php';
        $this->placeholders   = array(
            '{proposal_id}' => '',
            '{old_status}' => '',
            '{new_status}' => '',
        );

        // Listen to main workflow hook
        add_action( 'arsol_proposal_stage_changed', array( $this, 'trigger' ), 10, 3 );

        // Call parent constructor
        parent::__construct();

        // Set default recipient for display in settings (will be overridden dynamically)
        $this->recipient = __( 'Project Lead', 'arsol-pfw' );
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[Project Lead] Proposal {new_status} #{proposal_id}', 'arsol-pfw' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Proposal Status Update', 'arsol-pfw' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int    $proposal_id Proposal ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     */
    public function trigger( $proposal_id, $old_status, $new_status ) {
        $this->setup_locale();

        if ( $proposal_id ) {
            $this->object = get_post( $proposal_id );
            $this->placeholders['{proposal_id}'] = $proposal_id;
            $this->placeholders['{old_status}'] = ucfirst( str_replace( '-', ' ', $old_status ) );
            $this->placeholders['{new_status}'] = ucfirst( str_replace( '-', ' ', $new_status ) );
            
            // Get project lead from proposal meta
            $project_lead_id = get_post_meta( $proposal_id, 'project_lead_id', true );
            if ( $project_lead_id ) {
                $project_lead = get_user_by( 'id', $project_lead_id );
                if ( $project_lead ) {
                    $this->recipient = $project_lead->user_email;
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
                'proposal_id'   => $this->placeholders['{proposal_id}'],
                'old_status'    => $this->placeholders['{old_status}'],
                'new_status'    => $this->placeholders['{new_status}'],
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
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{proposal_id}, {old_status}, {new_status}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __( 'Email heading', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{proposal_id}, {old_status}, {new_status}</code>' ),
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

    /**
     * Get recipient for display in email settings.
     * Returns display text for settings, actual email set dynamically in trigger.
     *
     * @return string
     */
    public function get_recipient() {
        // If we're in admin settings context, show display text
        if ( is_admin() && ! wp_doing_ajax() ) {
            return __( 'Project Lead', 'arsol-pfw' );
        }
        // Otherwise return the actual recipient email (set dynamically in trigger)
        return $this->recipient;
    }
}
 