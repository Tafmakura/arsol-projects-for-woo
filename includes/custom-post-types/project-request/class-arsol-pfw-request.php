<?php
/**
 * Request Entity Class
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Entities
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Entity
 * 
 * Represents a single project request with clean CRUD operations
 */
class ARSOL_PFW_Request extends WC_Data implements ARSOL_PFW_Stage_Interface {
    
    /**
     * Data array for the object
     * @var array
     */
    protected $data = array(
        'name'        => '',
        'description' => '',
        'stage'       => 'pending-review',
        'customer_id' => 0,
        'project_id'  => 0,
        'budget'      => array(),
        'deadline'    => null,
        'start_date'  => null,
        'date_created' => null,
        'date_modified' => null,
    );
    
    /**
     * Core data keys (properties that exist in wp_posts table)
     * @var array
     */
    protected $core_data_keys = array('name', 'description', 'date_created', 'date_modified');
    
    /**
     * Object type for this entity
     * @var string
     */
    protected $object_type = 'arsol-pfw-request';
    
    /**
     * Post type for this entity
     * @var string
     */
    protected $post_type = 'arsol-pfw-request';
    
    /**
     * Cache group for this object type
     * @var string
     */
    protected $cache_group = 'arsol-pfw-requests';
    
    /**
     * Constructor
     *
     * @param int|ARSOL_PFW_Request|object $request Request to init
     */
    public function __construct($request = 0) {
        parent::__construct($request);
        
        if (is_numeric($request) && $request > 0) {
            $this->set_id($request);
        } elseif ($request instanceof self) {
            $this->set_id($request->get_id());
        } elseif (!empty($request->ID)) {
            $this->set_id($request->ID);
        } else {
            $this->set_object_read(true);
        }
        
        $this->data_store = WC_Data_Store::load('arsol-pfw-request');
        
        if ($this->get_id() > 0) {
            $this->data_store->read($this);
        }
    }
    
    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */
    
    /**
     * Get request name
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return string
     */
    public function get_name($context = 'view') {
        return $this->get_prop('name', $context);
    }
    
    /**
     * Get request description
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return string
     */
    public function get_description($context = 'view') {
        return $this->get_prop('description', $context);
    }
    
    /**
     * Get request stage
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return string
     */
    public function get_stage($context = 'view') {
        return $this->get_prop('stage', $context);
    }
    
    /**
     * Get customer ID
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return int
     */
    public function get_customer_id($context = 'view') {
        return $this->get_prop('customer_id', $context);
    }
    
    /**
     * Get project ID (if this is a project-tied request)
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return int
     */
    public function get_project_id($context = 'view') {
        return $this->get_prop('project_id', $context);
    }
    
    /**
     * Get budget data
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return array
     */
    public function get_budget($context = 'view') {
        return $this->get_prop('budget', $context);
    }
    
    /**
     * Get deadline
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return string|null
     */
    public function get_deadline($context = 'view') {
        return $this->get_prop('deadline', $context);
    }
    
