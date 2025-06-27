<?php
/**
 * Project Sidebar - Organism
 * 
 * Complete sidebar for project, proposal, and request pages.
 * Combines meta information, status display, and action buttons.
 * 
 * @package Arsol_Projects_For_Woo
 * @atomic-level organisms
 * 
 * Required Variables:
 * @var int    $post_id       - Post ID (project, proposal, or request)
 * @var string $post_type     - Post type ('arsol-project', 'arsol-proposal', 'arsol-request')
 * @var string $status        - Current status slug
 * @var string $status_label  - Human-readable status label
 * 
 * Optional Variables:
 * @var string $css_class     - Additional CSS classes (default: '')
 * @var bool   $show_meta     - Show metadata section (default: true)
 * @var bool   $show_actions  - Show action buttons (default: true)
 * @var array  $custom_actions - Custom action buttons (default: [])
 * 
 * Hooks:
 * @hook arsol_pfw_before_project_sidebar - Fired before sidebar output
 * @hook arsol_pfw_after_project_sidebar  - Fired after sidebar output
 * @hook arsol_pfw_sidebar_actions_{$post_type} - Custom actions for post type
 * 
 * Usage Example:
 * arsol_load_organism('ProjectSidebar.php', [
 *     'post_id' => 123,
 *     'post_type' => 'arsol-project',
 *     'status' => 'in-progress',
 *     'status_label' => 'In Progress'
 * ]);
 */

if (!defined('ABSPATH')) exit;

// Set defaults for optional variables
$css_class = $css_class ?? '';
$show_meta = $show_meta ?? true;
$show_actions = $show_actions ?? true;
$custom_actions = $custom_actions ?? [];

// Validate required variables
if (empty($post_id) || empty($post_type) || empty($status)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('ARSOL: Missing required variables (post_id, post_type, status) for ProjectSidebar');
    }
    return;
}

// Get post object for additional data
$post = get_post($post_id);
if (!$post) {
    return;
}

// Generate sidebar CSS classes
$sidebar_classes = ['arsol-project-sidebar', 'sidebar-' . sanitize_html_class($post_type)];
if (!empty($css_class)) {
    $sidebar_classes[] = $css_class;
}

// Component-specific hooks
do_action('arsol_pfw_before_project_sidebar', $post_id, $post_type, $status);

?>

<aside class="<?php echo esc_attr(implode(' ', $sidebar_classes)); ?>">
    
    <!-- Status Section -->
    <div class="arsol-sidebar-section arsol-sidebar-status">
        <h4 class="arsol-sidebar-title"><?php _e('Status', 'arsol-pfw'); ?></h4>
        <?php
        arsol_load_element('StatusBadge.php', [
            'status' => $status,
            'status_label' => $status_label,
            'css_class' => 'sidebar-status-badge',
            'show_icon' => true,
            'context' => 'sidebar'
        ]);
        ?>
    </div>

    <?php if ($show_meta): ?>
        <!-- Meta Information Section -->
        <?php
        arsol_load_molecule('SidebarMeta.php', [
            'post_id' => $post_id,
            'post_type' => $post_type,
            'css_class' => 'sidebar-meta-section'
        ]);
        ?>
    <?php endif; ?>

    <?php if ($show_actions): ?>
        <!-- Actions Section -->
        <div class="arsol-sidebar-section arsol-sidebar-actions">
            <h4 class="arsol-sidebar-title"><?php _e('Actions', 'arsol-pfw'); ?></h4>
            <div class="arsol-action-buttons">
                
                <?php
                // Default actions based on post type and status
                $default_actions = [];
                
                switch ($post_type) {
                    case 'arsol-project':
                        $default_actions = [
                            'edit' => [
                                'url' => wc_get_account_endpoint_url('project-create') . '?edit=' . $post_id,
                                'label' => __('Edit Project', 'arsol-pfw'),
                                'type' => 'secondary',
                                'icon' => 'dashicons-edit'
                            ]
                        ];
                        
                        // Status-specific actions
                        if ($status === 'not-started') {
                            $default_actions['start'] = [
                                'url' => add_query_arg(['action' => 'start_project', 'project_id' => $post_id], wc_get_account_endpoint_url('projects')),
                                'label' => __('Start Project', 'arsol-pfw'),
                                'type' => 'primary',
                                'icon' => 'dashicons-controls-play'
                            ];
                        } elseif ($status === 'in-progress') {
                            $default_actions['complete'] = [
                                'url' => add_query_arg(['action' => 'complete_project', 'project_id' => $post_id], wc_get_account_endpoint_url('projects')),
                                'label' => __('Mark Complete', 'arsol-pfw'),
                                'type' => 'primary',
                                'icon' => 'dashicons-yes-alt',
                                'confirm' => true,
                                'confirm_text' => __('Mark this project as complete?', 'arsol-pfw')
                            ];
                        }
                        break;
                        
                    case 'arsol-pfw-proposal':
                        $default_actions = [
                            'edit' => [
                                'url' => wc_get_account_endpoint_url('project-create') . '?edit_proposal=' . $post_id,
                                'label' => __('Edit Proposal', 'arsol-pfw'),
                                'type' => 'secondary',
                                'icon' => 'dashicons-edit'
                            ]
                        ];
                        
                        if (in_array($status, ['draft', 'processing'])) {
                            $default_actions['send'] = [
                                'url' => add_query_arg(['action' => 'send_proposal', 'proposal_id' => $post_id], wc_get_account_endpoint_url('projects')),
                                'label' => __('Send Proposal', 'arsol-pfw'),
                                'type' => 'primary',
                                'icon' => 'dashicons-email-alt'
                            ];
                        }
                        break;
                        
                    case 'arsol-pfw-request':
                        $default_actions = [
                            'convert' => [
                                'url' => wc_get_account_endpoint_url('project-create') . '?from_request=' . $post_id,
                                'label' => __('Convert to Project', 'arsol-pfw'),
                                'type' => 'primary',
                                'icon' => 'dashicons-arrow-right-alt'
                            ]
                        ];
                        break;
                }
                
                // Merge custom actions with defaults
                $all_actions = array_merge($default_actions, $custom_actions);
                
                // Allow filtering of actions
                $all_actions = apply_filters('arsol_pfw_sidebar_actions', $all_actions, $post_id, $post_type, $status);
                $all_actions = apply_filters("arsol_pfw_sidebar_actions_{$post_type}", $all_actions, $post_id, $status);
                
                // Render action buttons
                foreach ($all_actions as $action_key => $action_data) {
                    arsol_load_element('ActionButton.php', [
                        'url' => $action_data['url'],
                        'label' => $action_data['label'],
                        'type' => $action_data['type'] ?? 'secondary',
                        'icon' => $action_data['icon'] ?? '',
                        'confirm' => $action_data['confirm'] ?? false,
                        'confirm_text' => $action_data['confirm_text'] ?? '',
                        'method' => $action_data['method'] ?? 'GET',
                        'css_class' => 'sidebar-action-' . sanitize_html_class($action_key)
                    ]);
                }
                ?>
                
                <?php
                // Hook for additional custom actions
                do_action('arsol_pfw_sidebar_actions_' . $post_type, $post_id, $status);
                ?>
                
            </div>
        </div>
    <?php endif; ?>
    
    <?php
    // Hook for additional sidebar sections
    do_action('arsol_pfw_sidebar_additional_sections', $post_id, $post_type, $status);
    ?>

</aside>

<?php

// After component hook
do_action('arsol_pfw_after_project_sidebar', $post_id, $post_type, $status);
