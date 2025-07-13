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
class Arsol_PFW_Proposal {

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
     * @return Arsol_PFW_Proposal|false New proposal instance or false on failure
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
     * Set proposal stage
     * 
     * @param string $stage Stage slug
     * @return bool Success status
     */
    public function set_stage($stage) {
        return \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($this->proposal_id, 'proposal', $stage);
    }

    // ========================================
    // COMPLEX DATA STRUCTURE METHODS (Entity-Specific)
    // ========================================

    /**
     * Get complete proposal budget as array
     * 
     * @return array Complete proposal budget structure
     */
    public function get_proposal_budget() {
        return $this->get_prop('budget_data') ?: array();
    }

    /**
     * Set complete proposal budget as array
     * 
     * @param array $budget_data Complete proposal budget structure
     * @return bool Success status
     */
    public function set_proposal_budget($budget_data) {
        $this->set_prop('budget_data', $budget_data);
        return true;
    }

    /**
     * Get specific budget field
     * 
     * @param string $field Field name (onetime, recurring, notes, type)
     * @return mixed Field value
     */
    public function get_budget_field($field) {
        $budget_data = $this->get_proposal_budget();
        return isset($budget_data[$field]) ? $budget_data[$field] : null;
    }

    /**
     * Set specific budget field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @return bool Success status
     */
    public function set_budget_field($field, $value) {
        $budget_data = $this->get_proposal_budget();
        $budget_data[$field] = $value;
        return $this->set_proposal_budget($budget_data);
    }

    /**
     * Get complete proposal quotation as array
     * 
     * @return array Complete proposal quotation structure
     */
    public function get_proposal_quotation() {
        return $this->get_prop('quotation_data') ?: array();
    }

    /**
     * Set complete proposal quotation as array
     * 
     * @param array $quotation_data Complete proposal quotation structure
     * @return bool Success status
     */
    public function set_proposal_quotation($quotation_data) {
        $this->set_prop('quotation_data', $quotation_data);
        return true;
    }

    /**
     * Get specific quotation field
     * 
     * @param string $field Field name (line_items, currency, totals, notes)
     * @return mixed Field value
     */
    public function get_quotation_field($field) {
        $quotation_data = $this->get_proposal_quotation();
        return isset($quotation_data[$field]) ? $quotation_data[$field] : null;
    }

    /**
     * Set specific quotation field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @return bool Success status
     */
    public function set_quotation_field($field, $value) {
        $quotation_data = $this->get_proposal_quotation();
        $quotation_data[$field] = $value;
        return $this->set_proposal_quotation($quotation_data);
    }

    /**
     * Get complete request data as array
     * 
     * @return array Complete request data structure
     */
    public function get_request() {
        return $this->get_prop('original_request_data') ?: array();
    }

    /**
     * Set complete request data as array
     * 
     * @param array $request_data Complete request data structure
     */
    public function set_request($request_data) {
        $this->set_prop('original_request_data', $request_data);
    }

    /**
     * Get individual request field
     * 
     * @param string $field Field name
     * @return mixed Field value
     */
    public function get_request_field($field) {
        $request_data = $this->get_request();
        return isset($request_data[$field]) ? $request_data[$field] : null;
    }

    /**
     * Set individual request field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     */
    public function set_request_field($field, $value) {
        $request_data = $this->get_request();
        $request_data[$field] = $value;
        $this->set_request($request_data);
    }

    /**
     * Get complete WooCommerce data as array
     * 
     * @return array Complete WooCommerce data structure
     */
    public function get_woocommerce() {
        return $this->get_prop('woocommerce_data') ?: array();
    }

    /**
     * Set complete WooCommerce data as array
     * 
     * @param array $woocommerce_data Complete WooCommerce data structure
     */
    public function set_woocommerce($woocommerce_data) {
        $this->set_prop('woocommerce_data', $woocommerce_data);
    }

    /**
     * Get individual WooCommerce field
     * 
     * @param string $field Field name
     * @return mixed Field value
     */
    public function get_woocommerce_field($field) {
        $woocommerce_data = $this->get_woocommerce();
        return isset($woocommerce_data[$field]) ? $woocommerce_data[$field] : null;
    }

    /**
     * Set individual WooCommerce field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     */
    public function set_woocommerce_field($field, $value) {
        $woocommerce_data = $this->get_woocommerce();
        $woocommerce_data[$field] = $value;
        $this->set_woocommerce($woocommerce_data);
    }

