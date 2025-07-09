<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get project by ID
 *
 * @param int $project_id Project ID
 * @return \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_CPT|false
 */
function arsol_pfw_get_project($project_id) {
    if (!$project_id) {
        return false;
    }
    
    $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_CPT($project_id);
    return $project->get_id() ? $project : false;
}

/**
 * Create new project
 *
 * @param array $args Project arguments
 * @return \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_CPT|WP_Error
 */
function arsol_pfw_create_project($args = array()) {
    $defaults = array(
        'name'        => '',
        'customer_id' => 0,
        'budget'      => 0,
        'deadline'    => '',
        'timeline'    => '',
        'description' => '',
        'progress'    => 0,
        'stage'       => 'not-started',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Validate required fields
    if (empty($args['name'])) {
        return new WP_Error('missing_name', 'Project name is required');
    }
    
    if (empty($args['customer_id'])) {
        return new WP_Error('missing_customer', 'Customer ID is required');
    }
    
    $project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_CPT();
    
    // Set properties
    $project->set_name($args['name']);
    $project->set_customer_id($args['customer_id']);
    $project->set_budget($args['budget']);
    $project->set_deadline($args['deadline']);
    $project->set_progress($args['progress']);
    $project->set_prop('description', $args['description']);
    $project->set_prop('timeline', $args['timeline']);
    $project->set_stage($args['stage']);
    
    return $project;
}

/**
 * Get projects by stage
 *
 * @param string $stage Stage slug
 * @param array $args Query arguments
 * @return array
 */
function arsol_pfw_get_projects_by_stage($stage, $args = array()) {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_entities_by_stage('project', $stage, $args);
}

/**
 * Get project stage counts
 *
 * @return array
 */
function arsol_pfw_get_project_stage_counts() {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage_counts('project');
}

/**
 * Get available project stages
 *
 * @return array
 */
function arsol_pfw_get_project_available_stages() {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::get_available_stages('project');
}

/**
 * Update project stage
 *
 * @param int $project_id Project ID
 * @param string $new_stage New stage slug
 * @return bool|WP_Error
 */
function arsol_pfw_update_project_stage($project_id, $new_stage) {
    return \Arsol_Projects_For_Woo\Core\Stage_Manager::update_stage($project_id, 'project', $new_stage);
}

/**
 * Start project
 *
 * @param int $project_id Project ID
 * @return bool|WP_Error
 */
function arsol_pfw_start_project($project_id) {
    $project = arsol_pfw_get_project($project_id);
    if (!$project) {
        return new WP_Error('invalid_project', 'Project not found');
    }
    
    return $project->start();
}

/**
 * Complete project
 *
 * @param int $project_id Project ID
 * @return bool|WP_Error
 */
function arsol_pfw_complete_project($project_id) {
    $project = arsol_pfw_get_project($project_id);
    if (!$project) {
        return new WP_Error('invalid_project', 'Project not found');
    }
    
    return $project->complete();
}

/**
 * Cancel project
 *
 * @param int $project_id Project ID
 * @param string $reason Cancellation reason
 * @return bool|WP_Error
 */
function arsol_pfw_cancel_project($project_id, $reason = '') {
    $project = arsol_pfw_get_project($project_id);
    if (!$project) {
        return new WP_Error('invalid_project', 'Project not found');
    }
    
    return $project->cancel($reason);
}

/**
 * Get projects by customer
 *
 * @param int $customer_id Customer ID
 * @param array $args Query arguments
 * @return array
 */
function arsol_pfw_get_projects_by_customer($customer_id, $args = array()) {
    $default_args = array(
        'post_type'   => 'arsol-pfw-project',
        'post_status' => 'publish',
        'author'      => $customer_id,
        'posts_per_page' => -1,
    );
    
    $args = wp_parse_args($args, $default_args);
    return get_posts($args);
}

/**
 * Update project progress
 *
 * @param int $project_id Project ID
 * @param int $progress Progress percentage (0-100)
 * @return bool|WP_Error
 */
function arsol_pfw_update_project_progress($project_id, $progress) {
    $project = arsol_pfw_get_project($project_id);
    if (!$project) {
        return new WP_Error('invalid_project', 'Project not found');
    }
    
    $project->set_progress($progress);
    return $project->save();
}

/**
 * Get active projects
 *
 * @param array $args Query arguments
 * @return array
 */
function arsol_pfw_get_active_projects($args = array()) {
    return arsol_pfw_get_projects_by_stage('in-progress', $args);
}

/**
 * Get completed projects
 *
 * @param array $args Query arguments
 * @return array
 */
function arsol_pfw_get_completed_projects($args = array()) {
    return arsol_pfw_get_projects_by_stage('completed', $args);
} 