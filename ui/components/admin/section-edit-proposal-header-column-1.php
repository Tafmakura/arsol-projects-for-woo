<?php
/**
 * Admin Template: Edit Proposal Header - General Settings Column
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

$proposal_id = $proposal->get_id();
$customer_id = $proposal->get_customer_id();
$customer = get_userdata($customer_id);
$proposal_status = get_post_status($post);
$proposal_stage = $proposal->get_stage();
$proposal_project_lead = $proposal->get_proposed_project_lead();
$start_date = $proposal->get_proposed_project_start_date();
$delivery_date = $proposal->get_proposed_project_due_date();
$expiration_date = $proposal->get_expiration_date();
$cost_proposal_type = $proposal->get_costing_type();

// Check for project-tied proposal - URL parameter first, then meta data
$is_project_tied = false;
$parent_project_data = false;

// ALWAYS check URL parameter first (for new proposals)
if (isset($_GET['parent_project']) && !empty($_GET['parent_project'])) {
    $parent_project_id = intval($_GET['parent_project']);
    $parent_project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($parent_project_id);
    
    if ($parent_project) {
        $is_project_tied = true;
        
        // Get parent project data using CRUD methods
        $parent_customer_id = $parent_project->get_customer_id();
        $parent_lead_id = $parent_project->get_project_lead();
        
        $parent_project_data = array(
            'id' => $parent_project->get_id(),
            'title' => $parent_project->get_title(),
            'customer_id' => $parent_customer_id,
            'lead_id' => $parent_lead_id
        );
        
        // EXCLUSIVELY use parent project values - override completely
        $customer_id = $parent_customer_id;
        $customer = get_userdata($customer_id);
        $proposal_project_lead = $parent_lead_id;
        $cost_proposal_type = 'quotation'; // Always quotation for project-tied proposals
    }
} 
// Fallback to meta data check (for existing proposals)
elseif ($proposal_id > 0) {
    $parent_project_id = $proposal->get_parent_project_id();
    if (!empty($parent_project_id)) {
        $parent_project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($parent_project_id);
        if ($parent_project) {
            $is_project_tied = true;
            
            // Get parent project data using CRUD methods
            $parent_customer_id = $parent_project->get_customer_id();
            $parent_lead_id = $parent_project->get_project_lead();
            
            $parent_project_data = array(
                'id' => $parent_project->get_id(),
                'title' => $parent_project->get_title(),
                'customer_id' => $parent_customer_id,
                'lead_id' => $parent_lead_id
            );
            
            // EXCLUSIVELY use parent project values - override completely
            $customer_id = $parent_customer_id;
            $customer = get_userdata($customer_id);
            $proposal_project_lead = $parent_lead_id;
            $cost_proposal_type = 'quotation'; // Always quotation for project-tied proposals
        }
    }
}

// Get proposal stage using direct methods (WooCommerce style)
$current_proposal_stage = $proposal->get_stage();
if (empty($current_proposal_stage)) {
    $current_proposal_stage = 'processing'; // Default to processing
}

// Get available stages using direct methods
$available_stages = $proposal->get_available_stages();
?>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="arsol_pfw_proposal_start_date"><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="arsol_pfw_proposal_start_date" name="arsol_pfw_proposal_start_date" value="<?php echo esc_attr($start_date); ?>" class="widefat">
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="arsol_pfw_proposal_due_date"><?php _e('Proposed Due Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="arsol_pfw_proposal_due_date" name="arsol_pfw_proposal_due_date" value="<?php echo esc_attr($delivery_date); ?>" class="widefat">
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide wc-customer-user">
        <label for="customer_id">
            <?php _e('Customer:', 'arsol-pfw'); ?>
        </label>
        <?php if ($is_project_tied): ?>
            <!-- Locked customer field for project-tied proposals -->
            <input type="hidden" name="customer_id" value="<?php echo esc_attr($customer_id); ?>">
            <select class="arsol-disabled-select" disabled>
                <option value="<?php echo esc_attr($customer_id); ?>" selected>
                    <?php echo esc_html($customer->display_name); ?>
                </option>
            </select>
        <?php else: ?>
            <!-- Regular customer search field -->
            <select class="wc-customer-search" name="customer_id" data-placeholder="<?php esc_attr_e('Search for customer...', 'arsol-pfw'); ?>" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="<?php echo esc_attr(wp_create_nonce('search-customers')); ?>">
                <?php if ($customer_id): ?>
                    <option value="<?php echo esc_attr($customer_id); ?>" selected>
                        <?php echo esc_html($customer->display_name); ?>
                    </option>
                <?php endif; ?>
            </select>
        <?php endif; ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_project_lead"><?php _e('Project Lead:', 'arsol-pfw'); ?></label>
        <?php if ($is_project_tied): ?>
            <!-- Locked project lead field for project-tied proposals -->
            <?php 
            $lead_user = get_userdata($proposal_project_lead);
            if ($lead_user): ?>
                <select class="arsol-disabled-select" disabled>
                    <option selected><?php echo esc_html($lead_user->display_name . ' (' . $lead_user->user_email . ')'); ?></option>
                </select>
                <input type="hidden" name="proposal_project_lead" value="<?php echo esc_attr($proposal_project_lead); ?>">
            <?php else: ?>
                <!-- No project lead assigned to parent project -->
                <select class="arsol-disabled-select" disabled>
                    <option selected><?php _e('No project lead assigned', 'arsol-pfw'); ?></option>
                </select>
                <input type="hidden" name="proposal_project_lead" value="">
            <?php endif; ?>
        <?php else: ?>
            <!-- Regular project lead search field -->
            <?php
            \Arsol_Projects_For_Woo\Admin\Users::render_project_lead_search_field(array(
                'name' => 'proposal_project_lead',
                'id' => 'proposal_project_lead',
                'selected' => $proposal_project_lead,
                'placeholder' => __('Search for project lead...', 'arsol-pfw')
            ));
            ?>
        <?php endif; ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_stage"><?php _e('Proposal Stage:', 'arsol-pfw'); ?></label>
        <select id="proposal_stage" name="proposal_stage" class="wc-enhanced-select">
            <?php if (!empty($available_stages)) : ?>
                <?php foreach ($available_stages as $stage) : ?>
                    <option value="<?php echo esc_attr($stage->slug); ?>" <?php selected($current_proposal_stage, $stage->slug); ?>>
                        <?php echo esc_html($stage->name); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="arsol_pfw_proposal_costing_type"><?php _e('Cost Proposal Type:', 'arsol-pfw'); ?></label>
        <?php if ($is_project_tied): ?>
            <!-- Locked cost type field for project-tied proposals -->
            <!-- IMPORTANT: Keep same ID for JavaScript conditional logic to work -->
            <select id="arsol_pfw_proposal_costing_type" name="arsol_pfw_proposal_costing_type" class="arsol-disabled-select" disabled>
                <option value="quotation" selected><?php _e('Quotation', 'arsol-pfw'); ?></option>
            </select>
            <!-- Hidden input ensures value is submitted -->
            <input type="hidden" name="arsol_pfw_proposal_costing_type" value="quotation">
        <?php else: ?>
            <!-- Regular cost type field -->
            <select id="arsol_pfw_proposal_costing_type" name="arsol_pfw_proposal_costing_type" class="wc-enhanced-select">
                <option value="none" <?php selected($cost_proposal_type, 'none'); ?>><?php _e('None', 'arsol-pfw'); ?></option>
                <option value="budget" <?php selected($cost_proposal_type, 'budget'); ?>><?php _e('Budget', 'arsol-pfw'); ?></option>
                <option value="quotation" <?php selected($cost_proposal_type, 'quotation'); ?>><?php _e('Quotation', 'arsol-pfw'); ?></option>
            </select>
        <?php endif; ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label for="arsol_pfw_proposal_expiration_date"><?php _e('Proposal Expiration Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="arsol_pfw_proposal_expiration_date" name="arsol_pfw_proposal_expiration_date" value="<?php echo esc_attr($expiration_date); ?>" class="widefat">
    </p>
</div>

<?php if ($is_project_tied && $parent_project_data): ?>
    <!-- Hidden input to ensure parent project ID is always saved -->
    <input type="hidden" name="parent_project_id" value="<?php echo esc_attr($parent_project_data['id']); ?>">

<script type="text/javascript">
jQuery(document).ready(function($) {
        // Preserve parent_project URL parameter during form submission
        var parentProjectId = <?php echo json_encode($parent_project_data['id']); ?>;
        
        // Add parent_project parameter to form action URL
        var $form = $('#post');
        if ($form.length && parentProjectId) {
            var currentAction = $form.attr('action') || '';
            var separator = currentAction.indexOf('?') !== -1 ? '&' : '?';
            
            // Only add parameter if it's not already present
            if (currentAction.indexOf('parent_project=') === -1) {
                $form.attr('action', currentAction + separator + 'parent_project=' + parentProjectId);
            }
        }
        
        // Also preserve parameter when clicking update/publish buttons
        $('#publish, #save-post').on('click', function() {
            var currentUrl = window.location.href;
            if (currentUrl.indexOf('parent_project=') === -1) {
                var separator = currentUrl.indexOf('?') !== -1 ? '&' : '?';
                window.history.replaceState({}, '', currentUrl + separator + 'parent_project=' + parentProjectId);
            }
        });
});
</script>
<?php endif; ?>
