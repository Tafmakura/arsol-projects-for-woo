<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-project') {
    return;
}

$project_id = $post->ID;
$customer_id = $post->post_author;
$customer = get_userdata($customer_id);
$project_status_terms = wp_get_object_terms($project_id, 'arsol-project-status', array('fields' => 'slugs'));
$project_status = !empty($project_status_terms) ? $project_status_terms[0] : 'not-started';
$project_lead = get_post_meta($project_id, '_project_lead', true);
$start_date = get_post_meta($project_id, '_project_start_date', true);
$due_date = get_post_meta($project_id, '_project_due_date', true);

$all_statuses = get_terms(array(
    'taxonomy' => 'arsol-project-status',
    'hide_empty' => false,
));
?>

<div class="form-field-row">
    <p class="form-field form-field-wide wc-customer-user">
        <label for="post_author_override">
            <?php _e('Customer:', 'arsol-pfw'); ?>
            <?php if ($customer): ?>
                <a href="<?php echo admin_url('edit.php?post_status=all&post_type=arsol-project&author=' . $customer_id); ?>">
                    <?php _e('View other projects →', 'arsol-pfw'); ?>
                </a>
                <a href="<?php echo admin_url('user-edit.php?user_id=' . $customer_id); ?>">
                    <?php _e('Profile →', 'arsol-pfw'); ?>
                </a>
            <?php endif; ?>
        </label>
        <select class="wc-customer-search" name="post_author_override" data-placeholder="<?php esc_attr_e('Search for customer...', 'arsol-pfw'); ?>" data-allow_clear="true" data-action="woocommerce_json_search_customers" data-security="<?php echo esc_attr(wp_create_nonce('search-customers')); ?>">
            <?php if ($post->post_author): ?>
                <?php 
                $customer_user = get_userdata($post->post_author);
                if ($customer_user) {
                    // Format customer display like WooCommerce: "First Last (#ID – email)" or fallback to "Display Name (#ID – email)"
                    $customer_name = trim($customer_user->first_name . ' ' . $customer_user->last_name);
                    if (empty($customer_name)) {
                        $customer_name = $customer_user->display_name;
                    }
                    
                    printf(
                        '<option value="%s" selected="selected">%s (#%s &ndash; %s)</option>',
                        esc_attr($customer_user->ID),
                        esc_html($customer_name),
                        esc_html($customer_user->ID),
                        esc_html($customer_user->user_email)
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
        // Get users who can create projects based on Project Manager Roles setting
        $admin_users_helper = new \Arsol_Projects_For_Woo\Admin\Users();
        $project_lead_users = get_users(array(
            'fields' => array('ID', 'display_name'),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'wp_capabilities',
                    'value' => 'manage_projects',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'wp_capabilities', 
                    'value' => 'create_projects',
                    'compare' => 'LIKE'
                )
            )
        ));
        
        // Filter to only users who can actually create projects
        $valid_user_ids = array();
        foreach ($project_lead_users as $user) {
            if ($admin_users_helper->can_user_create_projects($user->ID)) {
                $valid_user_ids[] = $user->ID;
            }
        }
        
        // Use WordPress native dropdown
        wp_dropdown_users(array(
            'name' => 'project_lead',
            'id' => 'project_lead',
            'class' => 'arsol-user-select2',
            'selected' => $project_lead,
            'include' => $valid_user_ids,
            'show_option_none' => __('— Select Project Lead —', 'arsol-pfw'),
            'option_none_value' => ''
        ));
        ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="project_status"><?php _e('Project Status:', 'arsol-pfw'); ?></label>
        <select id="project_status" name="project_status" class="wc-enhanced-select">
            <?php foreach ($all_statuses as $status) : ?>
                <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($project_status, $status->slug); ?>>
                    <?php echo esc_html($status->name); ?>
                </option>
            <?php endforeach; ?>
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