<?php
/**
 * Admin Template: Edit Proposal Header - Request Details Column
 *
 * Variables passed from parent template:
 * $proposal (Arsol_PFW_Proposal object) - The proposal entity instance
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

        $budget = $proposal->get_budget();
        $delivery_date = $proposal->get_delivery_date();
        $expiration_date = $proposal->get_expiration_date();
        $start_date = $proposal->get_start_date();
        $costing_type = $proposal->get_costing_type();

$has_original_data = $original_request_id || $original_request_budget || $original_request_start_date || $original_request_delivery_date;
?>

<?php if ($has_original_data): ?>
    <?php include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/admin/subsection-edit-project-proposal-details.php'; ?>
<?php else: ?>
    <p><?php _e('This proposal was created directly without an initial customer request.', 'arsol-pfw'); ?></p>
<?php endif; ?> 