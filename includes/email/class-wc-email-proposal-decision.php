<?php
/**
 * Proposal Decision Email
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
 * Proposal Decision Email Class
 */
class WC_Email_Proposal_Decision extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'proposal_decision';
        $this->title          = __( 'Proposal Decision', 'arsol-projects-for-woo' );
        $this->description    = __( 'Proposal decision emails are sent when a proposal is approved or rejected.', 'arsol-projects-for-woo' );
        $this->template_html  = 'emails/email-proposal-decision.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';

        // Triggers for this email
        add_action( 'arsol_proposal_approved', array( $this, 'trigger' ), 10, 3 );
        add_action( 'arsol_proposal_rejected', array( $this, 'trigger' ), 10, 3 );

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
        return __( '[{site_title}] Proposal {decision} - #{proposal_id}', 'arsol-projects-for-woo' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Proposal Decision', 'arsol-projects-for-woo' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int    $proposal_id Proposal ID.
     * @param int    $customer_id Customer ID.
     * @param string $decision Decision (approved/rejected).
     */
    public function trigger( $proposal_id, $customer_id, $decision = '' ) {
        $this->setup_locale();

        if ( $proposal_id ) {
            $this->object = get_post( $proposal_id );
            
            if ( $this->object ) {
                $this->placeholders['{proposal_id}'] = $proposal_id;
                $this->placeholders['{decision}'] = ucfirst( $decision );
                $this->placeholders['{site_title}'] = $this->get_blogname();
                
                // Set recipient based on decision
                if ( $customer_id ) {
                    $customer = get_user_by( 'id', $customer_id );
                    if ( $customer ) {
                        $this->placeholders['{customer_name}'] = $customer->display_name;
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
                'proposal'      => $this->object,
                'decision'      => $this->placeholders['{decision}'] ?? '',
                'customer_name' => $this->placeholders['{customer_name}'] ?? '',
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
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {proposal_id}, {decision}, {customer_name}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {proposal_id}, {decision}, {customer_name}</code>' ),
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