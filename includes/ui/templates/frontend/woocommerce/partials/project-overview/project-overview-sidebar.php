<?php
/**
 * Project Overview Sidebar
 * 
 * Sidebar for individual project pages.
 * Variables: $post_id, $post_type, $status, $status_label, $css_class
 */

if (!defined('ABSPATH')) exit;

// Validate required variables
if (empty($post_id) || empty($post_type)) {
    return;
}

// Get post object
$post = get_post($post_id);
if (!$post) {
    return;
}

// Generate sidebar CSS classes
$sidebar_classes = ['arsol-project-overview-sidebar', 'sidebar-' . sanitize_html_class($post_type)];
if (!empty($css_class)) {
    $sidebar_classes[] = $css_class;
}

?>

<aside class="<?php echo esc_attr(implode(' ', $sidebar_classes)); ?>">
    
    <!-- Status Section -->
    <?php if (!empty($status) && !empty($status_label)): ?>
    <div class="arsol-sidebar-section arsol-sidebar-status">
        <h4 class="arsol-sidebar-title"><?php _e('Status', 'arsol-pfw'); ?></h4>
        <?php 
        // Include status badge component
        $show_icon = true;
        $css_class = 'sidebar-status-badge';
        include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/status-badge.php';
        ?>
    </div>
    <?php endif; ?>
    
    <!-- Meta Information Section -->
    <div class="arsol-sidebar-section arsol-sidebar-meta">
        <?php
        // Auto-detect fields based on post type
        $fields = [];
        $title = '';
        
        switch ($post_type) {
            case 'arsol-project':
                $fields = [
                    'budget' => ['label' => 'Budget', 'type' => 'currency'],
                    'start_date' => ['label' => 'Start Date', 'type' => 'date'],
                    'due_date' => ['label' => 'Due Date', 'type' => 'date'],
                    'client_name' => ['label' => 'Client', 'type' => 'text'],
                    'project_type' => ['label' => 'Type', 'type' => 'text']
                ];
                $title = __('Project Details', 'arsol-pfw');
                break;
                
            case 'arsol-pfw-proposal':
                $fields = [
                    'proposal_amount' => ['label' => 'Amount', 'type' => 'currency'],
                    'valid_until' => ['label' => 'Valid Until', 'type' => 'date'],
                    'client_name' => ['label' => 'Client', 'type' => 'text'],
                    'proposal_type' => ['label' => 'Type', 'type' => 'text']
                ];
                $title = __('Proposal Details', 'arsol-pfw');
                break;
                
            case 'arsol-pfw-request':
                $fields = [
                    'requested_budget' => ['label' => 'Budget', 'type' => 'currency'],
                    'requested_date' => ['label' => 'Requested Date', 'type' => 'date'],
                    'client_name' => ['label' => 'Client', 'type' => 'text'],
                    'request_type' => ['label' => 'Type', 'type' => 'text']
                ];
                $title = __('Request Details', 'arsol-pfw');
                break;
        }
        
        // Get metadata values
        $meta_values = [];
        foreach ($fields as $field_key => $field_config) {
            $value = get_post_meta($post_id, $field_key, true);
            if (!empty($value)) {
                $meta_values[$field_key] = [
                    'label' => $field_config['label'],
                    'value' => $value,
                    'type' => $field_config['type'] ?? 'text'
                ];
            }
        }
        
        // Show meta section if we have data
        if (!empty($meta_values)):
        ?>
            <h4 class="arsol-sidebar-title"><?php echo esc_html($title); ?></h4>
            <div class="arsol-meta-fields">
                <?php foreach ($meta_values as $field_key => $field_data): ?>
                    <?php
                    // Set up variables for meta field component
                    $label = $field_data['label'];
                    $value = $field_data['value'];
                    $type = $field_data['type'];
                    
                    // Include meta field component
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/meta-field.php';
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Actions Section -->
    <div class="arsol-sidebar-section arsol-sidebar-actions">
        <h4 class="arsol-sidebar-title"><?php _e('Actions', 'arsol-pfw'); ?></h4>
        <div class="arsol-action-buttons">
            <?php
            // Default actions based on post type and status
            $actions = [];
            
            switch ($post_type) {
                case 'arsol-project':
                    $actions = [
                        'edit' => [
                            'url' => wc_get_account_endpoint_url('project-create') . '?edit=' . $post_id,
                            'label' => __('Edit Project', 'arsol-pfw'),
                            'type' => 'secondary',
                            'icon' => 'dashicons-edit'
                        ]
                    ];
                    
                    // Status-specific actions
                    if ($status === 'not-started') {
                        $actions['start'] = [
                            'url' => add_query_arg(['action' => 'start_project', 'project_id' => $post_id], wc_get_account_endpoint_url('projects')),
                            'label' => __('Start Project', 'arsol-pfw'),
                            'type' => 'primary',
                            'icon' => 'dashicons-controls-play'
                        ];
                    } elseif ($status === 'in-progress') {
                        $actions['complete'] = [
                            'url' => add_query_arg(['action' => 'complete_project', 'project_id' => $post_id], wc_get_account_endpoint_url('projects')),
                            'label' => __('Mark Complete', 'arsol-pfw'),
                            'type' => 'primary',
                            'icon' => 'dashicons-yes-alt'
                        ];
                    }
                    break;
                    
                case 'arsol-pfw-proposal':
                    $actions = [
                        'edit' => [
                            'url' => wc_get_account_endpoint_url('project-create') . '?edit_proposal=' . $post_id,
                            'label' => __('Edit Proposal', 'arsol-pfw'),
                            'type' => 'secondary',
                            'icon' => 'dashicons-edit'
                        ]
                    ];
                    
                    if (in_array($status, ['draft', 'processing'])) {
                        $actions['send'] = [
                            'url' => add_query_arg(['action' => 'send_proposal', 'proposal_id' => $post_id], wc_get_account_endpoint_url('projects')),
                            'label' => __('Send Proposal', 'arsol-pfw'),
                            'type' => 'primary',
                            'icon' => 'dashicons-email-alt'
                        ];
                    }
                    break;
                    
                case 'arsol-pfw-request':
                    $actions = [
                        'convert' => [
                            'url' => wc_get_account_endpoint_url('project-create') . '?from_request=' . $post_id,
                            'label' => __('Convert to Project', 'arsol-pfw'),
                            'type' => 'primary',
                            'icon' => 'dashicons-arrow-right-alt'
                        ]
                    ];
                    break;
            }
            
            // Allow filtering of actions
            $actions = apply_filters('arsol_pfw_sidebar_actions', $actions, $post_id, $post_type, $status);
            $actions = apply_filters("arsol_pfw_sidebar_actions_{$post_type}", $actions, $post_id, $status);
            
            // Render action buttons
            foreach ($actions as $action_key => $action_data): ?>
                <?php
                // Set up variables for action button component
                $url = $action_data['url'];
                $label = $action_data['label'];
                $type = $action_data['type'] ?? 'secondary';
                $icon = $action_data['icon'] ?? '';
                $css_class = 'sidebar-action-' . sanitize_html_class($action_key);
                
                // Include action button component
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/action-button.php';
                ?>
            <?php endforeach; ?>
        </div>
    </div>
    
</aside>
