<?php
/**
 * Admin Settings Page: Files
 * 
 * Template for the files settings tab.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">
    <form method="post" action="options.php">
        <?php
        settings_fields('arsol_files_settings');
        do_settings_sections('arsol_files_settings');
        submit_button();
        ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Show/hide file organization options based on upload enablement
    $('.arsol-pfw-enable-uploads input[type="checkbox"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('.arsol-pfw-max-file-size, .arsol-pfw-allowed-types, .arsol-pfw-organization-method').closest('tr').show();
        } else {
            $('.arsol-pfw-max-file-size, .arsol-pfw-allowed-types, .arsol-pfw-organization-method').closest('tr').hide();
        }
    }).trigger('change');
    
    // Show/hide access method options
    $('.arsol-pfw-access-method select').on('change', function() {
        if ($(this).val() === 'protected' || $(this).val() === 'private') {
            $('.arsol-pfw-download-logging').closest('tr').show();
        } else {
            $('.arsol-pfw-download-logging').closest('tr').hide();
        }
    }).trigger('change');
});
</script> 