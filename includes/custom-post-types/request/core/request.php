<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Instance Management Class
 * 
 * Responsible for creating and managing individual request instances
 */
class Arsol_PFW_Request {

    /**
     * Request ID
     * 
     * @var int
     */
    protected $request_id = 0;

    /**
     * Request post object
     * 
     * @var WP_Post|null
     */
    protected $request = null;
    
    /**
     * Request data
     * 
     * @var array
     */
    protected $data = array();

    /**
     * Changes to be saved
     * 
     * @var array
     */
    protected $changes = array();
    
    /**
     * Data store instance
     * 
     * @var \Arsol_Projects_For_Woo\Data_Stores\Request_Data_Store
     */
    protected $data_store;
    
    /**
     * Setup initialization flag
     * 
     * @var bool
     */
    private static $setup_initialized = false;

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
     * @return string Current stage
     */
    public function get_stage() {
        // Use centralized stage manager
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage($this->request_id, 'request');
    }

    /**
     * Set request stage
     * 
     * @param string $stage Stage slug
     * @return bool Success status
     */
    public function set_stage($stage) {
        // Use centralized stage manager
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($this->request_id, 'request', $stage);
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
     * Get customer ID
     * 
     * @return int|null
     */
    public function get_customer_id() {
        return $this->get_meta('_arsol_pfw_customer_id');
    }

    /**
     * Set customer ID
     * 
     * @param int $customer_id Customer ID
     * @return bool Success status
     */
    public function set_customer_id($customer_id) {
        return $this->set_meta('_arsol_pfw_customer_id', (int) $customer_id);
    }

    /**
     * Get customer object
     * 
     * @return WP_User|null
     */
    public function get_customer() {
        $customer_id = $this->get_customer_id();
        return $customer_id ? get_user_by('id', $customer_id) : null;
    }

    /**
     * Get post author ID
     * 
     * @return int|null
     */
    public function get_post_author_id() {
        return $this->request ? $this->request->post_author : null;
    }

    /**
     * Set post author ID
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
     * Get post author object
     * 
     * @return WP_User|null
     */
    public function get_post_author() {
        $author_id = $this->get_post_author_id();
        return $author_id ? get_user_by('id', $author_id) : null;
    }

    /**
     * Get created via method
     * 
     * @return string
     */
    public function get_created_via() {
        return $this->get_meta('_arsol_pfw_created_via') ?: 'admin_creation';
    }

    /**
     * Set created via method
     * 
     * @param string $method Creation method
     * @return bool Success status
     */
    public function set_created_via($method) {
        return $this->set_meta('_arsol_pfw_created_via', sanitize_text_field($method));
    }

    /**
     * Update request data
     * 
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($data) {
        if (!$this->request_id) {
            return false;
        }

        $post_data = array('ID' => $this->request_id);
        
        if (isset($data['title'])) {
            $post_data['post_title'] = sanitize_text_field($data['title']);
        }
        
        if (isset($data['content'])) {
            $post_data['post_content'] = wp_kses_post($data['content']);
        }

        $result = wp_update_post($post_data);
        
        if (!is_wp_error($result)) {
            $this->request = get_post($this->request_id);
            return true;
        }
        
        return false;
    }

    /**
     * Delete request
     * 
     * @param bool $force_delete Force delete or move to trash
     * @return bool Success status
     */
    public function delete($force_delete = false) {
        if (!$this->request_id) {
            return false;
        }
        
        $result = wp_delete_post($this->request_id, $force_delete);
        
        if ($result) {
            $this->request_id = 0;
            $this->request = null;
            return true;
        }
        
        return false;
    }

    /**
     * Check if request exists
     * 
     * @return bool
     */
    public function exists() {
        return $this->request_id > 0 && $this->request !== null;
    }

    /**
     * Get post type
     * 
     * @return string
     */
    public static function get_post_type() {
        return 'arsol-pfw-request';
    }

    /**
     * Get stage taxonomy
     * 
     * @return string
     */
    public static function get_stage_taxonomy() {
        return 'arsol-pfw-request-stage';
    }

    /**
     * Find requests
     * 
     * @param array $args Query arguments
     * @return array Array of request objects
     */
    public static function find($args = array()) {
        $defaults = array(
            'post_type' => self::get_post_type(),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $args = wp_parse_args($args, $defaults);
        $query = new \WP_Query($args);
        
        $requests = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $requests[] = new self(get_post());
            }
        }
        wp_reset_postdata();
        
        return $requests;
    }

