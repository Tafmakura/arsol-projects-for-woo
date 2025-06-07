# Action Hooks Reference - Optimized for First Release

This document provides a comprehensive reference for all the action hooks available in the Arsol Projects for WooCommerce plugin. The hook system has been designed for maximum efficiency and consistency.

## Unified Sidebar Hook System

All sidebars (active, proposal, request) use the same consistent hook pattern for maximum efficiency and ease of use.

### Core Sidebar Hooks

```php
// Before sidebar content
do_action('arsol_pfw_sidebar_before', $type, $data);

// At the start of sidebar fields/content
do_action('arsol_pfw_sidebar_fields_start', $type, $data);

// At the end of sidebar fields/content  
do_action('arsol_pfw_sidebar_fields_end', $type, $data);

// After sidebar content
do_action('arsol_pfw_sidebar_after', $type, $data);
```

### Parameters

- **`$type`** (string): Sidebar type - `'active'`, `'proposal'`, or `'request'`
- **`$data`** (array): Comprehensive data array containing all relevant information

### Data Arrays by Type

#### Active Project Sidebar Data
```php
$data = [
    'project_id' => 123,
    'budget' => '5000.00',
    'start_date' => '2024-01-01',
    'delivery_date' => '2024-06-01',
    'status' => 'In Progress',
    'start_date_formatted' => 'January 1, 2024',
    'delivery_date_formatted' => 'June 1, 2024'
];
```

#### Proposal Sidebar Data
```php
$data = [
    'proposal_id' => 456
];
```

#### Request Sidebar Data
```php
$data = [
    'request_id' => 789
];
```

## Project Wrapper Hooks

### Core Wrapper Hooks

```php
// Before project wrapper
do_action('arsol_pfw_project_wrapper_before', $project_type, $wrapper_data);

// At start of project wrapper
do_action('arsol_pfw_project_wrapper_start', $project_type, $wrapper_data);

// Before project content
do_action('arsol_pfw_project_content_before', $project_type, $wrapper_data);

// After project content  
do_action('arsol_pfw_project_content_after', $project_type, $wrapper_data);

// Before sidebar wrapper
do_action('arsol_pfw_project_sidebar_wrapper_before', $project_type, $wrapper_data);

// After sidebar wrapper
do_action('arsol_pfw_project_sidebar_wrapper_after', $project_type, $wrapper_data);

// At end of project wrapper
do_action('arsol_pfw_project_wrapper_end', $project_type, $wrapper_data);

// After project wrapper
do_action('arsol_pfw_project_wrapper_after', $project_type, $wrapper_data);
```

### Wrapper Data Array
```php
$wrapper_data = [
    'project_id' => 123,
    'project_type' => 'active',    // 'active', 'proposal', 'request'
    'type' => 'overview',          // Current view type
    'content_template' => '/path/to/content/template.php',
    'sidebar_template' => '/path/to/sidebar/template.php'
];
```

## Usage Examples

### Example 1: Add Custom Content to All Sidebars

```php
// Add progress bar to active projects only
add_action('arsol_pfw_sidebar_fields_end', 'add_custom_sidebar_content', 10, 2);
function add_custom_sidebar_content($type, $data) {
    if ($type === 'active' && !empty($data['project_id'])) {
        $progress = get_post_meta($data['project_id'], '_project_progress', true);
        if ($progress) {
            echo '<div class="progress-widget">';
            echo '<p><strong>Progress:</strong> ' . intval($progress) . '%</p>';
            echo '<div class="progress-bar" style="width: ' . intval($progress) . '%"></div>';
            echo '</div>';
        }
    }
}
```

### Example 2: Add Analytics to All Sidebar Types

```php
// Track sidebar views for all types
add_action('arsol_pfw_sidebar_before', 'track_sidebar_views', 10, 2);
function track_sidebar_views($type, $data) {
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
    
    if ($post_id && is_user_logged_in()) {
        $views = get_post_meta($post_id, "_sidebar_views_{$type}", true);
        $views = $views ? intval($views) + 1 : 1;
        update_post_meta($post_id, "_sidebar_views_{$type}", $views);
    }
}
```

### Example 3: Conditional Content Based on Project Type

```php
// Add different widgets based on project type
add_action('arsol_pfw_sidebar_after', 'add_type_specific_widgets', 10, 2);
function add_type_specific_widgets($type, $data) {
    switch ($type) {
        case 'active':
            if (!empty($data['project_id'])) {
                echo '<div class="active-project-tools">Active Project Tools</div>';
            }
            break;
            
        case 'proposal':
            if (!empty($data['proposal_id'])) {
                echo '<div class="proposal-actions">Proposal Actions</div>';
            }
            break;
            
        case 'request':
            if (!empty($data['request_id'])) {
                echo '<div class="request-status">Request Status</div>';
            }
            break;
    }
}
```

