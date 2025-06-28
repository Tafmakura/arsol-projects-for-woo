<?php
/**
 * Project Sidebar - Active Projects
 *
 * @package Arsol_Projects_For_Woo
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Variables provided by parent template (project-overview.php):
// $project_id, $project_title, $current_post, $current_post_type, 
// $project_type, $current_status, $wrapper_data

$post_id = $project_id;
$post_type = $project_type;
$status = $current_status;
$cpt = 'arsol-project';

?>

<div class="project-sidebar sidebar-active">
    <?php
    /**
     * Hook: arsol_pfw_project_sidebar_before
     * 
     * @param string $post_type Project type: 'active'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_project_sidebar_before', $post_type, $status, $post_id, $cpt);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_sidebar_meta
     * 
     * @param string $post_type Project type: 'active'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_project_sidebar_meta', $post_type, $status, $post_id, $cpt);
    
    /**
     * Generic sidebar metadata section (backward compatibility)
     */
    do_action('arsol_pfw_sidebar_meta', $post_type, $status, $post_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_sidebar_form
     * 
     * @param string $post_type Project type: 'active'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_project_sidebar_form', $post_type, $status, $post_id, $cpt);
    
    /**
     * Generic sidebar form section (unified form with filterable fields) (backward compatibility)
     */
    do_action('arsol_pfw_sidebar_form', $post_type, $status, $post_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_sidebar_actions
     * 
     * @param string $post_type Project type: 'active'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_project_sidebar_actions', $post_type, $status, $post_id, $cpt);
    
    /**
     * Generic sidebar secondary actions section (backward compatibility)
     */
    do_action('arsol_pfw_sidebar_actions', $post_type, $status, $post_id);
    ?>
    
    <?php
    /**
     * Hook: arsol_pfw_project_sidebar_after
     * 
     * @param string $post_type Project type: 'active'
     * @param string $status Current status
     * @param int $post_id Post ID
     * @param string $cpt Custom post type
     */
    do_action('arsol_pfw_project_sidebar_after', $post_type, $status, $post_id, $cpt);
    ?>
</div>
