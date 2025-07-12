<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-project') {
    return;
}

// Use factory function to get project object
$project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Arsol_PFW_Project($post->ID);
if (!$project) {
    return;
}

$project_id = $project->get_id();
$customer_id = $project->get_customer_id();
$customer = get_userdata($customer_id);
$project_stage = $project->get_stage();
$project_lead = $project->get_project_lead();
$start_date = $project->get_start_date();
$due_date = $project->get_deadline();

// Get available stages using Stage Manager
$available_stages = \Arsol_Projects_For_Woo\Custom_Post_Types\Project\Project_Requests_CPT::get_available_stages();
?>

<div class="form-field-row">
    <p class="form-field form-field-wide wc-customer-user">
        <label for="post_author_override">
            <?php _e('Customer:', 'arsol-pfw'); ?>
            <?php if ($customer): ?>
                <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-pfw-project&author=' . $customer_id); ?>">
                    <?php _e('View other projects →', 'arsol-pfw'); ?>
                </a>
                <a href="<?php echo admin_url('user-edit.php?user_id=' . $customer_id); ?>">
                    <?php _e('Profile →', 'arsol-pfw'); ?>
                </a>
            <?php endif; ?>
        </label>
        <select class="wc-customer-search" name="post_author_override" data-placeholder="<?php esc_attr_e('Search for customer...', 'arsol-pfw'); ?>" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="<?php echo esc_attr(wp_create_nonce('search-customers')); ?>" required>
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
                }
                ?>
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
                <?php foreach ($available_stages as $stage_slug => $stage_name) : ?>
                    <option value="<?php echo esc_attr($stage_slug); ?>" <?php selected($project_stage, $stage_slug); ?>>
                        <?php echo esc_html($stage_name); ?>
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