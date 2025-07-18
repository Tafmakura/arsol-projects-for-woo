<?php
/**
 * Project Stage Entity for Arsol Projects for Woo
 *
 * Handles different behaviors for different project stages including:
 * - Stage transitions with validation
 * - WooCommerce integration
 * - Stage history tracking
 * - Duration calculations
 * - Stage-specific behaviors
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies\Stages\Project_Stage;

use Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Interface;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Project Stage Entity Class
 *
 * Handles project-specific stage behaviors and transitions.
 */
class Project_Stage implements Stage_Interface {
    
    /**
     * Project ID
     *
     * @var int
     */
    private $project_id;
    
    /**
     * Current stage
     *
     * @var string
     */
    private $current_stage;
    
    /**
     * Stage configuration
     *
     * @var array
     */
    private $stage_config;
    
    /**
     * Constructor
     *
     * @param int $project_id Project ID
     */
    public function __construct($project_id) {
        $this->project_id = (int) $project_id;
        $this->load_stage_config();
        $this->current_stage = $this->get_stage_from_taxonomy();
    }
    
    /**
     * Load stage configuration
     */
    private function load_stage_config() {
        $this->stage_config = [
            'not-started' => [
                'label' => 'Not Started',
                'transitions' => ['in-progress', 'cancelled'],
                'wc_status' => 'pending',
                'is_initial' => true,
                'is_final' => false,
                'behaviors' => [
                    'can_start_work' => true,
                    'can_cancel' => true,
                    'requires_approval' => false
                ]
            ],
            'in-progress' => [
                'label' => 'In Progress',
                'transitions' => ['paused', 'completed', 'cancelled'],
                'wc_status' => 'processing',
                'is_initial' => false,
                'is_final' => false,
                'behaviors' => [
                    'can_pause' => true,
                    'can_complete' => true,
                    'can_cancel' => true,
                    'tracks_time' => true
                ]
            ],
            'paused' => [
                'label' => 'Paused',
                'transitions' => ['in-progress', 'cancelled'],
                'wc_status' => 'on-hold',
                'is_initial' => false,
                'is_final' => false,
                'behaviors' => [
                    'can_resume' => true,
                    'can_cancel' => true,
                    'pauses_time_tracking' => true
                ]
            ],
            'completed' => [
                'label' => 'Completed',
                'transitions' => [],
                'wc_status' => 'completed',
                'is_initial' => false,
                'is_final' => true,
                'behaviors' => [
                    'triggers_completion_hooks' => true,
                    'sends_completion_email' => true,
                    'updates_wc_order' => true
                ]
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'transitions' => [],
                'wc_status' => 'cancelled',
                'is_initial' => false,
                'is_final' => true,
                'behaviors' => [
                    'triggers_cancellation_hooks' => true,
                    'sends_cancellation_email' => true,
                    'updates_wc_order' => true
                ]
            ]
        ];
    }
    
    /**
     * Get current stage from taxonomy
     *
     * @return string
     */
    private function get_stage_from_taxonomy() {
        $terms = wp_get_object_terms($this->project_id, 'arsol-pfw-project-stage', ['fields' => 'slugs']);
        return !empty($terms) && !is_wp_error($terms) ? $terms[0] : 'not-started';
    }
    
    /**
     * Get current stage
     * 
     * @return string Current stage slug
     */
    public function get_stage() {
        return $this->current_stage;
    }
    
    /**
     * Set stage with validation
     * 
     * @param string $stage Stage slug
     * @return bool Success status
     */
    public function set_stage($stage) {
        if (!$this->validate_transition($stage)) {
            return false;
        }
        
        $old_stage = $this->current_stage;
        $this->current_stage = $stage;
        
        // Update taxonomy
        $result = wp_set_object_terms($this->project_id, $stage, 'arsol-pfw-project-stage', false);
        
        if (!is_wp_error($result)) {
            $this->trigger_stage_change_hooks($old_stage, $stage);
            $this->handle_stage_specific_behaviors($old_stage, $stage);
            return true;
        }
        
        return false;
    }
    
