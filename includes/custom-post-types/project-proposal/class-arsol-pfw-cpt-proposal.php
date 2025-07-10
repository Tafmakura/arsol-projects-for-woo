<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Proposal Instance Management Class
 * 
 * Responsible for creating and managing individual project proposal instances
 */
class Project_Proposal_CPT {

    /**
     * Proposal ID
     * @var int
     */
    private $proposal_id;

    /**
     * Proposal post object
     * @var WP_Post
     */
    private $proposal;

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
     * @param int|WP_Post $proposal Proposal ID or post object
     */
    public function __construct($proposal = null) {
        // Initialize setup components if not already done
        if (!self::$setup_initialized) {
            $this->initialize_setup();
            self::$setup_initialized = true;
        }

        if ($proposal) {
            $this->load_proposal($proposal);
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
     * Load proposal data
     * 
     * @param int|WP_Post $proposal Proposal ID or post object
     * @return bool Success status
     */
    private function load_proposal($proposal) {
        if (is_numeric($proposal)) {
            $this->proposal_id = (int) $proposal;
            $this->proposal = get_post($this->proposal_id);
        } elseif ($proposal instanceof \WP_Post) {
            $this->proposal = $proposal;
            $this->proposal_id = $proposal->ID;
        }

        // Validate that this is actually a proposal
        if (!$this->proposal || $this->proposal->post_type !== self::get_post_type()) {
            return false;
        }

        // Load data from data store
        $this->read();

        return true;
    }

    /**
     * Create a new proposal instance
     * 
     * @param array $args Proposal creation arguments
     * @return Project_Proposal_CPT|false New proposal instance or false on failure
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
            $args['tax_input'][self::get_stage_taxonomy()] = 'processing';
        }

        $proposal_id = wp_insert_post($args);

        if (is_wp_error($proposal_id) || !$proposal_id) {
            return false;
        }

        return new self($proposal_id);
    }

    /**
     * Get proposal ID
     * 
     * @return int|null
     */
    public function get_id() {
        return $this->proposal_id;
    }

    /**
     * Get proposal post object
     * 
     * @return WP_Post|null
     */
    public function get_post() {
        return $this->proposal;
    }

    /**
     * Get proposal title
     * 
     * @return string
     */
    public function get_title() {
        return $this->proposal ? $this->proposal->post_title : '';
    }

    /**
     * Get proposal content
     * 
     * @return string
     */
    public function get_content() {
        return $this->proposal ? $this->proposal->post_content : '';
    }

    /**
     * Get stage
     * 
     * @return string Current stage
     */
    public function get_stage() {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage($this->proposal_id, 'proposal');
    }

    /**
     * Set stage
     * 
     * @param string $stage New stage
     * @return bool Success status
     */
    public function set_stage($stage) {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($this->proposal_id, 'proposal', $stage);
    }

    /**
     * Get proposal budget
     * 
     * @return array|null Budget data
     */
    public function get_budget() {
        return $this->get_prop('budget');
    }

    /**
     * Set proposal budget
     * 
     * @param array $budget Budget data
     * @return bool Success status
     */
    public function set_budget($budget) {
        $this->set_prop('budget', $budget);
        return true;
    }

    /**
     * Get proposal timeline
     * 
     * @return array Timeline data
     */
    public function get_timeline() {
        return array(
            'start_date' => $this->get_prop('start_date'),
            'delivery_date' => $this->get_meta('_arsol_pfw_proposal_delivery_date'),
            'expiration_date' => $this->get_meta('_arsol_pfw_proposal_expiration_date')
        );
    }

    /**
     * Set proposal timeline
     * 
     * @param array $timeline Timeline data
     * @return bool Success status
     */
    public function set_timeline($timeline) {
        $success = true;
        
        if (isset($timeline['start_date'])) {
            $this->set_prop('start_date', $timeline['start_date']);
        }
        
        if (isset($timeline['delivery_date'])) {
            $success = $success && $this->set_meta('_arsol_pfw_proposal_delivery_date', $timeline['delivery_date']);
        }
        
        if (isset($timeline['expiration_date'])) {
            $success = $success && $this->set_meta('_arsol_pfw_proposal_expiration_date', $timeline['expiration_date']);
        }

        return $success;
    }

    /**
     * Get source request ID
     * 
     * @return int|null
     */
    public function get_source_request_id() {
        return $this->get_meta('_arsol_pfw_source_request_id');
    }

    /**
     * Get project lead
     * 
     * @return int|null Project lead ID
     */
    public function get_project_lead() {
        return $this->get_prop('project_lead');
    }

    /**
     * Set project lead
     * 
     * @param int $user_id User ID
     * @return bool Success status
     */
    public function set_project_lead($user_id) {
        $this->set_prop('project_lead', (int) $user_id);
        return true;
    }

    /**
     * Get proposal start date
     * 
     * @return string Proposal start date
     */
    public function get_start_date() {
        return $this->get_prop('start_date');
    }

    /**
     * Set proposal start date
     * 
     * @param string $start_date Proposal start date
     * @return bool Success status
     */
    public function set_start_date($start_date) {
        $this->set_prop('start_date', $start_date);
        return true;
    }

    /**
     * Approve proposal and convert to project
     * 
     * @return int|false Project ID or false on failure
     */
    public function approve() {
        if (!$this->exists() || $this->get_stage() !== 'pending-approval') {
            return false;
        }

        // Update status to approved
        $this->set_stage('approved');

        // Create project from proposal
        $project_data = array(
            'post_title' => $this->get_title(),
            'post_content' => $this->get_content(),
            'post_author' => $this->proposal->post_author,
            'post_type' => 'arsol-pfw-project',
            'post_status' => 'publish',
            'meta_input' => array(
                '_arsol_pfw_source_proposal_id' => $this->proposal_id,
                '_arsol_pfw_project_budget' => $this->get_budget(),
                '_arsol_pfw_project_start_date' => $this->get_meta('_arsol_pfw_proposal_start_date'),
                '_arsol_pfw_project_delivery_date' => $this->get_meta('_arsol_pfw_proposal_delivery_date'),
                '_arsol_pfw_project_lead' => $this->get_meta('_arsol_pfw_proposal_project_lead')
            )
        );

        $project_id = wp_insert_post($project_data);

        if (!is_wp_error($project_id) && $project_id) {
            // Set project to not-started status
            wp_set_post_terms($project_id, array('not-started'), 'arsol-pfw-project-stage');
            
            do_action('arsol_proposal_approved_project_created', $project_id, $this->proposal_id, $this->proposal->post_author, $this->get_meta('_arsol_pfw_proposal_project_lead'));
            return $project_id;
        }

        return false;
    }

    /**
     * Reject proposal
     * 
     * @param string $reason Rejection reason
     * @return bool Success status
     */
    public function reject($reason = '') {
        if (!$this->exists()) {
            return false;
        }

        $success = $this->set_stage('rejected');
        
        if ($success && $reason) {
            $this->set_meta('_arsol_pfw_proposal_rejection_reason', $reason);
        }

        return $success;
    }

    /**
     * Get proposal meta value
     * 
     * @param string $key Meta key
     * @param bool $single Return single value
     * @return mixed Meta value
     */
    public function get_meta($key, $single = true) {
        if (!$this->proposal_id) {
            return $single ? '' : array();
        }

        return get_post_meta($this->proposal_id, $key, $single);
    }

    /**
     * Set proposal meta value
     * 
     * @param string $key Meta key
     * @param mixed $value Meta value
     * @return bool Success status
     */
    public function set_meta($key, $value) {
        if (!$this->proposal_id) {
            return false;
        }

        return update_post_meta($this->proposal_id, $key, $value);
    }

    /**
     * Get proposal customer
     * 
     * @return WP_User|null
     */
    public function get_customer() {
        if (!$this->proposal || !$this->proposal->post_author) {
            return null;
        }

        return get_userdata($this->proposal->post_author);
    }

    /**
     * Check if proposal exists and is valid
     * 
     * @return bool
     */
    public function exists() {
        return $this->proposal && $this->proposal_id && $this->proposal->post_type === self::get_post_type();
    }

    /**
     * Get proposal post type slug
     * 
     * @return string
     */
    public static function get_post_type() {
        return 'arsol-pfw-proposal';
    }

    /**
     * Get the taxonomy used for proposal stages
     *
     * @return string
     */
    public function get_stage_taxonomy() {
        return 'arsol-pfw-proposal-stage';
    }

    /**
     * Find proposals by criteria
     * 
     * @param array $args Query arguments
     * @return array Array of Project_Proposal_CPT instances
     */
    public static function find($args = array()) {
        $defaults = array(
            'post_type' => self::get_post_type(),
            'post_status' => 'publish',
            'posts_per_page' => -1
        );

        $args = wp_parse_args($args, $defaults);
        $posts = get_posts($args);

        $proposals = array();
        foreach ($posts as $post) {
            $proposals[] = new self($post);
        }

        return $proposals;
    }

    // ===== CRUD Enhancement Methods =====

    /**
     * Get proposal name (alias for get_title)
     * 
     * @return string
     */
    public function get_name() {
        return $this->get_title();
    }

    /**
     * Set proposal name
     * 
     * @param string $name Proposal name
     * @return bool Success status
     */
    public function set_name($name) {
        $this->set_prop('name', $name);
        return true;
    }

    /**
     * Get customer ID
     * 
     * @return int Customer ID
     */
    public function get_customer_id() {
        return $this->proposal ? (int) $this->proposal->post_author : 0;
    }

    /**
     * Set customer ID
     * 
     * @param int $customer_id Customer ID
     * @return bool Success status
     */
    public function set_customer_id($customer_id) {
        $this->set_prop('customer_id', (int) $customer_id);
        return true;
    }

    /**
     * Get quotation
     * 
     * @return array Quotation data
     */
    public function get_quotation() {
        return $this->get_meta('_arsol_pfw_proposal_quotation');
    }

    /**
     * Set quotation
     * 
     * @param array $quotation Quotation data
     * @return bool Success status
     */
    public function set_quotation($quotation) {
        $this->set_prop('quotation', $quotation);
        return true;
    }



    /**
     * Update stage with hooks
     * 
     * @param string $new_stage New stage
     * @return bool|WP_Error Success status or error
     */
    public function update_stage($new_stage) {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::update_stage($this->proposal_id, 'proposal', $new_stage);
    }

    /**
     * Get available stages
     * 
     * @return array Available stages
     */
    public function get_available_stages() {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_available_stages('proposal');
    }

    /**
     * Approve proposal (enhanced)
     * 
     * @return bool|WP_Error Success status or error
     */
    public function approve_enhanced() {
        return $this->update_stage('approved');
    }

    /**
     * Reject proposal (enhanced)
     * 
     * @param string $reason Rejection reason
     * @return bool|WP_Error Success status or error
     */
    public function reject_enhanced($reason = '') {
        $result = $this->update_stage('rejected');
        
        if ($result && !is_wp_error($result) && $reason) {
            $this->set_meta('_rejection_reason', $reason);
        }
        
        return $result;
    }

    /**
     * Mark proposal as expired
     * 
     * @return bool|WP_Error Success status or error
     */
    public function mark_expired() {
        return $this->update_stage('expired');
    }

    /**
     * Extend deadline
     * 
     * @param string $new_deadline New deadline
     * @return bool Success status
     */
    public function extend_deadline($new_deadline) {
        return $this->set_meta('_arsol_pfw_proposal_expiration_date', $new_deadline);
    }

    /**
     * Save proposal
     * 
     * @return bool|WP_Error Success status or error
     */
    public function save() {
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Proposal_Data_Store();
        
        if ($this->proposal_id > 0) {
            return $data_store->update($this);
        } else {
            $result = $data_store->create($this);
            
            if (!is_wp_error($result)) {
                $this->proposal_id = $result;
                $this->proposal = get_post($result);
            }
            
            return $result;
        }
    }

    /**
     * Read proposal data
     * 
     * @return bool Success status
     */
    public function read() {
        if (!$this->proposal_id) {
            return false;
        }
        
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Proposal_Data_Store();
        return $data_store->read($this);
    }

    /**
     * Delete proposal
     * 
     * @return bool Success status
     */
    public function delete() {
        if (!$this->proposal_id) {
            return false;
        }
        
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Proposal_Data_Store();
        return $data_store->delete($this);
    }

    /**
     * Delete meta value
     * 
     * @param string $key Meta key
     * @return bool Success status
     */
    public function delete_meta($key) {
        if (!$this->proposal_id) {
            return false;
        }
        
        return delete_post_meta($this->proposal_id, $key);
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
