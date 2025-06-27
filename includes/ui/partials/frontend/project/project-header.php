<?php
/**
 * Reusable Project Header
 * 
 * A flexible header component that can be used across different project-related pages.
 * 
 * Required Variables:
 * @var string $header_type - Type of header: 'project-item' or 'project-page'
 * 
 * For 'project-item' type (individual projects/proposals/requests):
 * @var int $post_id - The post ID
 * @var string $post_type - The post type (arsol-project, arsol-pfw-proposal, arsol-pfw-request)
 * @var string $status - Current status slug (optional)
 * @var string $status_label - Current status label (optional)
 * @var string $page_title_override - Override the default page title (optional)
 * 
 * For 'project-page' type (project-related pages):
 * @var string $page_title - Main page title
 * @var string $page_subtitle - Page subtitle (optional)
 * @var int $project_id - Related project ID (optional)
 * @var string $project_title - Related project title (optional)
 * @var array $breadcrumbs - Custom breadcrumb array (optional)
 * @var array $actions - Array of action buttons (optional)
 */

if (!defined('ABSPATH')) exit;

// Validate required variables
if (empty($header_type)) {
    return;
}

// Set default values
$header_class = 'arsol-project-header';
$show_breadcrumbs = true;
$show_actions = true;

if ($header_type === 'project-item') {
    // Individual project/proposal/request header
    if (empty($post_id)) {
        return;
    }
    
    $post = get_post($post_id);
    if (!$post) {
        return;
    }
    
    // Determine page title based on post type
    if (!empty($page_title_override)) {
        $page_title = $page_title_override;
    } else {
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
    }
    
    $main_title = $post->post_title;
    $subtitle = $page_title;
    $header_class .= ' project-item-header';
    
} elseif ($header_type === 'project-page') {
    // Project-related page header
    if (empty($page_title)) {
        return;
    }
    
    $main_title = $page_title;
    $subtitle = $page_subtitle ?? '';
    $header_class .= ' project-page-header';
    
} else {
    // Invalid header type
    return;
}

?>

<div class="<?php echo esc_attr($header_class); ?>">
    <div class="project-header-content">
        <?php if ($show_breadcrumbs): ?>
            <div class="project-breadcrumb">
                <?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                        <?php if ($index > 0): ?>
                            <span class="breadcrumb-separator">/</span>
                        <?php endif; ?>
                        <?php if (!empty($breadcrumb['url'])): ?>
                            <a href="<?php echo esc_url($breadcrumb['url']); ?>">
                                <?php echo esc_html($breadcrumb['label']); ?>
                            </a>
                        <?php else: ?>
                            <span><?php echo esc_html($breadcrumb['label']); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default breadcrumb: Back to Projects -->
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>">
                        <?php _e('← Back to Projects', 'arsol-pfw'); ?>
                    </a>
                    
                    <?php if ($header_type === 'project-page' && !empty($project_id) && !empty($project_title)): ?>
                        <span class="breadcrumb-separator">/</span>
                        <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-overview') . '/' . $project_id); ?>">
                            <?php echo esc_html($project_title); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <h1 class="project-title"><?php echo esc_html($main_title); ?></h1>
        <?php if (!empty($subtitle)): ?>
            <p class="project-subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
        
        <?php if ($header_type === 'project-item' && !empty($status) && !empty($status_label)): ?>
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
    
    <?php if ($show_actions): ?>
        <div class="project-header-actions">
            <?php if (!empty($actions) && is_array($actions)): ?>
                <?php foreach ($actions as $action): ?>
                    <?php if (!empty($action['url']) && !empty($action['label'])): ?>
                        <?php
                        $url = $action['url'];
                        $label = $action['label'];
                        $type = $action['type'] ?? 'secondary';
                        $icon = $action['icon'] ?? '';
                        $confirm_message = $action['confirm_message'] ?? '';
                        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/action-button.php';
                        ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php elseif ($header_type === 'project-item'): ?>
                <?php
                // Default actions based on post type
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
            <?php elseif ($header_type === 'project-page' && !empty($project_id)): ?>
                <!-- Default action for project pages: View Project -->
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-overview') . '/' . $project_id); ?>" class="button button-secondary">
                    <?php _e('View Project', 'arsol-pfw'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div> 