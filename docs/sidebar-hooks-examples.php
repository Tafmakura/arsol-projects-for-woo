<?php
/**
 * Sidebar Action Hooks - Optimized Examples (First Release)
 * 
 * This file contains practical examples of how to use the streamlined sidebar action hooks
 * in the Arsol Projects for WooCommerce plugin. Copy these examples to your theme's
 * functions.php file or a custom plugin.
 * 
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Example 1: Add Project Progress Bar to Active Projects
 * 
 * Uses the new efficient hook system with type checking.
 */
add_action('arsol_pfw_sidebar_fields_end', 'add_project_progress_bar', 10, 2);
function add_project_progress_bar($type, $data) {
    // Only for active projects
    if ($type !== 'active' || empty($data['project_id'])) {
        return;
    }
    
    $progress = get_post_meta($data['project_id'], '_project_progress', true);
    
    if ($progress) {
        $progress = max(0, min(100, intval($progress))); // Ensure 0-100 range
        ?>
        <div class="project-progress-section">
            <p><strong><?php _e('Progress:', 'your-textdomain'); ?></strong></p>
            <div class="progress-bar-container" style="background: #f1f1f1; border-radius: 4px; height: 20px; overflow: hidden;">
                <div class="progress-bar-fill" style="background: #2196F3; height: 100%; width: <?php echo $progress; ?>%; transition: width 0.3s ease;"></div>
            </div>
            <small style="color: #666;"><?php echo $progress; ?>% <?php _e('Complete', 'your-textdomain'); ?></small>
        </div>
        <?php
    }
}

/**
 * Example 2: Universal Analytics Tracking
 * 
 * Tracks views for all sidebar types using the unified system.
 */
add_action('arsol_pfw_sidebar_before', 'track_universal_sidebar_views', 10, 2);
function track_universal_sidebar_views($type, $data) {
    if (!is_user_logged_in()) {
        return;
    }
    
    $post_id = 0;
    
    // Extract ID based on type
    switch ($type) {
        case 'active':
            $post_id = $data['project_id'] ?? 0;
            break;
        case 'proposal':
            $post_id = $data['proposal_id'] ?? 0;
            break;
        case 'request':
            $post_id = $data['request_id'] ?? 0;
            break;
    }
    
    if ($post_id) {
        $views = get_post_meta($post_id, "_sidebar_views_{$type}", true);
        $views = $views ? intval($views) + 1 : 1;
        update_post_meta($post_id, "_sidebar_views_{$type}", $views);
        
        // Track last viewed time
        update_post_meta($post_id, "_sidebar_last_viewed_{$type}", current_time('timestamp'));
    }
}

/**
 * Example 3: Type-Specific Widgets
 * 
 * Shows different content based on sidebar type.
 */
add_action('arsol_pfw_sidebar_after', 'add_type_specific_widgets', 10, 2);
function add_type_specific_widgets($type, $data) {
    switch ($type) {
        case 'active':
            if (!empty($data['project_id'])) {
                add_active_project_tools($data);
            }
            break;
            
        case 'proposal':
            if (!empty($data['proposal_id'])) {
                add_proposal_actions($data);
            }
            break;
            
        case 'request':
            if (!empty($data['request_id'])) {
                add_request_status($data);
            }
            break;
    }
}

function add_active_project_tools($data) {
    $project_id = $data['project_id'];
    
    // Only show for users who can edit projects
    if (!current_user_can('edit_post', $project_id)) {
        return;
    }
    
    $edit_url = get_edit_post_link($project_id);
    ?>
    <div class="active-project-tools">
        <h4><?php _e('Quick Actions', 'your-textdomain'); ?></h4>
        <?php if ($edit_url) : ?>
            <a href="<?php echo esc_url($edit_url); ?>" class="button button-small">
                <?php _e('Edit Project', 'your-textdomain'); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}

function add_proposal_actions($data) {
    $proposal_id = $data['proposal_id'];
    echo '<div class="proposal-actions">';
    echo '<h4>' . __('Proposal Actions', 'your-textdomain') . '</h4>';
    echo '<p><small>' . sprintf(__('Proposal ID: %d', 'your-textdomain'), $proposal_id) . '</small></p>';
    echo '</div>';
}

function add_request_status($data) {
    $request_id = $data['request_id'];
    echo '<div class="request-status">';
    echo '<h4>' . __('Request Status', 'your-textdomain') . '</h4>';
    echo '<p><small>' . sprintf(__('Request ID: %d', 'your-textdomain'), $request_id) . '</small></p>';
    echo '</div>';
}

/**
 * Example 4: Enhanced Active Project Information
 * 
 * Uses all available data from the active project sidebar.
 */
