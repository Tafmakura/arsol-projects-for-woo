<?php
/**
 * Stage Handler for Arsol Projects for Woo
 *
 * Uses WordPress taxonomies directly - no complex interfaces or WooCommerce integration
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Taxonomies\Stages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stage Handler Class
 *
 * Provides simple, efficient stage management using WordPress taxonomies directly.
 * Replaces complex stage entities with lightweight taxonomy operations.
 */
class Stage_Handler {
    
    /**
     * Set stage for a post, optionally creating the stage if it doesn't exist
     * 
     * @param int $post_id The post ID
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @param bool $create_if_missing Whether to create the stage if it doesn't exist
     * @return bool True on success
     */
    public static function set_stage($post_id, $stage_slug, $taxonomy, $create_if_missing = false) {
        // Create stage if requested and missing
        if ($create_if_missing) {
            self::create_stage($stage_slug, $taxonomy);
        }
        
        // Set the stage using WordPress taxonomy
        $result = wp_set_object_terms($post_id, $stage_slug, $taxonomy, false);
        
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
     * Remove stage (removes all taxonomies from current post)
     * 
     * @param int $post_id The post ID
     * @param string $taxonomy The taxonomy name
     * @return bool True on success
     */
    public static function remove_stage($post_id, $taxonomy) {
        $result = wp_set_object_terms($post_id, [], $taxonomy, false);
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
     * Get posts by stage
     * 
     * @param string $stage_slug The stage slug
     * @param string $taxonomy The taxonomy name
     * @param string $post_type The post type
     * @param array $args Additional query arguments
     * @return array Array of post IDs
     */
    public static function get_posts_by_stage($stage_slug, $taxonomy, $post_type, $args = []) {
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
     * Get stage statistics
     * 
     * @param string $taxonomy The taxonomy name
     * @param string $post_type The post type
     * @return array Array of stage statistics
     */
    public static function get_stage_statistics($taxonomy, $post_type) {
        $stages = self::get_available_stages($taxonomy);
        $statistics = [];
        
        foreach ($stages as $stage) {
            $post_ids = self::get_posts_by_stage($stage->slug, $taxonomy, $post_type);
            $statistics[$stage->slug] = [
                'count' => count($post_ids),
                'label' => $stage->name,
                'term_id' => $stage->term_id,
            ];
        }
        
        return $statistics;
    }
}
