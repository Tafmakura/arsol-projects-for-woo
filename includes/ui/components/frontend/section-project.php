<?php
/**
 * Project Template Controller
 *
 * This template loads the correct content and sidebar based on the context.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get the type from GET parameters or default to 'overview'
$type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'overview';

// Map of valid types to their template files
$templates = array(
    'overview' => array(
        'content' => 'section-project-content-overview.php',
        'sidebar' => 'section-project-sidebar-overview.php'
    ),
    'proposal' => array(
        'content' => 'section-project-content-proposal.php',
        'sidebar' => 'section-project-sidebar-proposal.php'
    ),
    'request' => array(
        'content' => 'section-project-content-request.php',
        'sidebar' => 'section-project-sidebar-request.php'
    )
);

// Default to overview if type is not valid
if (!isset($templates[$type])) {
    $type = 'overview';
}

// Get template paths
$content_template = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/' . $templates[$type]['content'];
$sidebar_template = ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/' . $templates[$type]['sidebar'];

// Set sidebar type for backward compatibility
$sidebar_type = $type;
?>

<div class="project-overview-wrapper">
    <div class="project-content">
        <?php
        if (file_exists($content_template)) {
            include $content_template;
        } else {
            echo '<p>' . esc_html__('Content template not found.', 'arsol-pfw') . '</p>';
        }
        ?>
    </div>
    
    <div class="project-sidebar card">
        <?php
        if (file_exists($sidebar_template)) {
            include $sidebar_template;
        } else {
            echo '<p>' . esc_html__('Sidebar template not found.', 'arsol-pfw') . '</p>';
        }
        ?>
    </div>
</div> 