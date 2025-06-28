<?php
/**
 * Frontend Template Sidebar Actions
 *
 * Handles sidebar action buttons using direct action hooks for maximum flexibility.
 *
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Template_Sidebar_Actions {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // CPT-specific action hooks
        add_action('arsol_pfw_project_sidebar_actions', array($this, 'display_project_actions'), 10, 2);
        add_action('arsol_pfw_project_proposal_sidebar_actions', array($this, 'display_proposal_actions'), 10, 2);
        add_action('arsol_pfw_project_request_sidebar_actions', array($this, 'display_request_actions'), 10, 2);
    }

    /**
     * Display project actions
     *
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_project_actions($status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        echo '<div class="sidebar-actions">';
        $this->add_project_actions($post_id, $status);
        echo '</div>';
    }

    /**
     * Display proposal actions
     *
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_proposal_actions($status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        echo '<div class="sidebar-actions">';
        $this->add_proposal_actions($post_id, $status);
        echo '</div>';
    }

    /**
     * Display request actions
     *
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_request_actions($status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        echo '<div class="sidebar-actions">';
        $this->add_request_actions($post_id, $status);
        echo '</div>';
    }

    /**
     * Add proposal actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_proposal_actions($post_id, $status) {
        // Debug: always show buttons for testing
        // error_log("Proposal actions for status: $status");
        
        // Approve button (for pending-approval status)
        if ($status === 'pending-approval') {
            $approve_url = wp_nonce_url(
                admin_url('admin-post.php?action=arsol_approve_proposal&proposal_id=' . $post_id),
                'arsol_approve_proposal_nonce'
            );
            
            echo '<div class="action-item action-approve">';
            echo '<a href="' . esc_url($approve_url) . '" class="button button-primary" ';
            echo 'onclick="return confirm(\'' . esc_js__('Are you sure you want to approve this proposal? This will create a project and may generate WooCommerce orders.', 'arsol-pfw') . '\')">';
            echo esc_html__('Approve Proposal', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }

        // Reject button (for pending-approval status)  
        if ($status === 'pending-approval') {
            $reject_url = wp_nonce_url(
                admin_url('admin-post.php?action=arsol_reject_proposal&proposal_id=' . $post_id),
                'arsol_reject_proposal_nonce'
            );
            
            echo '<div class="action-item action-reject">';
            echo '<a href="' . esc_url($reject_url) . '" class="button button-secondary" ';
            echo 'onclick="return confirm(\'' . esc_js__('Are you sure you want to reject this proposal?', 'arsol-pfw') . '\')">';
            echo esc_html__('Reject Proposal', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }
        
        // Temporary test button to verify actions are working
        echo '<div class="action-item action-test">';
        echo '<span style="background: yellow; padding: 5px;">Test: Actions working for ' . esc_html($status) . '</span>';
        echo '</div>';
    }

    /**
     * Add request actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_request_actions($post_id, $status) {
        switch ($status) {
            case 'pending-review':
                $this->add_pending_review_actions($post_id);
                break;
            case 'under-review':
                $this->add_under_review_actions($post_id);
                break;
            case 'on-hold':
                $this->add_on_hold_actions($post_id);
                break;
            case 'approved':
                $this->add_approved_actions($post_id);
                break;
        }
    }

    /**
     * Add project actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_project_actions($post_id, $status) {
        // Project actions can be added via hooks
        // Example: add_action('arsol_pfw_project_sidebar_actions', 'my_custom_project_actions', 20, 2);
    }

    /**
     * Add actions for pending-review status
     *
     * @param int $post_id The post ID
     */
    private function add_pending_review_actions($post_id) {
        // Update Request button (form submit)
        echo '<div class="arsol-pfw-project-action">';
        echo '<button type="submit" form="arsol-request-edit-form" class="brxe-button bricks-button button-primary request-action-btn">';
        echo esc_html__('Update Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Cancel Request button
        echo '<div class="arsol-pfw-project-action">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-primary cancel-request-btn" ';
        echo 'data-confirm-text="' . esc_attr__('Are you sure you want to cancel this request?', 'arsol-pfw') . '">';
        echo esc_html__('Cancel Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';
    }

    /**
     * Add actions for under-review status
     *
     * @param int $post_id The post ID
     */
    private function add_under_review_actions($post_id) {
        // Contact Review Team
        echo '<div class="arsol-pfw-project-action">';
        echo '<a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo esc_html__('Contact Review Team', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Review Process FAQ
        echo '<div class="arsol-pfw-project-action">';
        echo '<a href="/faq/" class="brxe-button bricks-button sm outline bricks-color-secondary">';
        echo esc_html__('Review Process FAQ', 'arsol-pfw');
        echo '</a>';
        echo '</div>';
    }

    /**
     * Add actions for on-hold status
     *
     * @param int $post_id The post ID
     */
    private function add_on_hold_actions($post_id) {
        // Update Request button (form submit)
        echo '<div class="arsol-pfw-project-action">';
        echo '<button type="submit" form="arsol-request-edit-form" class="brxe-button bricks-button button-primary request-action-btn">';
        echo esc_html__('Update Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Cancel Request button
        echo '<div class="arsol-pfw-project-action">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-primary cancel-request-btn" ';
        echo 'data-confirm-text="' . esc_attr__('Are you sure you want to cancel this request?', 'arsol-pfw') . '">';
        echo esc_html__('Cancel Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Contact Support
        echo '<div class="arsol-pfw-project-action">';
        echo '<a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo esc_html__('Contact Support', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // View Our Services
        echo '<div class="arsol-pfw-project-action">';
        echo '<a href="/services/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo esc_html__('View Our Services', 'arsol-pfw');
        echo '</a>';
        echo '</div>';
    }

    /**
     * Add actions for approved status
     *
     * @param int $post_id The post ID
     */
    private function add_approved_actions($post_id) {
        // View All Projects
        echo '<div class="arsol-pfw-project-action">';
        echo '<a href="/projects/" class="brxe-button bricks-button button-primary">';
        echo esc_html__('View All Projects', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Contact Project Team
        echo '<div class="arsol-pfw-project-action">';
        echo '<a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo esc_html__('Contact Project Team', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Print Approval Details
        echo '<div class="arsol-pfw-project-action">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-secondary" onclick="window.print()">';
        echo esc_html__('Print Approval Details', 'arsol-pfw');
        echo '</button>';
        echo '</div>';
    }
}
