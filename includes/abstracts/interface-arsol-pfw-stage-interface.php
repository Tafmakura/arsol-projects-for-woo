<?php
/**
 * Stage Interface
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Interfaces
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stage Interface
 * 
 * For entities that support stage management (Request, Proposal, Project)
 */
interface ARSOL_PFW_Stage_Interface {
    
    /**
     * Get current stage
     *
     * @param string $context What the value is for. Valid values are 'view' and 'edit'.
     * @return string Current stage slug
     */
    public function get_stage($context = 'view');
    
    /**
     * Set stage
     *
     * @param string $stage Stage slug
     */
    public function set_stage($stage);
    
    /**
     * Update stage with hooks and validation
     *
     * @param string $new_stage New stage slug
     * @param string $note Optional note for the stage change
     */
    public function update_stage($new_stage, $note = '');
    
    /**
     * Get available stages for this entity
     *
     * @return array Array of stage_slug => stage_name
     */
    public function get_available_stages();
} 