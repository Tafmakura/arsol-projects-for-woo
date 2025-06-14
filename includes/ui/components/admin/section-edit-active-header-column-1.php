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
        <?php
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'wc-customer-search'
        ));
        echo $author_dropdown;
        ?>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="project_lead"><?php _e('Project Lead:', 'arsol-pfw'); ?></label>
        <select id="project_lead" name="project_lead" class="wc-enhanced-select">
            <option value=""><?php _e('— Select —', 'arsol-pfw'); ?></option>
            <?php
            // Use the Admin\Users helper to build the list of valid project leads
            $admin_users_helper = new \Arsol_Projects_For_Woo\Admin\Users();

            // Fetch all users who can create projects according to plugin permissions
            $potential_leads = get_users(array('fields' => array('ID', 'display_name')));

            foreach ($potential_leads as $user) {
                if ($admin_users_helper->can_user_create_projects($user->ID)) {
                    echo '<option value="' . esc_attr($user->ID) . '"' . selected($project_lead, $user->ID, false) . '>';
                    echo esc_html($user->display_name);
                    echo '</option>';
                }
            }
            ?>
        </select>
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