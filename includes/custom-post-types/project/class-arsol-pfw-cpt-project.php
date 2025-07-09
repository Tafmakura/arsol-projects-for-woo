<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Project;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Instance Management Class
 * 
 * Responsible for creating and managing individual project instances
 */
class Project_CPT {

    /**
     * Project ID
     * @var int
     */
    private $project_id;

    /**
     * Project post object
     * @var WP_Post
     */
    private $project;

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
     * @param int|WP_Post $project Project ID or post object
     */
    public function __construct($project = null) {
        // Initialize setup components if not already done
        if (!self::$setup_initialized) {
            $this->initialize_setup();
            self::$setup_initialized = true;
        }

        if ($project) {
            $this->load_project($project);
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
     * Load project data
     * 
     * @param int|WP_Post $project Project ID or post object
     * @return bool Success status
     */
    private function load_project($project) {
        if (is_numeric($project)) {
            $this->project_id = (int) $project;
            $this->project = get_post($this->project_id);
        } elseif ($project instanceof \WP_Post) {
            $this->project = $project;
            $this->project_id = $project->ID;
        }

        // Validate that this is actually a project
        if (!$this->project || $this->project->post_type !== self::get_post_type()) {
            return false;
        }

        return true;
    }

    /**
     * Create a new project instance
     * 
     * @param array $args Project creation arguments
     * @return Project_CPT|false New project instance or false on failure
     */
    public static function create($args = array()) {
        $defaults = array(
            'post_type' => self::get_post_type(),
            'post_status' => 'publish',
            'meta_input' => array()
        );

        $args = wp_parse_args($args, $defaults);

        $project_id = wp_insert_post($args);

        if (is_wp_error($project_id) || !$project_id) {
            return false;
        }

        return new self($project_id);
    }

    /**
     * Get project ID
     * 
     * @return int|null
     */
    public function get_id() {
        return $this->project_id;
    }

    /**
     * Get project post object
     * 
     * @return WP_Post|null
     */
    public function get_post() {
        return $this->project;
    }

    /**
     * Get project title
     * 
     * @return string
     */
    public function get_title() {
        return $this->project ? $this->project->post_title : '';
    }

    /**
     * Get project content
     * 
     * @return string
     */
    public function get_content() {
        return $this->project ? $this->project->post_content : '';
    }

    /**
     * Get project stage
     * 
     * @return string Current stage
     */
    public function get_stage() {
        // Use centralized stage manager
        return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage($this->project_id, 'project');
    }

    /**
     * Set project stage
     * 
     * @param string $stage Stage slug
     * @return bool Success status
     */
    public function set_stage($stage) {
        // Use centralized stage manager
        return \Arsol_Projects_For_Woo\Core\Stage_Manager::set_stage($this->project_id, 'project', $stage);
    }

    /**
     * Get project meta value
     * 
     * @param string $key Meta key
     * @param bool $single Return single value
     * @return mixed Meta value
     */
    public function get_meta($key, $single = true) {
        if (!$this->project_id) {
            return $single ? '' : array();
        }

        return get_post_meta($this->project_id, $key, $single);
    }

    /**
     * Set project meta value
     * 
     * @param string $key Meta key
     * @param mixed $value Meta value
     * @return bool Success status
     */
    public function set_meta($key, $value) {
        if (!$this->project_id) {
            return false;
        }

        return update_post_meta($this->project_id, $key, $value);
    }

    /**
     * Get project customer
     * 
     * @return WP_User|null
     */
    public function get_customer() {
        if (!$this->project || !$this->project->post_author) {
            return null;
        }

        return get_userdata($this->project->post_author);
    }

    /**
     * Update project data
     * 
     * @param array $data Update data
     * @return bool Success status
     */
    public function update($data) {
        if (!$this->project_id) {
            return false;
        }

        $data['ID'] = $this->project_id;
        $result = wp_update_post($data);

        if (!is_wp_error($result) && $result) {
            // Reload project data
            $this->project = get_post($this->project_id);
            return true;
        }

        return false;
    }

    /**
     * Delete project
     * 
     * @param bool $force_delete Force delete bypassing trash
     * @return bool Success status
     */
    public function delete($force_delete = false) {
        if (!$this->project_id) {
            return false;
        }

        $result = wp_delete_post($this->project_id, $force_delete);
        
        if ($result) {
            $this->project_id = null;
            $this->project = null;
            return true;
        }

        return false;
    }

    /**
     * Check if project exists and is valid
     * 
     * @return bool
     */
    public function exists() {
        return $this->project && $this->project_id && $this->project->post_type === self::get_post_type();
    }

    /**
     * Get project post type slug
     * 
     * @return string
     */
    public static function get_post_type() {
        return 'arsol-pfw-project';
    }

    /**
     * Get project stage taxonomy slug
     * 
     * @return string
     */
    public static function get_stage_taxonomy() {
        return 'arsol-pfw-project-stage';
    }

    /**
     * Find projects by criteria
     * 
     * @param array $args Query arguments
     * @return array Array of Project_CPT instances
     */
    public static function find($args = array()) {
        $defaults = array(
            'post_type' => self::get_post_type(),
            'post_status' => 'publish',
            'posts_per_page' => -1
        );

        $args = wp_parse_args($args, $defaults);
        $posts = get_posts($args);

        $projects = array();
        foreach ($posts as $post) {
            $projects[] = new self($post);
        }

        return $projects;
    }

    // ===== CRUD Enhancement Methods =====

    /**
     * Get project name (alias for get_title)
     * 
     * @return string
     */
    public function get_name() {
        return $this->get_title();
    }

    /**
     * Set project name
     * 
     * @param string $name Project name
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
        return $this->project ? (int) $this->project->post_author : 0;
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
     * Get project budget
     * 
     * @return array Project budget
     */
    public function get_budget() {
        return $this->get_meta('_arsol_pfw_project_budget');
    }

    /**
     * Set project budget
     * 
     * @param array $budget Project budget
     * @return bool Success status
     */
    public function set_budget($budget) {
        $this->set_prop('budget', $budget);
        return true;
    }

    /**
     * Get project lead
     * 
     * @return int Project lead ID
     */
    public function get_project_lead() {
        return $this->get_meta('_arsol_pfw_project_lead');
    }

    /**
     * Set project lead
     * 
     * @param int $lead_id Project lead ID
     * @return bool Success status
     */
    public function set_project_lead($lead_id) {
        $this->set_prop('project_lead', (int) $lead_id);
        return true;
    }

    /**
     * Get project deadline
     * 
     * @return string Project deadline
     */
    public function get_deadline() {
        return $this->get_meta('_arsol_pfw_project_due_date');
    }

    /**
     * Set project deadline
     * 
     * @param string $deadline Project deadline
     * @return bool Success status
     */
    public function set_deadline($deadline) {
        $this->set_prop('deadline', $deadline);
        return true;
    }

    /**
     * Get project progress
     * 
     * @return int Project progress percentage
     */
    public function get_progress() {
        return (int) $this->get_meta('_arsol_pfw_project_progress');
    }

    /**
     * Set project progress
     * 
     * @param int $progress Project progress percentage
     * @return bool Success status
     */
    public function set_progress($progress) {
        $this->set_prop('progress', (int) $progress);
        return true;
    }

    /**
     * Update stage with hooks
     * 
     * @param string $new_stage New stage
     * @return bool|WP_Error Success status or error
     */
    public function update_stage($new_stage) {
        return \Arsol_Projects_For_Woo\Core\Stage_Manager::update_stage($this->project_id, 'project', $new_stage);
    }

    /**
     * Get available stages
     * 
     * @return array Available stages
     */
    public function get_available_stages() {
        return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_available_stages('project');
    }

    /**
     * Start project
     * 
     * @return bool|WP_Error Success status or error
     */
    public function start() {
        return $this->update_stage('in-progress');
    }

    /**
     * Pause project
     * 
     * @return bool|WP_Error Success status or error
     */
    public function pause() {
        return $this->update_stage('paused');
    }

    /**
     * Resume project
     * 
     * @return bool|WP_Error Success status or error
     */
    public function resume() {
        return $this->update_stage('in-progress');
    }

    /**
     * Complete project
     * 
     * @return bool|WP_Error Success status or error
     */
    public function complete() {
        return $this->update_stage('completed');
    }

    /**
     * Cancel project
     * 
     * @return bool|WP_Error Success status or error
     */
    public function cancel() {
        return $this->update_stage('cancelled');
    }

    /**
     * Save project
     * 
     * @return bool|WP_Error Success status or error
     */
    public function save() {
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Project_Data_Store();
        
        if ($this->project_id > 0) {
            return $data_store->update($this);
        } else {
            $result = $data_store->create($this);
            
            if (!is_wp_error($result)) {
                $this->project_id = $result;
                $this->project = get_post($result);
            }
            
            return $result;
        }
    }

    /**
     * Read project data
     * 
     * @return bool Success status
     */
    public function read() {
        if (!$this->project_id) {
            return false;
        }
        
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Project_Data_Store();
        return $data_store->read($this);
    }

    /**
     * Delete project (enhanced)
     * 
     * @return bool Success status
     */
    public function delete_enhanced() {
        if (!$this->project_id) {
            return false;
        }
        
        $data_store = new \Arsol_Projects_For_Woo\Data_Stores\Project_Data_Store();
        return $data_store->delete($this);
    }

    /**
     * Delete meta value
     * 
     * @param string $key Meta key
     * @return bool Success status
     */
    public function delete_meta($key) {
        if (!$this->project_id) {
            return false;
        }
        
        return delete_post_meta($this->project_id, $key);
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
