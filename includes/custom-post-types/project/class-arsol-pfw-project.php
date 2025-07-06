<?php
/**
 * Project Entity Class for Arsol Projects for WooCommerce
 *
 * Represents a single project entity with business logic, validation,
 * and data management following WooCommerce patterns.
 *
 * @package Arsol_PFW\Custom_Post_Types\Project
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Custom_Post_Types\Project;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Entity Class
 *
 * Represents a single project with all its properties and business logic.
 * Follows WooCommerce's entity pattern for clean data management.
 */
class Entity {
    
    /**
     * Project ID
     *
     * @var int
     */
    protected $id = 0;
    
    /**
     * Project data
     *
     * @var array
     */
    protected $data = array(
        'title' => '',
        'description' => '',
        'status' => 'active',
        'customer_id' => 0,
        'date_created' => null,
        'date_modified' => null,
        'stage' => 'planning',
        'priority' => 'normal',
        'budget' => 0.0,
        'deadline' => null,
    );
    
    /**
     * Original data before changes
     *
     * @var array
     */
    protected $original_data = array();
    
    /**
     * Meta data
     *
     * @var array
     */
    protected $meta_data = array();
    
    /**
     * Constructor
     *
     * @param int|WP_Post|Entity $project Project ID, WP_Post object, or Entity object
     */
    public function __construct($project = 0) {
        if (is_numeric($project) && $project > 0) {
            $this->set_id($project);
            $this->load_data();
        } elseif ($project instanceof WP_Post) {
            $this->set_id($project->ID);
            $this->load_data();
        } elseif ($project instanceof self) {
            $this->set_id($project->get_id());
            $this->set_data($project->get_data());
        }
        
        // Store original data for change tracking
        $this->original_data = $this->data;
    }
    
    /**
     * Get project ID
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Set project ID
     *
     * @param int $id Project ID
     */
    public function set_id($id) {
        $this->id = absint($id);
    }
    
    /**
     * Get all data
     *
     * @return array
     */
    public function get_data() {
        return $this->data;
    }
    
    /**
     * Set all data
     *
     * @param array $data Project data
     */
    public function set_data($data) {
        $this->data = array_merge($this->data, $data);
    }
    
    /**
     * Get project title
     *
     * @return string
     */
    public function get_title() {
        return $this->data['title'];
    }
    
    /**
     * Set project title
     *
     * @param string $title Project title
     */
    public function set_title($title) {
        $this->data['title'] = sanitize_text_field($title);
    }
    
    /**
     * Get project description
     *
     * @return string
     */
    public function get_description() {
        return $this->data['description'];
    }
    
    /**
     * Set project description
     *
     * @param string $description Project description
     */
    public function set_description($description) {
        $this->data['description'] = wp_kses_post($description);
    }
    
    /**
     * Get project status
     *
     * @return string
     */
    public function get_status() {
        return $this->data['status'];
    }
    
    /**
     * Set project status
     *
     * @param string $status Project status
     */
    public function set_status($status) {
        $allowed_statuses = array('active', 'completed', 'on-hold', 'cancelled');
        if (in_array($status, $allowed_statuses)) {
            $this->data['status'] = $status;
        }
    }
    
    /**
     * Get customer ID
     *
     * @return int
     */
    public function get_customer_id() {
        return $this->data['customer_id'];
    }
    
    /**
     * Set customer ID
     *
     * @param int $customer_id Customer ID
     */
    public function set_customer_id($customer_id) {
        $this->data['customer_id'] = absint($customer_id);
    }
    
    /**
     * Get date created
     *
     * @return string|null
     */
    public function get_date_created() {
        return $this->data['date_created'];
    }
    
    /**
     * Set date created
     *
     * @param string $date Date created
     */
    public function set_date_created($date) {
        $this->data['date_created'] = sanitize_text_field($date);
    }
    
    /**
     * Get project stage
     *
     * @return string
     */
    public function get_stage() {
        return $this->data['stage'];
    }
    
    /**
     * Set project stage
     *
     * @param string $stage Project stage
     */
    public function set_stage($stage) {
        $this->data['stage'] = sanitize_text_field($stage);
    }
    
    /**
     * Get project budget
     *
     * @return float
     */
    public function get_budget() {
        return (float) $this->data['budget'];
    }
    
    /**
     * Set project budget
     *
     * @param float $budget Project budget
     */
    public function set_budget($budget) {
        $this->data['budget'] = (float) $budget;
    }
    
