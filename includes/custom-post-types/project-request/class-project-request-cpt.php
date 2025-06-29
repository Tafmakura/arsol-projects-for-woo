<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Request Instance Management Class
 * 
 * Responsible for creating and managing individual project request instances
 */
class Project_Request_CPT {

    /**
     * Request ID
     * @var int
     */
    private $request_id;

    /**
     * Request post object
     * @var WP_Post
     */
    private $request;

    /**
     * Constructor
     * 
     * @param int|WP_Post $request Request ID or post object
     */
    public function __construct($request = null) {
        if ($request) {
            $this->load_request($request);
        }
    }

    /**
     * Load request data
     * 
     * @param int|WP_Post $request Request ID or post object
     * @return bool Success status
     */
    private function load_request($request) {
        if (is_numeric($request)) {
            $this->request_id = (int) $request;
            $this->request = get_post($this->request_id);
        } elseif ($request instanceof \WP_Post) {
            $this->request = $request;
            $this->request_id = $request->ID;
        }

        // Validate that this is actually a request
        if (!$this->request || $this->request->post_type !== self::get_post_type()) {
            return false;
        }

        return true;
    }

    /**
     * Create a new request instance
     * 
     * @param array $args Request creation arguments
     * @return Project_Request_CPT|false New request instance or false on failure
     */
    public static function create($args = array()) {
        $defaults = array(
            'post_type' => self::get_post_type(),
            'post_status' => 'publish',
            'meta_input' => array()
        );

        $args = wp_parse_args($args, $defaults);

        // Set default status
        if (!isset($args['tax_input'])) {
            $args['tax_input'] = array();
        }
        if (!isset($args['tax_input'][self::get_status_taxonomy()])) {
            $args['tax_input'][self::get_status_taxonomy()] = 'pending-review';
        }

        $request_id = wp_insert_post($args);

        if (is_wp_error($request_id) || !$request_id) {
            return false;
        }

        return new self($request_id);
    }

    /**
     * Get request ID
     * 
     * @return int|null
     */
    public function get_id() {
        return $this->request_id;
    }

    /**
     * Get request post object
     * 
     * @return WP_Post|null
     */
    public function get_post() {
        return $this->request;
    }

    /**
     * Get request title
     * 
     * @return string
     */
    public function get_title() {
        return $this->request ? $this->request->post_title : '';
    }

    /**
     * Get request content
     * 
     * @return string
     */
    public function get_content() {
        return $this->request ? $this->request->post_content : '';
    }

    /**
     * Get request status
     * 
     * @return string|null Status slug
     */
    public function get_status() {
        if (!$this->request_id) {
            return null;
        }

        $terms = wp_get_post_terms($this->request_id, self::get_status_taxonomy());
        return !empty($terms) && !is_wp_error($terms) ? $terms[0]->slug : null;
    }

    /**
     * Set request status
     * 
     * @param string $status Status slug
     * @return bool Success status
     */
    public function set_status($status) {
        if (!$this->request_id) {
            return false;
        }

        $old_status = $this->get_status();
        $result = wp_set_post_terms($this->request_id, array($status), self::get_status_taxonomy());
        
        if (!is_wp_error($result)) {
            // Trigger status change action
            do_action('arsol_request_status_changed', $this->request_id, $old_status, $status, $this->request->post_author);
            return true;
        }

        return false;
    }

    /**
     * Get request budget
     * 
     * @return array|null Budget data
     */
    public function get_budget() {
        return $this->get_meta('_arsol_pfw_request_budget');
    }

    /**
     * Set request budget
     * 
     * @param array $budget Budget data
     * @return bool Success status
     */
    public function set_budget($budget) {
        return $this->set_meta('_arsol_pfw_request_budget', $budget);
    }

