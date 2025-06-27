<?php
/**
 * Project Overview Header
 * 
 * Header section for individual project pages.
 * Variables: $post_id, $post_type, $status, $status_label
 */

if (!defined('ABSPATH')) exit;

if (empty($post_id)) {
    return;
}

$post = get_post($post_id);
if (!$post) {
    return;
}

// Determine page title based on post type
$page_title = '';
switch ($post_type) {
    case 'arsol-project':
        $page_title = __('Project Overview', 'arsol-pfw');
        break;
    case 'arsol-pfw-proposal':
        $page_title = __('Proposal Details', 'arsol-pfw');
        break;
    case 'arsol-pfw-request':
        $page_title = __('Request Details', 'arsol-pfw');
        break;
    default:
        $page_title = __('Project Overview', 'arsol-pfw');
        break;
}

?>

<div class="arsol-project-overview-header">
    <div class="project-header-content">
        <div class="project-breadcrumb">
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>">
                <?php _e('← Back to Projects', 'arsol-pfw'); ?>
            </a>
        </div>
        
        <h1 class="project-title"><?php echo esc_html($post->post_title); ?></h1>
        <p class="project-subtitle"><?php echo esc_html($page_title); ?></p>
        
        <?php if (!empty($status) && !empty($status_label)): ?>
            <div class="project-header-status">
                <?php 
                // Include status badge component
                $show_icon = true;
                $css_class = 'header-status-badge';
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/status-badge.php';
                ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="project-header-actions">
        <?php
        // Quick action buttons based on post type
        switch ($post_type) {
            case 'arsol-project':
                $url = wc_get_account_endpoint_url('project-create') . '?edit=' . $post_id;
                $label = __('Edit Project', 'arsol-pfw');
                $type = 'secondary';
                $icon = 'dashicons-edit';
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/action-button.php';
                break;
                
            case 'arsol-pfw-proposal':
                $url = wc_get_account_endpoint_url('project-create') . '?edit_proposal=' . $post_id;
                $label = __('Edit Proposal', 'arsol-pfw');
                $type = 'secondary';
                $icon = 'dashicons-edit';
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/action-button.php';
                break;
                
            case 'arsol-pfw-request':
                $url = wc_get_account_endpoint_url('project-create') . '?from_request=' . $post_id;
                $label = __('Convert to Project', 'arsol-pfw');
                $type = 'primary';
                $icon = 'dashicons-arrow-right-alt';
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/action-button.php';
                break;
        }
        ?>
    </div>
</div>
