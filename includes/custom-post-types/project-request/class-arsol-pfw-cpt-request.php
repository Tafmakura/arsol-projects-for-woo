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
class Arsol_PFW_Request {

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
     * Static flag to ensure setup only runs once
     * @var bool
     */
    private static $setup_initialized = false;

    /**
     * Data properties
     * @var array
     */
    protected $data = array();

    /**
     * Changes tracking
     * @var array
     */
    protected $changes = array();

    /**
     * Constructor
     * 
     * @param int|WP_Post $request Request ID or post object
     */
    public function __construct($request = null) {
        // Initialize setup components if not already done
        if (!self::$setup_initialized) {
            $this->initialize_setup();
            self::$setup_initialized = true;
        }

        if ($request) {
            $this->load_request($request);
        }
    }

    /**
     * Initialize setup and admin components
     */
    private function initialize_setup() {
        // All admin class instantiations are now handled in the main CPT setup file
        // This prevents duplicate instantiations and centralizes management
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

        // Load data from data store
        $this->read();

        return true;
    }

    /**
     * Create a new request instance
     * 
     * @param array $args Request creation arguments
     * @return Arsol_PFW_Request|false New request instance or false on failure
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
        if (!isset($args['tax_input'][self::get_stage_taxonomy()])) {
            $args['tax_input'][self::get_stage_taxonomy()] = 'pending-review';
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
     * Get request stage
     * 
     * @return string|null Current request stage slug
     */
    public function get_status() {
        // Use centralized stage manager
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage($this->request_id, 'request');
    }

    /**
     * Get request stage (convenience method for consistency with other CPT classes)
     * 
     * @return string|null Current request stage slug
     */
    public function get_stage() {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage($this->request_id, 'request');
    }

    /**
     * Set request stage
     * 
     * @param string $stage New stage slug
     * @return bool Success status
     */
    public function set_stage($stage) {
        // Use centralized stage manager
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($this->request_id, 'request', $stage);
    }

    /**
     * Get request budget
     * 
     * @return float Request budget
     */
    public function get_budget() {
        return $this->get_prop('budget');
    }

    /**
     * Set request budget
     * 
     * @param float $budget Request budget
     * @return bool Success status
     */
    public function set_budget($budget) {
        $this->set_prop('budget', $budget);
        return true;
    }

    /**
     * Get request due date
     * 
     * @return string Request due date
     */
    public function get_due_date() {
        return $this->get_prop('due_date');
    }

    /**
     * Set request due date
     * 
     * @param string $due_date Request due date
     * @return bool Success status
     */
    public function set_due_date($due_date) {
        return $this->set_prop('due_date', $due_date);
    }

    /**
     * Get request project lead
     * 
     * @return int|null Project lead ID
     */
    public function get_project_lead() {
        return $this->get_prop('project_lead');
    }

    /**
     * Set request project lead
     * 
     * @param int $lead_id Project lead ID
     * @return bool Success status
     */
    public function set_project_lead($lead_id) {
        $this->set_prop('project_lead', (int) $lead_id);
        return true;
    }

    /**
     * Get request start date
     * 
     * @return string Request start date
     */
    public function get_start_date() {
        return $this->get_prop('start_date');
    }

    /**
     * Set request start date
     * 
     * @param string $start_date Request start date
     * @return bool Success status
     */
    public function set_start_date($start_date) {
        $this->set_prop('start_date', $start_date);
        return true;
    }

    /**
     * Get parent project ID
     * 
     * @return int|null Parent project ID
     */
    public function get_parent_project_id() {
        return $this->get_prop('parent_project_id');
    }

    /**
     * Set parent project ID
     * 
     * @param int $parent_project_id Parent project ID
     * @return bool Success status
     */
    public function set_parent_project_id($parent_project_id) {
        $this->set_prop('parent_project_id', (int) $parent_project_id);
        return true;
    }

    /**
     * Get request timeline
     * 
     * @return array|null Timeline data
     */
    public function get_timeline() {
        return array(
            'start_date' => $this->get_prop('start_date'),
            'due_date' => $this->get_prop('due_date')
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
            $this->set_prop('start_date', $timeline['start_date']);
        }
        
        if (isset($timeline['due_date'])) {
            $this->set_prop('due_date', $timeline['due_date']);
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
                '_arsol_pfw_proposed_start_date' => $this->get_meta('_arsol_pfw_requested_start_date'),
                '_arsol_pfw_proposed_due_date' => $this->get_meta('_arsol_pfw_requested_due_date')
            )
        );

        $proposal_id = wp_insert_post($proposal_data);

        if (!is_wp_error($proposal_id) && $proposal_id) {
            // Set proposal to processing stage
            wp_set_post_terms($proposal_id, array('processing'), 'arsol-pfw-proposal-stage');
            
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
     * Get customer ID (from meta, like WooCommerce)
     * 
     * @return int Customer ID
     */
    public function get_customer_id() {
        return (int) $this->get_meta('_arsol_pfw_customer_id');
    }

    /**
     * Set customer ID (to meta, like WooCommerce)
     * 
     * @param int $customer_id Customer ID
     * @return bool Success status
     */
    public function set_customer_id($customer_id) {
        return $this->set_meta('_arsol_pfw_customer_id', (int) $customer_id);
    }

    /**
     * Get post author ID (who created the request) - from post_author
     * 
     * @return int Post author ID
     */
    public function get_post_author_id() {
        return $this->request ? (int) $this->request->post_author : 0;
    }

    /**
     * Set post author ID (who created the request) - updates post_author
     * 
     * @param int $post_author_id Post author ID
     * @return bool Success status
     */
    public function set_post_author_id($post_author_id) {
        if (!$this->request_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->request_id,
            'post_author' => (int) $post_author_id
        ));
        
        if (!is_wp_error($result)) {
            $this->request = get_post($this->request_id);
            return true;
        }
        
        return false;
    }

