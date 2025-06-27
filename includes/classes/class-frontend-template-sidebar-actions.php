<?php
/**
 * Frontend Template Sidebar Actions
 *
 * Handles sidebar secondary actions/buttons for all project types using filterable arrays.
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

        $actions = $this->get_default_actions($post_type, $status, $post_id);

        /**
         * Filter sidebar secondary actions
         *
         * @param array $actions Array of action items
         * @param string $post_type The post type
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        $actions = apply_filters('arsol_pfw_sidebar_actions', $actions, $post_type, $status, $post_id);

        if (empty($actions)) {
            return;
        }

        echo '<div class="sidebar-actions">';
        foreach ($actions as $key => $action) {
            $this->render_action_item($key, $action, $post_type, $status, $post_id);
        }
        echo '</div>';
    }

    /**
     * Get default actions based on post type and status
     *
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     * @return array Array of action items
     */
    private function get_default_actions($post_type, $status, $post_id) {
        $actions = array();

        switch ($post_type) {
            case 'active':
                $actions = $this->get_project_actions($post_id, $status);
                break;
            case 'proposal':
                $actions = $this->get_proposal_actions($post_id, $status);
                break;
            case 'request':
                $actions = $this->get_request_actions($post_id, $status);
                break;
        }

        return $this->filter_actions_by_status($actions, $status);
    }

    /**
     * Get project actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of action items
     */
    private function get_project_actions($post_id, $status) {
        return array(
            'view_details' => array(
                'label' => __('View Full Details', 'arsol-pfw'),
                'url' => get_permalink($post_id),
                'class' => 'button secondary-button',
                'icon' => 'dashicons-visibility'
            ),
            'download_files' => array(
                'label' => __('Download Files', 'arsol-pfw'),
                'url' => wp_nonce_url(
                    add_query_arg(array('action' => 'download_project_files', 'project_id' => $post_id)),
                    'download_files_' . $post_id
                ),
                'class' => 'button secondary-button',
                'icon' => 'dashicons-download',
                'show_if' => array('status' => array('active', 'completed'))
            ),
            'mark_complete' => array(
                'label' => __('Mark Complete', 'arsol-pfw'),
                'url' => wp_nonce_url(
                    add_query_arg(array('action' => 'complete_project', 'project_id' => $post_id)),
                    'complete_project_' . $post_id
                ),
                'class' => 'button button-primary',
                'confirm' => __('Are you sure you want to mark this project as complete?', 'arsol-pfw'),
                'show_if' => array('status' => array('active'))
            )
        );
    }

    /**
     * Get proposal actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of action items
     */
    private function get_proposal_actions($post_id, $status) {
        return array(
            'view_proposal' => array(
                'label' => __('View Full Proposal', 'arsol-pfw'),
                'url' => get_permalink($post_id),
                'class' => 'button secondary-button',
                'icon' => 'dashicons-visibility'
            ),
            'download_pdf' => array(
                'label' => __('Download PDF', 'arsol-pfw'),
                'url' => wp_nonce_url(
                    add_query_arg(array('action' => 'download_proposal_pdf', 'proposal_id' => $post_id)),
                    'download_pdf_' . $post_id
                ),
                'class' => 'button secondary-button',
                'icon' => 'dashicons-pdf'
            ),
            'reject_proposal' => array(
                'label' => __('Reject', 'arsol-pfw'),
                'url' => wp_nonce_url(
                    add_query_arg(array('action' => 'reject_proposal', 'proposal_id' => $post_id)),
                    'reject_proposal_' . $post_id
                ),
                'class' => 'button button-secondary',
                'confirm' => __('Are you sure you want to reject this proposal?', 'arsol-pfw'),
                'show_if' => array('status' => array('sent', 'pending-approval'))
            ),
            'request_revision' => array(
                'label' => __('Request Revision', 'arsol-pfw'),
                'url' => wp_nonce_url(
                    add_query_arg(array('action' => 'request_revision', 'proposal_id' => $post_id)),
                    'request_revision_' . $post_id
                ),
                'class' => 'button secondary-button',
                'show_if' => array('status' => array('sent', 'pending-approval'))
            )
        );
    }

    /**
     * Get request actions
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of action items
     */
    private function get_request_actions($post_id, $status) {
        return array(
            'view_request' => array(
                'label' => __('View Full Request', 'arsol-pfw'),
                'url' => get_permalink($post_id),
                'class' => 'button secondary-button',
                'icon' => 'dashicons-visibility'
            ),
            'edit_request' => array(
                'label' => __('Edit Request', 'arsol-pfw'),
                'url' => get_edit_post_link($post_id),
                'class' => 'button secondary-button',
                'icon' => 'dashicons-edit',
                'show_if' => array('status' => array('pending', 'draft'))
            ),
            'cancel_request' => array(
                'label' => __('Cancel Request', 'arsol-pfw'),
                'url' => wp_nonce_url(
                    add_query_arg(array('action' => 'cancel_request', 'request_id' => $post_id)),
                    'cancel_request_' . $post_id
                ),
                'class' => 'button button-link-delete',
                'confirm' => __('Are you sure you want to cancel this request? This action cannot be undone.', 'arsol-pfw'),
                'show_if' => array('status' => array('pending', 'under-review'))
            ),
            'duplicate_request' => array(
                'label' => __('Duplicate Request', 'arsol-pfw'),
                'url' => wp_nonce_url(
                    add_query_arg(array('action' => 'duplicate_request', 'request_id' => $post_id)),
                    'duplicate_request_' . $post_id
                ),
                'class' => 'button secondary-button',
                'icon' => 'dashicons-admin-page',
                'show_if' => array('status' => array('approved', 'rejected', 'cancelled'))
            )
        );
    }

    /**
     * Filter actions by status conditions
     *
     * @param array $actions Array of action items
     * @param string $current_status The current status
     * @return array Filtered actions array
     */
    private function filter_actions_by_status($actions, $current_status) {
        return array_filter($actions, function($action) use ($current_status) {
            if (!isset($action['show_if'])) {
                return true;
            }

            if (isset($action['show_if']['status'])) {
                return in_array($current_status, $action['show_if']['status']);
            }

            return true;
        });
    }

    /**
     * Render a single action item
     *
     * @param string $key The action key
     * @param array $action The action configuration
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    private function render_action_item($key, $action, $post_type, $status, $post_id) {
        if (empty($action['label']) || empty($action['url'])) {
            return;
        }

        $label = esc_html($action['label']);
        $url = esc_url($action['url']);
        $class = isset($action['class']) ? esc_attr($action['class']) : 'button';
        $icon = isset($action['icon']) ? $action['icon'] : '';
        $confirm = isset($action['confirm']) ? $action['confirm'] : '';
        $target = isset($action['target']) ? $action['target'] : '';

        $attributes = array();
        
        if (!empty($target)) {
            $attributes[] = 'target="' . esc_attr($target) . '"';
        }

        if (!empty($confirm)) {
            $attributes[] = 'onclick="return confirm(\'' . esc_js($confirm) . '\')"';
        }

        $attributes_str = implode(' ', $attributes);

        echo '<div class="action-item action-' . esc_attr($key) . '">';
        
        echo '<a href="' . $url . '" class="' . $class . '" ' . $attributes_str . '>';
        
        if (!empty($icon)) {
            echo '<span class="dashicons ' . esc_attr($icon) . '"></span> ';
        }
        
        echo $label;
        echo '</a>';
        
        echo '</div>';
    }
} 