    /**
     * Get complete workflow data as array
     * 
     * @return array Complete workflow data structure
     */
    public function get_workflow() {
        return $this->get_prop('workflow_data') ?: array();
    }

    /**
     * Set complete workflow data as array
     * 
     * @param array $workflow_data Complete workflow data structure
     */
    public function set_workflow($workflow_data) {
        $this->set_prop('workflow_data', $workflow_data);
    }

    /**
     * Get individual workflow field
     * 
     * @param string $field Field name
     * @return mixed Field value
     */
    public function get_workflow_field($field) {
        $workflow_data = $this->get_workflow();
        return isset($workflow_data[$field]) ? $workflow_data[$field] : null;
    }

    /**
     * Set individual workflow field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     */
    public function set_workflow_field($field, $value) {
        $workflow_data = $this->get_workflow();
        $workflow_data[$field] = $value;
        $this->set_workflow($workflow_data);
    }

    // ========================================
    // INDIVIDUAL FIELD METHODS (Entity-Specific)
    // ========================================

    /**
     * Get budget onetime amount
     * 
     * @return array|null Onetime amount data
     */
    public function get_budget_onetime_amount() {
        return $this->get_budget_field('onetime');
    }

    /**
     * Set budget onetime amount
     * 
     * @param array $amount_data Onetime amount data
     * @return bool Success status
     */
    public function set_budget_onetime_amount($amount_data) {
        return $this->set_budget_field('onetime', $amount_data);
    }

    /**
     * Get budget recurring amount
     * 
     * @return array|null Recurring amount data
     */
    public function get_budget_recurring_amount() {
        return $this->get_budget_field('recurring');
    }

    /**
     * Set budget recurring amount
     * 
     * @param array $amount_data Recurring amount data
     * @return bool Success status
     */
    public function set_budget_recurring_amount($amount_data) {
        return $this->set_budget_field('recurring', $amount_data);
    }

    /**
     * Get budget notes
     * 
     * @return string|null Budget notes
     */
    public function get_budget_notes() {
        return $this->get_budget_field('notes');
    }

    /**
     * Set budget notes
     * 
     * @param string $notes Budget notes
     * @return bool Success status
     */
    public function set_budget_notes($notes) {
        return $this->set_budget_field('notes', $notes);
    }

    /**
     * Get budget type
     * 
     * @return string|null Budget type
     */
    public function get_budget_type() {
        return $this->get_budget_field('type');
    }

    /**
     * Set budget type
     * 
     * @param string $type Budget type
     * @return bool Success status
     */
    public function set_budget_type($type) {
        return $this->set_budget_field('type', $type);
    }

    /**
     * Get quotation currency
     * 
     * @return string|null Quotation currency
     */
    public function get_quotation_currency() {
        return $this->get_quotation_field('currency');
    }

    /**
     * Set quotation currency
     * 
     * @param string $currency Quotation currency
     * @return bool Success status
     */
    public function set_quotation_currency($currency) {
        return $this->set_quotation_field('currency', $currency);
    }

    /**
     * Get quotation total
     * 
     * @return array|null Quotation totals
     */
    public function get_quotation_total() {
        return $this->get_quotation_field('totals');
    }

    /**
     * Set quotation total
     * 
     * @param array $totals Quotation totals
     * @return bool Success status
     */
    public function set_quotation_total($totals) {
        return $this->set_quotation_field('totals', $totals);
    }

    /**
     * Get quotation notes
     * 
     * @return string|null Quotation notes
     */
    public function get_quotation_notes() {
        return $this->get_quotation_field('notes');
    }

    /**
     * Set quotation notes
     * 
     * @param string $notes Quotation notes
     * @return bool Success status
     */
    public function set_quotation_notes($notes) {
        return $this->set_quotation_field('notes', $notes);
    }

    /**
     * Get quotation line items
     * 
     * @return array|null Quotation line items
     */
    public function get_quotation_line_items() {
        return $this->get_quotation_field('line_items');
    }

    /**
     * Set quotation line items
     * 
     * @param array $line_items Quotation line items
     * @return bool Success status
     */
    public function set_quotation_line_items($line_items) {
        return $this->set_quotation_field('line_items', $line_items);
    }

    /**
     * Get request budget
     * 
     * @return array|null Request budget
     */
    public function get_request_budget() {
        return $this->get_request_field('budget');
    }

    /**
     * Set request budget
     * 
     * @param array $budget Request budget
     * @return bool Success status
     */
    public function set_request_budget($budget) {
        return $this->set_request_field('budget', $budget);
    }

