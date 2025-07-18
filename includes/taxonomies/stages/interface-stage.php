<?php
/**
 * Stage Interface for Arsol Projects for Woo
 *
 * Defines the interface for stage entities that can have different behaviors
 * for different stages (transitions, validation, WooCommerce integration, etc.)
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies\Stages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stage Interface
 *
 * Defines the contract for stage entities that can have different behaviors
 * for different stages.
 */
interface Stage_Interface {
    
    /**
     * Get current stage
     * 
     * @return string Current stage slug
     */
    public function get_stage();
    
    /**
     * Set stage with validation
     * 
     * @param string $stage Stage slug
     * @return bool Success status
     */
    public function set_stage($stage);
    
    /**
     * Update stage with optional notes
     *
     * @param string $new_stage New stage slug
     * @param string $note Optional note about the stage change
     * @return bool Success status
     */
    public function update_stage($new_stage, $note = '');
    
    /**
     * Get stage label
     *
     * @return string Human-readable stage label
     */
    public function get_stage_label();
    
    /**
     * Get stage notes
     *
     * @return string Stage notes
     */
    public function get_stage_notes();
    
    /**
     * Set stage notes
     *
     * @param string $notes Stage notes
     * @return bool Success status
     */
    public function set_stage_notes($notes);
    
    /**
     * Get stage history
     *
     * @return array Array of stage change history
     */
    public function get_stage_history();
    
    /**
     * Get allowed transitions
     *
     * @return array Array of allowed stage transitions
     */
    public function get_allowed_transitions();
    
    /**
     * Check if stage transition is allowed
     *
     * @param string $new_stage New stage slug
     * @return bool True if transition is allowed
     */
    public function can_transition_to($new_stage);
    
    /**
     * Check if current stage is final
     *
     * @return bool True if current stage is final
     */
    public function is_final_stage();
    
    /**
     * Check if current stage is initial
     *
     * @return bool True if current stage is initial
     */
    public function is_initial_stage();
    
    /**
     * Get available stages
     *
     * @return array Array of available stages
     */
    public function get_available_stages();
    
    /**
     * Get stage duration
     *
     * @return int Duration in seconds
     */
    public function get_stage_duration();
    
    /**
     * Get stage start time
     *
     * @return int Unix timestamp
     */
    public function get_stage_start_time();
    
    /**
     * Get WooCommerce order status mapping
     *
     * @return string WooCommerce order status
     */
    public function get_wc_order_status();
    
    /**
     * Set WooCommerce order status
     *
     * @param string $wc_status WooCommerce order status
     * @return bool Success status
     */
    public function set_wc_order_status($wc_status);
    
    /**
     * Trigger stage change hooks
     *
     * @param string $old_stage Old stage slug
     * @param string $new_stage New stage slug
     * @return void
     */
    public function trigger_stage_change_hooks($old_stage, $new_stage);
    
    /**
     * Validate stage transition
     *
     * @param string $new_stage New stage slug
     * @return bool True if transition is valid
     */
    public function validate_transition($new_stage);
    
    /**
     * Get stage configuration
     *
     * @return array Stage configuration
     */
    public function get_stage_config();
    
    /**
     * Set stage configuration
     *
     * @param array $config Stage configuration
     * @return bool Success status
     */
    public function set_stage_config($config);
} 