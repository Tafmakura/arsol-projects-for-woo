<?php
/**
 * Admin Template: Edit Project/Proposal Details Subsection
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

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-project') {
    return;
}

// Use Project entity for meta access
$project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($post->ID);
$request_id = $project->get_meta('_arsol_pfw_request_id');
$requested_budget = $project->get_meta('_arsol_pfw_requested_project_budget');
$requested_start_date = $project->get_meta('_arsol_pfw_requested_project_start_date');
$requested_due_date = $project->get_meta('_arsol_pfw_requested_project_due_date');
?>

<?php if ($has_request_data): ?>
    <div class="arsol-request-details">
        <h4><?php _e('Original Request Details', 'arsol-pfw'); ?></h4>
        
        <table class="widefat">
            <tr>
                <th><?php _e('Request ID:', 'arsol-pfw'); ?></th>
                <td><?php echo !empty($request_id) ? esc_html($request_id) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?></td>
            </tr>
            <tr>
                <th><?php _e('Start Date:', 'arsol-pfw'); ?></th>
                <td><?php echo !empty($requested_start_date) ? esc_html(date_i18n(get_option('date_format'), strtotime($requested_start_date))) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?></td>
            </tr>
            <tr>
                <th><?php _e('Due Date:', 'arsol-pfw'); ?></th>
                <td><?php echo !empty($requested_due_date) ? esc_html(date_i18n(get_option('date_format'), strtotime($requested_due_date))) : '<em>' . __('Not provided', 'arsol-pfw') . '</em>'; ?></td>
            </tr>
            <tr>
                <th><?php _e('Budget:', 'arsol-pfw'); ?></th>
                <td>
                    <?php if (!empty($requested_budget)): ?>
                        <?php if (is_array($requested_budget) && isset($requested_budget['amount'])): ?>
                            <?php 
                            $currency = isset($requested_budget['currency']) ? $requested_budget['currency'] : get_woocommerce_currency();
                            echo wc_price($requested_budget['amount'], array('currency' => $currency));
                            ?>
                        <?php else: ?>
                            <?php echo wc_price($requested_budget); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <em><?php _e('Not provided', 'arsol-pfw'); ?></em>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
<?php else: ?>
    <p><?php _e('No original request data available.', 'arsol-pfw'); ?></p>
<?php endif; ?> 