add_action('arsol_pfw_sidebar_fields_end', 'add_enhanced_active_info', 10, 2);
function add_enhanced_active_info($type, $data) {
    if ($type !== 'active' || empty($data['project_id'])) {
        return;
    }
    
    $project_id = $data['project_id'];
    
    // Get additional project metadata
    $priority = get_post_meta($project_id, '_project_priority', true);
    $client = get_post_meta($project_id, '_project_client', true);
    $team_members = get_post_meta($project_id, '_project_team_members', true);
    
    echo '<div class="enhanced-project-info">';
    
    // Priority badge
    if ($priority) {
        $priority_colors = [
            'high' => '#f44336',
            'medium' => '#ff9800',
            'low' => '#4caf50'
        ];
        $color = $priority_colors[$priority] ?? '#666';
        
        echo '<p><strong>' . __('Priority:', 'your-textdomain') . '</strong> ';
        echo '<span style="background: ' . $color . '; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px;">';
        echo esc_html(strtoupper($priority));
        echo '</span></p>';
    }
    
    // Client information
    if ($client) {
        echo '<p><strong>' . __('Client:', 'your-textdomain') . '</strong> ' . esc_html($client) . '</p>';
    }
    
    // Team members
    if ($team_members && is_array($team_members)) {
        echo '<div class="team-members">';
        echo '<p><strong>' . __('Team Members:', 'your-textdomain') . '</strong></p>';
        echo '<ul style="margin: 0; padding-left: 20px;">';
        foreach ($team_members as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                echo '<li>' . esc_html($user->display_name) . '</li>';
            }
        }
        echo '</ul>';
        echo '</div>';
    }
    
    // Calculate project duration using existing data
    if (!empty($data['start_date']) && !empty($data['delivery_date'])) {
        $start = strtotime($data['start_date']);
        $end = strtotime($data['delivery_date']);
        $duration_days = round(($end - $start) / DAY_IN_SECONDS);
        
        if ($duration_days > 0) {
            echo '<p><strong>' . __('Duration:', 'your-textdomain') . '</strong> ';
            echo sprintf(_n('%d day', '%d days', $duration_days, 'your-textdomain'), $duration_days);
            echo '</p>';
        }
    }
    
    echo '</div>';
}

/**
 * Example 5: Project Wrapper Customization
 * 
 * Modifies the project wrapper based on project data.
 */
add_action('arsol_pfw_project_wrapper_start', 'add_project_wrapper_classes', 10, 2);
function add_project_wrapper_classes($project_type, $wrapper_data) {
    $project_id = $wrapper_data['project_id'] ?? 0;
    
    if (!$project_id) {
        return;
    }
    
    $priority = get_post_meta($project_id, '_project_priority', true);
    $status = '';
    
    if ($project_type === 'active') {
        $status_terms = wp_get_post_terms($project_id, 'arsol-project-status', array('fields' => 'slugs'));
        $status = !empty($status_terms) ? $status_terms[0] : '';
    }
    
    $classes = ['project-enhanced'];
    
    if ($priority) {
        $classes[] = 'priority-' . $priority;
    }
    
    if ($status) {
        $classes[] = 'status-' . $status;
    }
    
    echo '<div class="' . esc_attr(implode(' ', $classes)) . '">';
}

add_action('arsol_pfw_project_wrapper_end', 'close_project_wrapper_classes', 10, 2);
function close_project_wrapper_classes($project_type, $wrapper_data) {
    echo '</div><!-- .project-enhanced -->';
}

/**
 * Example 6: Conditional Content Based on User Role
 * 
 * Shows different information to different user roles.
 */
add_action('arsol_pfw_sidebar_before', 'add_role_specific_content', 10, 2);
function add_role_specific_content($type, $data) {
    $current_user = wp_get_current_user();
    
    if (in_array('administrator', $current_user->roles)) {
        $post_id = 0;
        
        switch ($type) {
            case 'active':
                $post_id = $data['project_id'] ?? 0;
                break;
            case 'proposal':
                $post_id = $data['proposal_id'] ?? 0;
                break;
            case 'request':
                $post_id = $data['request_id'] ?? 0;
                break;
        }
        
        if ($post_id) {
            echo '<div class="admin-info" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 8px; margin-bottom: 10px; border-radius: 3px; font-size: 12px;">';
            echo '<strong>' . __('Admin Info:', 'your-textdomain') . '</strong> ';
            echo sprintf(__('%s ID: %d', 'your-textdomain'), ucfirst($type), $post_id);
            echo '</div>';
        }
    }
}

/**
 * Example 7: Time Tracking Integration
 * 
 * Shows time tracking information for active projects.
 */
