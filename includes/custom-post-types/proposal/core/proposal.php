<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Proposal Instance Management Class
 * 
 * Responsible for creating and managing individual proposal instances
 */
class Arsol_PFW_Proposal {

    /**
     * Proposal ID
     * 
     * @var int
     */
    protected $proposal_id = 0;

    /**
     * Proposal post object
     * 
     * @var WP_Post|null
     */
    protected $proposal = null;
    
    /**
     * Proposal data
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
     * @var \Arsol_Projects_For_Woo\Data_Stores\Proposal_Data_Store
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
     * @return Arsol_PFW_Proposal|false New proposal instance or false on failure
     */
    public static function create($args = array()) {
        $defaults = array(
            'post_type' => self::get_post_type(),
            'post_status' => 'publish',
            'meta_input' => array()
        );

        $args = wp_parse_args($args, $defaults);

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
     * Get proposal stage entity (internal use)
     *
     * @return \Arsol_Projects_For_Woo\Taxonomies\Stages\Proposal_Stage
     */
    private function get_stage_entity() {
        return new \Arsol_Projects_For_Woo\Taxonomies\Stages\Proposal_Stage($this->proposal_id);
    }

    /**
     * Get proposal stage
     *
     * @return string Current stage
     */
    public function get_stage() {
        return $this->get_stage_entity()->get_stage();
    }

    /**
     * Set proposal stage
     *
     * @param string $stage Stage slug
     * @return bool Success status
     */
    public function set_stage($stage) {
        return $this->get_stage_entity()->set_stage($stage);
    }

    /**
     * Update proposal stage with optional notes
     *
     * @param string $new_stage New stage slug
     * @param string $note Optional note about the stage change
     * @return bool Success status
     */
    public function update_stage($new_stage, $note = '') {
        return $this->get_stage_entity()->update_stage($new_stage, $note);
    }

    /**
     * Get proposal stage label
     *
     * @return string Human-readable stage label
     */
    public function get_stage_label() {
        return $this->get_stage_entity()->get_stage_label();
    }

    /**
     * Get proposal stage notes
     *
     * @return string Stage notes
     */
    public function get_stage_notes() {
        return $this->get_stage_entity()->get_stage_notes();
    }

    /**
     * Set proposal stage notes
     *
     * @param string $notes Stage notes
     * @return bool Success status
     */
    public function set_stage_notes($notes) {
        return $this->get_stage_entity()->set_stage_notes($notes);
    }

    /**
     * Get proposal stage history
     *
     * @return array Array of stage change history
     */
    public function get_stage_history() {
        return $this->get_stage_entity()->get_stage_history();
    }

    /**
     * Get allowed stage transitions
     *
     * @return array Array of allowed stage transitions
     */
    public function get_allowed_stage_transitions() {
        return $this->get_stage_entity()->get_allowed_transitions();
    }

    /**
     * Check if stage transition is allowed
     *
     * @param string $new_stage Stage to transition to
     * @return bool True if transition is allowed
     */
    public function can_transition_to($new_stage) {
        return $this->get_stage_entity()->can_transition_to($new_stage);
    }

    /**
     * Check if current stage is final
     *
     * @return bool true if this is a final stage
     */
    public function is_final_stage() {
        return $this->get_stage_entity()->is_final_stage();
    }

    /**
     * Check if current stage is initial
     *
     * @return bool true this is an initial stage
     */
    public function is_initial_stage() {
        return $this->get_stage_entity()->is_initial_stage();
    }

    /**
     * Get available stages
     *
     * @return array Array of available stages
     */
    public function get_available_stages() {
        return $this->get_stage_entity()->get_available_stages();
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
        return $this->proposal ? $this->proposal->post_author : null;
    }

    /**
     * Set post author ID
     * 
     * @param int $post_author_id Post author ID
     * @return bool Success status
     */
    public function set_post_author_id($post_author_id) {
        if (!$this->proposal_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->proposal_id,
            'post_author' => (int) $post_author_id
        ));
        
        if (!is_wp_error($result)) {
            $this->proposal = get_post($this->proposal_id);
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
     * Update proposal data
     * 
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($data) {
        if (!$this->proposal_id) {
            return false;
        }

        $post_data = array('ID' => $this->proposal_id);
        
        if (isset($data['title'])) {
            $post_data['post_title'] = sanitize_text_field($data['title']);
        }
        
        if (isset($data['content'])) {
            $post_data['post_content'] = wp_kses_post($data['content']);
        }

        $result = wp_update_post($post_data);
        
        if (!is_wp_error($result)) {
            $this->proposal = get_post($this->proposal_id);
            return true;
        }
        
        return false;
    }

    /**
     * Delete proposal
     * 
     * @param bool $force_delete Force delete or move to trash
     * @return bool Success status
     */
    public function delete($force_delete = false) {
        if (!$this->proposal_id) {
            return false;
        }
        
        $result = wp_delete_post($this->proposal_id, $force_delete);
        
        if ($result) {
            $this->proposal_id = 0;
            $this->proposal = null;
            return true;
        }
        
        return false;
    }

    /**
     * Check if proposal exists
     * 
     * @return bool
     */
    public function exists() {
        return $this->proposal_id > 0 && $this->proposal !== null;
    }

    /**
     * Get post type
     * 
     * @return string
     */
    public static function get_post_type() {
        return 'arsol-pfw-proposal';
    }

    /**
     * Get stage taxonomy
     * 
     * @return string
     */
    public static function get_stage_taxonomy() {
        return 'arsol-pfw-proposal-stage';
    }

    /**
     * Find proposals
     * 
     * @param array $args Query arguments
     * @return array Array of proposal objects
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
        
        $proposals = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $proposals[] = new self(get_post());
            }
        }
        wp_reset_postdata();
        
        return $proposals;
    }

    /**
     * Get proposal name (alias for title)
     * 
     * @return string
     */
    public function get_name() {
        return $this->get_title();
    }

    /**
     * Set proposal title
     * 
     * @param string $title Title
     * @return bool Success status
     */
    public function set_title($title) {
        return $this->update(array('title' => $title));
    }

    /**
     * Get proposal description (alias for content)
     * 
     * @return string
     */
    public function get_description() {
        return $this->get_content();
    }

    /**
     * Set proposal description
     * 
     * @param string $description Description
     * @return bool Success status
     */
    public function set_description($description) {
        return $this->update(array('content' => $description));
    }

    /**
     * Get proposal budget
     * 
     * @return array Budget data
     */
    public function get_proposal_budget() {
        return $this->get_meta('_arsol_pfw_proposed_project_budget_line_items') ?: array();
    }

    /**
     * Set proposal budget
     * 
     * @param array $budget Budget data
     * @return bool Success status
     */
    public function set_proposal_budget($budget) {
        return $this->set_meta('_arsol_pfw_proposed_project_budget_line_items', $budget);
    }

    /**
     * Get proposal quotation
     * 
     * @return array Quotation data
     */
    public function get_proposal_quotation() {
        return $this->get_meta('_arsol_pfw_proposed_project_quotation_line_items') ?: array();
    }

    /**
     * Set proposal quotation
     * 
     * @param array $quotation Quotation data
     * @return bool Success status
     */
    public function set_proposal_quotation($quotation) {
        return $this->set_meta('_arsol_pfw_proposed_project_quotation_line_items', $quotation);
    }

    /**
     * Get proposal due date
     * 
     * @return string Due date
     */
    public function get_proposal_due_date() {
        return $this->get_meta('_arsol_pfw_proposed_project_due_date');
    }

    /**
     * Set proposal due date
     * 
     * @param string $due_date Due date
     * @return bool Success status
     */
    public function set_proposal_due_date($due_date) {
        return $this->set_meta('_arsol_pfw_proposed_project_due_date', sanitize_text_field($due_date));
    }

    /**
     * Get proposal lead
     * 
     * @return int Lead ID
     */
    public function get_proposal_lead() {
        return $this->get_meta('_arsol_pfw_proposed_project_lead');
    }

    /**
     * Set proposal lead
     * 
     * @param int $lead_id Lead ID
     * @return bool Success status
     */
    public function set_proposal_lead($lead_id) {
        return $this->set_meta('_arsol_pfw_proposed_project_lead', (int) $lead_id);
    }

    /**
     * Get proposal start date
     * 
     * @return string Start date
     */
    public function get_proposal_start_date() {
        return $this->get_meta('_arsol_pfw_proposed_project_start_date');
    }

    /**
     * Set proposal start date
     * 
     * @param string $start_date Start date
     * @return bool Success status
     */
    public function set_proposal_start_date($start_date) {
        return $this->set_meta('_arsol_pfw_proposed_project_start_date', sanitize_text_field($start_date));
    }

    /**
     * Get proposal expiration date
     * 
     * @return string Expiration date
     */
    public function get_proposal_expiration_date() {
        return $this->get_meta('_arsol_pfw_proposal_expiration_date');
    }

    /**
     * Set proposal expiration date
     * 
     * @param string $expiration_date Expiration date
     * @return bool Success status
     */
    public function set_proposal_expiration_date($expiration_date) {
        return $this->set_meta('_arsol_pfw_proposal_expiration_date', sanitize_text_field($expiration_date));
    }

    /**
     * Get proposal costing type
     * 
     * @return string Costing type
     */
    public function get_proposal_costing_type() {
        return $this->get_meta('_arsol_pfw_proposal_costing_type') ?: 'none';
    }

    /**
     * Set proposal costing type
     * 
     * @param string $costing_type Costing type
     * @return bool Success status
     */
    public function set_proposal_costing_type($costing_type) {
        return $this->set_meta('_arsol_pfw_proposal_costing_type', sanitize_text_field($costing_type));
    }

    /**
     * Get proposal customer notice (renamed for clarity)
     * 
     * @return string Customer notice
     */
    public function get_proposal_customer_notice() {
        return $this->get_meta('_arsol_pfw_proposal_customer_notice');
    }

    /**
     * Set proposal customer notice (renamed for clarity)
     * 
     * @param string $customer_notice Customer notice
     * @return bool Success status
     */
    public function set_proposal_customer_notice($customer_notice) {
        return $this->set_meta('_arsol_pfw_proposal_customer_notice', wp_kses_post($customer_notice));
    }

    /**
     * Get customer notice (legacy method for backward compatibility)
     * 
     * @return string Customer notice
     */
    public function get_customer_notice() {
        return $this->get_proposal_customer_notice();
    }

    /**
     * Set customer notice (legacy method for backward compatibility)
     * 
     * @param string $customer_notice Customer notice
     * @return bool Success status
     */
    public function set_customer_notice($customer_notice) {
        return $this->set_proposal_customer_notice($customer_notice);
    }

    /**
     * Get proposal secondary status
     * 
     * @return string Secondary status
     */
    public function get_proposal_secondary_status() {
        return $this->get_meta('_arsol_pfw_proposal_secondary_status');
    }

    /**
     * Set proposal secondary status
     * 
     * @param string $secondary_status Secondary status
     * @return bool Success status
     */
    public function set_proposal_secondary_status($secondary_status) {
        return $this->set_meta('_arsol_pfw_proposal_secondary_status', sanitize_text_field($secondary_status));
    }

    /**
     * Get proposal notes
     * 
     * @return string Notes
     */
    public function get_proposal_notes() {
        return $this->get_meta('_arsol_pfw_proposal_notes');
    }

    /**
     * Set proposal notes
     * 
     * @param string $notes Notes
     * @return bool Success status
     */
    public function set_proposal_notes($notes) {
        return $this->set_meta('_arsol_pfw_proposal_notes', wp_kses_post($notes));
    }

    /**
     * Get parent project ID
     * 
     * @return int Parent project ID
     */
    public function get_parent_project_id() {
        return $this->get_meta('_arsol_pfw_parent_project_id');
    }

    /**
     * Set parent project ID
     * 
     * @param int $project_id Parent project ID
     * @return bool Success status
     */
    public function set_parent_project_id($project_id) {
        return $this->set_meta('_arsol_pfw_parent_project_id', (int) $project_id);
    }

    /**
     * Get request ID (if converted from request)
     * 
     * @return int Request ID
     */
    public function get_request_id() {
        return $this->get_meta('_arsol_pfw_request_id');
    }

    /**
     * Set request ID
     * 
     * @param int $request_id Request ID
     * @return bool Success status
     */
    public function set_request_id($request_id) {
        return $this->set_meta('_arsol_pfw_request_id', (int) $request_id);
    }

    /**
     * Get request details (if converted from request)
     * 
     * @return string Request details
     */
    public function get_request_details() {
        return $this->get_meta('_arsol_pfw_request_details');
    }

    /**
     * Set request details
     * 
     * @param string $details Request details
     * @return bool Success status
     */
    public function set_request_details($details) {
        return $this->set_meta('_arsol_pfw_request_details', wp_kses_post($details));
    }

    /**
     * Get request title (if converted from request)
     * 
     * @return string Request title
     */
    public function get_request_title() {
        return $this->get_meta('_arsol_pfw_request_title');
    }

    /**
     * Set request title
     * 
     * @param string $title Request title
     * @return bool Success status
     */
    public function set_request_title($title) {
        return $this->set_meta('_arsol_pfw_request_title', sanitize_text_field($title));
    }

    /**
     * Get request date (if converted from request)
     * 
     * @return string Request date
     */
    public function get_request_date() {
        return $this->get_meta('_arsol_pfw_request_date');
    }

    /**
     * Set request date
     * 
     * @param string $date Request date
     * @return bool Success status
     */
    public function set_request_date($date) {
        return $this->set_meta('_arsol_pfw_request_date', sanitize_text_field($date));
    }

    /**
     * Get request budget (if converted from request)
     * 
     * @return array Request budget
     */
    public function get_request_budget() {
        return $this->get_meta('_arsol_pfw_requested_project_budget') ?: array();
    }

    /**
     * Set request budget
     * 
     * @param array $budget Request budget
     * @return bool Success status
     */
    public function set_request_budget($budget) {
        return $this->set_meta('_arsol_pfw_requested_project_budget', $budget);
    }

    /**
     * Get request start date (cross-entity access for historical data)
     * 
     * @return string Request start date
     */
    public function get_request_start_date() {
        return $this->get_meta('_arsol_pfw_requested_project_start_date');
    }

    /**
     * Set request start date
     * 
     * @param string $start_date Request start date
     * @return bool Success status
     */
    public function set_request_start_date($start_date) {
        return $this->set_meta('_arsol_pfw_requested_project_start_date', sanitize_text_field($start_date));
    }

    /**
     * Get request due date (cross-entity access for historical data)
     * 
     * @return string Request due date
     */
    public function get_request_due_date() {
        return $this->get_meta('_arsol_pfw_requested_project_due_date');
    }

    /**
     * Set request due date
     * 
     * @param string $due_date Request due date
     * @return bool Success status
     */
    public function set_request_due_date($due_date) {
        return $this->set_meta('_arsol_pfw_requested_project_due_date', sanitize_text_field($due_date));
    }

    /**
     * Get request attachments (if converted from request)
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
     * Save proposal
     * 
     * @return bool Success status
     */
    public function save() {
        if (!$this->proposal_id) {
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
     * Read proposal data
     * 
     * @return bool Success status
     */
    public function read() {
        if (!$this->proposal_id) {
            return false;
        }

        // Load basic data
        $this->data = array(
            'id' => $this->proposal_id,
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
     * Delete proposal with enhanced cleanup
     * 
     * @return bool Success status
     */
    public function delete_enhanced() {
        if (!$this->proposal_id) {
            return false;
        }

        // Delete all associated meta
        $meta_keys = array(
            '_arsol_pfw_customer_id',
            '_arsol_pfw_created_via',
            '_arsol_pfw_proposed_project_budget_line_items',
            '_arsol_pfw_proposed_project_quotation_line_items',
            '_arsol_pfw_proposed_project_lead',
            '_arsol_pfw_proposed_project_start_date',
            '_arsol_pfw_proposed_project_due_date',
            '_arsol_pfw_proposal_expiration_date',
            '_arsol_pfw_proposal_costing_type',
            '_arsol_pfw_proposal_customer_notice',
            '_arsol_pfw_proposal_secondary_status',
            '_arsol_pfw_proposal_notes',
            '_arsol_pfw_parent_project_id',
            '_arsol_pfw_request_id',
            '_arsol_pfw_request_details',
            '_arsol_pfw_request_title',
            '_arsol_pfw_request_date',
            '_arsol_pfw_requested_project_budget',
            '_arsol_pfw_requested_project_start_date',
            '_arsol_pfw_requested_project_due_date',
            '_arsol_pfw_request_attachments',
        );

        foreach ($meta_keys as $meta_key) {
            delete_post_meta($this->proposal_id, $meta_key);
        }

        return $this->delete(true);
    }

    /**
     * Delete proposal meta
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
     * Get proposal property
     * 
     * @param string $prop Property name
     * @return mixed Property value
     */
    public function get_prop($prop) {
        return isset($this->data[$prop]) ? $this->data[$prop] : null;
    }

    /**
     * Set proposal property
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
     * Get proposal status (alias for stage, WooCommerce compatibility)
     * 
     * @return string Current status
     */
    public function get_status() {
        return $this->get_stage();
    }

    /**
     * Set proposal status (alias for stage, WooCommerce compatibility)
     * 
     * @param string $status Status
     * @return bool Success status
     */
    public function set_status($status) {
        return $this->set_stage($status);
    }

    /**
     * Update proposal status (alias for stage, WooCommerce compatibility)
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
        return $this->proposal ? $this->proposal->post_date : '';
    }

    /**
     * Set date created
     * 
     * @param string $date_created Date created
     * @return bool Success status
     */
    public function set_date_created($date_created) {
        if (!$this->proposal_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->proposal_id,
            'post_date' => $date_created
        ));
        
        if (!is_wp_error($result)) {
            $this->proposal = get_post($this->proposal_id);
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
        return $this->proposal ? $this->proposal->post_modified : '';
    }

    /**
     * Set date modified
     * 
     * @param string $date_modified Date modified
     * @return bool Success status
     */
    public function set_date_modified($date_modified) {
        if (!$this->proposal_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->proposal_id,
            'post_modified' => $date_modified
        ));
        
        if (!is_wp_error($result)) {
            $this->proposal = get_post($this->proposal_id);
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
} 