    /**
     * Get request post author (WP_User object) - from post_author
     * 
     * @return WP_User|null
     */
    public function get_post_author() {
        $post_author_id = $this->get_post_author_id();
        return $post_author_id ? get_userdata($post_author_id) : null;
    }

    /**
     * Get how the request was created
     * 
     * @return string Creation method
     */
    public function get_created_via() {
        return $this->get_meta('_arsol_pfw_created_via');
    }

    /**
     * Set how the request was created
     * 
     * @param string $method Creation method
     * @return bool Success status
     */
    public function set_created_via($method) {
        return $this->set_meta('_arsol_pfw_created_via', $method);
    }

    /**
     * Get request customer (WP_User object)
     * 
     * @return WP_User|null
     */
    public function get_customer() {
        $customer_id = $this->get_customer_id();
        return $customer_id ? get_userdata($customer_id) : null;
    }

    /**
     * Get request creator (WP_User object)
     * 
     * @return WP_User|null
     */
    public function get_creator() {
        $creator_id = $this->get_creator_id();
        return $creator_id ? get_userdata($creator_id) : null;
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
     * Get request stage taxonomy slug
     *
     * @return string
     */
    public function get_stage_taxonomy() {
        return 'arsol-pfw-request-stage';
    }

    /**
     * Find requests by criteria
     * 
     * @param array $args Query arguments
     * @return array Array of Arsol_PFW_Request instances
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

    // ===== CRUD Enhancement Methods =====

    /**
     * Get request name (alias for get_title)
     * 
     * @return string
     */
    public function get_name() {
        return $this->get_title();
    }

    /**
     * Set request title
     * 
     * @param string $title Request title
     * @return bool Success status
     */
    public function set_title($title) {
        $this->set_prop('name', $title);
        return true;
    }

    /**
     * Get request description
     * 
     * @return string Request description
     */
    public function get_description() {
        return $this->get_prop('description');
    }

    /**
     * Set request description
     * 
     * @param string $description Request description
     * @return bool Success status
     */
    public function set_description($description) {
        $this->set_prop('description', $description);
        return true;
    }

    /**
     * Update stage with hooks
     * 
     * @param string $new_stage New stage
     * @return bool|WP_Error Success status or error
     */
    public function update_stage($new_stage) {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::update_stage($this->request_id, 'request', $new_stage);
    }

    /**
     * Get available stages
     * 
     * @return array Available stages
     */
    public function get_available_stages() {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_available_stages('request');
    }

    /**
     * Approve request
     * 
     * @return bool|WP_Error Success status or error
     */
    public function approve() {
        return $this->update_stage('approved');
    }

    /**
     * Reject request
     * 
     * @param string $reason Rejection reason
     * @return bool|WP_Error Success status or error
     */
    public function reject($reason = '') {
        $result = $this->update_stage('rejected');
        
        if ($result && !is_wp_error($result) && $reason) {
            $this->set_meta('_rejection_reason', $reason);
        }
        
        return $result;
    }

    /**
     * Put request on hold
     * 
     * @return bool|WP_Error Success status or error
     */
    public function put_on_hold() {
        return $this->update_stage('on-hold');
    }

    /**
     * Resume request
     * 
     * @return bool|WP_Error Success status or error
     */
    public function resume() {
        return $this->update_stage('under-review');
    }

    /**
     * Save request
     * 
     * @return bool|WP_Error Success status or error
     */
    public function save() {
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Request_Data_Store();
        
        if ($this->request_id > 0) {
            return $data_store->update($this);
        } else {
            $result = $data_store->create($this);
            
            if (!is_wp_error($result)) {
                $this->request_id = $result;
                $this->request = get_post($result);
                // Load the data after creation
                $this->read();
            }
            
            return $result;
        }
    }

    /**
     * Read request data
     * 
     * @return bool Success status
     */
    public function read() {
        if (!$this->request_id) {
            return false;
        }
        
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Request_Data_Store();
        return $data_store->read($this);
    }

    /**
     * Delete request
     * 
     * @return bool Success status
     */
    public function delete() {
        if (!$this->request_id) {
            return false;
        }
        
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Request_Data_Store();
        return $data_store->delete($this);
    }

    /**
     * Delete meta value
     * 
     * @param string $key Meta key
     * @return bool Success status
     */
    public function delete_meta($key) {
        if (!$this->request_id) {
            return false;
        }
        
        return delete_post_meta($this->request_id, $key);
    }

    /**
     * Get property value
     * 
     * @param string $prop Property name
     * @return mixed Property value
     */
    public function get_prop($prop) {
        return isset($this->data[$prop]) ? $this->data[$prop] : null;
    }

    /**
     * Set property value
     * 
     * @param string $prop Property name
     * @param mixed $value Property value
     * @return bool Success status
     */
    public function set_prop($prop, $value) {
        if ($this->get_prop($prop) !== $value) {
            $this->changes[$prop] = $value;
            $this->data[$prop] = $value;
        }
        
        return true;
    }

    /**
     * Get changes
     * 
     * @return array Changes array
     */
    public function get_changes() {
        return $this->changes;
    }
}
