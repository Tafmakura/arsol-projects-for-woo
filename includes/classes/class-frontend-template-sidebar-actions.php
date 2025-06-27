<?php
/**
 * Frontend Template Sidebar Actions
 *
 * Handles sidebar secondary actions using direct action hooks for maximum flexibility.
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
        add_action('arsol_pfw_add_sidebar_actions', array($this, 'add_default_actions'), 20, 3);
    }

    /**
     * Display sidebar actions container
     *
     * @param string $post_type The post type (active, proposal, request)
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_sidebar_actions($post_type, $status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        echo '<div class="sidebar-actions">';
        
        /**
         * Action hook for adding custom sidebar actions
         *
         * Developers can hook into this to add their own buttons/actions.
         * Just echo your HTML directly.
         *
         * @param string $post_type The post type
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        do_action('arsol_pfw_add_sidebar_actions', $post_type, $status, $post_id);
        
        echo '</div>';
    }

    /**
     * Add default actions for each post type
     *
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function add_default_actions($post_type, $status, $post_id) {
        switch ($post_type) {
            case 'active':
                $this->add_project_actions($post_id, $status);
                break;
            case 'proposal':
                $this->add_proposal_actions($post_id, $status);
                break;
            case 'request':
                $this->add_request_actions($post_id, $status);
                break;
        }
    }

    /**
     * Add default project actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_project_actions($post_id, $status) {
        // View details button (always shown)
        echo '<div class="action-item action-view-details">';
        echo '<a href="' . esc_url(get_permalink($post_id)) . '" class="button secondary-button">';
        echo '<span class="dashicons dashicons-visibility"></span> ';
        echo esc_html__('View Full Details', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Download files (for active and completed projects)
        if (in_array($status, array('active', 'completed'))) {
            $download_url = wp_nonce_url(
                add_query_arg(array('action' => 'download_project_files', 'project_id' => $post_id)),
                'download_files_' . $post_id
            );
            
            echo '<div class="action-item action-download-files">';
            echo '<a href="' . esc_url($download_url) . '" class="button secondary-button">';
            echo '<span class="dashicons dashicons-download"></span> ';
            echo esc_html__('Download Files', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }

        // Mark complete (for active projects only)
        if ($status === 'active') {
            $complete_url = wp_nonce_url(
                add_query_arg(array('action' => 'complete_project', 'project_id' => $post_id)),
                'complete_project_' . $post_id
            );
            
            echo '<div class="action-item action-mark-complete">';
            echo '<a href="' . esc_url($complete_url) . '" class="button button-primary" ';
            echo 'onclick="return confirm(\'' . esc_js__('Are you sure you want to mark this project as complete?', 'arsol-pfw') . '\')">';
            echo esc_html__('Mark Complete', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }
    }

    /**
     * Add default proposal actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_proposal_actions($post_id, $status) {
        // View proposal button (always shown)
        echo '<div class="action-item action-view-proposal">';
        echo '<a href="' . esc_url(get_permalink($post_id)) . '" class="button secondary-button">';
        echo '<span class="dashicons dashicons-visibility"></span> ';
        echo esc_html__('View Full Proposal', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Download PDF (always shown)
        $pdf_url = wp_nonce_url(
            add_query_arg(array('action' => 'download_proposal_pdf', 'proposal_id' => $post_id)),
            'download_pdf_' . $post_id
        );
        
        echo '<div class="action-item action-download-pdf">';
        echo '<a href="' . esc_url($pdf_url) . '" class="button secondary-button">';
        echo '<span class="dashicons dashicons-pdf"></span> ';
        echo esc_html__('Download PDF', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Reject button (for sent and pending-approval)
        if (in_array($status, array('sent', 'pending-approval'))) {
            $reject_url = wp_nonce_url(
                add_query_arg(array('action' => 'reject_proposal', 'proposal_id' => $post_id)),
                'reject_proposal_' . $post_id
            );
            
            echo '<div class="action-item action-reject">';
            echo '<a href="' . esc_url($reject_url) . '" class="button button-secondary" ';
            echo 'onclick="return confirm(\'' . esc_js__('Are you sure you want to reject this proposal?', 'arsol-pfw') . '\')">';
            echo esc_html__('Reject', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }

        // Request revision (for sent and pending-approval)
        if (in_array($status, array('sent', 'pending-approval'))) {
            $revision_url = wp_nonce_url(
                add_query_arg(array('action' => 'request_revision', 'proposal_id' => $post_id)),
                'request_revision_' . $post_id
            );
            
            echo '<div class="action-item action-request-revision">';
            echo '<a href="' . esc_url($revision_url) . '" class="button secondary-button">';
            echo esc_html__('Request Revision', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }
    }

    /**
     * Add default request actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     */
    private function add_request_actions($post_id, $status) {
        // View request button (always shown)
        echo '<div class="action-item action-view-request">';
        echo '<a href="' . esc_url(get_permalink($post_id)) . '" class="button secondary-button">';
        echo '<span class="dashicons dashicons-visibility"></span> ';
        echo esc_html__('View Full Request', 'arsol-pfw');
        echo '</a>';
        echo '</div>';

        // Edit request (for pending and draft)
        if (in_array($status, array('pending', 'draft'))) {
            echo '<div class="action-item action-edit-request">';
            echo '<a href="' . esc_url(get_edit_post_link($post_id)) . '" class="button secondary-button">';
            echo '<span class="dashicons dashicons-edit"></span> ';
            echo esc_html__('Edit Request', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }

        // Cancel request (for pending and under-review)
        if (in_array($status, array('pending', 'under-review'))) {
            $cancel_url = wp_nonce_url(
                add_query_arg(array('action' => 'cancel_request', 'request_id' => $post_id)),
                'cancel_request_' . $post_id
            );
            
            echo '<div class="action-item action-cancel-request">';
            echo '<a href="' . esc_url($cancel_url) . '" class="button button-link-delete" ';
            echo 'onclick="return confirm(\'' . esc_js__('Are you sure you want to cancel this request? This action cannot be undone.', 'arsol-pfw') . '\')">';
            echo esc_html__('Cancel Request', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }

        // Duplicate request (for completed statuses)
        if (in_array($status, array('approved', 'rejected', 'cancelled'))) {
            $duplicate_url = wp_nonce_url(
                add_query_arg(array('action' => 'duplicate_request', 'request_id' => $post_id)),
                'duplicate_request_' . $post_id
            );
            
            echo '<div class="action-item action-duplicate-request">';
            echo '<a href="' . esc_url($duplicate_url) . '" class="button secondary-button">';
            echo '<span class="dashicons dashicons-admin-page"></span> ';
            echo esc_html__('Duplicate Request', 'arsol-pfw');
            echo '</a>';
            echo '</div>';
        }
    }
}
