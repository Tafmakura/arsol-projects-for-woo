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

// Display access denied message directly
echo '<div class="woocommerce-info">';
echo '<p>' . esc_html__('You do not have permission to access this content.', 'arsol-pfw') . '</p>';
echo '</div>'; 