    /**
     * Get request name (alias for title)
     * 
     * @return string
     */
    public function get_name() {
        return $this->get_title();
    }

    /**
     * Set request title
     * 
     * @param string $title Title
     * @return bool Success status
     */
    public function set_title($title) {
        return $this->update(array('title' => $title));
    }

    /**
     * Get request description (alias for content)
     * 
     * @return string
     */
    public function get_description() {
        return $this->get_content();
    }

    /**
     * Set request description
     * 
     * @param string $description Description
     * @return bool Success status
     */
    public function set_description($description) {
        return $this->update(array('content' => $description));
    }

    /**
     * Get request budget
     * 
     * @return array Budget data
     */
    public function get_request_budget() {
        return $this->get_meta('_arsol_pfw_requested_project_budget') ?: array();
    }

    /**
     * Set request budget
     * 
     * @param array $budget Budget data
     * @return bool Success status
     */
    public function set_request_budget($budget) {
        return $this->set_meta('_arsol_pfw_requested_project_budget', $budget);
    }

    /**
     * Get request quotation
     * 
     * @return array Quotation data
     */
    public function get_request_quotation() {
        return $this->get_meta('_arsol_pfw_requested_project_quotation_line_items') ?: array();
    }

    /**
     * Set request quotation
     * 
     * @param array $quotation Quotation data
     * @return bool Success status
     */
    public function set_request_quotation($quotation) {
        return $this->set_meta('_arsol_pfw_requested_project_quotation_line_items', $quotation);
    }

    /**
     * Get request due date
     * 
     * @return string Due date
     */
    public function get_request_due_date() {
        return $this->get_meta('_arsol_pfw_requested_project_due_date');
    }

    /**
     * Set request due date
     * 
     * @param string $due_date Due date
     * @return bool Success status
     */
    public function set_request_due_date($due_date) {
        return $this->set_meta('_arsol_pfw_requested_project_due_date', sanitize_text_field($due_date));
    }

    /**
     * Get request start date
     * 
     * @return string Start date
     */
    public function get_request_start_date() {
        return $this->get_meta('_arsol_pfw_requested_project_start_date');
    }

    /**
     * Set request start date
     * 
     * @param string $start_date Start date
     * @return bool Success status
     */
    public function set_request_start_date($start_date) {
        return $this->set_meta('_arsol_pfw_requested_project_start_date', sanitize_text_field($start_date));
    }

    /**
     * Get request customer notice
     * 
     * @return string Customer notice
     */
    public function get_request_customer_notice() {
        return $this->get_meta('_arsol_pfw_request_customer_notice');
    }

    /**
     * Set request customer notice
     * 
     * @param string $customer_notice Customer notice
     * @return bool Success status
     */
    public function set_request_customer_notice($customer_notice) {
        return $this->set_meta('_arsol_pfw_request_customer_notice', wp_kses_post($customer_notice));
    }

    /**
     * Get request attachments
     * 
     * @return array Request attachments
     */
    public function get_request_attachments() {
        return $this->get_meta('_arsol_pfw_request_attachments') ?: array();
    }

    /**
     * Set request attachments
     * 
     * @param array $attachments Request attachments
     * @return bool Success status
     */
    public function set_request_attachments($attachments) {
        return $this->set_meta('_arsol_pfw_request_attachments', $attachments);
    }

