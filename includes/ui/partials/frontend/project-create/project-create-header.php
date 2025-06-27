<?php
/**
 * Project Create Header
 * 
 * Header section for project creation page.
 * Variables: $edit_mode (optional), $project_id (optional), $form_type (optional)
 */

if (!defined('ABSPATH')) exit;

$edit_mode = $edit_mode ?? false;
$form_type = $form_type ?? 'project'; // project, proposal, request

// Determine page title and subtitle
$page_title = '';
$page_subtitle = '';

if ($edit_mode) {
    switch ($form_type) {
        case 'proposal':
            $page_title = __('Edit Proposal', 'arsol-pfw');
            $page_subtitle = __('Update your proposal details', 'arsol-pfw');
            break;
        case 'request':
            $page_title = __('Edit Request', 'arsol-pfw');
            $page_subtitle = __('Update your request details', 'arsol-pfw');
            break;
        default:
            $page_title = __('Edit Project', 'arsol-pfw');
            $page_subtitle = __('Update your project details', 'arsol-pfw');
            break;
    }
} else {
    switch ($form_type) {
        case 'proposal':
            $page_title = __('Create Proposal', 'arsol-pfw');
            $page_subtitle = __('Submit a new proposal', 'arsol-pfw');
            break;
        case 'request':
            $page_title = __('Request Project', 'arsol-pfw');
            $page_subtitle = __('Submit a new project request', 'arsol-pfw');
            break;
        default:
            $page_title = __('Create Project', 'arsol-pfw');
            $page_subtitle = __('Start a new project', 'arsol-pfw');
            break;
    }
}

?>

<div class="arsol-project-create-header">
    <div class="create-header-content">
        <div class="project-breadcrumb">
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>">
                <?php _e('← Back to Projects', 'arsol-pfw'); ?>
            </a>
        </div>
        
        <h1 class="create-title"><?php echo esc_html($page_title); ?></h1>
        <p class="create-subtitle"><?php echo esc_html($page_subtitle); ?></p>
    </div>
</div>
