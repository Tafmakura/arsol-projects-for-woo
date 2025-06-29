<?php
/**
 * Request Stage Email
 * 
 * An email sent to customers when their request stage changes.
 * 
 * @class    WC_Email_Request_Stage
 * @package  Arsol_Projects_For_Woo\Emails
 * @since    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Stage Email Class
 */
class WC_Email_Request_Stage extends WC_Email {

    /**
     * Constructor
     */
    public function __construct() {
        
        $this->id             = 'request_stage';
        $this->title          = __( 'Project Customer: Request Stage Update', 'arsol-pfw' );
        $this->description    = __( 'Customer notification when their request stage changes.', 'arsol-pfw' );
        $this->template_html  = 'email-request-stage.php';
        $this->template_plain = 'email-request-stage.php';
        
        // Hook in.
        add_action( 'arsol_request_stage_changed', array( $this, 'trigger' ), 10, 4 );
        
        // Call parent constructor.
        parent::__construct();
    }

    /**
     * Get email subject.
     *
     * @since  1.0.0
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_title}] Request Stage Update - #{request_id}', 'arsol-pfw' );
    }

    /**
     * Get email heading.
     *
     * @since  1.0.0
     * @return string
     */
    public function get_default_heading() {
        return __( 'Request Stage Update', 'arsol-pfw' );
    }

    /**
     * Trigger function.
     *
     * @param int    $request_id
     * @param string $old_stage
     * @param string $new_stage
     * @param int    $customer_id
     */
    public function trigger( $request_id, $old_stage, $new_stage, $customer_id ) {
        
        if ( ! $this->is_enabled() || ! $request_id ) {
            return;
        }

        $this->object                 = get_post( $request_id );
        $this->request_id             = $request_id;
        $this->old_stage             = $old_stage;
        $this->new_stage             = $new_stage;
        $this->customer_id           = $customer_id;
        
        if ( ! $this->object || ! $this->get_recipient() ) {
            return;
        }
        
        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
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
                'request_id'    => $this->request_id,
                'old_stage'    => $this->old_stage,
                'new_stage'    => $this->new_stage,
                'customer_id'   => $this->customer_id,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => false,
                'email'         => $this,
            ),
            'arsol-pfw/',
            ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/'
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
                'request'       => $this->object,
                'request_id'    => $this->request_id,
                'old_stage'    => $this->old_stage,
                'new_stage'    => $this->new_stage,
                'customer_id'   => $this->customer_id,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'    => true,
                'email'         => $this,
            ),
            'arsol-pfw/',
            ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/'
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
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{site_title}, {request_id}, {old_stage}, {new_stage}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{site_title}, {request_id}, {old_stage}, {new_stage}</code>' ),
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
