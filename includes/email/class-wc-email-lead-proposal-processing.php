<?php
/**
 * Lead Proposal Processing Email
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
 * Lead Proposal Processing Email Class
 */
class WC_Email_Lead_Proposal_Processing extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'lead_proposal_processing';
        $this->title          = __( 'Proposal Processing (Project Lead)', 'arsol-projects-for-woo' );
        $this->description    = __( 'Lead proposal processing emails are sent to project leads when proposal work begins.', 'arsol-projects-for-woo' );
        $this->template_html  = 'emails/email-lead-proposal-processing.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';

        // Triggers for this email
        add_action( 'arsol_proposal_processing_lead_notification', array( $this, 'trigger' ), 10, 3 );

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
        return __( '[{site_title}] Now Processing: {proposal_title} - #{proposal_id}', 'arsol-projects-for-woo' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Proposal Assignment Active', 'arsol-projects-for-woo' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $proposal_id Proposal ID.
     * @param int $customer_id Customer ID.
     * @param int $project_lead_id Project lead ID.
     */
    public function trigger( $proposal_id, $customer_id, $project_lead_id ) {
        $this->setup_locale();

        if ( $proposal_id && $project_lead_id ) {
            $this->object = get_post( $proposal_id );
            $project_lead = get_user_by( 'id', $project_lead_id );
            
            if ( $this->object && $project_lead ) {
                $this->recipient = $project_lead->user_email;
                $this->placeholders['{proposal_id}'] = $proposal_id;
                $this->placeholders['{proposal_title}'] = $this->object->post_title;
                $this->placeholders['{project_lead_name}'] = $project_lead->display_name;
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
                'proposal'           => $this->object,
                'project_lead_name'  => $this->placeholders['{project_lead_name}'] ?? '',
                'customer_name'      => $this->placeholders['{customer_name}'] ?? '',
                'customer_email'     => $this->placeholders['{customer_email}'] ?? '',
                'email_heading'      => $this->get_heading(),
                'sent_to_admin'      => false,
                'plain_text'         => false,
                'email'              => $this,
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
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {proposal_id}, {proposal_title}, {project_lead_name}, {customer_name}, {customer_email}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {proposal_id}, {proposal_title}, {project_lead_name}, {customer_name}, {customer_email}</code>' ),
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