    /**
     * Get converted to proposal flag
     * 
     * @return bool Converted to proposal
     */
    public function get_converted_to_proposal() {
        return (bool) $this->get_meta('_arsol_pfw_request_converted_to_proposal');
    }

    /**
     * Set converted to proposal flag
     * 
     * @param bool $converted Converted to proposal
     * @return bool Success status
     */
    public function set_converted_to_proposal($converted) {
        return $this->set_meta('_arsol_pfw_request_converted_to_proposal', (bool) $converted);
    }

    /**
     * Get request ID (for internal reference)
     * 
     * @return string Request ID
     */
    public function get_request_id() {
        return $this->get_meta('_arsol_pfw_request_id');
    }

    /**
     * Set request ID
     * 
     * @param string $request_id Request ID
     * @return bool Success status
     */
    public function set_request_id($request_id) {
        return $this->set_meta('_arsol_pfw_request_id', sanitize_text_field($request_id));
    }

    /**
     * Update request stage
     * 
     * @param string $new_stage New stage
     * @return bool Success status
     */
    public function update_stage($new_stage) {
        return $this->set_stage($new_stage);
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
     * Save request
     * 
     * @return bool Success status
     */
    public function save() {
        if (!$this->request_id) {
            return false;
        }

        // Save any pending changes
        if (!empty($this->changes)) {
            foreach ($this->changes as $key => $value) {
                $this->set_meta($key, $value);
            }
            $this->changes = array();
        }

        return true;
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

        // Load basic data
        $this->data = array(
            'id' => $this->request_id,
            'title' => $this->get_title(),
            'content' => $this->get_content(),
            'stage' => $this->get_stage(),
            'customer_id' => $this->get_customer_id(),
            'created_via' => $this->get_created_via(),
            'post_author_id' => $this->get_post_author_id(),
        );

        return true;
    }

    /**
     * Delete request with enhanced cleanup
     * 
     * @return bool Success status
     */
    public function delete_enhanced() {
        if (!$this->request_id) {
            return false;
        }

        // Delete all associated meta
        $meta_keys = array(
            '_arsol_pfw_customer_id',
            '_arsol_pfw_created_via',
            '_arsol_pfw_requested_project_budget',
            '_arsol_pfw_requested_project_quotation_line_items',
            '_arsol_pfw_requested_project_start_date',
            '_arsol_pfw_requested_project_due_date',
            '_arsol_pfw_request_customer_notice',
            '_arsol_pfw_request_attachments',
            '_arsol_pfw_request_converted_to_proposal',
            '_arsol_pfw_request_id',
        );

        foreach ($meta_keys as $meta_key) {
            delete_post_meta($this->request_id, $meta_key);
        }

        return $this->delete(true);
    }

    /**
     * Delete request meta
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
     * Get request property
     * 
     * @param string $prop Property name
     * @return mixed Property value
     */
    public function get_prop($prop) {
        return isset($this->data[$prop]) ? $this->data[$prop] : null;
    }

    /**
     * Set request property
     * 
     * @param string $prop Property name
     * @param mixed $value Property value
     */
    public function set_prop($prop, $value) {
        $this->data[$prop] = $value;
        $this->changes[$prop] = $value;
    }

    /**
     * Get changes
     * 
     * @return array Changes
     */
    public function get_changes() {
        return $this->changes;
    }

    /**
     * Get request status (alias for stage, WooCommerce compatibility)
     * 
     * @return string Current status
     */
    public function get_status() {
        return $this->get_stage();
    }

    /**
     * Set request status (alias for stage, WooCommerce compatibility)
     * 
     * @param string $status Status
     * @return bool Success status
     */
    public function set_status($status) {
        return $this->set_stage($status);
    }

    /**
     * Update request status (alias for stage, WooCommerce compatibility)
     * 
     * @param string $status New status
     * @return bool|WP_Error Success status or error
     */
    public function update_status($status) {
        return $this->update_stage($status);
    }

    /**
     * Get customer user ID (WooCommerce compatibility - same as customer_id for our entities)
     * 
     * @return int Customer user ID
     */
    public function get_customer_user_id() {
        return $this->get_customer_id();
    }

    /**
     * Set customer user ID (WooCommerce compatibility - same as customer_id for our entities)
     * 
     * @param int $customer_user_id Customer user ID
     * @return bool Success status
     */
    public function set_customer_user_id($customer_user_id) {
        return $this->set_customer_id($customer_user_id);
    }

    /**
     * Get date created
     * 
     * @return string Date created
     */
    public function get_date_created() {
        return $this->request ? $this->request->post_date : '';
    }

    /**
     * Set date created
     * 
     * @param string $date_created Date created
     * @return bool Success status
     */
    public function set_date_created($date_created) {
        if (!$this->request_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->request_id,
            'post_date' => $date_created
        ));
        
        if (!is_wp_error($result)) {
            $this->request = get_post($this->request_id);
            return true;
        }
        
        return false;
    }