    /**
     * Get request start date
     * 
     * @return string|null Request start date
     */
    public function get_request_start_date() {
        return $this->get_request_field('start_date');
    }

    /**
     * Set request start date
     * 
     * @param string $start_date Request start date
     * @return bool Success status
     */
    public function set_request_start_date($start_date) {
        return $this->set_request_field('start_date', $start_date);
    }

    /**
     * Get request title
     * 
     * @return string|null Request title
     */
    public function get_request_title() {
        return $this->get_request_field('title');
    }

    /**
     * Set request title
     * 
     * @param string $title Request title
     * @return bool Success status
     */
    public function set_request_title($title) {
        return $this->set_request_field('title', $title);
    }

    /**
     * Get WooCommerce order ID
     * 
     * @return int|null WooCommerce order ID
     */
    public function get_woocommerce_order_id() {
        return $this->get_woocommerce_field('order_id');
    }

    /**
     * Set WooCommerce order ID
     * 
     * @param int $order_id WooCommerce order ID
     * @return bool Success status
     */
    public function set_woocommerce_order_id($order_id) {
        return $this->set_woocommerce_field('order_id', $order_id);
    }

    /**
     * Get WooCommerce subscription ID
     * 
     * @return int|null WooCommerce subscription ID
     */
    public function get_woocommerce_subscription_id() {
        return $this->get_woocommerce_field('subscription_id');
    }

    /**
     * Set WooCommerce subscription ID
     * 
     * @param int $subscription_id WooCommerce subscription ID
     * @return bool Success status
     */
    public function set_woocommerce_subscription_id($subscription_id) {
        return $this->set_woocommerce_field('subscription_id', $subscription_id);
    }

    /**
     * Get WooCommerce conversion date
     * 
     * @return string|null WooCommerce conversion date
     */
    public function get_woocommerce_conversion_date() {
        return $this->get_woocommerce_field('conversion_date');
    }

    /**
     * Set WooCommerce conversion date
     * 
     * @param string $conversion_date WooCommerce conversion date
     * @return bool Success status
     */
    public function set_woocommerce_conversion_date($conversion_date) {
        return $this->set_woocommerce_field('conversion_date', $conversion_date);
    }

    /**
     * Get workflow rejection reason
     * 
     * @return string|null Workflow rejection reason
     */
    public function get_workflow_rejection_reason() {
        return $this->get_workflow_field('rejection_reason');
    }

    /**
     * Set workflow rejection reason
     * 
     * @param string $reason Workflow rejection reason
     * @return bool Success status
     */
    public function set_workflow_rejection_reason($reason) {
        return $this->set_workflow_field('rejection_reason', $reason);
    }

    /**
     * Get workflow approval date
     * 
     * @return string|null Workflow approval date
     */
    public function get_workflow_approval_date() {
        return $this->get_workflow_field('approval_date');
    }

    /**
     * Set workflow approval date
     * 
     * @param string $approval_date Workflow approval date
     * @return bool Success status
     */
    public function set_workflow_approval_date($approval_date) {
        return $this->set_workflow_field('approval_date', $approval_date);
    }

    /**
     * Get workflow approver ID
     * 
     * @return int|null Workflow approver ID
     */
    public function get_workflow_approver_id() {
        return $this->get_workflow_field('approver_id');
    }

    /**
     * Set workflow approver ID
     * 
     * @param int $approver_id Workflow approver ID
     * @return bool Success status
     */
    public function set_workflow_approver_id($approver_id) {
        return $this->set_workflow_field('approver_id', $approver_id);
    }

    // ========================================
    // SIMPLE FIELD METHODS (Individual fields)
    // ========================================

