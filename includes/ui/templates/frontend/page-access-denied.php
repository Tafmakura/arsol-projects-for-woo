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
    // Default access denied content
    ?>
    <div class="arsol-no-permission">
        <h3><?php _e('Access Denied', 'arsol-pfw'); ?></h3>
        <p><?php _e('You do not have permission to access this feature. Please contact an administrator if you believe this is an error.', 'arsol-pfw'); ?></p>
    </div>
    <?php
} 