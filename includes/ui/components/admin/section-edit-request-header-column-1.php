<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

// Use factory function to get request object
$request = new \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Request_CPT($post->ID);
if (!$request) {
    return;
}

$request_id = $request->get_id();
$customer_id = $request->get_customer_id();
$customer = get_userdata($customer_id);
$request_stage = $request->get_stage();
$budget_data = $request->get_budget();
$start_date = $request->get_start_date();
$delivery_date = $request->get_deadline();
$request_project_lead = $request->get_project_lead();

// Get available stages using Stage Manager
$available_stages = \Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Project_Requests_CPT::get_available_stages();

// Set default stage if none set
if (empty($request_stage)) {
    $request_stage = 'pending-review';
}
?>

<div class="form-field-row">
    <p class="form-field form-field-wide wc-customer-user">
        <label for="post_author_override">
            <?php _e('Customer:', 'arsol-pfw'); ?>
            <?php if ($customer): ?>
                <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-pfw-request&author=' . $customer_id); ?>">
                    <?php _e('View other requests →', 'arsol-pfw'); ?>
                </a>
                <a href="<?php echo admin_url('user-edit.php?user_id=' . $customer_id); ?>">
                    <?php _e('Profile →', 'arsol-pfw'); ?>
                </a>
            <?php endif; ?>
        </label>
        <select class="arsol-disabled-select" name="post_author_override" disabled>
            <?php if ($customer_id): ?>
                <?php
                $customer_user = get_userdata($customer_id);
                if ($customer_user) {
                    $customer_display = \Arsol_Projects_For_Woo\Woocommerce::format_customer_admin_display($customer_user);
                    
                    printf(
                        '<option value="%s" selected="selected">%s</option>',
                        esc_attr($customer_user->ID),
                        esc_html($customer_display)
                    );
                } else {
                    echo '<option value="">' . esc_html__('Customer not found', 'arsol-pfw') . '</option>';
                }
                ?>
            <?php else: ?>
                <option value=""><?php esc_html_e('No customer assigned', 'arsol-pfw'); ?></option>
            <?php endif; ?>
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
        <input type="number" id="request_budget" name="request_budget" value="<?php echo esc_attr($budget_data); ?>" step="0.01" class="widefat">
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-half">
        <label for="request_start_date"><?php _e('Start Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="request_start_date" name="request_start_date" value="<?php echo esc_attr($start_date); ?>" class="widefat">
    </p>
    <p class="form-field form-field-half">
        <label for="request_delivery_date"><?php _e('Delivery Date:', 'arsol-pfw'); ?></label>
        <input type="date" id="request_delivery_date" name="request_delivery_date" value="<?php echo esc_attr($delivery_date); ?>" class="widefat">
    </p>
</div>

 