    /**
     * Load data from database
     */
    protected function load_data() {
        if (!$this->get_id()) {
            return;
        }
        
        $post = get_post($this->get_id());
        if (!$post || $post->post_type !== 'arsol-pfw-project') {
            return;
        }
        
        // Load post data
        $this->data['title'] = $post->post_title;
        $this->data['description'] = $post->post_content;
        $this->data['status'] = $post->post_status;
        $this->data['date_created'] = $post->post_date;
        $this->data['date_modified'] = $post->post_modified;
        
        // Load meta data
        $this->data['customer_id'] = (int) get_post_meta($this->get_id(), '_arsol_pfw_customer_id', true);
        $this->data['stage'] = get_post_meta($this->get_id(), '_arsol_pfw_project_stage', true) ?: 'planning';
        $this->data['priority'] = get_post_meta($this->get_id(), '_arsol_pfw_project_priority', true) ?: 'normal';
        $this->data['budget'] = (float) get_post_meta($this->get_id(), '_arsol_pfw_project_budget', true);
        $this->data['deadline'] = get_post_meta($this->get_id(), '_arsol_pfw_project_deadline', true);
    }
    
    /**
     * Save project data
     *
     * @return bool True on success, false on failure
     */
    public function save() {
        // Validate data before saving
        if (!$this->validate()) {
            return false;
        }
        
        // Prepare post data
        $post_data = array(
            'post_title' => $this->get_title(),
            'post_content' => $this->get_description(),
            'post_status' => $this->get_status(),
            'post_type' => 'arsol-pfw-project',
        );
        
        // Insert or update
        if ($this->get_id()) {
            $post_data['ID'] = $this->get_id();
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
            if ($result && !is_wp_error($result)) {
                $this->set_id($result);
            }
        }
        
        // Save meta data
        if ($result && !is_wp_error($result)) {
            $this->save_meta_data();
            
            // Fire action after save
            do_action('arsol_pfw_project_saved', $this);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Save meta data
     */
    protected function save_meta_data() {
        update_post_meta($this->get_id(), '_arsol_pfw_customer_id', $this->get_customer_id());
        update_post_meta($this->get_id(), '_arsol_pfw_project_stage', $this->get_stage());
        update_post_meta($this->get_id(), '_arsol_pfw_project_priority', $this->data['priority']);
        update_post_meta($this->get_id(), '_arsol_pfw_project_budget', $this->get_budget());
        update_post_meta($this->get_id(), '_arsol_pfw_project_deadline', $this->data['deadline']);
    }
    
    /**
     * Validate project data
     *
     * @return bool True if valid, false otherwise
     */
    public function validate() {
        $errors = array();
        
        // Title is required
        if (empty($this->get_title())) {
            $errors[] = __('Project title is required.', 'arsol-pfw');
        }
        
        // Customer ID is required
        if (!$this->get_customer_id()) {
            $errors[] = __('Customer ID is required.', 'arsol-pfw');
        }
        
        // Validate customer exists
        if ($this->get_customer_id() && !get_user_by('id', $this->get_customer_id())) {
            $errors[] = __('Invalid customer ID.', 'arsol-pfw');
        }
        
        // Fire validation filter
        $errors = apply_filters('arsol_pfw_project_validation_errors', $errors, $this);
        
        return empty($errors);
    }
    
    /**
     * Delete project
     *
     * @param bool $force_delete Force delete (bypass trash)
     * @return bool True on success, false on failure
     */
    public function delete($force_delete = false) {
        if (!$this->get_id()) {
            return false;
        }
        
        // Fire action before delete
        do_action('arsol_pfw_project_before_delete', $this);
        
        $result = wp_delete_post($this->get_id(), $force_delete);
        
        if ($result) {
            // Fire action after delete
            do_action('arsol_pfw_project_deleted', $this->get_id());
            $this->set_id(0);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if project has changes
     *
     * @return bool True if has changes, false otherwise
     */
    public function has_changes() {
        return $this->data !== $this->original_data;
    }
    
    /**
     * Get changed data
     *
     * @return array Changed data
     */
    public function get_changes() {
        $changes = array();
        foreach ($this->data as $key => $value) {
            if ($value !== $this->original_data[$key]) {
                $changes[$key] = $value;
            }
        }
        return $changes;
    }
} 