    /**
     * Get request timeline
     * 
     * @return array|null Timeline data
     */
    public function get_timeline() {
        return array(
            'start_date' => $this->get_meta('_arsol_pfw_request_start_date'),
            'delivery_date' => $this->get_meta('_arsol_pfw_request_delivery_date')
        );
    }

    /**
     * Set request timeline
     * 
     * @param array $timeline Timeline data
     * @return bool Success status
     */
    public function set_timeline($timeline) {
        $success = true;
        
        if (isset($timeline['start_date'])) {
            $success = $success && $this->set_meta('_arsol_pfw_request_start_date', $timeline['start_date']);
        }
        
        if (isset($timeline['delivery_date'])) {
            $success = $success && $this->set_meta('_arsol_pfw_request_delivery_date', $timeline['delivery_date']);
        }

        return $success;
    }

    /**
     * Convert request to proposal
     * 
     * @return int|false Proposal ID or false on failure
     */
    public function convert_to_proposal() {
        if (!$this->exists() || $this->get_status() !== 'approved') {
            return false;
        }

        // Create proposal with request data
        $proposal_data = array(
            'post_title' => $this->get_title(),
            'post_content' => $this->get_content(),
            'post_author' => $this->request->post_author,
            'post_type' => 'arsol-pfw-proposal',
            'post_status' => 'publish',
            'meta_input' => array(
                '_arsol_pfw_source_request_id' => $this->request_id,
                '_arsol_pfw_proposal_budget' => $this->get_budget(),
                '_arsol_pfw_proposal_start_date' => $this->get_meta('_arsol_pfw_request_start_date'),
                '_arsol_pfw_proposal_delivery_date' => $this->get_meta('_arsol_pfw_request_delivery_date')
            )
        );

        $proposal_id = wp_insert_post($proposal_data);

        if (!is_wp_error($proposal_id) && $proposal_id) {
            // Set proposal to processing status
            wp_set_post_terms($proposal_id, array('processing'), 'arsol-pfw-proposal-status');
            
            do_action('arsol_request_converted_to_proposal', $this->request_id, $proposal_id);
            return $proposal_id;
        }

        return false;
    }

    /**
     * Get request meta value
     * 
     * @param string $key Meta key
     * @param bool $single Return single value
     * @return mixed Meta value
     */
    public function get_meta($key, $single = true) {
        if (!$this->request_id) {
            return $single ? '' : array();
        }

        return get_post_meta($this->request_id, $key, $single);
    }

    /**
     * Set request meta value
     * 
     * @param string $key Meta key
     * @param mixed $value Meta value
     * @return bool Success status
     */
    public function set_meta($key, $value) {
        if (!$this->request_id) {
            return false;
        }

        return update_post_meta($this->request_id, $key, $value);
    }

    /**
     * Get request customer
     * 
     * @return WP_User|null
     */
    public function get_customer() {
        if (!$this->request || !$this->request->post_author) {
            return null;
        }

        return get_userdata($this->request->post_author);
    }

    /**
     * Check if request exists and is valid
     * 
     * @return bool
     */
    public function exists() {
        return $this->request && $this->request_id && $this->request->post_type === self::get_post_type();
    }

    /**
     * Get request post type slug
     * 
     * @return string
     */
    public static function get_post_type() {
        return 'arsol-pfw-request';
    }

    /**
     * Get request status taxonomy slug
     * 
     * @return string
     */
    public static function get_status_taxonomy() {
        return 'arsol-pfw-request-status';
    }

    /**
     * Find requests by criteria
     * 
     * @param array $args Query arguments
     * @return array Array of Project_Request_CPT instances
     */
    public static function find($args = array()) {
        $defaults = array(
            'post_type' => self::get_post_type(),
            'post_status' => 'publish',
            'posts_per_page' => -1
        );

        $args = wp_parse_args($args, $defaults);
        $posts = get_posts($args);

        $requests = array();
        foreach ($posts as $post) {
            $requests[] = new self($post);
        }

        return $requests;
    }
}
