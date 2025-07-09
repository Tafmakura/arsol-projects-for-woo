<?php

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dynamic Stage Manager following WooCommerce status management patterns
 * Works with taxonomy terms dynamically - no hardcoded stages
 * Graceful behavior when no stages exist
 */
class Stage_Handler {
    
    // Entity type to taxonomy mapping
    protected static $taxonomies = array(
        'request'  => 'arsol-pfw-request-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'project'  => 'arsol-pfw-project-stage',
    );

    /**
     * Stage transition tracking
     * @var array
     */
    protected static $stage_transition = array();

    /**
     * Cache for available stages
     * @var array
     */
    protected static $stages_cache = array();

    /**
     * Get stage for entity (handles taxonomy internally)
     *
     * @param int $entity_id Entity ID
     * @param string $entity_type Entity type (request, proposal, project)
     * @return string
     */
    public static function get_stage($entity_id, $entity_type) {
        if (!$entity_id || !$entity_type) {
            return '';
        }

        $taxonomy = self::get_taxonomy($entity_type);
        if (!$taxonomy) {
            return '';
        }
        
        $terms = wp_get_object_terms($entity_id, $taxonomy, array('fields' => 'slugs'));
        
        if (!empty($terms) && !is_wp_error($terms)) {
            return $terms[0];
        }
        
        // No stage set - return empty string (graceful behavior)
        return '';
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
        if (!$entity_id || !$entity_type || !$stage) {
            return new \WP_Error('missing_params', 'Missing required parameters');
        }

        $taxonomy = self::get_taxonomy($entity_type);
        if (!$taxonomy) {
            return new \WP_Error('invalid_entity_type', 'Invalid entity type: ' . $entity_type);
        }
        
        // Validate stage exists in taxonomy
        if (!self::stage_exists_in_taxonomy($entity_type, $stage)) {
            return new \WP_Error('invalid_stage', 'Stage does not exist: ' . $stage);
        }
        
        // Set taxonomy term
        $result = wp_set_object_terms($entity_id, $stage, $taxonomy);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return true;
    }

    /**
     * Update stage with hooks (EXACT WooCommerce pattern)
     *
     * @param int $entity_id Entity ID
     * @param string $entity_type Entity type
     * @param string $new_stage New stage slug
     * @param bool $manual Whether this is a manual change
     * @param string $note Optional note about the change
     * @return bool|WP_Error
     */
    public static function update_stage($entity_id, $entity_type, $new_stage, $manual = true, $note = '') {
        $old_stage = self::get_stage($entity_id, $entity_type);
        
        if ($old_stage === $new_stage) {
            return true;
        }

        // Store transition data (like WooCommerce)
        self::$stage_transition = array(
            'from' => $old_stage,
            'to' => $new_stage,
            'note' => $note,
            'manual' => $manual,
        );
        
        // Pre-update hook
        $can_update = apply_filters("arsol_pfw_can_update_{$entity_type}_stage", true, $entity_id, $old_stage, $new_stage);
        if (!$can_update) {
            // Reset transition data
            self::$stage_transition = array();
            return new \WP_Error('stage_update_prevented', 'Stage update was prevented by filter');
        }
        
        // Update stage
        $result = self::set_stage($entity_id, $entity_type, $new_stage);
        
        if (is_wp_error($result)) {
            // Reset transition data
            self::$stage_transition = array();
            return $result;
        }

        // Fire hooks in EXACT WooCommerce order
        self::trigger_stage_transition($entity_id, $entity_type);
        
        return true;
    }

