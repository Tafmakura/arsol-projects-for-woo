<?php
/**
 * Frontend Template Sidebar Buttons
 *
 * Handles sidebar action buttons using CPT-specific hooks for maximum flexibility.
 *
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Template_Sidebar_Buttons {

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
        // CPT-specific button hooks
        add_action('arsol_pfw_project_sidebar_buttons', array($this, 'display_project_buttons'), 10, 2);
        add_action('arsol_pfw_project_proposal_sidebar_buttons', array($this, 'display_proposal_buttons'), 10, 2);
        add_action('arsol_pfw_project_request_sidebar_buttons', array($this, 'display_request_buttons'), 10, 2);
    }

    /**
     * Display project buttons
     *
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_project_buttons($status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        echo '<div class="sidebar-buttons">';
        $this->add_project_buttons($post_id, $status);
        echo '</div>';
    }

    /**
     * Display proposal buttons
     *
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_proposal_buttons($status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        echo '<div class="sidebar-buttons">';
        $this->add_proposal_buttons($post_id, $status);
        echo '</div>';
    }

    /**
     * Display request buttons
     *
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_request_buttons($status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        echo '<div class="sidebar-buttons">';
        $this->add_request_buttons($post_id, $status);
        echo '</div>';
    }

    /**
     * Add proposal buttons
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_proposal_buttons($post_id, $status) {
        // Approve button (for pending-approval status)
        if ($status === 'pending-approval') {
            $approve_url = wp_nonce_url(
                admin_url('admin-post.php?action=arsol_approve_proposal&proposal_id=' . $post_id),
                'arsol_approve_proposal_nonce'
            );
            
            echo '<div class="button-item button-approve">';
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
            
            echo '<div class="button-item button-reject">';
            echo '<a href="' . esc_url($reject_url) . '" class="button button-secondary" ';
            echo 'onclick="return confirm(\'' . esc_js__('Are you sure you want to reject this proposal?', 'arsol-pfw') . '\')">';
            echo esc_html__('Reject Proposal', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }
    }

    /**
     * Add request buttons
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_request_buttons($post_id, $status) {
        switch ($status) {
            case 'pending-review':
                $this->add_pending_review_buttons($post_id);
                break;
            case 'under-review':
                $this->add_under_review_buttons($post_id);
                break;
            case 'on-hold':
                $this->add_on_hold_buttons($post_id);
                break;
            case 'approved':
                $this->add_approved_buttons($post_id);
                break;
        }
    }

    /**
     * Add project buttons
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_project_buttons($post_id, $status) {
        // Project buttons can be added via hooks
        // Example: add_action('arsol_pfw_project_sidebar_buttons', 'my_custom_project_buttons', 20, 2);
    }

    /**
     * Add buttons for pending-review status
     *
     * @param int $post_id The post ID
     */
    private function add_pending_review_buttons($post_id) {
        // Update Request button (form submit)
        echo '<div class="arsol-pfw-project-button">';
        echo '<button type="submit" form="arsol-request-edit-form" class="brxe-button bricks-button button-primary request-button-btn">';
        echo esc_html__('Update Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Cancel Request button
        echo '<div class="arsol-pfw-project-button">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-primary cancel-request-btn" ';
        echo 'data-confirm-text="' . esc_attr__('Are you sure you want to cancel this request?', 'arsol-pfw') . '">';
        echo esc_html__('Cancel Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';
    }

    /**
     * Add buttons for under-review status
     *
     * @param int $post_id The post ID
     */
    private function add_under_review_buttons($post_id) {
        // Contact Review Team
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo esc_html__('Contact Review Team', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Review Process FAQ
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/faq/" class="brxe-button bricks-button sm outline bricks-color-secondary">';
        echo esc_html__('Review Process FAQ', 'arsol-pfw');
        echo '</a>';
        echo '</div>';
    }

    /**
     * Add buttons for on-hold status
     *
     * @param int $post_id The post ID
     */
    private function add_on_hold_buttons($post_id) {
        // Update Request button (form submit)
        echo '<div class="arsol-pfw-project-button">';
        echo '<button type="submit" form="arsol-request-edit-form" class="brxe-button bricks-button button-primary request-button-btn">';
        echo esc_html__('Update Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Cancel Request button
        echo '<div class="arsol-pfw-project-button">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-primary cancel-request-btn" ';
        echo 'data-confirm-text="' . esc_attr__('Are you sure you want to cancel this request?', 'arsol-pfw') . '">';
        echo esc_html__('Cancel Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Contact Support
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo esc_html__('Contact Support', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // View Our Services
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/services/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo esc_html__('View Our Services', 'arsol-pfw');
        echo '</a>';
        echo '</div>';
    }

    /**
     * Add buttons for approved status
     *
     * @param int $post_id The post ID
     */
    private function add_approved_buttons($post_id) {
        // View All Projects
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/projects/" class="brxe-button bricks-button button-primary">';
        echo esc_html__('View All Projects', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Contact Project Team
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo esc_html__('Contact Project Team', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Print Approval Details
        echo '<div class="arsol-pfw-project-button">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-secondary" onclick="window.print()">';
        echo esc_html__('Print Approval Details', 'arsol-pfw');
        echo '</button>';
        echo '</div>';
    }
}
