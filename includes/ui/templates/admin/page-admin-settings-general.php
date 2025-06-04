<?php
/**
 * Admin Settings Page Template
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get saved settings
$settings = get_option('arsol_projects_settings', array());
?>

<div class="wrap">
    <h1>Arsol Projects for Woo</h1>
    
    <form action="options.php" method="post">
        <?php
        settings_fields('arsol_projects_settings');
        do_settings_sections('arsol_projects_settings');
        submit_button();
        ?>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Function to toggle the conditional field
    function toggleDefaultUserPermission() {
        var globalPermission = $('#user_project_permissions').val();
        var defaultPermissionRow = $('#default_user_permission').closest('tr');
        
        if (globalPermission === 'user_specific') {
            defaultPermissionRow.show();
        } else {
            defaultPermissionRow.hide();
        }
    }
    
    // Initial state
    toggleDefaultUserPermission();
    
    // Listen for changes
    $('#user_project_permissions').on('change', toggleDefaultUserPermission);
});
</script>
