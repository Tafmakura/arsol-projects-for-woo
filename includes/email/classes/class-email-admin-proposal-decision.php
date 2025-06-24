<?php
/**
 * Admin Proposal Decision Email
 *
 * Email sent to shop managers when proposal decisions need action.
 *
 * @package Arsol_Projects_For_Woo\Emails
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

class Arsol_Email_Admin_Proposal_Decision extends WC_Email {

    public $decision;

    public function __construct() {
        $this->id             = 'arsol_admin_proposal_decision';
        $this->title          = __('Proposal Decision Action Required (Admin)', 'arsol-projects-for-woo');
        $this->description    = __('Email sent to shop managers when approved proposals need conversion to projects/orders.', 'arsol-projects-for-woo');
        $this->template_html  = 'email-admin-proposal-decision.php';
        $this->template_base  = ARSOL_PFW_PLUGIN_DIR . 'includes/email/templates/';
        
        $this->recipient = $this->get_option('recipient', get_option('admin_email'));
        
        parent::__construct();
    }

    public function get_default_subject() {
        return __('Approved Proposal Needs Conversion: {proposal_title}', 'arsol-projects-for-woo');
    }

    public function get_default_heading() {
        return __('Proposal Approved - Action Required', 'arsol-projects-for-woo');
    }

    public function trigger($proposal_id, $decision) {
        if ($decision !== 'approved') {
            return; // Only send for approved proposals
        }
        
        $this->setup_locale();
        
        if ($proposal_id && !is_a($proposal_id, 'WP_Post')) {
            $this->object = get_post($proposal_id);
        }
        
        if (!$this->is_enabled() || !$this->get_recipient() || !$this->object) {
            $this->restore_locale();
            return;
        }
        
        $this->decision = $decision;
        
        $this->placeholders = array(
            '{proposal_title}' => $this->object->post_title,
            '{proposal_id}'    => $this->object->ID,
            '{decision}'       => ucfirst($decision),
            '{site_title}'     => $this->get_blogname(),
        );
        
        $this->send(
            $this->get_recipient(),
            $this->get_subject(),
            $this->get_content(),
            $this->get_headers(),
            $this->get_attachments()
        );
        
        $this->restore_locale();
    }

    /**
     * Get content (used by WooCommerce for previews)
     */
    public function get_content() {
        return $this->get_content_html();
    }

    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'proposal'      => $this->object,
                'customer'      => $this->get_customer(),
                'decision'      => $this->decision,
                'email_heading' => $this->get_heading(),
                'admin_url'     => $this->get_admin_url(),
                'email'         => $this,
            ),
            '',
            $this->template_base
        );
    }

    public function get_content_plain() {
        return $this->get_content_html();
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'arsol-projects-for-woo'),
                'type'    => 'checkbox',
                'label'   => __('Enable this email notification', 'arsol-projects-for-woo'),
                'default' => 'yes',
            ),
            'recipient' => array(
                'title'       => __('Recipient(s)', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'description' => sprintf(__('Enter recipients (comma separated) for this email. Defaults to %s.', 'arsol-projects-for-woo'), '<code>' . esc_attr(get_option('admin_email')) . '</code>'),
                'placeholder' => '',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'subject' => array(
                'title'       => __('Subject', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'arsol-projects-for-woo'), '<code>{proposal_title}, {proposal_id}, {decision}, {site_title}</code>'),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __('Email heading', 'arsol-projects-for-woo'),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf(__('Available placeholders: %s', 'arsol-projects-for-woo'), '<code>{proposal_title}, {proposal_id}, {decision}, {site_title}</code>'),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __('Additional content', 'arsol-projects-for-woo'),
                'description' => __('Text to appear below the main email content.', 'arsol-projects-for-woo'),
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __('N/A', 'arsol-projects-for-woo'),
                'type'        => 'textarea',
                'default'     => '',
                'desc_tip'    => true,
            ),
            'email_type' => array(
                'title'       => __('Email type', 'arsol-projects-for-woo'),
                'type'        => 'select',
                'description' => __('Choose which format of email to send.', 'arsol-projects-for-woo'),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }

    private function get_customer() {
        if (!$this->object) return null;
        $customer_id = get_post_meta($this->object->ID, '_arsol_pfw_proposal_customer_id', true);
        return $customer_id ? get_user_by('id', $customer_id) : null;
    }

    private function get_admin_url() {
        if (!$this->object) return '';
        return admin_url('post.php?post=' . $this->object->ID . '&action=edit');
    }
} 