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
?>

<div class="arsol-project-request-container">
    <?php wc_print_notices(); ?>
    <?php 
    // Enqueue jQuery and localize script
    wp_enqueue_script('jquery');
    wp_localize_script('jquery', 'arsol_pfw_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('arsol_create_request')
    ));
    
    \Arsol_Projects_For_Woo\Frontend_Template_Overrides::render_template(
        'create_project_request_form',
        ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/form-project-create-request.php'
    );
    ?>
</div>
