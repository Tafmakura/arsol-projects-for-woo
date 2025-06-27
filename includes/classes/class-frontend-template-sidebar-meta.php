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
        $metadata = array();
        
        // Customer
        $post = get_post($post_id);
        if ($post && $post->post_author) {
            $customer = get_userdata($post->post_author);
            if ($customer) {
                $metadata['customer'] = array(
                    'label' => __('Customer', 'arsol-pfw'),
                    'value' => $customer->display_name,
                    'type' => 'text'
                );
            }
        }
        
        // Project Lead
        $lead_id = get_post_meta($post_id, '_arsol_pfw_project_lead', true);
        if (!empty($lead_id)) {
            $lead = get_userdata($lead_id);
            if ($lead) {
                $metadata['project_lead'] = array(
                    'label' => __('Project Lead', 'arsol-pfw'),
                    'value' => $lead->display_name,
                    'type' => 'text'
                );
            }
        }
        
        // Start Date
        $start_date = get_post_meta($post_id, '_arsol_pfw_project_start_date', true);
        if (!empty($start_date)) {
            $metadata['start_date'] = array(
                'label' => __('Start Date', 'arsol-pfw'),
                'value' => $start_date,
                'type' => 'date'
            );
        }
        
        // Due Date
        $due_date = get_post_meta($post_id, '_arsol_pfw_project_due_date', true);
        if (!empty($due_date)) {
            $metadata['due_date'] = array(
                'label' => __('Due Date', 'arsol-pfw'),
                'value' => $due_date,
                'type' => 'date'
            );
        }
        
        return $metadata;
    }

    /**
     * Get proposal metadata
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of metadata items
     */
    private function get_proposal_metadata($post_id, $status) {
        $metadata = array();
        
        // Customer
        $post = get_post($post_id);
        if ($post && $post->post_author) {
            $customer = get_userdata($post->post_author);
            if ($customer) {
                $metadata['customer'] = array(
                    'label' => __('Customer', 'arsol-pfw'),
                    'value' => $customer->display_name,
                    'type' => 'text'
                );
            }
        }
        
        // Project Lead
        $lead_id = get_post_meta($post_id, '_arsol_pfw_proposal_project_lead', true);
        if (!empty($lead_id)) {
            $lead = get_userdata($lead_id);
            if ($lead) {
                $metadata['project_lead'] = array(
                    'label' => __('Project Lead', 'arsol-pfw'),
                    'value' => $lead->display_name,
                    'type' => 'text'
                );
            }
        }
        
        // Budget
        $budget = get_post_meta($post_id, '_arsol_pfw_proposal_budget_onetime_amount', true);
        if (!empty($budget)) {
            if (is_array($budget) && isset($budget['amount'])) {
                $metadata['budget'] = array(
                    'label' => __('Budget', 'arsol-pfw'),
                    'value' => wc_price($budget['amount'], array('currency' => $budget['currency'] ?? get_woocommerce_currency())),
                    'type' => 'currency'
                );
            }
        }
        
        // Start Date
        $start_date = get_post_meta($post_id, '_arsol_pfw_proposal_start_date', true);
        if (!empty($start_date)) {
            $metadata['start_date'] = array(
                'label' => __('Start Date', 'arsol-pfw'),
                'value' => $start_date,
                'type' => 'date'
            );
        }
        
        // Delivery Date
        $delivery_date = get_post_meta($post_id, '_arsol_pfw_proposal_delivery_date', true);
        if (!empty($delivery_date)) {
            $metadata['delivery_date'] = array(
                'label' => __('Delivery Date', 'arsol-pfw'),
                'value' => $delivery_date,
                'type' => 'date'
            );
        }
        
        // Expiration Date
        $expiration_date = get_post_meta($post_id, '_arsol_pfw_proposal_expiration_date', true);
        if (!empty($expiration_date)) {
            $metadata['expiration_date'] = array(
                'label' => __('Expiration Date', 'arsol-pfw'),
                'value' => $expiration_date,
                'type' => 'date'
            );
        }
        
        return $metadata;
    }

    /**
     * Get request metadata
     *
     * @param int $post_id The post ID
     * @param string $status The current status
     * @return array Array of metadata items
     */
    private function get_request_metadata($post_id, $status) {
        $metadata = array();
        
        // Budget
        $budget = get_post_meta($post_id, '_arsol_pfw_request_budget', true);
        if (!empty($budget)) {
            if (is_array($budget) && isset($budget['amount'])) {
                $metadata['budget'] = array(
                    'label' => __('Budget', 'arsol-pfw'),
                    'value' => wc_price($budget['amount'], array('currency' => $budget['currency'] ?? get_woocommerce_currency())),
                    'type' => 'currency'
                );
            }
        }
        
        // Start Date
        $start_date = get_post_meta($post_id, '_arsol_pfw_request_start_date', true);
        if (!empty($start_date)) {
            $metadata['start_date'] = array(
                'label' => __('Requested Start Date', 'arsol-pfw'),
                'value' => $start_date,
                'type' => 'date'
            );
        }
        
        // Delivery Date
        $delivery_date = get_post_meta($post_id, '_arsol_pfw_request_delivery_date', true);
        if (!empty($delivery_date)) {
            $metadata['delivery_date'] = array(
                'label' => __('Requested Delivery Date', 'arsol-pfw'),
                'value' => $delivery_date,
                'type' => 'date'
            );
        }
        
        return $metadata;
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

        $classes = array('metadata-item', 'metadata-' . $key);
        if (!empty($meta['class'])) {
            $classes[] = $meta['class'];
        }

        echo '<div class="' . esc_attr(implode(' ', $classes)) . '">';
        echo '<strong>' . esc_html($meta['label']) . ':</strong> ';
        echo $this->format_metadata_value($meta['value'], $meta['type'], $meta);
        echo '</div>';
    }

    /**
     * Format metadata value based on type
     *
     * @param mixed $value The raw value
     * @param string $type The value type
     * @param array $meta The metadata configuration
     * @return string Formatted value
     */
    private function format_metadata_value($value, $type, $meta) {
        switch ($type) {
            case 'currency':
                return wp_kses_post($value);
                
            case 'date':
                if (!empty($value)) {
                    $format = isset($meta['format']) ? $meta['format'] : get_option('date_format');
                    return esc_html(date_i18n($format, strtotime($value)));
                }
                return '';
                
            case 'badge':
                $badge_class = isset($meta['class']) ? $meta['class'] : 'badge';
                return '<span class="' . esc_attr($badge_class) . '">' . esc_html($value) . '</span>';
                
            case 'text':
            default:
                return esc_html($value);
        }
    }
} 