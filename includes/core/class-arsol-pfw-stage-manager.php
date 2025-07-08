<?php

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Stage_Manager {
    
    // Entity type to taxonomy mapping
    protected static $taxonomies = array(
        'request'  => 'arsol-pfw-request-stage',
        'proposal' => 'arsol-pfw-proposal-stage',
        'project'  => 'arsol-pfw-project-stage',
    );
    
    // Default stages for each entity type
    protected static $default_stages = array(
        'request' => array(
            'pending-review' => 'Pending Review',
            'under-review'   => 'Under Review',
            'on-hold'        => 'On Hold',
            'approved'       => 'Approved',
            'rejected'       => 'Rejected',
        ),
        'proposal' => array(
            'processing' => 'Processing',
            'approved'   => 'Approved',
            'rejected'   => 'Rejected',
            'expired'    => 'Expired',
        ),
        'project' => array(
            'not-started' => 'Not Started',
            'in-progress' => 'In Progress',
            'on-hold'     => 'On Hold',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
        ),
    );
    
    /**
     * Get stage for entity (handles taxonomy internally)
     *
     * @param int $entity_id Entity ID
     * @param string $entity_type Entity type (request, proposal, project)
     * @return string
     */
    public static function get_stage($entity_id, $entity_type) {
        $taxonomy = self::get_taxonomy($entity_type);
        if (!$taxonomy) {
            return '';
        }
        
        $terms = wp_get_object_terms($entity_id, $taxonomy, array('fields' => 'slugs'));
        
        if (!empty($terms) && !is_wp_error($terms)) {
            return $terms[0];
        }
        
        // Return default stage if none set
        $default_stages = self::get_default_stages($entity_type);
        return !empty($default_stages) ? array_key_first($default_stages) : '';
    }
    
    /**
     * Set stage for entity (handles taxonomy internally)
     *
     * @param int $entity_id Entity ID
     * @param string $entity_type Entity type
     * @param string $stage Stage slug
     * @return bool|WP_Error
     */
    public static function set_stage($entity_id, $entity_type, $stage) {
        $taxonomy = self::get_taxonomy($entity_type);
        if (!$taxonomy) {
            return new \WP_Error('invalid_entity_type', 'Invalid entity type: ' . $entity_type);
        }
        
        // Validate stage
        if (!self::is_valid_stage($entity_type, $stage)) {
            return new \WP_Error('invalid_stage', 'Invalid stage for ' . $entity_type . ': ' . $stage);
        }
        
        // Set taxonomy term
        $result = wp_set_object_terms($entity_id, $stage, $taxonomy);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return true;
    }
    
    /**
     * Update stage with hooks (WooCommerce pattern)
     *
     * @param int $entity_id Entity ID
     * @param string $entity_type Entity type
     * @param string $new_stage New stage slug
     * @return bool|WP_Error
     */
    public static function update_stage($entity_id, $entity_type, $new_stage) {
        $old_stage = self::get_stage($entity_id, $entity_type);
        
        if ($old_stage === $new_stage) {
            return true;
        }
        
        // Pre-update hook
        $can_update = apply_filters("arsol_pfw_can_update_{$entity_type}_stage", true, $entity_id, $old_stage, $new_stage);
        if (!$can_update) {
            return new \WP_Error('stage_update_prevented', 'Stage update was prevented by filter');
        }
        
        // Update stage
        $result = self::set_stage($entity_id, $entity_type, $new_stage);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Fire hooks (WooCommerce pattern)
        do_action("arsol_pfw_{$entity_type}_stage_changed", $entity_id, $old_stage, $new_stage);
        do_action("arsol_pfw_{$entity_type}_stage_{$old_stage}_to_{$new_stage}", $entity_id);
        
        return true;
    }
    
    /**
     * Get available stages for entity type
     *
     * @param string $entity_type Entity type
     * @return array
     */
    public static function get_available_stages($entity_type) {
        return apply_filters("arsol_pfw_{$entity_type}_available_stages", self::get_default_stages($entity_type));
    }
    
    /**
     * Get entities by stage
     *
     * @param string $entity_type Entity type
     * @param string $stage Stage slug
     * @param array $args Query arguments
     * @return array
     */
    public static function get_entities_by_stage($entity_type, $stage, $args = array()) {
        $post_type = "arsol-pfw-{$entity_type}";
        $taxonomy = self::get_taxonomy($entity_type);
        
        if (!$taxonomy) {
            return array();
        }
        
        $default_args = array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $stage,
                ),
            ),
        );
        
        $args = wp_parse_args($args, $default_args);
        return get_posts($args);
    }
    
    /**
     * Get stage counts for entity type
     *
     * @param string $entity_type Entity type
     * @return array
     */
    public static function get_stage_counts($entity_type) {
        $taxonomy = self::get_taxonomy($entity_type);
        if (!$taxonomy) {
            return array();
        }
        
        $terms = get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ));
        
        $counts = array();
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $counts[$term->slug] = $term->count;
            }
        }
        
        return $counts;
    }
    
    /**
     * Get taxonomy for entity type
     *
     * @param string $entity_type Entity type
     * @return string
     */
    protected static function get_taxonomy($entity_type) {
        return isset(self::$taxonomies[$entity_type]) ? self::$taxonomies[$entity_type] : '';
    }
    
    /**
     * Get default stages for entity type
     *
     * @param string $entity_type Entity type
     * @return array
     */
    protected static function get_default_stages($entity_type) {
        return isset(self::$default_stages[$entity_type]) ? self::$default_stages[$entity_type] : array();
    }
    
    /**
     * Check if stage is valid for entity type
     *
     * @param string $entity_type Entity type
     * @param string $stage Stage slug
     * @return bool
     */
    protected static function is_valid_stage($entity_type, $stage) {
        $available_stages = self::get_available_stages($entity_type);
        return array_key_exists($stage, $available_stages);
    }
} 