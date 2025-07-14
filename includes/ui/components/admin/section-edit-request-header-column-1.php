<?php
/**
 * Admin Template: Edit Request Header - General Settings Column
 *
 * Variables passed from parent template:
 * $request (Arsol_PFW_Request object) - The request entity instance
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure we have the request entity instance
if (!isset($request) || !is_object($request)) {
    return;
}

$request_id = $request->get_id();
$customer_id = $request->get_customer_id();
$customer = get_userdata($customer_id);
$request_stage = $request->get_stage();
$budget = $request->get_budget();
$start_date = $request->get_start_date();
$due_date = $request->get_due_date();
$request_project_lead = $request->get_project_lead();

// Get available stages using the request entity
$available_stages = $request->get_available_stages();

// Set default stage if none set
if (empty($request_stage)) {
    $request_stage = 'pending-review';
}
?>

<div class="form-field-row">
    <p class="form-field form-field-wide wc-customer-user">
        <label for="customer_id">
            <?php _e('Customer:', 'arsol-pfw'); ?>
        </label>
        <select class="arsol-disabled-select" name="customer_id" disabled>
            <option value="<?php echo esc_attr($customer_id); ?>" selected>
                <?php echo esc_html($customer->display_name); ?>
            </option>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="request-stage"><?php _e('Stage:', 'arsol-pfw'); ?></label>
        <select id="request-stage" name="request_stage" class="wc-enhanced-select">
            <?php if (!empty($available_stages)): ?>
                <?php foreach ($available_stages as $stage_slug => $stage_name): ?>
                    <option value="<?php echo esc_attr($stage_slug); ?>" <?php selected($request_stage, $stage_slug); ?>>
                        <?php echo esc_html($stage_name); ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value=""><?php _e('No stages available', 'arsol-pfw'); ?></option>
            <?php endif; ?>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="request_project_lead"><?php _e('Project Lead:', 'arsol-pfw'); ?></label>
        <?php
        // Use the Admin Users class method for project lead search field
        \Arsol_Projects_For_Woo\Admin\Users::render_project_lead_search_field(array(
            'name' => 'request_project_lead',
            'id' => 'request_project_lead',
            'selected' => $request_project_lead,
            'placeholder' => __('Search for project lead...', 'arsol-pfw')
        ));
        ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="request_budget"><?php _e('Budget:', 'arsol-pfw'); ?></label>
        <input type="number" id="request_budget" name="request_budget" value="<?php echo esc_attr($budget); ?>" step="0.01" class="widefat">
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label for="request_start_date"><?php _e('Start Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="request_start_date" name="request_start_date" value="<?php echo esc_attr($start_date); ?>" class="widefat">
    </p>
    <p class="form-field form-field-half">
        <label for="request_due_date"><?php _e('Due Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="request_due_date" name="request_due_date" value="<?php echo esc_attr($due_date); ?>" class="widefat">
    </p>
</div>

 