### Example 4: Modify Project Wrapper

```php
// Add custom CSS classes to wrapper
add_action('arsol_pfw_project_wrapper_start', 'add_custom_wrapper_classes', 10, 2);
function add_custom_wrapper_classes($project_type, $wrapper_data) {
    $project_id = $wrapper_data['project_id'] ?? 0;
    $priority = get_post_meta($project_id, '_project_priority', true);
    
    if ($priority === 'high') {
        echo '<div class="high-priority-overlay">';
    }
}

add_action('arsol_pfw_project_wrapper_end', 'close_custom_wrapper_classes', 10, 2);
function close_custom_wrapper_classes($project_type, $wrapper_data) {
    $project_id = $wrapper_data['project_id'] ?? 0;
    $priority = get_post_meta($project_id, '_project_priority', true);
    
    if ($priority === 'high') {
        echo '</div><!-- .high-priority-overlay -->';
    }
}
```

### Example 5: Enhanced Active Project Sidebar

```php
// Add comprehensive project information
add_action('arsol_pfw_sidebar_fields_end', 'add_enhanced_project_info', 10, 2);
function add_enhanced_project_info($type, $data) {
    // Only for active projects
    if ($type !== 'active' || empty($data['project_id'])) {
        return;
    }
    
    $project_id = $data['project_id'];
    
    // Get additional meta
    $team_members = get_post_meta($project_id, '_project_team_members', true);
    $priority = get_post_meta($project_id, '_project_priority', true);
    $client = get_post_meta($project_id, '_project_client', true);
    
    echo '<div class="enhanced-project-info">';
    
    // Priority
    if ($priority) {
        $priority_class = 'priority-' . strtolower($priority);
        echo '<p><strong>Priority:</strong> <span class="' . esc_attr($priority_class) . '">' . esc_html(ucfirst($priority)) . '</span></p>';
    }
    
    // Client
    if ($client) {
        echo '<p><strong>Client:</strong> ' . esc_html($client) . '</p>';
    }
    
    // Team members
    if ($team_members && is_array($team_members)) {
        echo '<p><strong>Team:</strong></p>';
        echo '<ul class="team-list">';
        foreach ($team_members as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                echo '<li>' . esc_html($user->display_name) . '</li>';
            }
        }
        echo '</ul>';
    }
    
    echo '</div>';
}
```

## Hook Efficiency Benefits

### Performance Optimizations

1. **Reduced Hook Calls**: From 15+ hooks per sidebar to just 4 essential hooks
2. **Comprehensive Data**: All relevant data passed in single array, reducing meta queries
3. **Consistent Interface**: Same hooks work for all sidebar types
4. **Type-Based Logic**: Easy conditional logic based on `$type` parameter

### Developer Benefits

1. **Simplified API**: Fewer hooks to remember and implement
2. **Flexible Data Access**: Rich data arrays provide all needed information
3. **Type Safety**: Clear type parameters prevent hook misuse
4. **Future Proof**: Extensible data arrays for new features

## Best Practices

### 1. Type Checking
```php
add_action('arsol_pfw_sidebar_after', 'my_sidebar_function', 10, 2);
function my_sidebar_function($type, $data) {
    // Always check type first
    if ($type !== 'active') {
        return;
    }
    
    // Then check for required data
    if (empty($data['project_id'])) {
        return;
    }
    
    // Your code here
}
```

### 2. Data Validation
```php
add_action('arsol_pfw_sidebar_fields_end', 'my_field_function', 10, 2);
function my_field_function($type, $data) {
    // Extract with defaults
    $project_id = $data['project_id'] ?? 0;
    $status = $data['status'] ?? '';
    
    // Validate data exists
    if (!$project_id || !$status) {
        return;
    }
    
    // Your code here
}
```

### 3. Efficient Hook Priority
```php
// Use priority 5 for early modifications
add_action('arsol_pfw_sidebar_before', 'early_function', 5, 2);

// Use priority 10 (default) for normal additions
add_action('arsol_pfw_sidebar_fields_end', 'normal_function', 10, 2);

// Use priority 15 for late modifications
add_action('arsol_pfw_sidebar_after', 'late_function', 15, 2);
```

This optimized hook system provides maximum flexibility with minimum overhead, perfect for the plugin's first release. 