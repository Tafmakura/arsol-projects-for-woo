<?php
/**
 * Project Creation Template
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$admin_users = new \Arsol_Projects_For_Woo\Admin\Users();

if (!$admin_users->can_user_create_projects($user_id)) {
    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/section-project-no-permission.php';
    return;
}
?>

<div class="woocommerce-MyAccount-content">
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="arsol-project-form">
        <?php wp_nonce_field('arsol_create_project', 'arsol_project_nonce'); ?>
        <input type="hidden" name="action" value="arsol_create_project">
        
        <div class="form-row">
            <label for="project_title"><?php _e('Project Title', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="text" name="project_title" id="project_title" required>
        </div>

        <div class="form-row">
            <label for="project_description"><?php _e('Project Description', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <textarea name="project_description" id="project_description" rows="5" required></textarea>
        </div>

        <div class="form-row">
            <label for="project_due_date"><?php _e('Due Date', 'arsol-pfw'); ?> <span class="required">*</span></label>
            <input type="date" name="project_due_date" id="project_due_date" required>
        </div>

        <div class="form-row">
            <label for="project_proposal"><?php _e('Related Proposal', 'arsol-pfw'); ?></label>
            <select name="project_proposal" id="project_proposal">
                <option value=""><?php _e('Select a proposal', 'arsol-pfw'); ?></option>
                <?php
                $proposals = get_posts(array(
                    'post_type' => 'arsol-project-proposal',
                    'author' => $user_id,
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ));

                foreach ($proposals as $proposal) {
                    echo '<option value="' . esc_attr($proposal->ID) . '">' . esc_html($proposal->post_title) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-row">
            <button type="submit" class="button"><?php _e('Create Project', 'arsol-pfw'); ?></button>
        </div>
    </form>
</div>
