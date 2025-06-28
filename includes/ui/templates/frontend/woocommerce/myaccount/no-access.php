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

// Check for shortcode override using the new system
$override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_no_access]');
if ($override) {
    echo do_shortcode($override);
} else {
    echo do_shortcode('[arsol_pfw_no_access]');
} 