# Creation Hooks Reference

This document provides a comprehensive reference for all creation hooks in the Arsol Projects for WooCommerce plugin. The plugin now includes hooks for direct creation of all three main post types, plus conversion hooks.

## Overview

The plugin implements comprehensive hook systems for:

1. **Request Creation** (Direct form submission): 11 hooks
2. **Proposal Creation** (Direct form submission): 11 hooks  
3. **Project Creation** (Direct form submission): 11 hooks
4. **Proposal Conversion** (Request → Proposal): 12 hooks
5. **Project Conversion** (Proposal → Project): 15 hooks

All systems follow the same architectural pattern with rich context data and extensive customization points.

## Request Creation Hooks

### Implementation Location
`includes/workflow/class-workflow-handler.php` - `handle_create_request()` method

### Hook Sequence

#### Phase 1: Pre-Validation
- `arsol_before_request_creation_validation` - Before any validation checks
- `arsol_after_request_creation_validated` - After validation passes

#### Phase 2: Post Creation
- `arsol_request_creation_args` (FILTER) - Modify request arguments
- `arsol_before_request_creation_post_creation` - Before request creation
- `arsol_after_request_creation_post_created` - After request created
- `arsol_request_creation_post_creation_failed` - On request creation failure

#### Phase 3: Status Assignment
- `arsol_before_request_creation_status_assignment` - Before status assignment
- `arsol_after_request_creation_status_assigned` - After status assigned

#### Phase 4: Metadata & Completion
- `arsol_before_request_creation_metadata_save` - Before metadata save
- `arsol_after_request_creation_metadata_saved` - After metadata saved
- `arsol_after_request_creation_complete` - Creation complete
- `arsol_before_request_creation_redirect` - Before redirect

### Context Data
```php
$creation_data = array(
    'user_id' => int,
    'creation_method' => 'frontend_form',
    'timestamp' => int,
    'form_data' => array,  // $_POST data
    'request_id' => int    // Added after creation
);
```

## Proposal Creation Hooks

### Implementation Location
`includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php` - `handle_project_proposal_submission()` method

### Hook Sequence

#### Phase 1: Pre-Validation
- `arsol_before_proposal_creation_validation` - Before any validation checks
- `arsol_after_proposal_creation_validated` - After validation passes

#### Phase 2: Post Creation
- `arsol_proposal_creation_args` (FILTER) - Modify proposal arguments
- `arsol_before_proposal_creation_post_creation` - Before proposal creation
- `arsol_after_proposal_creation_post_created` - After proposal created
- `arsol_proposal_creation_post_creation_failed` - On proposal creation failure

#### Phase 3: Status Assignment
- `arsol_before_proposal_creation_status_assignment` - Before status assignment
- `arsol_after_proposal_creation_status_assigned` - After status assigned

#### Phase 4: Metadata & Completion
- `arsol_before_proposal_creation_metadata_save` - Before metadata save
- `arsol_after_proposal_creation_metadata_saved` - After metadata saved
- `arsol_after_proposal_creation_complete` - Creation complete
- `arsol_before_proposal_creation_redirect` - Before redirect

### Context Data
```php
$creation_data = array(
    'user_id' => int,
    'creation_method' => 'frontend_form',
    'timestamp' => int,
    'form_data' => array,  // $_POST data
    'proposal_id' => int   // Added after creation
);
```

### Backward Compatibility
The system maintains existing hooks:
- `arsol_before_project_proposal_insert` (FILTER) - Still available
- `arsol_after_project_proposal_insert` - Still available

## Project Creation Hooks

### Implementation Location
`includes/custom-post-types/project/class-project-cpt-frontend-handler.php` - `handle_create_project_submission()` method

### Hook Sequence

#### Phase 1: Pre-Validation
- `arsol_before_project_creation_validation` - Before any validation checks
- `arsol_after_project_creation_validated` - After validation passes

#### Phase 2: Post Creation
- `arsol_project_creation_args` (FILTER) - Modify project arguments
- `arsol_before_project_creation_post_creation` - Before project creation
- `arsol_after_project_creation_post_created` - After project created
- `arsol_project_creation_post_creation_failed` - On project creation failure

#### Phase 3: Status Assignment
- `arsol_before_project_creation_status_assignment` - Before status assignment
- `arsol_after_project_creation_status_assigned` - After status assigned

#### Phase 4: Metadata & Completion
- `arsol_before_project_creation_metadata_save` - Before metadata save
- `arsol_after_project_creation_metadata_saved` - After metadata saved
- `arsol_after_project_creation_complete` - Creation complete
- `arsol_before_project_creation_redirect` - Before redirect

### Context Data
```php
$creation_data = array(
    'user_id' => int,
    'creation_method' => 'frontend_form',
    'timestamp' => int,
    'form_data' => array,  // $_POST data
    'project_id' => int    // Added after creation
);
```

## Usage Examples

### Logging All Direct Creations
```php
// Log request creations
add_action('arsol_after_request_creation_complete', function($request_id, $creation_data) {
    error_log("Request {$request_id} created by user {$creation_data['user_id']}");
});

// Log proposal creations
add_action('arsol_after_proposal_creation_complete', function($proposal_id, $creation_data) {
    error_log("Proposal {$proposal_id} created by user {$creation_data['user_id']}");
});

// Log project creations
add_action('arsol_after_project_creation_complete', function($project_id, $creation_data) {
    error_log("Project {$project_id} created by user {$creation_data['user_id']}");
});
```

