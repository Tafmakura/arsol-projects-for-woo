<?php
/**
 * Project Request Template
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$admin_users = new \Arsol_Projects_For_Woo\Admin\Users();

if (!$admin_users->can_user_request_projects($user_id)) {
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-no-permission.php';
    return;
}
?>

<div class="woocommerce-MyAccount-content">
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="arsol-project-form">
        <?php wp_nonce_field('arsol_create_project_request', 'arsol_project_request_nonce'); ?>
        <input type="hidden" name="action" value="arsol_create_project_request">
        
        <div class="form-row">
            <label for="request_title"><?php _e('Request Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" name="request_title" id="request_title" required>
        </div>

        <div class="form-row">
            <label for="request_description"><?php _e('Request Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea name="request_description" id="request_description" rows="5" required></textarea>
        </div>

        <div class="form-row">
            <label for="request_due_date"><?php _e('Desired Due Date', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="date" name="request_due_date" id="request_due_date" required>
        </div>

        <div class="form-row">
            <label for="request_budget"><?php _e('Budget Range', 'arsol-pfw'); ?></label>
            <input type="text" name="request_budget" id="request_budget" placeholder="<?php _e('e.g., $1000 - $2000', 'arsol-pfw'); ?>">
        </div>

        <div class="form-row">
            <label for="request_requirements"><?php _e('Additional Requirements', 'arsol-pfw'); ?></label>
            <textarea name="request_requirements" id="request_requirements" rows="3"></textarea>
        </div>

        <div class="form-row">
            <button type="submit" class="button"><?php _e('Submit Request', 'arsol-pfw'); ?></button>
        </div>
    </form>
</div>
