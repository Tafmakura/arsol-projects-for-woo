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
     * Initialize setup components for post type and taxonomy registration
     */
    private function initialize_setup() {
        // Instantiate the setup class that handles post type and taxonomy registration
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Setup();
        
        // Instantiate admin components
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposals();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Quotation();
        new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Budget();
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
        if (!isset($args['tax_input'][self::get_status_taxonomy()])) {
            $args['tax_input'][self::get_status_taxonomy()] = 'processing';
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
     * Get proposal status
     * 
     * @return string|null Status slug
     */
    public function get_status() {
        if (!$this->proposal_id) {
            return null;
        }

        $terms = wp_get_post_terms($this->proposal_id, self::get_status_taxonomy());
        return !empty($terms) && !is_wp_error($terms) ? $terms[0]->slug : null;
    }

    /**
     * Set proposal status
     * 
     * @param string $status Status slug
     * @return bool Success status
     */
    public function set_status($status) {
        if (!$this->proposal_id) {
            return false;
        }

        $old_status = $this->get_status();
        $result = wp_set_post_terms($this->proposal_id, array($status), self::get_status_taxonomy());
        
        if (!is_wp_error($result)) {
            // Trigger status change action
            do_action('arsol_proposal_stage_changed', $this->proposal_id, $old_status, $status);
            return true;
        }

        return false;
    }

    /**
     * Get proposal budget
     * 
     * @return array|null Budget data
     */
    public function get_budget() {
        return $this->get_meta('_arsol_pfw_proposal_budget');
    }

    /**
     * Set proposal budget
     * 
     * @param array $budget Budget data
     * @return bool Success status
     */
    public function set_budget($budget) {
        return $this->set_meta('_arsol_pfw_proposal_budget', $budget);
    }

    /**
     * Get proposal timeline
     * 
     * @return array Timeline data
     */
    public function get_timeline() {
        return array(
            'start_date' => $this->get_meta('_arsol_pfw_proposal_start_date'),
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
            $success = $success && $this->set_meta('_arsol_pfw_proposal_start_date', $timeline['start_date']);
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
     * @return WP_User|null
     */
    public function get_project_lead() {
        $lead_id = $this->get_meta('_arsol_pfw_proposal_project_lead');
        return $lead_id ? get_userdata($lead_id) : null;
    }

    /**
     * Set project lead
     * 
     * @param int $user_id User ID
     * @return bool Success status
     */
    public function set_project_lead($user_id) {
        return $this->set_meta('_arsol_pfw_proposal_project_lead', $user_id);
    }

    /**
     * Approve proposal and convert to project
     * 
     * @return int|false Project ID or false on failure
     */
    public function approve() {
        if (!$this->exists() || $this->get_status() !== 'pending-approval') {
            return false;
        }

        // Update status to approved
        $this->set_status('approved');

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

        $success = $this->set_status('rejected');
        
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
    public function get_status_taxonomy() {
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
}