### Custom Validation
```php
// Add custom request validation
add_action('arsol_after_request_creation_validated', function($creation_data) {
    if (empty($creation_data['form_data']['request_budget'])) {
        wc_add_notice('Budget is required for all requests', 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('project-create-request'));
        exit;
    }
});

// Add custom project validation  
add_action('arsol_after_project_creation_validated', function($creation_data) {
    $user_project_count = count_user_posts($creation_data['user_id'], 'arsol-project');
    if ($user_project_count >= 5) {
        wc_add_notice('Maximum project limit reached', 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('projects'));
        exit;
    }
});
```

### Custom Default Data
```php
// Set custom default status for proposals
add_action('arsol_before_proposal_creation_status_assignment', function($proposal_id, $default_status, $creation_data) {
    // Override default status based on user role
    if (current_user_can('administrator')) {
        wp_set_object_terms($proposal_id, 'approved', 'arsol-proposal-status');
        // Skip the default status assignment
        remove_action('current_action', 'wp_set_object_terms');
    }
});

// Add auto-generated project codes
add_filter('arsol_project_creation_args', function($project_data, $creation_data) {
    $project_data['post_title'] = '[PRJ-' . time() . '] ' . $project_data['post_title'];
    return $project_data;
}, 10, 2);
```

### Integration with External Systems
```php
// Sync with CRM on request creation
add_action('arsol_after_request_creation_complete', function($request_id, $creation_data) {
    ExternalCRM::create_lead($request_id, $creation_data);
});

// Send notifications on project creation
add_action('arsol_after_project_creation_complete', function($project_id, $creation_data) {
    // Notify team members
    NotificationService::notify_team('New project created', $project_id);
    
    // Create project in external project management tool
    ProjectManagementAPI::create_project($project_id, $creation_data);
});

// Auto-assign proposals to team members
add_action('arsol_after_proposal_creation_post_created', function($proposal_id, $proposal_data, $creation_data) {
    $assigned_user = AutoAssignment::get_next_available_user();
    update_post_meta($proposal_id, '_assigned_to', $assigned_user);
});
```

### Custom Metadata Handling
```php
// Add custom request metadata
add_action('arsol_after_request_creation_metadata_saved', function($request_id, $form_data, $creation_data) {
    // Calculate priority score based on budget and timeline
    $budget = isset($form_data['request_budget']) ? floatval($form_data['request_budget']) : 0;
    $priority = $budget > 10000 ? 'high' : ($budget > 5000 ? 'medium' : 'low');
    update_post_meta($request_id, '_request_priority', $priority);
});

// Auto-populate project timeline
add_action('arsol_before_project_creation_metadata_save', function($project_id, $form_data, $creation_data) {
    if (empty($form_data['project_delivery_date']) && !empty($form_data['project_start_date'])) {
        // Auto-set delivery date to 30 days from start
        $start_date = new DateTime($form_data['project_start_date']);
        $delivery_date = $start_date->add(new DateInterval('P30D'));
        $_POST['project_delivery_date'] = $delivery_date->format('Y-m-d');
    }
});
```

### Error Handling and Recovery
```php
// Handle request creation failures
add_action('arsol_request_creation_post_creation_failed', function($error, $post_data, $creation_data) {
    // Log detailed error information
    error_log('Request creation failed: ' . $error->get_error_message());
    error_log('User ID: ' . $creation_data['user_id']);
    error_log('Form data: ' . print_r($creation_data['form_data'], true));
    
    // Send notification to admin
    wp_mail(get_option('admin_email'), 'Request Creation Failed', 
        'A user request creation failed. Check error logs for details.');
});

// Cleanup on project creation failure
add_action('arsol_project_creation_post_creation_failed', function($error, $project_data, $creation_data) {
    // Cleanup any related data that might have been created
    CleanupService::remove_temp_files($creation_data['user_id']);
});
```

## Key Features

### 🔄 **Consistent Architecture**
- All creation systems follow identical patterns
- Same naming conventions throughout
- Similar hook placement strategies

### 🛡️ **Error Handling**
- Dedicated failure hooks for each creation type
- Rich error context data
- Graceful degradation

### 🎯 **Flexibility**
- Filter hooks for arguments modification
- Rich context data for all hooks
- Multiple integration points per workflow

### ⚡ **Performance**
- Hooks fire only when needed
- Minimal overhead
- Clean separation of concerns

### 🔒 **Backward Compatibility**
- Legacy hooks maintained where they existed
- No breaking changes to existing functionality
- Smooth upgrade path

## Total Hook System Summary

The plugin now provides **60 total hooks** across all workflows:
- **Request Creation**: 11 hooks
- **Proposal Creation**: 11 hooks
- **Project Creation**: 11 hooks
- **Proposal Conversion**: 12 hooks
- **Project Conversion**: 15 hooks

This comprehensive system provides maximum flexibility for developers while maintaining full compatibility with existing functionality. 