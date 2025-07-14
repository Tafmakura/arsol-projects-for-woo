<?php
/**
 * Admin Template: Edit Project Header - Details Column
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

// Check for proposal data first
$proposal_id = get_post_meta($project_id, '_arsol_pfw_project_proposal_id', true);
$has_proposal_data = false;

// If from proposal, get proposal budget/date data
if ($proposal_id) {
    $has_proposal_data = true;
    $budget_data = $project->get_proposal_budget_onetime_amount();
    $recurring_budget_data = $project->get_proposal_budget_recurring_amount();
    $billing_interval = $project->get_proposal_budget_recurring_billing_interval();
    $billing_period = $project->get_proposal_budget_recurring_billing_period();
    $proposed_start_date = get_post_meta($project_id, '_arsol_pfw_proposed_start_date', true);
    $proposed_due_date = get_post_meta($project_id, '_arsol_pfw_proposed_due_date', true);
    $proposed_expiration_date = get_post_meta($project_id, '_arsol_pfw_proposal_expiration_date', true);
}

        // Check if has request data
        $has_request_data = false;
        $request_id = get_post_meta($project_id, '_arsol_pfw_project_request_id', true);
        $request_title = get_post_meta($project_id, '_arsol_pfw_project_request_title', true);
        $request_content = get_post_meta($project_id, '_arsol_pfw_project_request_content', true);
        $requested_budget = get_post_meta($project_id, '_arsol_pfw_project_requested_budget', true);
        $requested_start_date = get_post_meta($project_id, '_arsol_pfw_project_requested_start_date', true);
        $requested_due_date = get_post_meta($project_id, '_arsol_pfw_project_request_delivery_date', true);

        $has_request_data = $request_id || $requested_budget || $requested_start_date || $requested_due_date;
?>

<?php if ($has_proposal_data): ?>

    <p class="form-field form-field-wide">
        <label><strong><?php _e('Proposed Budget:', 'arsol-pfw'); ?></strong></label>
        <?php echo (!empty($budget_data) && is_array($budget_data)) ? wc_price($budget_data['amount'], array('currency' => $budget_data['currency'])) : __('N/A', 'arsol-pfw'); ?>
    </p>

    <p class="form-field form-field-wide">
        <label><strong><?php _e('Proposed Recurring Budget:', 'arsol-pfw'); ?></strong></label>
        <?php
        if (!empty($recurring_budget_data) && is_array($recurring_budget_data)) {
            $intervals = array('1' => __('every', 'arsol-pfw'), '2' => __('every 2nd', 'arsol-pfw'), '3' => __('every 3rd', 'arsol-pfw'), '4' => __('every 4th', 'arsol-pfw'), '5' => __('every 5th', 'arsol-pfw'), '6' => __('every 6th', 'arsol-pfw'));
            $periods = array('day' => __('day', 'arsol-pfw'), 'week' => __('week', 'arsol-pfw'), 'month' => __('month', 'arsol-pfw'), 'year' => __('year', 'arsol-pfw'));
            $interval_text = isset($intervals[$billing_interval]) ? $intervals[$billing_interval] : '';
            $period_text = isset($periods[$billing_period]) ? $periods[$billing_period] : '';
            $cycle_text = trim($interval_text . ' ' . $period_text);
            
            $output_string = wc_price($recurring_budget_data['amount'], array('currency' => $recurring_budget_data['currency']));
            if (!empty($cycle_text)) {
                $output_string .= ' ' . esc_html($cycle_text);
            }
            echo $output_string;
        } else {
            echo __('N/A', 'arsol-pfw');
        }
        ?>
    </p>

    <?php if (!empty($proposed_start_date)): ?>
    <p class="form-field form-field-wide">
        <label><strong><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposed_start_date))); ?>
    </p>
    <?php endif; ?>

    <?php if (!empty($proposed_due_date)): ?>
    <p class="form-field form-field-wide">
        <label><strong><?php _e('Proposed Due Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposed_due_date))); ?>
    </p>
    <?php endif; ?>

    <?php /* Proposal Expiration Date removed from project post type display */ ?>

<?php elseif ($has_request_data): ?>
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/subsection-edit-project-proposal-details.php'; ?>
<?php endif; ?>