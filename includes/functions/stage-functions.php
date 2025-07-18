<?php
/**
 * Stage Factory Functions for Arsol Projects for Woo
 *
 * Provides WooCommerce-aligned factory functions for stage management
 * Similar to wc_get_order_statuses(), wc_get_order_status_name(), etc.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Stage Handler instance
 * 
 * Factory function for Stage_Handler class
 * Similar to wc_get_order(), wc_get_product()
 * 
 * @return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler
 */
function arsol_pfw_get_stage_handler() {
    return new \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler();
}

/**
 * Set stage for a post
 * 
 * @param int $post_id The post ID
 * @param string $stage_slug The stage slug
 * @param string $taxonomy The taxonomy name
 * @param bool $create_if_missing Whether to create the stage if it doesn't exist
 * @return bool True on success
 */
function arsol_pfw_set_stage($post_id, $stage_slug, $taxonomy, $create_if_missing = false) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::set_stage($post_id, $stage_slug, $taxonomy, $create_if_missing);
}

/**
 * Create a stage if it doesn't exist
 * 
 * @param string $stage_slug The stage slug
 * @param string $taxonomy The taxonomy name
 * @param string $stage_name Optional stage name (defaults to slug)
 * @return bool True if created or already exists
 */
function arsol_pfw_create_stage($stage_slug, $taxonomy, $stage_name = null) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::create_stage($stage_slug, $taxonomy, $stage_name);
}

/**
 * Remove stage from a post
 * 
 * @param int $post_id The post ID
 * @param string $taxonomy The taxonomy name
 * @return bool True on success
 */
function arsol_pfw_remove_stage($post_id, $taxonomy) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::remove_stage($post_id, $taxonomy);
}

/**
 * Get stage for a post
 * 
 * @param int $post_id The post ID
 * @param string $taxonomy The taxonomy name
 * @return string|false Stage slug or false if not found
 */
function arsol_pfw_get_stage($post_id, $taxonomy) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage($post_id, $taxonomy);
}

/**
 * Get stage name for a post
 * 
 * @param int $post_id The post ID
 * @param string $taxonomy The taxonomy name
 * @return string|false Stage name or false if not found
 */
function arsol_pfw_get_stage_name($post_id, $taxonomy) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_name($post_id, $taxonomy);
}

/**
 * Get stage name by slug
 * 
 * @param string $stage_slug The stage slug
 * @param string $taxonomy The taxonomy name
 * @return string|false Stage name or false if not found
 */
function arsol_pfw_get_stage_name_by_slug($stage_slug, $taxonomy) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_name_by_slug($stage_slug, $taxonomy);
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
function arsol_pfw_get_posts_by_stage($stage_slug, $taxonomy, $post_type, $args = []) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_posts_by_stage($stage_slug, $taxonomy, $post_type, $args);
}

/**
 * Get stage statistics
 * 
 * @param string $taxonomy The taxonomy name
 * @param string $post_type The post type
 * @return array Array of stage statistics
 */
function arsol_pfw_get_stage_statistics($taxonomy, $post_type) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_statistics($taxonomy, $post_type);
}

/**
 * Get all available project stages
 * 
 * @return array Array of WP_Term objects
 */
function arsol_pfw_get_project_stages() {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_available_stages('arsol-pfw-project-stage');
}

/**
 * Get all available proposal stages
 * 
 * @return array Array of WP_Term objects
 */
function arsol_pfw_get_proposal_stages() {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_available_stages('arsol-pfw-proposal-stage');
}

/**
 * Get all available request stages
 * 
 * @return array Array of WP_Term objects
 */
function arsol_pfw_get_request_stages() {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_available_stages('arsol-pfw-request-stage');
}

/**
 * Get project stage name
 * 
 * @param string $stage_slug Stage slug
 * @return string|false Stage name or false if not found
 */
function arsol_pfw_get_project_stage_name($stage_slug) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_name_by_slug($stage_slug, 'arsol-pfw-project-stage');
}

/**
 * Get proposal stage name
 * 
 * @param string $stage_slug Stage slug
 * @return string|false Stage name or false if not found
 */
function arsol_pfw_get_proposal_stage_name($stage_slug) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_name_by_slug($stage_slug, 'arsol-pfw-proposal-stage');
}

/**
 * Get request stage name
 * 
 * @param string $stage_slug Stage slug
 * @return string|false Stage name or false if not found
 */
function arsol_pfw_get_request_stage_name($stage_slug) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_name_by_slug($stage_slug, 'arsol-pfw-request-stage');
}

/**
 * Get projects by stage
 * 
 * @param string $stage_slug Stage slug
 * @param array $args Additional query arguments
 * @return array Array of project IDs
 */
function arsol_pfw_get_projects_by_stage($stage_slug, $args = []) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_posts_by_stage($stage_slug, 'arsol-pfw-project-stage', 'arsol-pfw-project', $args);
}

/**
 * Get proposals by stage
 * 
 * @param string $stage_slug Stage slug
 * @param array $args Additional query arguments
 * @return array Array of proposal IDs
 */
function arsol_pfw_get_proposals_by_stage($stage_slug, $args = []) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_posts_by_stage($stage_slug, 'arsol-pfw-proposal-stage', 'arsol-pfw-proposal', $args);
}

/**
 * Get requests by stage
 * 
 * @param string $stage_slug Stage slug
 * @param array $args Additional query arguments
 * @return array Array of request IDs
 */
function arsol_pfw_get_requests_by_stage($stage_slug, $args = []) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_posts_by_stage($stage_slug, 'arsol-pfw-request-stage', 'arsol-pfw-request', $args);
}

/**
 * Get project stage statistics
 * 
 * @return array Array of stage statistics
 */
function arsol_pfw_get_project_stage_statistics() {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_statistics('arsol-pfw-project-stage', 'arsol-pfw-project');
}

/**
 * Get proposal stage statistics
 * 
 * @return array Array of stage statistics
 */
function arsol_pfw_get_proposal_stage_statistics() {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_statistics('arsol-pfw-proposal-stage', 'arsol-pfw-proposal');
}

/**
 * Get request stage statistics
 * 
 * @return array Array of stage statistics
 */
function arsol_pfw_get_request_stage_statistics() {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_statistics('arsol-pfw-request-stage', 'arsol-pfw-request');
}
