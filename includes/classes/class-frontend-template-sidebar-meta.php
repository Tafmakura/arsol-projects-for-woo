<?php
/**
 * Frontend Template Sidebar Meta
 *
 * Handles sidebar metadata display for all project types using filterable arrays.
 *
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Template_Sidebar_Meta {

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
        add_action('arsol_pfw_sidebar_meta', array($this, 'display_sidebar_meta'), 10, 3);
    }

    /**
     * Display sidebar metadata
     *
     * @param string $post_type The post type (active, proposal, request)
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    public function display_sidebar_meta($post_type, $status, $post_id) {
        if (empty($post_id)) {
            return;
        }

        $metadata = $this->get_default_metadata($post_type, $status, $post_id);

        /**
         * Filter sidebar metadata
         *
         * @param array $metadata Array of metadata items
         * @param string $post_type The post type
         * @param string $status The current status
         * @param int $post_id The post ID
         */
        $metadata = apply_filters('arsol_pfw_sidebar_metadata', $metadata, $post_type, $status, $post_id);

        if (empty($metadata)) {
            return;
        }

        echo '<div class="sidebar-metadata">';
        foreach ($metadata as $key => $meta) {
            $this->render_metadata_item($key, $meta, $post_type, $status, $post_id);
        }
        echo '</div>';
    }

    /**
     * Get default metadata based on post type and status
     *
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     * @return array Array of metadata items
     */
    private function get_default_metadata($post_type, $status, $post_id) {
        $metadata = array();

        switch ($post_type) {
            case 'active':
                $metadata = $this->get_project_metadata($post_id, $status);
                break;
            case 'proposal':
                $metadata = $this->get_proposal_metadata($post_id, $status);
                break;
            case 'request':
                $metadata = $this->get_request_metadata($post_id, $status);
                break;
        }

        return $this->filter_metadata_by_status($metadata, $status);
    }

    /**
     * Get project metadata
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of metadata items
     */
    private function get_project_metadata($post_id, $status) {
        return array(
            'status' => array(
                'label' => __('Status', 'arsol-pfw'),
                'value' => $this->format_status_display($status),
                'type' => 'badge',
                'class' => 'status-badge status-' . sanitize_html_class($status)
            ),
            'budget' => array(
                'label' => __('Budget', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'project_budget', true),
                'type' => 'currency',
                'show_if' => array('status' => array('active', 'completed'))
            ),
            'start_date' => array(
                'label' => __('Start Date', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'project_start_date', true),
                'type' => 'date',
                'format' => 'F j, Y'
            ),
            'deadline' => array(
                'label' => __('Deadline', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'project_deadline', true),
                'type' => 'date',
                'format' => 'F j, Y',
                'class' => 'deadline-date'
            ),
            'client' => array(
                'label' => __('Client', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'client_name', true),
                'type' => 'text'
            )
        );
    }

    /**
     * Get proposal metadata
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of metadata items
     */
    private function get_proposal_metadata($post_id, $status) {
        return array(
            'status' => array(
                'label' => __('Status', 'arsol-pfw'),
                'value' => $this->format_status_display($status),
                'type' => 'badge',
                'class' => 'status-badge status-' . sanitize_html_class($status)
            ),
            'budget' => array(
                'label' => __('Proposed Budget', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'proposal_budget', true),
                'type' => 'currency'
            ),
            'timeline' => array(
                'label' => __('Timeline', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'proposal_timeline', true),
                'type' => 'text',
                'suffix' => __('weeks', 'arsol-pfw')
            ),
            'submitted_date' => array(
                'label' => __('Submitted', 'arsol-pfw'),
                'value' => get_the_date('Y-m-d H:i:s', $post_id),
                'type' => 'date',
                'format' => 'F j, Y'
            ),
            'expires_date' => array(
                'label' => __('Expires', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'proposal_expiry_date', true),
                'type' => 'date',
                'format' => 'F j, Y',
                'class' => 'expiry-date',
                'show_if' => array('status' => array('sent', 'pending-approval'))
            )
        );
    }

    /**
     * Get request metadata
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of metadata items
     */
    private function get_request_metadata($post_id, $status) {
        return array(
            'status' => array(
                'label' => __('Status', 'arsol-pfw'),
                'value' => $this->format_status_display($status),
                'type' => 'badge',
                'class' => 'status-badge status-' . sanitize_html_class($status)
            ),
            'budget_range' => array(
                'label' => __('Budget Range', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'request_budget_range', true),
                'type' => 'currency_array'
            ),
            'requested_date' => array(
                'label' => __('Requested', 'arsol-pfw'),
                'value' => get_the_date('Y-m-d H:i:s', $post_id),
                'type' => 'date',
                'format' => 'F j, Y'
            ),
            'urgency' => array(
                'label' => __('Urgency', 'arsol-pfw'),
                'value' => get_post_meta($post_id, 'request_urgency', true),
                'type' => 'badge',
                'class' => 'urgency-badge'
            ),
            'category' => array(
                'label' => __('Category', 'arsol-pfw'),
                'value' => $this->get_request_categories($post_id),
                'type' => 'list'
            )
        );
    }

    /**
     * Filter metadata by status conditions
     *
     * @param array $metadata Array of metadata items
     * @param string $current_status The current status
     * @return array Filtered metadata array
     */
    private function filter_metadata_by_status($metadata, $current_status) {
        return array_filter($metadata, function($meta) use ($current_status) {
            if (!isset($meta['show_if'])) {
                return true;
            }

            if (isset($meta['show_if']['status'])) {
                return in_array($current_status, $meta['show_if']['status']);
            }

            return true;
        });
    }

    /**
     * Render a single metadata item
     *
     * @param string $key The metadata key
     * @param array $meta The metadata configuration
     * @param string $post_type The post type
     * @param string $status The current status
     * @param int $post_id The post ID
     */
    private function render_metadata_item($key, $meta, $post_type, $status, $post_id) {
        if (empty($meta['value']) && $meta['type'] !== 'badge') {
            return;
        }

        $label = $meta['label'] ?? '';
        $value = $meta['value'] ?? '';
        $type = $meta['type'] ?? 'text';
        $class = isset($meta['class']) ? ' class="' . esc_attr($meta['class']) . '"' : '';

        echo '<div class="metadata-item metadata-' . esc_attr($key) . $class . '">';
        
        if (!empty($label)) {
            echo '<label class="metadata-label">' . esc_html($label) . ':</label>';
        }
        
        echo '<div class="metadata-value">';
        echo $this->format_metadata_value($value, $type, $meta);
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Format metadata value based on type
     *
     * @param mixed $value The value to format
     * @param string $type The value type
     * @param array $meta The full metadata configuration
     * @return string Formatted value
     */
    private function format_metadata_value($value, $type, $meta) {
        switch ($type) {
            case 'currency':
                if (empty($value)) return '—';
                return function_exists('wc_price') ? wc_price($value) : '$' . number_format($value, 2);

            case 'currency_array':
                if (empty($value) || !is_array($value)) return '—';
                $min = $value['min'] ?? 0;
                $max = $value['max'] ?? 0;
                if ($min && $max) {
                    $formatted_min = function_exists('wc_price') ? wc_price($min) : '$' . number_format($min, 2);
                    $formatted_max = function_exists('wc_price') ? wc_price($max) : '$' . number_format($max, 2);
                    return $formatted_min . ' - ' . $formatted_max;
                }
                return '—';

            case 'date':
                if (empty($value)) return '—';
                $format = $meta['format'] ?? 'F j, Y';
                $timestamp = is_numeric($value) ? $value : strtotime($value);
                return $timestamp ? date_i18n($format, $timestamp) : '—';

            case 'badge':
                $badge_class = isset($meta['class']) ? $meta['class'] : 'default-badge';
                return '<span class="' . esc_attr($badge_class) . '">' . esc_html($value) . '</span>';

            case 'link':
                if (empty($value)) return '—';
                $url = $meta['url'] ?? '#';
                $target = isset($meta['target']) ? ' target="' . esc_attr($meta['target']) . '"' : '';
                return '<a href="' . esc_url($url) . '"' . $target . '>' . esc_html($value) . '</a>';

            case 'list':
                if (empty($value)) return '—';
                if (!is_array($value)) return esc_html($value);
                return '<ul><li>' . implode('</li><li>', array_map('esc_html', $value)) . '</li></ul>';

            case 'text':
            default:
                if (empty($value)) return '—';
                $formatted = esc_html($value);
                if (isset($meta['suffix'])) {
                    $formatted .= ' ' . esc_html($meta['suffix']);
                }
                return $formatted;
        }
    }

    /**
     * Format status display
     *
     * @param string $status The status slug
     * @return string Formatted status
     */
    private function format_status_display($status) {
        $statuses = array(
            'active' => __('Active', 'arsol-pfw'),
            'completed' => __('Completed', 'arsol-pfw'),
            'on-hold' => __('On Hold', 'arsol-pfw'),
            'cancelled' => __('Cancelled', 'arsol-pfw'),
            'draft' => __('Draft', 'arsol-pfw'),
            'sent' => __('Sent', 'arsol-pfw'),
            'pending-approval' => __('Pending Approval', 'arsol-pfw'),
            'accepted' => __('Accepted', 'arsol-pfw'),
            'rejected' => __('Rejected', 'arsol-pfw'),
            'expired' => __('Expired', 'arsol-pfw'),
            'pending' => __('Pending', 'arsol-pfw'),
            'under-review' => __('Under Review', 'arsol-pfw'),
            'approved' => __('Approved', 'arsol-pfw')
        );

        return $statuses[$status] ?? ucfirst(str_replace(array('-', '_'), ' ', $status));
    }

    /**
     * Get request categories
     *
     * @param int $post_id The post ID
     * @return array Array of category names
     */
    private function get_request_categories($post_id) {
        $terms = get_the_terms($post_id, 'arsol-request-category');
        if (is_wp_error($terms) || empty($terms)) {
            return array();
        }

        return array_map(function($term) {
            return $term->name;
        }, $terms);
    }
} 