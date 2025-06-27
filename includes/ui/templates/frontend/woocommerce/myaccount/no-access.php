<?php
/**
 * No Access template
 * 
 * Shows when user has no access to any projects-related content
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load the existing access denied template
include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/page-access-denied.php'; 