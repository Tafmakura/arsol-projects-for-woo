<?php
/**
 * Admin Template: Edit Project Header - General Settings Column
 *
 * Variables passed from parent template:
 * $project (Arsol_PFW_Project object) - The project entity instance
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure we have the project entity instance
if (!isset($project) || !is_object($project)) {
    return;
}

$project_id = $project->get_id();
$customer_id = $project->get_customer_id();
$customer = $project->get_customer();

// Use direct stage methods (WooCommerce style)
$project_stage = $project->get_stage();
$available_stages = $project->get_available_stages();

$project_lead = $project->get_project_lead();
$start_date = $project->get_project_start_date();
$due_date = $project->get_project_due_date();
?>

<div class="form-field-row">
    <p class="form-field form-field-wide wc-customer-user">
        <label for="customer_id">
            <?php _e('Customer:', 'arsol-pfw'); ?>
        </label>
        <select class="wc-customer-search" name="customer_id" data-placeholder="<?php esc_attr_e('Search for customer...', 'arsol-pfw'); ?>" data-allow_clear="true" data-action="arsol_pfw_ajax_search_users" data-search-type="customers" data-security="<?php echo esc_attr(wp_create_nonce('search-users')); ?>">
            <?php if ($customer_id && $customer && is_object($customer)): ?>
                <option value="<?php echo esc_attr($customer_id); ?>" selected>
                    <?php echo esc_html(arsol_pfw_format_user($customer_id, 'display_name', false, true, false)); ?>
                </option>
            <?php endif; ?>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="project_lead"><?php _e('Project Lead:', 'arsol-pfw'); ?></label>
        <?php
        // Use the Admin Users class method for project lead search field
        \Arsol_Projects_For_Woo\Admin\Users::render_project_lead_search_field(array(
            'name' => 'project_lead',
            'id' => 'project_lead',
            'selected' => $project_lead,
            'placeholder' => __('Search for project lead...', 'arsol-pfw')
        ));
        ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="project_stage"><?php _e('Project Stage:', 'arsol-pfw'); ?></label>
        <select id="project_stage" name="project_stage" class="wc-enhanced-select">
            <?php if (!empty($available_stages)) : ?>
                <?php foreach ($available_stages as $stage) : ?>
                    <option value="<?php echo esc_attr($stage->slug); ?>" <?php selected($project_stage, $stage->slug); ?>>
                        <?php echo esc_html($stage->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label for="project_start_date"><?php _e('Start Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="project_start_date" name="project_start_date" value="<?php echo esc_attr($start_date); ?>" class="widefat">
    </p>
    <p class="form-field form-field-half">
        <label for="project_due_date"><?php _e('Due Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="project_due_date" name="project_due_date" value="<?php echo esc_attr($due_date); ?>" class="widefat">
    </p>
</div> 