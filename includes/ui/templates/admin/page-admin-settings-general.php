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
        <table class="form-table">
            <?php
            settings_fields('arsol_projects_settings');
            do_settings_sections('arsol_projects_settings');
            // Manually render the conditional field row for Default User
            $value = isset($settings['default_user_permission']) ? $settings['default_user_permission'] : 'none';
            ?>
            <tr class="arsol-conditional-field" data-condition-field="user_project_permissions" data-condition-value="user_specific">
                <th scope="row">
                    <label for="default_user_permission"><?php echo esc_html__('Default User', 'arsol-pfw'); ?></label>
                </th>
                <td>
                    <select id="default_user_permission" name="arsol_projects_settings[default_user_permission]">
                        <option value="none" <?php selected($value, 'none'); ?>><?php esc_html_e('None', 'arsol-pfw'); ?></option>
                        <option value="request" <?php selected($value, 'request'); ?>><?php esc_html_e('Can request projects', 'arsol-pfw'); ?></option>
                        <option value="create" <?php selected($value, 'create'); ?>><?php esc_html_e('Can create projects', 'arsol-pfw'); ?></option>
                    </select>
                    <p class="description">
                        <?php esc_html_e('Default permission level assigned to new users (only applies when "User Specific" is selected above)', 'arsol-pfw'); ?>
                    </p>
                </td>
            </tr>
            <?php
            ?>
        </table>
        <?php submit_button(); ?>
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
