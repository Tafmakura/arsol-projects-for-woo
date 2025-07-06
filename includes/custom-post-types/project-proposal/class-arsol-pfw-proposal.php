<?php
/**
 * Proposal Entity Class for Arsol Projects for WooCommerce
 *
 * Represents a single proposal entity with business logic, validation,
 * and data management following WooCommerce patterns.
 *
 * @package Arsol_PFW\Custom_Post_Types\Proposal
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Custom_Post_Types\Proposal;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Proposal Entity Class
 *
 * Represents a single proposal with all its properties and business logic.
 */
class Entity {
    
    /**
     * Proposal ID
     *
     * @var int
     */
    protected $id = 0;
    
    /**
     * Proposal data
     *
     * @var array
     */
    protected $data = array(
        'title' => '',
        'description' => '',
        'status' => 'draft',
        'customer_id' => 0,
        'project_id' => 0,
        'date_created' => null,
        'date_modified' => null,
        'stage' => 'draft',
        'total_amount' => 0.0,
        'validity_period' => 30,
        'acceptance_date' => null,
    );
    
    /**
     * Original data before changes
     *
     * @var array
     */
    protected $original_data = array();
    
    /**
     * Constructor
     *
     * @param int|WP_Post|Entity $proposal Proposal ID, WP_Post object, or Entity object
     */
    public function __construct($proposal = 0) {
        if (is_numeric($proposal) && $proposal > 0) {
            $this->set_id($proposal);
            $this->load_data();
        } elseif ($proposal instanceof \WP_Post) {
            $this->set_id($proposal->ID);
            $this->load_data();
        } elseif ($proposal instanceof self) {
            $this->set_id($proposal->get_id());
            $this->set_data($proposal->get_data());
        }
        
        $this->original_data = $this->data;
    }
    
    /**
     * Get proposal ID
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Set proposal ID
     *
     * @param int $id Proposal ID
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
     * @param array $data Proposal data
     */
    public function set_data($data) {
        $this->data = array_merge($this->data, $data);
    }
    
    /**
     * Get proposal title
     *
     * @return string
     */
    public function get_title() {
        return $this->data['title'];
    }
    
    /**
     * Set proposal title
     *
     * @param string $title Proposal title
     */
    public function set_title($title) {
        $this->data['title'] = sanitize_text_field($title);
    }
    
    /**
     * Get proposal status
     *
     * @return string
     */
    public function get_status() {
        return $this->data['status'];
    }
    
    /**
     * Set proposal status
     *
     * @param string $status Proposal status
     */
    public function set_status($status) {
        $allowed_statuses = array('draft', 'sent', 'accepted', 'rejected', 'expired');
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
     * Get project ID
     *
     * @return int
     */
    public function get_project_id() {
        return $this->data['project_id'];
    }
    
    /**
     * Set project ID
     *
     * @param int $project_id Project ID
     */
    public function set_project_id($project_id) {
        $this->data['project_id'] = absint($project_id);
    }
    
    /**
     * Get total amount
     *
     * @return float
     */
    public function get_total_amount() {
        return (float) $this->data['total_amount'];
    }
    
    /**
     * Set total amount
     *
     * @param float $amount Total amount
     */
    public function set_total_amount($amount) {
        $this->data['total_amount'] = (float) $amount;
    }
    
    /**
     * Load data from database
     */
    protected function load_data() {
        if (!$this->get_id()) {
            return;
        }
        
        $post = get_post($this->get_id());
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
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
        $this->data['project_id'] = (int) get_post_meta($this->get_id(), '_arsol_pfw_project_id', true);
        $this->data['stage'] = get_post_meta($this->get_id(), '_arsol_pfw_proposal_stage', true) ?: 'draft';
        $this->data['total_amount'] = (float) get_post_meta($this->get_id(), '_arsol_pfw_proposal_total_amount', true);
        $this->data['validity_period'] = (int) get_post_meta($this->get_id(), '_arsol_pfw_proposal_validity_period', true) ?: 30;
        $this->data['acceptance_date'] = get_post_meta($this->get_id(), '_arsol_pfw_proposal_acceptance_date', true);
    }
    
    /**
     * Save proposal data
     *
     * @return bool True on success, false on failure
     */
    public function save() {
        if (!$this->validate()) {
            return false;
        }
        
        $post_data = array(
            'post_title' => $this->get_title(),
            'post_content' => $this->data['description'],
            'post_status' => $this->get_status(),
            'post_type' => 'arsol-pfw-proposal',
        );
        
        if ($this->get_id()) {
            $post_data['ID'] = $this->get_id();
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
            if ($result && !is_wp_error($result)) {
                $this->set_id($result);
            }
        }
        
        if ($result && !is_wp_error($result)) {
            $this->save_meta_data();
            do_action('arsol_pfw_proposal_saved', $this);
            return true;
        }
        
        return false;
    }
    
    /**
     * Save meta data
     */
    protected function save_meta_data() {
        update_post_meta($this->get_id(), '_arsol_pfw_customer_id', $this->get_customer_id());
        update_post_meta($this->get_id(), '_arsol_pfw_project_id', $this->get_project_id());
        update_post_meta($this->get_id(), '_arsol_pfw_proposal_stage', $this->data['stage']);
        update_post_meta($this->get_id(), '_arsol_pfw_proposal_total_amount', $this->get_total_amount());
        update_post_meta($this->get_id(), '_arsol_pfw_proposal_validity_period', $this->data['validity_period']);
        update_post_meta($this->get_id(), '_arsol_pfw_proposal_acceptance_date', $this->data['acceptance_date']);
    }
    
    /**
     * Validate proposal data
     *
     * @return bool True if valid, false otherwise
     */
    public function validate() {
        $errors = array();
        
        if (empty($this->get_title())) {
            $errors[] = __('Proposal title is required.', 'arsol-pfw');
        }
        
        if (!$this->get_customer_id()) {
            $errors[] = __('Customer ID is required.', 'arsol-pfw');
        }
        
        $errors = apply_filters('arsol_pfw_proposal_validation_errors', $errors, $this);
        
        return empty($errors);
    }
    
    /**
     * Delete proposal
     *
     * @param bool $force_delete Force delete (bypass trash)
     * @return bool True on success, false on failure
     */
    public function delete($force_delete = false) {
        if (!$this->get_id()) {
            return false;
        }
        
        do_action('arsol_pfw_proposal_before_delete', $this);
        
        $result = wp_delete_post($this->get_id(), $force_delete);
        
        if ($result) {
            do_action('arsol_pfw_proposal_deleted', $this->get_id());
            $this->set_id(0);
            return true;
        }
        
        return false;
    }
} 