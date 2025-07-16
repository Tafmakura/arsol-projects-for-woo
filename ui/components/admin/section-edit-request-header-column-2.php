<?php
/**
 * Admin Template: Edit Request Header - Details Column
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
$request_content = $request->get_content();
$attachments = get_attached_media('', $request_id);
        $budget = $request->get_requested_project_budget();
        $start_date = $request->get_requested_project_start_date();
        $delivery_date = $request->get_requested_project_due_date();

// Get request stage (with proper error handling)
$request_stage_terms = wp_get_object_terms($request_id, 'arsol-pfw-request-stage', array('fields' => 'names'));
$request_stage = '';
if (!is_wp_error($request_stage_terms) && !empty($request_stage_terms)) {
    $request_stage = $request_stage_terms[0];
}

$customer = $request->get_customer();
$submission_date = get_the_time('l j F \a\t g:ia', $request->get_post());
?>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label><strong><?php _e('Submission Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo $submission_date ? esc_html($submission_date) : __('N/A', 'arsol-pfw'); ?>
    </p>
    <p class="form-field form-field-half">
        <label><strong><?php _e('Requested Budget:', 'arsol-pfw'); ?></strong></label>
        <?php 
        if (!empty($budget['amount'])) {
            $currency = !empty($budget['currency']) ? $budget['currency'] : get_woocommerce_currency();
            echo get_woocommerce_currency_symbol($currency) . number_format((float)$budget['amount'], 2);
        } else {
            echo __('N/A', 'arsol-pfw');
        }
        ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label><strong><?php _e('Requested Start Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo $start_date ? esc_html(date_i18n(get_option('date_format'), strtotime($start_date))) : __('N/A', 'arsol-pfw'); ?>
    </p>
    <p class="form-field form-field-half">
        <label><strong><?php _e('Requested Due Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo $delivery_date ? esc_html(date_i18n(get_option('date_format'), strtotime($delivery_date))) : __('N/A', 'arsol-pfw'); ?>
    </p>
</div>