add_action('arsol_pfw_sidebar_after', 'add_time_tracking_widget', 10, 2);
function add_time_tracking_widget($type, $data) {
    if ($type !== 'active' || empty($data['project_id'])) {
        return;
    }
    
    $project_id = $data['project_id'];
    $time_entries = get_post_meta($project_id, '_project_time_entries', true);
    
    if (!$time_entries || !is_array($time_entries)) {
        return;
    }
    
    $total_hours = 0;
    foreach ($time_entries as $entry) {
        $total_hours += floatval($entry['hours'] ?? 0);
    }
    
    if ($total_hours > 0) {
        $hourly_rate = get_post_meta($project_id, '_project_hourly_rate', true);
        $total_cost = $hourly_rate ? $total_hours * floatval($hourly_rate) : 0;
        
        echo '<div class="time-tracking-widget" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">';
        echo '<h4 style="margin: 0 0 8px 0; font-size: 14px;">' . __('Time Tracking', 'your-textdomain') . '</h4>';
        echo '<p style="margin: 5px 0;">';
        echo '<strong>' . __('Time Spent:', 'your-textdomain') . '</strong> ';
        echo number_format($total_hours, 1) . ' ' . __('hours', 'your-textdomain');
        echo '</p>';
        
        if ($total_cost > 0) {
            echo '<p style="margin: 5px 0;">';
            echo '<strong>' . __('Cost:', 'your-textdomain') . '</strong> ';
            echo wc_price($total_cost);
            echo '</p>';
        }
        echo '</div>';
    }
}

/**
 * Example 8: Performance-Optimized Custom Fields
 * 
 * Efficiently displays multiple custom fields.
 */
add_action('arsol_pfw_sidebar_fields_end', 'add_custom_fields_optimized', 10, 2);
function add_custom_fields_optimized($type, $data) {
    if ($type !== 'active' || empty($data['project_id'])) {
        return;
    }
    
    $project_id = $data['project_id'];
    
    // Get all meta in one query for performance
    $meta = get_post_meta($project_id);
    
    $custom_fields = [
        '_project_category' => __('Category', 'your-textdomain'),
        '_project_complexity' => __('Complexity', 'your-textdomain'),
        '_project_estimated_hours' => __('Est. Hours', 'your-textdomain'),
    ];
    
    $has_custom_fields = false;
    foreach ($custom_fields as $key => $label) {
        if (!empty($meta[$key][0])) {
            $has_custom_fields = true;
            break;
        }
    }
    
    if ($has_custom_fields) {
        echo '<div class="custom-fields-section" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">';
        echo '<h4 style="margin: 0 0 8px 0; font-size: 14px;">' . __('Additional Info', 'your-textdomain') . '</h4>';
        
        foreach ($custom_fields as $key => $label) {
            $value = $meta[$key][0] ?? '';
            if ($value) {
                echo '<p style="margin: 5px 0;">';
                echo '<strong>' . esc_html($label) . ':</strong> ';
                
                // Special formatting for estimated hours
                if ($key === '_project_estimated_hours') {
                    echo number_format(floatval($value), 1) . ' ' . __('hours', 'your-textdomain');
                } else {
                    echo esc_html($value);
                }
                echo '</p>';
            }
        }
        echo '</div>';
    }
}

/**
 * Example 9: File Attachments with Type Support
 * 
 * Shows attachments for any project type.
 */
add_action('arsol_pfw_sidebar_after', 'add_universal_attachments', 10, 2);
function add_universal_attachments($type, $data) {
    $post_id = 0;
    
    switch ($type) {
        case 'active':
            $post_id = $data['project_id'] ?? 0;
            $meta_key = '_project_attachments';
            break;
        case 'proposal':
            $post_id = $data['proposal_id'] ?? 0;
            $meta_key = '_proposal_attachments';
            break;
        case 'request':
            $post_id = $data['request_id'] ?? 0;
            $meta_key = '_request_attachments';
            break;
        default:
            return;
    }
    
    if (!$post_id) {
        return;
    }
    
    $attachments = get_post_meta($post_id, $meta_key, true);
    
    if ($attachments && is_array($attachments)) {
        echo '<div class="attachments-section" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">';
        echo '<h4 style="margin: 0 0 8px 0; font-size: 14px;">' . __('Attachments', 'your-textdomain') . '</h4>';
        echo '<ul style="margin: 0; padding: 0; list-style: none;">';
        
        foreach ($attachments as $attachment_id) {
            $file = get_post($attachment_id);
            if ($file) {
                $file_url = wp_get_attachment_url($attachment_id);
                $file_path = get_attached_file($attachment_id);
                $file_size = file_exists($file_path) ? size_format(filesize($file_path)) : '';
                
                echo '<li style="margin-bottom: 5px; padding: 5px 0; border-bottom: 1px solid #f5f5f5;">';
                echo '<a href="' . esc_url($file_url) . '" target="_blank" style="text-decoration: none; color: #0073aa;">';
                echo '<strong>' . esc_html($file->post_title) . '</strong>';
                if ($file_size) {
                    echo '<br><small style="color: #666;">' . esc_html($file_size) . '</small>';
                }
                echo '</a>';
                echo '</li>';
            }
        }
        echo '</ul>';
        echo '</div>';
    }
}

/*
 * Performance Benefits of the New System:
 * 
 * 1. Reduced Hook Calls: From 15+ hooks to just 4 per sidebar
 * 2. Efficient Data Passing: All data in single array
 * 3. Type-Based Logic: Easy conditional execution
 * 4. Future-Proof: Extensible data structures
 * 
 * Usage Tips:
 * - Always check $type first for efficiency
 * - Extract data with null coalescing (??) for safety
 * - Use early returns to avoid unnecessary processing
 * - Leverage the comprehensive data arrays
 */ 