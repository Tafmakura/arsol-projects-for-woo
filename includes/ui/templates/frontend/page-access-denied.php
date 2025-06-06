<?php
/**
 * Access Denied Page
 *
 * This template acts as the main container for the access denied message.
 * It calls the overridable content section.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// Render the access denied content, allowing for overrides
\Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
    'access_denied',
    ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-no-permission.php'
); 