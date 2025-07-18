<?php
/**
 * Project Functions
 * 
 * Global functions for working with projects, following WooCommerce patterns
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get a single project
 *
 * @param int|WP_Post|Arsol_PFW_Project $the_project Project ID, post object, or project object
 * @return Arsol_PFW_Project|false Project object or false if not found
 */
function arsol_pfw_get_project($the_project = false) {
    if (empty($the_project)) {
        return false;
    }
    
    if ($the_project instanceof Arsol_PFW_Project) {
        return $the_project;
    }
    
    if (is_numeric($the_project)) {
        $the_project = get_post($the_project);
    }
    
    if (!$the_project || !is_object($the_project) || $the_project->post_type !== 'arsol-pfw-project') {
        return false;
    }
    
    return new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($the_project);
}

/**
 * Get multiple projects
 * 
 * @param array $args Query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_projects($args = array()) {
    $defaults = array(
        'post_type' => 'arsol-pfw-project',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    $args = wp_parse_args($args, $defaults);
    $posts = get_posts($args);
    
    return array_map('arsol_pfw_get_project', $posts);
}

/**
 * Get projects by customer ID
 * 
 * @param int $customer_id Customer ID
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_projects_by_customer($customer_id) {
    $args = array(
        'post_type' => 'arsol-pfw-project',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_arsol_pfw_customer_id',
                'value' => $customer_id,
                'compare' => '='
            )
        )
    );
    
    $posts = get_posts($args);
    $projects = array();
    
    foreach ($posts as $post) {
        $projects[] = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post);
    }
    
    return $projects;
}

/**
 * Get projects by post author
 * 
 * @param int $user_id User ID
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_projects_by_post_author($user_id) {
    $args = array(
        'post_type' => 'arsol-pfw-project',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'author' => $user_id, // Query by post_author
    );
    
    $posts = get_posts($args);
    $projects = array();
    
    foreach ($posts as $post) {
        $projects[] = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post);
    }
    
    return $projects;
}

/**
 * Get projects by user (customer or post author)
 * 
 * @param int $user_id User ID
 * @param string $role 'customer' or 'post_author' or 'both'
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_projects_by_user($user_id, $role = 'both') {
    if ($role === 'customer') {
        return arsol_pfw_get_projects_by_customer($user_id);
    } elseif ($role === 'post_author') {
        return arsol_pfw_get_projects_by_post_author($user_id);
    } else {
        // Get both customer and post author projects
        $customer_projects = arsol_pfw_get_projects_by_customer($user_id);
        $post_author_projects = arsol_pfw_get_projects_by_post_author($user_id);
        
        // Merge and remove duplicates
        $all_projects = array_merge($customer_projects, $post_author_projects);
        $unique_projects = array();
        
        foreach ($all_projects as $project) {
            $unique_projects[$project->get_id()] = $project;
        }
        
        return array_values($unique_projects);
    }
}

/**
 * Get projects by stage
 *
 * @param string $stage Stage slug
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_projects_by_stage($stage, $args = array()) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'arsol-pfw-project-stage',
            'field' => 'slug',
            'terms' => $stage,
        ),
    );
    return arsol_pfw_get_projects($args);
}

/**
 * Search projects
 * 
 * @param string $search_term Search term
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_search_projects($search_term, $args = array()) {
    $args['s'] = $search_term;
    return arsol_pfw_get_projects($args);
}

/**
 * Get project count
 * 
 * @param array $args Query arguments
 * @return int Number of projects
 */
function arsol_pfw_get_project_count($args = array()) {
    $args['posts_per_page'] = -1;
    $args['fields'] = 'ids';
    $posts = get_posts($args);
    return count($posts);
}

/**
 * Get count by user
 * 
 * @param int $user_id User ID
 * @param array $args Additional query arguments
 * @return int Number of projects
 */
function arsol_pfw_get_project_count_by_user($user_id, $args = array()) {
    $args['author'] = $user_id;
    return arsol_pfw_get_project_count($args);
}

/**
 * Get recent projects
 * 
 * @param int $limit Number of projects to return
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_recent_projects($limit = 5, $args = array()) {
    $args['posts_per_page'] = $limit;
    return arsol_pfw_get_projects($args);
}

/**
 * Get projects with orders
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_projects_with_orders($args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_project_woocommerce_order_id',
            'compare' => 'EXISTS',
        ),
    );
    return arsol_pfw_get_projects($args);
}

/**
 * Get projects with subscriptions
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_projects_with_subscriptions($args = array()) {
    $args['meta_query'] = array(
        array(
            'key' => '_arsol_pfw_project_woocommerce_subscription_id',
            'compare' => 'EXISTS',
        ),
    );
    return arsol_pfw_get_projects($args);
}

/**
 * Get stage counts using OOP stage entities
 * 
 * @return array Array of stage counts
 */
function arsol_pfw_get_project_stage_counts() {
    $stage_entity = new \Arsol_Projects_For_Woo\Taxonomies\Stages\Project_Stage(0);
    $statistics = $stage_entity->get_stage_statistics();
    
    $counts = array();
    foreach ($statistics as $stage_slug => $stage_data) {
        $counts[$stage_slug] = $stage_data['count'];
    }
    
    return $counts;
}

/**
 * Get available stages using OOP stage entities
 * 
 * @return array Array of available stages
 */
function arsol_pfw_get_available_project_stages() {
    $stage_entity = new \Arsol_Projects_For_Woo\Taxonomies\Stages\Project_Stage(0);
    $stages = $stage_entity->get_available_stages();
    
    $stage_options = array();
    foreach ($stages as $stage_slug => $stage_data) {
        $stage_options[$stage_slug] = $stage_data['label'];
    }
    
    return $stage_options;
}

/**
 * Get active projects
 *
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_active_projects($args = array()) {
    return arsol_pfw_get_projects_by_stage('in-progress', $args);
}

/**
 * Get completed projects
 *
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_completed_projects($args = array()) {
    return arsol_pfw_get_projects_by_stage('completed', $args);
}

/**
 * Get not started projects
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_not_started_projects($args = array()) {
    return arsol_pfw_get_projects_by_stage('not-started', $args);
}

/**
 * Get cancelled projects
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_cancelled_projects($args = array()) {
    return arsol_pfw_get_projects_by_stage('cancelled', $args);
}

/**
 * Get paused projects
 * 
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_paused_projects($args = array()) {
    return arsol_pfw_get_projects_by_stage('on-hold', $args);
}

/**
 * Get projects by date range
 * 
 * @param string $start_date Start date
 * @param string $end_date End date
 * @param array $args Additional query arguments
 * @return array Array of Arsol_PFW_Project objects
 */
function arsol_pfw_get_projects_by_date_range($start_date, $end_date, $args = array()) {
    $args['date_query'] = array(
        array(
            'after' => $start_date,
            'before' => $end_date,
            'inclusive' => true,
        ),
    );
    return arsol_pfw_get_projects($args);
}

/**
 * Bulk update project stages using OOP stage entities
 * 
 * @param array $project_ids Array of project IDs
 * @param string $stage_slug Stage slug to set
 * @return int Number of projects updated
 */
function arsol_pfw_bulk_update_project_stages($project_ids, $stage_slug) {
    return \Arsol_Projects_For_Woo\Taxonomies\Stages\Project_Stage::bulk_update_stage($project_ids, $stage_slug);
} 