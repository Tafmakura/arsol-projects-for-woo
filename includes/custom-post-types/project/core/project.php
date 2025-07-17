<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Instance Management Class
 * 
 * Responsible for creating and managing individual project instances
 */
class Project {

    /**
     * Project ID
     * 
     * @var int
     */
    protected $project_id = 0;

    /**
     * Project post object
     * 
     * @var WP_Post|null
     */
    protected $project = null;
    
    /**
     * Project data
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
     * @var \Arsol_Projects_For_Woo\Data_Stores\Project_Data_Store
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

        // Load data from data store
        $this->read();

        return true;
    }

    /**
     * Create a new project instance
     * 
     * @param array $args Project creation arguments
     * @return Project|false New project instance or false on failure
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
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage($this->project_id, 'project');
    }

    /**
     * Set project stage
     * 
     * @param string $stage Stage slug
     * @return bool Success status
     */
    public function set_stage($stage) {
        // Use centralized stage manager
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($this->project_id, 'project', $stage);
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
     * Get project customer (WP_User object)
     * 
     * @return WP_User|null
     */
    public function get_customer() {
        $customer_id = $this->get_customer_id();
        return $customer_id ? get_userdata($customer_id) : null;
    }

    /**
     * Get post author ID (who created the project) - from post_author
     * 
     * @return int Post author ID
     */
    public function get_post_author_id() {
        return $this->project ? (int) $this->project->post_author : 0;
    }

    /**
     * Set post author ID (who created the project) - updates post_author
     * 
     * @param int $post_author_id Post author ID
     * @return bool Success status
     */
    public function set_post_author_id($post_author_id) {
        if (!$this->project_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->project_id,
            'post_author' => (int) $post_author_id
        ));
        
        if (!is_wp_error($result)) {
            $this->project = get_post($this->project_id);
            return true;
        }
        
        return false;
    }

    /**
     * Get project post author (WP_User object) - from post_author
     * 
     * @return WP_User|null
     */
    public function get_post_author() {
        $post_author_id = $this->get_post_author_id();
        return $post_author_id ? get_userdata($post_author_id) : null;
    }

    /**
     * Get how the project was created
     * 
     * @return string Creation method
     */
    public function get_created_via() {
        return $this->get_meta('_arsol_pfw_created_via');
    }

