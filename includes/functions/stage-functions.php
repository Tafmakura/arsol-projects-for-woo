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
 * Get all available project stages
 * 
 * @return array Array of WP_Term objects
 */
function arsol_pfw_get_project_stages() {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_available_stages('arsol-pfw-project-stage');
}

/**
 * Get all available proposal stages
 * 
 * @return array Array of WP_Term objects
 */
function arsol_pfw_get_proposal_stages() {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_available_stages('arsol-pfw-proposal-stage');
}

/**
 * Get all available request stages
 * 
 * @return array Array of WP_Term objects
 */
function arsol_pfw_get_request_stages() {
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_available_stages('arsol-pfw-request-stage');
}

/**
 * Get stage name by slug and entity type
 * 
 * @param string $stage_slug The stage slug
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return string|false Stage name or false if not found
 */
function arsol_pfw_get_stage_name($stage_slug, $entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage_name_by_slug($stage_slug, $taxonomy);
}

/**
 * Get current stage for a post
 * 
 * @param int $post_id The post ID
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return string|false Stage slug or false if not found
 */
function arsol_pfw_get_stage($post_id, $entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage($post_id, $taxonomy);
}

/**
 * Get current stage name for a post
 * 
 * @param int $post_id The post ID
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return string|false Stage name or false if not found
 */
function arsol_pfw_get_stage_name_for_post($post_id, $entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage_name($post_id, $taxonomy);
}

/**
 * Set stage for one or more posts
 * 
 * @param int|array $post_ids Single post ID or array of post IDs
 * @param string $stage_slug The stage slug
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @param bool $create_if_missing Whether to create the stage if it doesn't exist
 * @return bool True on success
 */
function arsol_pfw_set_stage($post_ids, $stage_slug, $entity_type, $create_if_missing = false) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::set_stage($post_ids, $stage_slug, $taxonomy, $create_if_missing);
}

/**
 * Create a new stage
 * 
 * @param string $stage_slug The stage slug
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @param string $stage_name Optional stage name (defaults to slug)
 * @return bool True if created or already exists
 */
function arsol_pfw_create_stage($stage_slug, $entity_type, $stage_name = null) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::create_stage($stage_slug, $taxonomy, $stage_name);
}

/**
 * Remove stage from one or more posts
 * 
 * @param int|array $post_ids Single post ID or array of post IDs
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return bool True on success
 */
function arsol_pfw_remove_stage($post_ids, $entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::remove_stage($post_ids, $taxonomy);
}

/**
 * Get items by stage
 * 
 * @param string $stage_slug The stage slug
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @param array $args Additional query arguments
 * @return array Array of post IDs
 */
function arsol_pfw_get_items_by_stage($stage_slug, $entity_type, $args = []) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $post_type_map = [
        'project' => 'arsol-pfw-project',
        'proposal' => 'arsol-pfw-proposal', 
        'request' => 'arsol-pfw-request'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    $post_type = $post_type_map[$entity_type] ?? '';
    
    if (!$taxonomy || !$post_type) {
        return [];
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_items_by_stage($stage_slug, $taxonomy, $post_type, $args);
}

/**
 * Get stage count using native WordPress term count properties
 * 
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return array Array of stage counts with native term properties
 */
function arsol_pfw_get_stage_count($entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $post_type_map = [
        'project' => 'arsol-pfw-project',
        'proposal' => 'arsol-pfw-proposal', 
        'request' => 'arsol-pfw-request'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    $post_type = $post_type_map[$entity_type] ?? '';
    
    if (!$taxonomy || !$post_type) {
        return [];
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage_count($taxonomy, $post_type);
}

/**
 * Get stage count for a specific stage
 * 
 * @param string $stage_slug The stage slug
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return int Count of items in the stage
 */
function arsol_pfw_get_stage_item_count($stage_slug, $entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $post_type_map = [
        'project' => 'arsol-pfw-project',
        'proposal' => 'arsol-pfw-proposal', 
        'request' => 'arsol-pfw-request'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    $post_type = $post_type_map[$entity_type] ?? '';
    
    if (!$taxonomy || !$post_type) {
        return 0;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::get_stage_item_count($stage_slug, $taxonomy, $post_type);
}

/**
 * Check if a stage exists
 * 
 * @param string $stage_slug The stage slug
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return bool True if stage exists
 */
function arsol_pfw_stage_exists($stage_slug, $entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::stage_exists($stage_slug, $taxonomy);
}

/**
 * Delete a stage
 * 
 * @param string $stage_slug The stage slug
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return bool True on success
 */
function arsol_pfw_delete_stage($stage_slug, $entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::delete_stage($stage_slug, $taxonomy);
}

/**
 * Update stage name
 * 
 * @param string $stage_slug The stage slug
 * @param string $new_name The new stage name
 * @param string $entity_type The entity type: 'project', 'proposal', or 'request'
 * @return bool True on success
 */
function arsol_pfw_update_stage_name($stage_slug, $new_name, $entity_type) {
    $taxonomy_map = [
        'project' => 'arsol-pfw-project-stage',
        'proposal' => 'arsol-pfw-proposal-stage', 
        'request' => 'arsol-pfw-request-stage'
    ];
    
    $taxonomy = $taxonomy_map[$entity_type] ?? '';
    if (!$taxonomy) {
        return false;
    }
    
    return \Arsol_Projects_For_Woo\Core\Stage_Handler::update_stage_name($stage_slug, $new_name, $taxonomy);
}
