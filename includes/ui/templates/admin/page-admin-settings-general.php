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
    // Function to toggle conditional fields
    function toggleConditionalFields() {
        $('.arsol-conditional-field').each(function() {
            var $field = $(this);
            var conditionField = $field.data('condition-field');
            var conditionValue = $field.data('condition-value');
            var currentValue = $('#' + conditionField).val();
            
            if (currentValue === conditionValue) {
                $field.addClass('arsol-show-field');
            } else {
                $field.removeClass('arsol-show-field');
            }
        });
    }
    
    // Initial state - all conditional fields are hidden by CSS
    toggleConditionalFields();
    
    // Listen for changes on all form elements that might be condition fields
    $('select[id]').on('change', function() {
        toggleConditionalFields();
    });
});
</script>
