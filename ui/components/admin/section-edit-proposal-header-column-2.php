<?php
/**
 * Admin Template: Edit Proposal Header - Request Details Column
 *
 * Variables passed from parent template:
 * $proposal (Arsol_PFW_Proposal object) - The proposal entity instance
 * $request_id - Source request ID
 * $request_budget - Request budget data
 * $request_start_date - Request start date
 * $request_due_date - Request due date
 * $has_request_data - Whether request data exists
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure we have the proposal entity instance
if (!isset($proposal) || !is_object($proposal)) {
    return;
}

// Use variables passed from parent template
$budget_data = $proposal->get_budget();
$delivery_date = $proposal->get_due_date();
$expiration_date = $proposal->get_proposal_expiration_date();
$start_date = $proposal->get_start_date();
$costing_type = $proposal->get_costing_type();
?>

<?php if ($has_request_data): ?>
    <?php include ARSOL_PFW_PLUGIN_DIR . 'ui/components/admin/subsection-edit-project-proposal-details.php'; ?>
<?php else: ?>
    <p><?php _e('This proposal was created directly without an initial customer request.', 'arsol-pfw'); ?></p>
<?php endif; ?> 