    /**
     * Get date modified
     * 
     * @return string Date modified
     */
    public function get_date_modified() {
        return $this->request ? $this->request->post_modified : '';
    }

    /**
     * Set date modified
     * 
     * @param string $date_modified Date modified
     * @return bool Success status
     */
    public function set_date_modified($date_modified) {
        if (!$this->request_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->request_id,
            'post_modified' => $date_modified
        ));
        
        if (!is_wp_error($result)) {
            $this->request = get_post($this->request_id);
            return true;
        }
        
        return false;
    }

    /**
     * Get project budget (cross-entity access for historical data)
     * 
     * @return array|string Project budget
     */
    public function get_project_budget() {
        return $this->get_meta('_arsol_pfw_project_budget');
    }

    /**
     * Get proposal budget (cross-entity access for historical data)
     * 
     * @return array|string Proposal budget
     */
    public function get_proposal_budget() {
        return $this->get_meta('_arsol_pfw_proposal_budget');
    }

    /**
     * Get project start date (cross-entity access for historical data)
     * 
     * @return string Project start date
     */
    public function get_project_start_date() {
        return $this->get_meta('_arsol_pfw_project_start_date');
    }

    /**
     * Get project due date (cross-entity access for historical data)
     * 
     * @return string Project due date
     */
    public function get_project_due_date() {
        return $this->get_meta('_arsol_pfw_project_due_date');
    }

    /**
     * Get proposal start date (cross-entity access for historical data)
     * 
     * @return string Proposal start date
     */
    public function get_proposal_start_date() {
        return $this->get_meta('_arsol_pfw_proposal_start_date');
    }

    /**
     * Get proposal due date (cross-entity access for historical data)
     * 
     * @return string Proposal due date
     */
    public function get_proposal_due_date() {
        return $this->get_meta('_arsol_pfw_proposal_due_date');
    }

    /**
     * Get request customer notice (renamed for clarity)
     * 
     * @return string Customer notice
     */
    public function get_request_customer_notice() {
        return $this->get_meta('_arsol_pfw_request_customer_notice');
    }

    /**
     * Set request customer notice (renamed for clarity)
     * 
     * @param string $customer_notice Customer notice
     * @return bool Success status
     */
    public function set_request_customer_notice($customer_notice) {
        return $this->set_meta('_arsol_pfw_request_customer_notice', wp_kses_post($customer_notice));
    }

    /**
     * Get customer notice (legacy method for backward compatibility)
     * 
     * @return string Customer notice
     */
    public function get_customer_notice() {
        return $this->get_request_customer_notice();
    }

    /**
     * Set customer notice (legacy method for backward compatibility)
     * 
     * @param string $customer_notice Customer notice
     * @return bool Success status
     */
    public function set_customer_notice($customer_notice) {
        return $this->set_request_customer_notice($customer_notice);
    }
} 