    /**
     * Get start date
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return string|null
     */
    public function get_start_date($context = 'view') {
        return $this->get_prop('start_date', $context);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Setters
    |--------------------------------------------------------------------------
    */
    
    /**
     * Set request name
     *
     * @param string $name Request name
     */
    public function set_name($name) {
        $this->set_prop('name', $name);
    }
    
    /**
     * Set request description
     *
     * @param string $description Request description
     */
    public function set_description($description) {
        $this->set_prop('description', $description);
    }
    
    /**
     * Set request stage
     *
     * @param string $stage Request stage slug
     */
    public function set_stage($stage) {
        $this->set_prop('stage', $stage);
    }
    
    /**
     * Set customer ID
     *
     * @param int $customer_id Customer user ID
     */
    public function set_customer_id($customer_id) {
        $this->set_prop('customer_id', absint($customer_id));
    }
    
    /**
     * Set project ID
     *
     * @param int $project_id Project ID
     */
    public function set_project_id($project_id) {
        $this->set_prop('project_id', absint($project_id));
    }
    
    /**
     * Set budget data
     *
     * @param array $budget Budget data array
     */
    public function set_budget($budget) {
        $this->set_prop('budget', $budget);
    }
    
    /**
     * Set deadline
     *
     * @param string $deadline Deadline date
     */
    public function set_deadline($deadline) {
        $this->set_prop('deadline', $deadline);
    }
    
    /**
     * Set start date
     *
     * @param string $start_date Start date
     */
    public function set_start_date($start_date) {
        $this->set_prop('start_date', $start_date);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Stage Management (ARSOL_PFW_Stage_Interface Implementation)
    |--------------------------------------------------------------------------
    */
    
    /**
     * Update stage with hooks and validation
     *
     * @param string $new_stage New stage slug
     * @param string $note Optional note for the stage change
     */
    public function update_stage($new_stage, $note = '') {
        $old_stage = $this->get_stage();
        
        // Validate stage transition
        $available_stages = $this->get_available_stages();
        if (!array_key_exists($new_stage, $available_stages)) {
            throw new Exception(sprintf('Invalid stage: %s', $new_stage));
        }
        
        // Set new stage and save
        $this->set_stage($new_stage);
        $this->save();
        
        // Add note if provided
        if (!empty($note)) {
            $this->add_note($note);
        }
        
        // Fire hooks
        do_action('arsol_pfw_request_stage_changed', $this->get_id(), $old_stage, $new_stage, $this);
        do_action("arsol_pfw_request_stage_{$old_stage}_to_{$new_stage}", $this->get_id(), $this);
    }
    
    /**
     * Get available stages for requests
     *
     * @return array Array of stage_slug => stage_name
     */
    public function get_available_stages() {
        $stages = array(
            'pending-review' => __('Pending Review', 'arsol-pfw'),
            'under-review'   => __('Under Review', 'arsol-pfw'),
            'on-hold'        => __('On Hold', 'arsol-pfw'),
            'approved'       => __('Approved', 'arsol-pfw'),
            'rejected'       => __('Rejected', 'arsol-pfw'),
        );
        
        return apply_filters('arsol_pfw_request_available_stages', $stages, $this);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Business Logic Methods
    |--------------------------------------------------------------------------
    */
    
    /**
     * Approve request
     */
    public function approve() {
        $this->update_stage('approved');
        do_action('arsol_pfw_request_approved', $this->get_id(), $this);
    }
    
    /**
     * Reject request with reason
     *
     * @param string $reason Rejection reason
     */
    public function reject($reason = '') {
        $this->update_stage('rejected');
        
        if (!empty($reason)) {
            $this->add_note($reason);
        }
        
        do_action('arsol_pfw_request_rejected', $this->get_id(), $this, $reason);
    }
    
    /**
     * Put request on hold
     *
     * @param string $reason Hold reason
     */
    public function hold($reason = '') {
        $this->update_stage('on-hold');
        
        if (!empty($reason)) {
            $this->add_note($reason);
        }
        
        do_action('arsol_pfw_request_on_hold', $this->get_id(), $this, $reason);
    }
    
    /**
     * Check if request is approved
     *
     * @return bool
     */
    public function is_approved() {
        return 'approved' === $this->get_stage();
    }
    
    /**
     * Check if request can be converted to proposal
     *
     * @return bool
     */
    public function can_convert_to_proposal() {
        return $this->is_approved();
    }
    
    /**
     * Check if this is a project-tied request
     *
     * @return bool
     */
    public function is_project_tied() {
        return $this->get_project_id() > 0;
    }
    
    /**
     * Get customer object
     *
     * @return WP_User|false
     */
    public function get_customer() {
        $customer_id = $this->get_customer_id();
        return $customer_id ? get_userdata($customer_id) : false;
    }
    
    /**
     * Add a note to the request
     *
     * @param string $note Note content
     * @param bool $is_customer_note Whether this note is visible to customer
     * @param int $added_by_user User ID who added the note
     * @return int Note ID
     */
    public function add_note($note, $is_customer_note = false, $added_by_user = 0) {
        if (empty($added_by_user)) {
            $added_by_user = get_current_user_id();
        }
        
        $note_data = array(
            'content' => $note,
            'customer_note' => $is_customer_note,
            'added_by' => $added_by_user,
            'date_created' => current_time('mysql'),
        );
        
        $notes = $this->get_meta('_arsol_pfw_request_notes');
        if (!is_array($notes)) {
            $notes = array();
        }
        
        $note_id = count($notes) + 1;
        $notes[$note_id] = $note_data;
        
        update_post_meta($this->get_id(), '_arsol_pfw_request_notes', $notes);
        
        do_action('arsol_pfw_request_note_added', $note_id, $this->get_id(), $note_data);
        
        return $note_id;
    }
    
    /**
     * Get request notes
     *
     * @param bool $customer_notes_only Whether to get only customer notes
     * @return array Array of notes
     */
    public function get_notes($customer_notes_only = false) {
        $notes = $this->get_meta('_arsol_pfw_request_notes');
        if (!is_array($notes)) {
            return array();
        }
        
        if ($customer_notes_only) {
            return array_filter($notes, function($note) {
                return !empty($note['customer_note']);
            });
        }
        
        return $notes;
    }
    
    /**
     * Get meta data
     *
     * @param string $key Meta key
     * @param bool $single Whether to return single value
     * @return mixed Meta value
     */
    public function get_meta($key, $single = true) {
        return get_post_meta($this->get_id(), $key, $single);
    }
    
    /**
     * Set meta data
     *
     * @param string $key Meta key
     * @param mixed $value Meta value
     * @return bool Success status
     */
    public function set_meta($key, $value) {
        return update_post_meta($this->get_id(), $key, $value);
    }
    
    /*
    |--------------------------------------------------------------------------
    | Data Store Integration
    |--------------------------------------------------------------------------
    */
    
    /**
     * Save request data to the database
     *
     * @return int Request ID
     */
    public function save() {
        try {
            /**
             * Trigger action before saving to the DB. Allows you to adjust object props before save.
             */
            do_action('arsol_pfw_before_' . $this->object_type . '_object_save', $this, $this->data_store);
            
            if ($this->data_store) {
                // Trigger save
                if ($this->get_id()) {
                    $this->data_store->update($this);
                } else {
                    $this->data_store->create($this);
                }
            }
            
            /**
             * Trigger action after saving to the DB.
             */
            do_action('arsol_pfw_after_' . $this->object_type . '_object_save', $this, $this->data_store);
            
        } catch (Exception $e) {
            $this->handle_exception($e, __('Error saving request', 'arsol-pfw'));
        }
        
        return $this->get_id();
    }
    
    /**
     * Delete request from the database
     *
     * @param bool $force_delete Whether to force delete (bypass trash)
     * @return bool Success status
     */
    public function delete($force_delete = false) {
        if ($this->data_store) {
            $this->data_store->delete($this, array('force_delete' => $force_delete));
            $this->set_id(0);
            return true;
        }
        return false;
    }
    
    /**
     * Handle exceptions during save/delete operations
     *
     * @param Exception $e Exception object
     * @param string $message Error message
     * @throws Exception
     */
    protected function handle_exception($e, $message) {
        $logger = wc_get_logger();
        $logger->error(
            $message . ': ' . $e->getMessage(),
            array(
                'source' => 'arsol-pfw-request',
                'request_id' => $this->get_id(),
            )
        );
        throw $e;
    }
} 