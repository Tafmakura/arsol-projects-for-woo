<?php
/**
 * Project View Proposal Header
 * 
 * Header section for viewing individual proposals.
 * Variables: $proposal_id, $proposal_title (optional)
 */

if (!defined('ABSPATH')) exit;

$proposal_title = $proposal_title ?? get_the_title($proposal_id);

?>

<div class="arsol-project-view-proposal-header">
    <div class="view-proposal-header-content">
        <div class="project-breadcrumb">
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>">
                <?php _e('← Back to Projects', 'arsol-pfw'); ?>
            </a>
        </div>
        
        <h1 class="view-proposal-title"><?php echo esc_html($proposal_title); ?></h1>
        <p class="view-proposal-subtitle"><?php _e('Proposal Details', 'arsol-pfw'); ?></p>
    </div>
    
    <div class="view-proposal-header-actions">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('project-create') . '?edit_proposal=' . $proposal_id); ?>" class="button button-secondary">
            <?php _e('Edit Proposal', 'arsol-pfw'); ?>
        </a>
    </div>
</div>