    /**
     * Handle stage transition (follows WooCommerce status_transition exactly)
     *
     * @param int $entity_id Entity ID
     * @param string $entity_type Entity type
     */
    protected static function trigger_stage_transition($entity_id, $entity_type) {
        $stage_transition = self::$stage_transition;
        
        // Reset stage transition variable (like WooCommerce)
        self::$stage_transition = array();
        
        if ($stage_transition) {
            try {
                // Get entity object for hooks
                $entity_object = self::get_entity_object($entity_id, $entity_type);
                
                /**
                 * Hook 1: arsol_pfw_{entity}_stage_{new_stage}
                 * Fires when entity stage is changed to specific stage
                 * 
                 * @param int $entity_id Entity ID
                 * @param object $entity_object Entity object
                 * @param array $stage_transition Stage transition data
                 */
                do_action("arsol_pfw_{$entity_type}_stage_{$stage_transition['to']}", $entity_id, $entity_object, $stage_transition);
                
                if (!empty($stage_transition['from'])) {
                    /**
                     * Hook 2: arsol_pfw_{entity}_stage_{old_stage}_to_{new_stage}
                     * Fires when entity stage changes from specific stage to specific stage
                     * 
                     * @param int $entity_id Entity ID
                     * @param object $entity_object Entity object
                     */
                    do_action("arsol_pfw_{$entity_type}_stage_{$stage_transition['from']}_to_{$stage_transition['to']}", $entity_id, $entity_object);
                    
                    /**
                     * Hook 3: arsol_pfw_{entity}_stage_changed
                     * Fires when entity stage is changed (general hook)
                     * 
                     * @param int $entity_id Entity ID
                     * @param string $old_stage Old stage
                     * @param string $new_stage New stage
                     * @param object $entity_object Entity object
                     */
                    do_action("arsol_pfw_{$entity_type}_stage_changed", $entity_id, $stage_transition['from'], $stage_transition['to'], $entity_object);
                }
                
            } catch (Exception $e) {
                // Log error (like WooCommerce)
                if (function_exists('wc_get_logger')) {
                    $logger = wc_get_logger();
                    $logger->error("Stage transition error for {$entity_type} {$entity_id}: " . $e->getMessage(), array('source' => 'arsol-pfw'));
                } else {
                    error_log("Stage transition error for {$entity_type} {$entity_id}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Get available stages for entity type (dynamic from taxonomy)
     *
     * @param string $entity_type Entity type
     * @return array
     */
    public static function get_available_stages($entity_type) {
        // Check cache first
        if (isset(self::$stages_cache[$entity_type])) {
            return self::$stages_cache[$entity_type];
        }

        $taxonomy = self::get_taxonomy($entity_type);
        if (!$taxonomy) {
            return array();
        }

        // Get all terms from taxonomy
        $terms = get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'term_order',
            'order'      => 'ASC',
        ));

        $stages = array();
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $stages[$term->slug] = $term->name;
            }
        }

        // Cache the result
        self::$stages_cache[$entity_type] = $stages;

        return $stages;
    }

    /**
     * Check if stage exists in taxonomy
     *
     * @param string $entity_type Entity type
     * @param string $stage_slug Stage slug
     * @return bool
     */
    public static function stage_exists_in_taxonomy($entity_type, $stage_slug) {
        $taxonomy = self::get_taxonomy($entity_type);
        if (!$taxonomy) {
            return false;
        }

        $term = get_term_by('slug', $stage_slug, $taxonomy);
        return !empty($term) && !is_wp_error($term);
    }

    /**
     * Get stage label
     *
     * @param string $entity_type Entity type
     * @param string $stage_slug Stage slug
     * @return string
     */
    public static function get_stage_label($entity_type, $stage_slug) {
        $taxonomy = self::get_taxonomy($entity_type);
        if (!$taxonomy) {
            return $stage_slug;
        }

        $term = get_term_by('slug', $stage_slug, $taxonomy);
        if ($term && !is_wp_error($term)) {
            return $term->name;
        }

        return $stage_slug;
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
     * Clear stages cache
     *
     * @param string $entity_type Optional entity type to clear specific cache
     */
    public static function clear_stages_cache($entity_type = null) {
        if ($entity_type) {
            unset(self::$stages_cache[$entity_type]);
        } else {
            self::$stages_cache = array();
        }
    }

    /**
     * Get entity object for hooks
     *
     * @param int $entity_id Entity ID
     * @param string $entity_type Entity type
     * @return object|null
     */
    protected static function get_entity_object($entity_id, $entity_type) {
        try {
            switch ($entity_type) {
                case 'request':
                    if (class_exists('\Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Request_CPT')) {
                        return new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Request_CPT($entity_id);
                    }
                    break;
                case 'proposal':
                    if (class_exists('\Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Project_Proposal_CPT')) {
                        return new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Project_Proposal_CPT($entity_id);
                    }
                    break;
                case 'project':
                    if (class_exists('\Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_CPT')) {
                        return new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_CPT($entity_id);
                    }
                    break;
            }
        } catch (Exception $e) {
            // Log error but don't break the hook system
            error_log("Error creating entity object for {$entity_type} {$entity_id}: " . $e->getMessage());
        }
        
        return null;
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
}
?> 