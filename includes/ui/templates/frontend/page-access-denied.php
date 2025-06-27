<?php
/**
 * Access Denied Page
 *
 * This template displays the access denied message when users don't have
 * permission to access a particular feature or page.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

// Check for override first
if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_template_override('access_denied')) {
    echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_template_override('access_denied');
} else {
    // Default access denied content using shortcode
    echo do_shortcode('[arsol_pfw_access_denied]');
} 