    /**
     * Update stage with optional notes
     *
     * @param string $new_stage New stage slug
     * @param string $note Optional note about the stage change
     * @return bool Success status
     */
    public function update_stage($new_stage, $note = '') {
        if ($note) {
            $this->add_stage_note($note);
        }
        
        return $this->set_stage($new_stage);
    }
    
    /**
     * Get stage label
     *
     * @return string Human-readable stage label
     */
    public function get_stage_label() {
        return isset($this->stage_config[$this->current_stage]) 
            ? $this->stage_config[$this->current_stage]['label'] 
            : $this->current_stage;
    }
    
    /**
     * Get stage notes
     *
     * @return string Stage notes
     */
    public function get_stage_notes() {
        return get_post_meta($this->project_id, '_arsol_pfw_stage_notes', true);
    }
    
    /**
     * Set stage notes
     *
     * @param string $notes Stage notes
     * @return bool Success status
     */
    public function set_stage_notes($notes) {
        return update_post_meta($this->project_id, '_arsol_pfw_stage_notes', $notes);
    }
    
    /**
     * Add stage note
     *
     * @param string $note Stage note
     * @return bool Success status
     */
    private function add_stage_note($note) {
        $notes = $this->get_stage_notes();
        $notes .= "\n" . date('Y-m-d H:i:s') . ": " . $note;
        return $this->set_stage_notes($notes);
    }
    
    /**
     * Get stage history
     *
     * @return array Array of stage change history
     */
    public function get_stage_history() {
        return get_post_meta($this->project_id, '_arsol_pfw_stage_history', true) ?: [];
    }
    
    /**
     * Add stage history entry
     *
     * @param string $old_stage Old stage
     * @param string $new_stage New stage
     * @param string $note Optional note
     */
    private function add_stage_history($old_stage, $new_stage, $note = '') {
        $history = $this->get_stage_history();
        $history[] = [
            'timestamp' => current_time('timestamp'),
            'old_stage' => $old_stage,
            'new_stage' => $new_stage,
            'note' => $note
        ];
        update_post_meta($this->project_id, '_arsol_pfw_stage_history', $history);
    }
    
    /**
     * Get allowed transitions
     *
     * @return array Array of allowed stage transitions
     */
    public function get_allowed_transitions() {
        return isset($this->stage_config[$this->current_stage]) 
            ? $this->stage_config[$this->current_stage]['transitions'] 
            : [];
    }
    
    /**
     * Check if stage transition is allowed
     *
     * @param string $new_stage New stage slug
     * @return bool True if transition is allowed
     */
    public function can_transition_to($new_stage) {
        return in_array($new_stage, $this->get_allowed_transitions());
    }
    
    /**
     * Check if current stage is final
     *
     * @return bool True if current stage is final
     */
    public function is_final_stage() {
        return isset($this->stage_config[$this->current_stage]) 
            ? $this->stage_config[$this->current_stage]['is_final'] 
            : false;
    }
    
    /**
     * Check if current stage is initial
     *
     * @return bool True if current stage is initial
     */
    public function is_initial_stage() {
        return isset($this->stage_config[$this->current_stage]) 
            ? $this->stage_config[$this->current_stage]['is_initial'] 
            : false;
    }
    
    /**
     * Get available stages
     *
     * @return array Array of available stages
     */
    public function get_available_stages() {
        return array_keys($this->stage_config);
    }
    
    /**
     * Get stage duration
     *
     * @return int Duration in seconds
     */
    public function get_stage_duration() {
        $start_time = $this->get_stage_start_time();
        if (!$start_time) {
            return 0;
        }
        
        return current_time('timestamp') - $start_time;
    }
    
    /**
     * Get stage start time
     *
     * @return int Unix timestamp
     */
    public function get_stage_start_time() {
        return get_post_meta($this->project_id, '_arsol_pfw_stage_start_time', true);
    }
    
    /**
     * Set stage start time
     *
     * @param int $timestamp Unix timestamp
     * @return bool Success status
     */
    private function set_stage_start_time($timestamp) {
        return update_post_meta($this->project_id, '_arsol_pfw_stage_start_time', $timestamp);
    }
    
    /**
     * Get WooCommerce order status mapping
     *
     * @return string WooCommerce order status
     */
    public function get_wc_order_status() {
        return isset($this->stage_config[$this->current_stage]) 
            ? $this->stage_config[$this->current_stage]['wc_status'] 
            : 'pending';
    }
    
