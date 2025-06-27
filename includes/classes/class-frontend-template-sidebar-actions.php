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
        add_action('arsol_pfw_sidebar_actions', array($this, 'display_sidebar_actions'), 10, 3);
        add_action('arsol_pfw_add_sidebar_actions', array($this, 'add_default_actions'), 10, 3);
    }

    /**
     * Display sidebar actions
     *
     * @param string $post_type The post type (active, proposal, request)
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_sidebar_actions($post_type, $status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        // Debug: uncomment to see what parameters are passed
        // error_log("Actions display - Post: $post_id, Type: $post_type, Status: $status");

        echo '<div class="sidebar-actions">';
        
        /**
         * Hook: arsol_pfw_add_sidebar_actions
         * 
         * Developers can hook into this to add their own action buttons.
         * Simply echo the HTML for buttons/links directly.
         * 
         * @param string $post_type The post type
         * @param string $status The current status  
         * @param int $post_id The post ID
         */
        do_action('arsol_pfw_add_sidebar_actions', $post_type, $status, $post_id);
        
        echo '</div>';
    }

    /**
     * Add default actions based on post type and status
     *
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function add_default_actions($post_type, $status, $post_id) {
        switch ($post_type) {
            case 'proposal':
                $this->add_proposal_actions($post_id, $status);
                break;
            case 'request':
                $this->add_request_actions($post_id, $status);
                break;
            case 'active':
                $this->add_project_actions($post_id, $status);
                break;
        }
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
        // Cancel button (for pending-review and under-review)
        if (in_array($status, array('pending-review', 'under-review'))) {
            $cancel_url = wp_nonce_url(
                admin_url('admin-post.php?action=arsol_cancel_request&request_id=' . $post_id),
                'arsol_cancel_request_nonce'
            );
            
            echo '<div class="action-item action-cancel">';
            echo '<a href="' . esc_url($cancel_url) . '" class="button button-link-delete" ';
            echo 'onclick="return confirm(\'' . esc_js__('Are you sure you want to cancel this request? This action cannot be undone.', 'arsol-pfw') . '\')">';
            echo esc_html__('Cancel Request', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }
        
        // Temporary test button to verify actions are working
        echo '<div class="action-item action-test">';
        echo '<span style="background: lightblue; padding: 5px;">Test: Request actions for ' . esc_html($status) . '</span>';
        echo '</div>';
    }

    /**
     * Add project actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_project_actions($post_id, $status) {
        // Add any default project actions if needed
        // For now, projects mainly have custom actions added via hooks
        
        // Temporary test button to verify actions are working
        echo '<div class="action-item action-test">';
        echo '<span style="background: lightgreen; padding: 5px;">Test: Project actions for ' . esc_html($status) . '</span>';
        echo '</div>';
    }
}