    /**
     * Set how the project was created
     * 
     * @param string $method Creation method
     * @return bool Success status
     */
    public function set_created_via($method) {
        return $this->set_meta('_arsol_pfw_created_via', $method);
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
     * @return array Array of Arsol_PFW_Project instances
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
     * Set project title
     * 
     * @param string $title Project title
     * @return bool Success status
     */
    public function set_title($title) {
        $this->set_prop('name', $title);
        return true;
    }

    /**
     * Get project description
     * 
     * @return string Project description
     */
    public function get_description() {
        return $this->get_prop('description');
    }

    /**
     * Set project description
     * 
     * @param string $description Project description
     * @return bool Success status
     */
    public function set_description($description) {
        $this->set_prop('description', $description);
        return true;
    }

    /**
     * Get project budget
     * 
     * @return array Project budget data
     */
    public function get_project_budget() {
        return $this->get_prop('budget');
    }

    /**
     * Set project budget
     * 
     * @param array $budget Project budget data
     * @return bool Success status
     */
    public function set_project_budget($budget) {
        $this->set_prop('budget', $budget);
        return true;
    }

    /**
     * Get project due date
     * 
     * @return string Project due date
     */
    public function get_project_due_date() {
        return $this->get_prop('due_date');
    }

    /**
     * Set project due date
     * 
     * @param string $due_date Project due date
     * @return bool Success status
     */
    public function set_project_due_date($due_date) {
        return $this->set_prop('due_date', $due_date);
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
     * @param int $lead_id Project lead ID
     * @return bool Success status
     */
    public function set_project_lead($lead_id) {
        $this->set_prop('project_lead', (int) $lead_id);
        return true;
    }

    /**
     * Get project start date
     * 
     * @return string Project start date
     */
    public function get_project_start_date() {
        return $this->get_prop('start_date');
    }

    /**
     * Set project start date
     * 
     * @param string $start_date Project start date
     * @return bool Success status
     */
    public function set_project_start_date($start_date) {
        $this->set_prop('start_date', $start_date);
        return true;
    }

    /**
     * Get project customer notice
     * 
     * @return string Project customer notice
     */
    public function get_project_customer_notice() {
        return $this->get_meta('_arsol_pfw_project_customer_notice');
    }

    /**
     * Set project customer notice
     * 
     * @param string $customer_notice Project customer notice
     * @return bool Success status
     */
    public function set_project_customer_notice($customer_notice) {
        return $this->set_meta('_arsol_pfw_project_customer_notice', wp_kses_post($customer_notice));
    }

    /**
     * Update stage with hooks
     * 
     * @param string $new_stage New stage
     * @return bool|WP_Error Success status or error
     */
    public function update_stage($new_stage) {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::update_stage($this->project_id, 'project', $new_stage);
    }

    /**
     * Get available stages
     * 
     * @return array Available stages
     */
    public function get_available_stages() {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_available_stages('project');
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
                // Load the data after creation
                $this->read();
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

    /**
     * Get proposal budget onetime amount
     * 
     * @return array|string Proposal budget onetime amount
     */
    public function get_proposal_budget_onetime_amount() {
        return $this->get_meta('_arsol_pfw_project_proposal_budget_onetime_amount');
    }

    /**
     * Get proposal budget recurring amount
     * 
     * @return array|string Proposal budget recurring amount
     */
    public function get_proposal_budget_recurring_amount() {
        return $this->get_meta('_arsol_pfw_project_proposal_budget_recurring_amount');
    }

    /**
     * Get proposal budget recurring billing interval
     * 
     * @return string Proposal budget recurring billing interval
     */
    public function get_proposal_budget_recurring_billing_interval() {
        return $this->get_meta('_arsol_pfw_project_proposal_budget_recurring_amount_billing_interval');
    }

    /**
     * Get proposal budget recurring billing period
     * 
     * @return string Proposal budget recurring billing period
     */
    public function get_proposal_budget_recurring_billing_period() {
        return $this->get_meta('_arsol_pfw_project_proposal_budget_recurring_amount_billing_period');
    }

    /**
     * Get project status (alias for stage, WooCommerce compatibility)
     * 
     * @return string Current status
     */
    public function get_status() {
        return $this->get_stage();
    }

    /**
     * Set project status (alias for stage, WooCommerce compatibility)
     * 
     * @param string $status Status
     * @return bool Success status
     */
    public function set_status($status) {
        return $this->set_stage($status);
    }

    /**
     * Update project status (alias for stage, WooCommerce compatibility)
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
        return $this->project ? $this->project->post_date : '';
    }

    /**
     * Set date created
     * 
     * @param string $date_created Date created
     * @return bool Success status
     */
    public function set_date_created($date_created) {
        if (!$this->project_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->project_id,
            'post_date' => $date_created
        ));
        
        if (!is_wp_error($result)) {
            $this->project = get_post($this->project_id);
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
        return $this->project ? $this->project->post_modified : '';
    }

    /**
     * Set date modified
     * 
     * @param string $date_modified Date modified
     * @return bool Success status
     */
    public function set_date_modified($date_modified) {
        if (!$this->project_id) {
            return false;
        }
        
        $result = wp_update_post(array(
            'ID' => $this->project_id,
            'post_modified' => $date_modified
        ));
        
        if (!is_wp_error($result)) {
            $this->project = get_post($this->project_id);
            return true;
        }
        
        return false;
    }

    /**
     * Get proposal budget (cross-entity access for historical data)
     * 
     * @return array|string Proposal budget
     */
    public function get_proposal_budget() {
        // First try current proposal budget
        $current_budget = $this->get_meta('_arsol_pfw_proposal_budget');
        if (!empty($current_budget)) {
            return $current_budget;
        }
        
        // Fallback to historical proposal budget
        return $this->get_meta('_arsol_pfw_project_proposal_budget');
    }

    /**
     * Get request budget (cross-entity access for historical data)
     * 
     * @return array|string Request budget
     */
    public function get_request_budget() {
        return $this->get_meta('_arsol_pfw_project_requested_budget');
    }

    /**
     * Get proposal start date (cross-entity access for historical data)
     * 
     * @return string Proposal start date
     */
    public function get_proposal_start_date() {
        // First try current proposal start date
        $current_date = $this->get_meta('_arsol_pfw_proposal_start_date');
        if (!empty($current_date)) {
            return $current_date;
        }
        
        // Fallback to historical proposal start date
        return $this->get_meta('_arsol_pfw_proposed_project_start_date');
    }

    /**
     * Get proposal due date (cross-entity access for historical data)
     * 
     * @return string Proposal due date
     */
    public function get_proposal_due_date() {
        // First try current proposal due date
        $current_date = $this->get_meta('_arsol_pfw_proposal_due_date');
        if (!empty($current_date)) {
            return $current_date;
        }
        
        // Fallback to historical proposal due date
        return $this->get_meta('_arsol_pfw_proposed_project_due_date');
    }

    /**
     * Get request start date (cross-entity access for historical data)
     * 
     * @return string Request start date
     */
    public function get_request_start_date() {
        return $this->get_meta('_arsol_pfw_project_requested_start_date');
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
     * Get customer notice (legacy method for backward compatibility)
     * 
     * @return string Customer notice
     */
    public function get_customer_notice() {
        return $this->get_project_customer_notice();
    }

    /**
     * Set customer notice (legacy method for backward compatibility)
     * 
     * @param string $customer_notice Customer notice
     * @return bool Success status
     */
    public function set_customer_notice($customer_notice) {
        return $this->set_project_customer_notice($customer_notice);
    }
}
