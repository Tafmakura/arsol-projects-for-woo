<?php
/**
 * Project View Request Header
 * 
 * Header section for viewing individual requests.
 * Variables: $request_id, $request_title (optional)
 */

if (!defined('ABSPATH')) exit;

$request_title = $request_title ?? get_the_title($request_id);

?>

<div class="arsol-project-view-request-header">
    <div class="view-request-header-content">
        <div class="project-breadcrumb">
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>">
                <?php _e('← Back to Projects', 'arsol-pfw'); ?>
            </a>
        </div>
        
        <h1 class="view-request-title"><?php echo esc_html($request_title); ?></h1>
        <p class="view-request-subtitle"><?php _e('Request Details', 'arsol-pfw'); ?></p>
    </div>
    
    <div class="view-request-header-actions">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-create') . '?from_request=' . $request_id); ?>" class="button button-primary">
            <?php _e('Convert to Project', 'arsol-pfw'); ?>
        </a>
    </div>
</div>
