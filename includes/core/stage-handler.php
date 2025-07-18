<?php
/**
 * Stage Handler for Arsol Projects for Woo
 *
 * Uses WordPress taxonomies directly - no complex interfaces or WooCommerce integration
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stage Handler Class
 *
 * Provides simple, efficient stage management using WordPress taxonomies directly.
 * Replaces complex stage entities with lightweight taxonomy operations.
 * 
 * All management methods accept single post ID or array of post IDs for unified API.
 */
class Stage_Handler {
    
    /**
     * Set stage for one or more posts, optionally creating the stage if it doesn't exist
     * 
     * @param int|array $post_ids Single post ID or array of post IDs
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @param bool $create_if_missing Whether to create the stage if it doesn't exist
     * @return bool True on success
     */
    public static function set_stage($post_ids, $stage_slug, $taxonomy, $create_if_missing = false) {
        // Normalize to array
        $post_ids = is_array($post_ids) ? $post_ids : [$post_ids];
        
        if (empty($post_ids)) {
            return false;
        }
        
        // Create stage if requested and missing
        if ($create_if_missing) {
            self::create_stage($stage_slug, $taxonomy);
        }
        
        // Set the stage using WordPress taxonomy
        $result = wp_set_object_terms($post_ids, $stage_slug, $taxonomy, false);
        
        return !is_wp_error($result);
    }
    
    /**
     * Create a stage if it doesn't exist
     * 
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @param string $stage_name Optional stage name (defaults to slug)
     * @return bool True if created or already exists
     */
    public static function create_stage($stage_slug, $taxonomy, $stage_name = null) {
        // Check if stage already exists
        if (term_exists($stage_slug, $taxonomy)) {
            return true;
        }
        
        // Use slug as name if not provided
        $stage_name = $stage_name ?: ucfirst(str_replace('-', ' ', $stage_slug));
        
        // Create the term
        $result = wp_insert_term($stage_name, $taxonomy, [
            'slug' => $stage_slug,
            'description' => $stage_name . ' stage'
        ]);
        
        return !is_wp_error($result);
    }
    
    /**
     * Remove stage from one or more posts (removes all taxonomies from current posts)
     * 
     * @param int|array $post_ids Single post ID or array of post IDs
     * @param string $taxonomy The taxonomy name
     * @return bool True on success
     */
    public static function remove_stage($post_ids, $taxonomy) {
        // Normalize to array
        $post_ids = is_array($post_ids) ? $post_ids : [$post_ids];
        
        if (empty($post_ids)) {
            return false;
        }
        
        $result = wp_set_object_terms($post_ids, [], $taxonomy, false);
        return !is_wp_error($result);
    }
    
    /**
     * Get available stages for a taxonomy
     * 
     * @param string $taxonomy The taxonomy name
     * @return array Array of stage terms
     */
    public static function get_available_stages($taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        if (is_wp_error($terms)) {
            return [];
        }
        
        return $terms;
    }
    
    /**
     * Get stage for a post
     * 
     * @param int $post_id The post ID
     * @param string $taxonomy The taxonomy name
     * @return string|false Stage slug or false if not found
     */
    public static function get_stage($post_id, $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'slugs']);
        
        if (is_wp_error($terms) || empty($terms)) {
            return false;
        }
        
        return $terms[0];
    }
    
    /**
     * Get stage name for a post
     * 
     * @param int $post_id The post ID
     * @param string $taxonomy The taxonomy name
     * @return string|false Stage name or false if not found
     */
    public static function get_stage_name($post_id, $taxonomy) {
        $terms = wp_get_object_terms($post_id, $taxonomy);
        
        if (is_wp_error($terms) || empty($terms)) {
            return false;
        }
        
        return $terms[0]->name;
    }
    
    /**
     * Get stage name by slug
     * 
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @return string|false Stage name or false if not found
     */
    public static function get_stage_name_by_slug($stage_slug, $taxonomy) {
        $term = get_term_by('slug', $stage_slug, $taxonomy);
        
        if (!$term || is_wp_error($term)) {
            return false;
        }
        
        return $term->name;
    }
    
    /**
     * Get items by stage
     * 
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @param string $post_type The post type
     * @param array $args Additional query arguments
     * @return array Array of post IDs
     */
    public static function get_items_by_stage($stage_slug, $taxonomy, $post_type, $args = []) {
        $defaults = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'field' => 'slug',
                    'terms' => $stage_slug,
                ],
            ],
            'fields' => 'ids',
            'posts_per_page' => -1,
        ];
        
        $query_args = wp_parse_args($args, $defaults);
        return get_posts($query_args);
    }
    
    /**
     * Get stage count using native WordPress term count properties
     * 
     * @param string $taxonomy The taxonomy name
     * @param string $post_type The post type
     * @return array Array of stage counts with native term properties
     */
    public static function get_stage_count($taxonomy, $post_type) {
        $stages = self::get_available_stages($taxonomy);
        $statistics = [];
        
        foreach ($stages as $stage) {
            // Use native WordPress term count property for efficiency
            $statistics[$stage->slug] = [
                'count' => $stage->count,
                'label' => $stage->name,
                'term_id' => $stage->term_id,
                'slug' => $stage->slug,
                'description' => $stage->description,
            ];
        }
        
        return $statistics;
    }
    
    /**
     * Get stage count for a specific stage
     * 
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @param string $post_type The post type
     * @return int Count of items in the stage
     */
    public static function get_stage_item_count($stage_slug, $taxonomy, $post_type) {
        $term = get_term_by('slug', $stage_slug, $taxonomy);
        
        if (!$term || is_wp_error($term)) {
            return 0;
        }
        
        return $term->count;
    }
    
    /**
     * Check if a stage exists
     * 
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @return bool True if stage exists
     */
    public static function stage_exists($stage_slug, $taxonomy) {
        return term_exists($stage_slug, $taxonomy) !== 0;
    }
    
    /**
     * Delete a stage (removes the term from the taxonomy)
     * 
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @return bool True on success
     */
    public static function delete_stage($stage_slug, $taxonomy) {
        $term = get_term_by('slug', $stage_slug, $taxonomy);
        
        if (!$term || is_wp_error($term)) {
            return false;
        }
        
        $result = wp_delete_term($term->term_id, $taxonomy);
        return !is_wp_error($result);
    }
    
    /**
     * Update stage name
     * 
     * @param string $stage_slug The stage slug
     * @param string $new_name The new stage name
     * @param string $taxonomy The taxonomy name
     * @return bool True on success
     */
    public static function update_stage_name($stage_slug, $new_name, $taxonomy) {
        $term = get_term_by('slug', $stage_slug, $taxonomy);
        
        if (!$term || is_wp_error($term)) {
            return false;
        }
        
        $result = wp_update_term($term->term_id, $taxonomy, [
            'name' => $new_name
        ]);
        
        return !is_wp_error($result);
    }
}
