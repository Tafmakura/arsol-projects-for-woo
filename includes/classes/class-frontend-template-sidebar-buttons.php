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
     * @param string $current_stage The current stage
     * @param int $project_id The project ID
     */
    public function display_project_buttons($current_stage, $project_id) {
        error_log("ARSOL DEBUG: Project buttons - Stage: '$current_stage', Project ID: $project_id");
        
        if (empty($project_id)) {
            error_log("ARSOL DEBUG: Project buttons - Empty project ID, returning");
            return;
        }

        echo '<div class="sidebar-buttons">';
        $this->add_project_buttons($project_id, $current_stage);
        echo '</div>';
    }

    /**
     * Display proposal buttons
     *
     * @param string $current_status The current status
     * @param int $project_proposal_id The proposal ID
     */
    public function display_proposal_buttons($current_status, $project_proposal_id) {
        error_log("ARSOL DEBUG: Proposal buttons - Status: '$current_status', Proposal ID: $project_proposal_id");
        
        if (empty($project_proposal_id)) {
            error_log("ARSOL DEBUG: Proposal buttons - Empty proposal ID, returning");
            return;
        }

        echo '<div class="sidebar-buttons">';
        $this->add_proposal_buttons($project_proposal_id, $current_status);
        echo '</div>';
    }

    /**
     * Display request buttons
     *
     * @param string $current_stage The current stage
     * @param int $project_request_id The request ID
     */
    public function display_request_buttons($current_stage, $project_request_id) {
        error_log("ARSOL DEBUG: Request buttons - Stage: '$current_stage', Request ID: $project_request_id");
        
        if (empty($project_request_id)) {
            error_log("ARSOL DEBUG: Request buttons - Empty request ID, returning");
            return;
        }

        echo '<div class="sidebar-buttons">';
        $this->add_request_buttons($project_request_id, $current_stage);
        echo '</div>';
    }

    /**
     * Add proposal buttons
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_proposal_buttons($post_id, $status) {
        error_log("ARSOL DEBUG: add_proposal_buttons called - Status: '$status', Post ID: $post_id");
        
        // If no status is set, show informational message
        if (empty($status)) {
            echo '<div class="button-item button-no-status">';
            echo '<p style="margin-bottom: 10px;"><strong>No status set for this proposal.</strong></p>';
            echo '<button class="button button-secondary" disabled>Awaiting Status Assignment</button>';
            echo '</div>';
            return;
        }
        
        // Show approve/reject buttons for pending-approval status (these use existing handlers)
        if ($status === 'pending-approval') {
            error_log("ARSOL DEBUG: Adding approve/reject buttons for pending-approval status");
            $approve_url = wp_nonce_url(
                admin_url('admin-post.php?action=arsol_approve_proposal&proposal_id=' . $post_id),
                'arsol_approve_proposal_nonce'
            );
            
            echo '<div class="button-item button-approve">';
            echo '<a href="' . esc_url($approve_url) . '" class="button button-primary" ';
            echo 'onclick="return confirm(\'' . \esc_js(\__('Are you sure you want to approve this proposal? This will create a project and may generate WooCommerce orders.', 'arsol-pfw')) . '\')">';
            echo \esc_html__('Approve Proposal', 'arsol-pfw');
            echo '</a>';
            echo '</div>';

            $reject_url = wp_nonce_url(
                admin_url('admin-post.php?action=arsol_reject_proposal&proposal_id=' . $post_id),
                'arsol_reject_proposal_nonce'
            );
            
            echo '<div class="button-item button-reject">';
            echo '<a href="' . esc_url($reject_url) . '" class="button button-secondary" ';
            echo 'onclick="return confirm(\'' . \esc_js(\__('Are you sure you want to reject this proposal?', 'arsol-pfw')) . '\')">';
            echo \esc_html__('Reject Proposal', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }
        
        // Show informational buttons for other statuses
        if ($status === 'processing') {
            echo '<div class="button-item button-processing">';
            echo '<button class="button button-secondary" disabled>Proposal is Processing</button>';
            echo '</div>';
        }
        
        if ($status === 'approved') {
            echo '<div class="button-item button-approved">';
            echo '<a href="/my-account/projects/" class="button button-success">View Created Project</a>';
            echo '</div>';
        }
        
        if ($status === 'rejected') {
            echo '<div class="button-item button-rejected">';
            echo '<button class="button button-secondary" disabled>Proposal Rejected</button>';
            echo '</div>';
        }
    }

    /**
     * Add request buttons
     *
     * @param int $post_id The post ID
     * @param string $stage The current stage
     */
    private function add_request_buttons($post_id, $stage) {
        error_log("ARSOL DEBUG: add_request_buttons called - Stage: '$stage', Post ID: $post_id");
        
        switch ($stage) {
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
            default:
                echo '<div class="button-item button-no-stage">';
                if (empty($stage)) {
                    echo '<button class="button button-warning" disabled>No Stage Set</button>';
                } else {
                    echo '<button class="button button-warning" disabled>Unknown Stage: ' . esc_html($stage) . '</button>';
                }
                echo '</div>';
                break;
        }
    }

    /**
     * Add project buttons
     *
     * @param int $post_id The post ID
     * @param string $stage The current stage
     */
    private function add_project_buttons($post_id, $stage) {
        error_log("ARSOL DEBUG: add_project_buttons called - Stage: '$stage', Post ID: $post_id");
        
        // If no stage is set, show informational message
        if (empty($stage)) {
            echo '<div class="button-item button-no-stage">';
            echo '<p style="margin-bottom: 10px;"><strong>No stage set for this project.</strong></p>';
            echo '<button class="button button-secondary" disabled>Awaiting Stage Assignment</button>';
            echo '</div>';
            return;
        }
        
        // Show informational buttons based on project stage
        switch ($stage) {
            case 'not-started':
                echo '<div class="button-item button-not-started">';
                echo '<button class="button button-secondary" disabled>Project Not Started</button>';
                echo '</div>';
                break;
                
            case 'in-progress':
                echo '<div class="button-item button-in-progress">';
                echo '<button class="button button-primary" disabled>Project In Progress</button>';
                echo '</div>';
                break;
                
            case 'on-hold':
                echo '<div class="button-item button-on-hold">';
                echo '<button class="button button-warning" disabled>Project On Hold</button>';
                echo '</div>';
                break;
                
            case 'completed':
                echo '<div class="button-item button-completed">';
                echo '<button class="button button-success" disabled>Project Completed</button>';
                echo '<a href="/my-account/projects/" class="button button-secondary" style="margin-left: 10px;">View All Projects</a>';
                echo '</div>';
                break;
                
            case 'cancelled':
                echo '<div class="button-item button-cancelled">';
                echo '<button class="button button-secondary" disabled>Project Cancelled</button>';
                echo '<a href="/contact-us/" class="button button-primary" style="margin-left: 10px;">Contact Support</a>';
                echo '</div>';
                break;
                
            default:
                echo '<div class="button-item button-unknown">';
                echo '<button class="button button-warning" disabled>Unknown Stage: ' . esc_html($stage) . '</button>';
                echo '</div>';
                break;
        }
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
        echo \esc_html__('Update Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Cancel Request button
        echo '<div class="arsol-pfw-project-button">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-primary cancel-request-btn" ';
        echo 'data-confirm-text="' . \esc_attr__('Are you sure you want to cancel this request?', 'arsol-pfw') . '">';
        echo \esc_html__('Cancel Request', 'arsol-pfw');
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
        echo \esc_html__('Contact Review Team', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Review Process FAQ
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/faq/" class="brxe-button bricks-button sm outline bricks-color-secondary">';
        echo \esc_html__('Review Process FAQ', 'arsol-pfw');
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
        echo \esc_html__('Update Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Cancel Request button
        echo '<div class="arsol-pfw-project-button">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-primary cancel-request-btn" ';
        echo 'data-confirm-text="' . \esc_attr__('Are you sure you want to cancel this request?', 'arsol-pfw') . '">';
        echo \esc_html__('Cancel Request', 'arsol-pfw');
        echo '</button>';
        echo '</div>';

        // Contact Support
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo \esc_html__('Contact Support', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // View Our Services
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/services/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo \esc_html__('View Our Services', 'arsol-pfw');
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
        echo \esc_html__('View All Projects', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Contact Project Team
        echo '<div class="arsol-pfw-project-button">';
        echo '<a href="/contact-us/" class="brxe-button bricks-button sm outline bricks-color-primary">';
        echo \esc_html__('Contact Project Team', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Print Approval Details
        echo '<div class="arsol-pfw-project-button">';
        echo '<button type="button" class="brxe-button bricks-button sm outline bricks-color-secondary" onclick="window.print()">';
        echo \esc_html__('Print Approval Details', 'arsol-pfw');
        echo '</button>';
        echo '</div>';
    }
}
