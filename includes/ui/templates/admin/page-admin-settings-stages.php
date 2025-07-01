<?php
/**
 * Admin Settings Page: Stages
 * 
 * Template for the stages settings tab.
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
        settings_fields('arsol_stages_settings');
        do_settings_sections('arsol_stages_settings');
        submit_button();
        ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Add any JavaScript interactions here
    $('.arsol-pfw-stage-notifications input[type="checkbox"]').on('change', function() {
        if ($(this).is(':checked')) {
            $('.arsol-pfw-stage-history').closest('tr').show();
        } else {
            $('.arsol-pfw-stage-history').closest('tr').hide();
        }
    }).trigger('change');
});
</script> 