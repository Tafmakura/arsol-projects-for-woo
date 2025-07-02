<?php
/**
 * Request Project Page
 *
 * This template acts as the main container for the request project form.
 * It calls the overridable content section.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Use shortcode for form rendering
echo do_shortcode('[arsol_pfw_request_form]');