    /**
     * Get proposal timeline
     * 
     * @return array Timeline data
     */
    public function get_timeline() {
        return array(
            'start_date' => $this->get_prop('start_date'),
            'due_date' => $this->get_prop('due_date'),
            'expiration_date' => $this->get_prop('expiration_date')
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
        
        if (isset($timeline['due_date'])) {
            $this->set_prop('due_date', $timeline['due_date']);
        }
        
        if (isset($timeline['expiration_date'])) {
            $this->set_prop('expiration_date', $timeline['expiration_date']);
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
     * Get proposal due date
     * 
     * @return string Proposal due date
     */
    public function get_due_date() {
        return $this->get_prop('due_date');
    }
    
    /**
     * Set proposal due date
     * 
     * @param string $due_date Proposal due date
     * @return bool Success status
     */
    public function set_due_date($due_date) {
        return $this->set_prop('due_date', $due_date);
    }

    /**
     * Get proposal expiration date
     * 
     * @return string Proposal expiration date
     */
    public function get_expiration_date() {
        return $this->get_prop('expiration_date');
    }

    /**
     * Set proposal expiration date
     * 
     * @param string $expiration_date Proposal expiration date
     * @return bool Success status
     */
    public function set_expiration_date($expiration_date) {
        $this->set_prop('expiration_date', $expiration_date);
        return true;
    }

    /**
     * Get proposal costing type
     * 
     * @return string Proposal costing type
     */
    public function get_costing_type() {
        return $this->get_prop('costing_type');
    }

    /**
     * Set proposal costing type
     * 
     * @param string $costing_type Proposal costing type
     * @return bool Success status
     */
    public function set_costing_type($costing_type) {
        $this->set_prop('costing_type', $costing_type);
        return true;
    }

    /**
     * Get parent project ID
     * 
     * @return int Parent project ID
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

    // ========================================
    // WORKFLOW METHODS
    // ========================================

    /**
     * Approve proposal and convert to project
     * 
     * @return int|WP_Error New project ID or error
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
                '_arsol_pfw_project_due_date' => $this->get_meta('_arsol_pfw_proposal_due_date'),
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
     * Get post author ID (who created the proposal) - from post_author
     * 
     * @return int Post author ID
     */
    public function get_post_author_id() {
        return $this->proposal ? (int) $this->proposal->post_author : 0;
    }

    /**
     * Set post author ID (who created the proposal) - updates post_author
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
     * Get proposal post author (WP_User object) - from post_author
     * 
     * @return WP_User|null
     */
    public function get_post_author() {
        $post_author_id = $this->get_post_author_id();
        return $post_author_id ? get_userdata($post_author_id) : null;
    }

    /**
     * Get how the proposal was created
     * 
     * @return string Creation method
     */
    public function get_created_via() {
        return $this->get_meta('_arsol_pfw_created_via');
    }

    /**
     * Set how the proposal was created
     * 
     * @param string $method Creation method
     * @return bool Success status
     */
    public function set_created_via($method) {
        return $this->set_meta('_arsol_pfw_created_via', $method);
    }

    /**
     * Get proposal customer (WP_User object)
     * 
     * @return WP_User|null
     */
    public function get_customer() {
        $customer_id = $this->get_customer_id();
        return $customer_id ? get_userdata($customer_id) : null;
    }

    /**
     * Get proposal creator (WP_User object)
     * 
     * @return WP_User|null
     */
    public function get_creator() {
        $creator_id = $this->get_creator_id();
        return $creator_id ? get_userdata($creator_id) : null;
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
     * @return array Array of Arsol_PFW_Proposal instances
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
     * Set proposal title
     * 
     * @param string $title Proposal title
     * @return bool Success status
     */
    public function set_title($title) {
        $this->set_prop('name', $title);
        return true;
    }

    /**
     * Get proposal description
     * 
     * @return string Proposal description
     */
    public function get_description() {
        return $this->get_prop('description');
    }

    /**
     * Set proposal description
     * 
     * @param string $description Proposal description
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
                // Load the data after creation
                $this->read();
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

    // ========================================
    // INDIVIDUAL META KEY ACCESS (WooCommerce Pattern)
    // ========================================

    /**
     * Get individual budget meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'onetime_amount', 'recurring_amount', 'notes')
     * @return mixed Field value
     */
    public function get_budget_meta($field) {
        $meta_key = '_arsol_pfw_proposal_budget_' . $field;
        return get_post_meta($this->proposal_id, $meta_key, true);
    }

    /**
     * Set individual budget meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'onetime_amount', 'recurring_amount', 'notes')
     * @param mixed $value Field value
     */
    public function set_budget_meta($field, $value) {
        $meta_key = '_arsol_pfw_proposal_budget_' . $field;
        update_post_meta($this->proposal_id, $meta_key, $value);
        
        // Update the cached data structure
        $budget_data = $this->get_proposal_budget();
        $budget_data[$field] = $value;
        $this->set_prop('budget_data', $budget_data);
    }

    /**
     * Get individual quotation meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'line_items', 'onetime_total', 'currency')
     * @return mixed Field value
     */
    public function get_quotation_meta($field) {
        $meta_key = '_arsol_pfw_proposal_quotation_' . $field;
        return get_post_meta($this->proposal_id, $meta_key, true);
    }

    /**
     * Set individual quotation meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'line_items', 'onetime_total', 'currency')
     * @param mixed $value Field value
     */
    public function set_quotation_meta($field, $value) {
        $meta_key = '_arsol_pfw_proposal_quotation_' . $field;
        update_post_meta($this->proposal_id, $meta_key, $value);
        
        // Update the cached data structure
        $quotation_data = $this->get_proposal_quotation();
        $quotation_data[$field] = $value;
        $this->set_prop('quotation_data', $quotation_data);
    }

    /**
     * Get individual request meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'details', 'title', 'budget')
     * @return mixed Field value
     */
    public function get_request_meta($field) {
        $meta_key = '_arsol_pfw_proposal_request_' . $field;
        return get_post_meta($this->proposal_id, $meta_key, true);
    }

    /**
     * Set individual request meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'details', 'title', 'budget')
     * @param mixed $value Field value
     */
    public function set_request_meta($field, $value) {
        $meta_key = '_arsol_pfw_proposal_request_' . $field;
        update_post_meta($this->proposal_id, $meta_key, $value);
        
        // Update the cached data structure
        $request_data = $this->get_request();
        $request_data[$field] = $value;
        $this->set_prop('original_request_data', $request_data);
    }

    /**
     * Get individual WooCommerce meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'order_id', 'subscription_id', 'created_via')
     * @return mixed Field value
     */
    public function get_woocommerce_meta($field) {
        $meta_key = '_arsol_pfw_woocommerce_' . $field;
        return get_post_meta($this->proposal_id, $meta_key, true);
    }

    /**
     * Set individual WooCommerce meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'order_id', 'subscription_id', 'created_via')
     * @param mixed $value Field value
     */
    public function set_woocommerce_meta($field, $value) {
        $meta_key = '_arsol_pfw_woocommerce_' . $field;
        update_post_meta($this->proposal_id, $meta_key, $value);
        
        // Update the cached data structure
        $woocommerce_data = $this->get_woocommerce();
        $woocommerce_data[$field] = $value;
        $this->set_prop('woocommerce_data', $woocommerce_data);
    }

    /**
     * Get individual workflow meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'status', 'type', 'step')
     * @return mixed Field value
     */
    public function get_workflow_meta($field) {
        $meta_key = '_arsol_pfw_workflow_' . $field;
        return get_post_meta($this->proposal_id, $meta_key, true);
    }

    /**
     * Set individual workflow meta field (WooCommerce pattern)
     * 
     * @param string $field Field name (e.g., 'status', 'type', 'step')
     * @param mixed $value Field value
     */
    public function set_workflow_meta($field, $value) {
        $meta_key = '_arsol_pfw_workflow_' . $field;
        update_post_meta($this->proposal_id, $meta_key, $value);
        
        // Update the cached data structure
        $workflow_data = $this->get_workflow();
        $workflow_data[$field] = $value;
        $this->set_prop('workflow_data', $workflow_data);
    }

    // ========================================
    // LEGACY META KEY ACCESS (Backward Compatibility)
    // ========================================

    /**
     * Get legacy budget onetime amount (backward compatibility)
     * 
     * @return array|mixed Legacy format
     */
    public function get_legacy_budget_onetime_amount() {
        return get_post_meta($this->proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', true);
    }

    /**
     * Set legacy budget onetime amount (backward compatibility)
     * 
     * @param array|mixed $data Legacy format
     */
    public function set_legacy_budget_onetime_amount($data) {
        update_post_meta($this->proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', $data);
    }

    /**
     * Get legacy budget recurring amount (backward compatibility)
     * 
     * @return array|mixed Legacy format
     */
    public function get_legacy_budget_recurring_amount() {
        return get_post_meta($this->proposal_id, '_arsol_pfw_proposal_budget_recurring_amount', true);
    }

    /**
     * Set legacy budget recurring amount (backward compatibility)
     * 
     * @param array|mixed $data Legacy format
     */
    public function set_legacy_budget_recurring_amount($data) {
        update_post_meta($this->proposal_id, '_arsol_pfw_proposal_budget_recurring_amount', $data);
    }

    /**
     * Get legacy quotation line items (backward compatibility)
     * 
     * @return array|mixed Legacy format
     */
    public function get_legacy_quotation_line_items() {
        return get_post_meta($this->proposal_id, '_arsol_pfw_proposal_quotation_line_items', true);
    }

    /**
     * Set legacy quotation line items (backward compatibility)
     * 
     * @param array|mixed $data Legacy format
     */
    public function set_legacy_quotation_line_items($data) {
        update_post_meta($this->proposal_id, '_arsol_pfw_proposal_quotation_line_items', $data);
    }
}
