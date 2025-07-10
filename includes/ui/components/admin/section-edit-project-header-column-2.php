<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-project') {
    return;
}

$project_id = $post->ID;

// Check for original proposal data first
$original_proposal_id = get_post_meta($project_id, '_arsol_pfw_project_proposal_id', true);
$has_proposal_data = false;

// If from proposal, get proposal budget/date data
if ($original_proposal_id) {
    $has_proposal_data = true;
    $budget_data = get_post_meta($project_id, '_arsol_pfw_project_budget', true);
    $recurring_budget_data = get_post_meta($project_id, '_arsol_pfw_project_recurring_budget', true);
    $billing_interval = get_post_meta($project_id, '_arsol_pfw_project_billing_interval', true);
    $billing_period = get_post_meta($project_id, '_arsol_pfw_project_billing_period', true);
    $proposed_start_date = get_post_meta($project_id, '_arsol_pfw_proposal_start_date', true);
    $proposed_delivery_date = get_post_meta($project_id, '_arsol_pfw_proposal_delivery_date', true);
    $proposed_expiration_date = get_post_meta($project_id, '_arsol_pfw_proposal_expiration_date', true);
}

// Check for original request data (fallback if no proposal data)
$original_request_id = get_post_meta($project_id, '_arsol_pfw_project_request_id', true);
$original_request_title = get_post_meta($project_id, '_arsol_pfw_project_request_title', true);
$original_request_content = get_post_meta($project_id, '_arsol_pfw_project_request_details', true);
$original_request_budget = get_post_meta($project_id, '_arsol_pfw_project_request_budget', true);
$original_request_start_date = get_post_meta($project_id, '_arsol_pfw_project_request_start_date', true);
$original_request_delivery_date = get_post_meta($project_id, '_arsol_pfw_project_request_delivery_date', true);

$has_original_data = $original_request_id || $original_request_budget || $original_request_start_date || $original_request_delivery_date;
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

    <?php if (!empty($proposed_delivery_date)): ?>
    <p class="form-field form-field-wide">
        <label><strong><?php _e('Proposed Delivery Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposed_delivery_date))); ?>
    </p>
    <?php endif; ?>

    <?php /* Proposal Expiration Date removed from project post type display */ ?>

<?php elseif ($has_original_data): ?>
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/subsection-edit-project-proposal-details.php'; ?>
<?php endif; ?>