    /**
     * Set WooCommerce order status
     *
     * @param string $wc_status WooCommerce order status
     * @return bool Success status
     */
    public function set_wc_order_status($wc_status) {
        // This would update associated WooCommerce orders
        // Implementation depends on your WooCommerce integration
        return true;
    }
    
    /**
     * Trigger stage change hooks
     *
     * @param string $old_stage Old stage slug
     * @param string $new_stage New stage slug
     * @return void
     */
    public function trigger_stage_change_hooks($old_stage, $new_stage) {
        // Add to history
        $this->add_stage_history($old_stage, $new_stage);
        
        // Trigger WordPress hooks
        do_action('arsol_pfw_project_stage_changed', $this->project_id, $old_stage, $new_stage);
        do_action("arsol_pfw_project_stage_{$old_stage}_to_{$new_stage}", $this->project_id);
        
        // Set stage start time
        $this->set_stage_start_time(current_time('timestamp'));
    }
    
    /**
     * Validate stage transition
     *
     * @param string $new_stage New stage slug
     * @return bool True if transition is valid
     */
    public function validate_transition($new_stage) {
        // Check if stage exists
        if (!isset($this->stage_config[$new_stage])) {
            return false;
        }
        
        // Check if transition is allowed
        if (!$this->can_transition_to($new_stage)) {
            return false;
        }
        
        // Additional validation can be added here
        return true;
    }
    
    /**
     * Handle stage-specific behaviors
     *
     * @param string $old_stage Old stage
     * @param string $new_stage New stage
     */
    private function handle_stage_specific_behaviors($old_stage, $new_stage) {
        $config = $this->stage_config[$new_stage];
        
        if (isset($config['behaviors'])) {
            foreach ($config['behaviors'] as $behavior => $enabled) {
                if ($enabled) {
                    $this->execute_behavior($behavior, $old_stage, $new_stage);
                }
            }
        }
    }
    
    /**
     * Execute specific behavior
     *
     * @param string $behavior Behavior name
     * @param string $old_stage Old stage
     * @param string $new_stage New stage
     */
    private function execute_behavior($behavior, $old_stage, $new_stage) {
        switch ($behavior) {
            case 'triggers_completion_hooks':
                do_action('arsol_pfw_project_completed', $this->project_id);
                break;
                
            case 'triggers_cancellation_hooks':
                do_action('arsol_pfw_project_cancelled', $this->project_id);
                break;
                
            case 'sends_completion_email':
                // Send completion email
                break;
                
            case 'sends_cancellation_email':
                // Send cancellation email
                break;
                
            case 'updates_wc_order':
                $this->set_wc_order_status($this->get_wc_order_status());
                break;
        }
    }
    
    /**
     * Get stage configuration
     *
     * @return array Stage configuration
     */
    public function get_stage_config() {
        return $this->stage_config;
    }
    
    /**
     * Set stage configuration
     *
     * @param array $config Stage configuration
     * @return bool Success status
     */
    public function set_stage_config($config) {
        $this->stage_config = $config;
        return true;
    }
    
    /**
     * Static method to get project stages
     *
     * @return array Array of project stages
     */
    public static function get_project_stages() {
        $instance = new self(0);
        return $instance->get_available_stages();
    }
    
    /**
     * Static method to get project stage by slug
     *
     * @param string $slug Stage slug
     * @return array|null Stage configuration
     */
    public static function get_project_stage_by_slug($slug) {
        $instance = new self(0);
        $config = $instance->get_stage_config();
        return isset($config[$slug]) ? $config[$slug] : null;
    }
    
    /**
     * Static method to bulk update stages
     *
     * @param array $project_ids Array of project IDs
     * @param string $stage_slug Stage slug
     * @return bool Success status
     */
    public static function bulk_update_stage($project_ids, $stage_slug) {
        $success = true;
        
        foreach ($project_ids as $project_id) {
            $stage_entity = new self($project_id);
            if (!$stage_entity->set_stage($stage_slug)) {
                $success = false;
            }
        }
        
        return $success;
    }
} 