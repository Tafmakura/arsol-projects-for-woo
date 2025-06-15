# Complete Hook System Summary

This plugin now includes comprehensive action and filter hooks for all creation and conversion workflows, following WooCommerce's before/during/after pattern.

## Overview

We've implemented five complete hook systems:

1. **Request Creation** (Direct form submission): 11 hooks
2. **Proposal Creation** (Direct form submission): 11 hooks  
3. **Project Creation** (Direct form submission): 11 hooks
4. **Proposal Conversion** (Request → Proposal): 12 hooks
5. **Project Conversion** (Proposal → Project): 15 hooks

All systems follow the same architectural pattern with rich context data and extensive customization points.

## Direct Creation Hooks

### Request Creation Hooks
**Location**: `includes/workflow/class-workflow-handler.php`

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

### Proposal Creation Hooks
**Location**: `includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php`

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

### Project Creation Hooks
**Location**: `includes/custom-post-types/project/class-project-cpt-frontend-handler.php`

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

## Conversion Hooks

### Project Conversion Hooks (Proposal → Project)
**Location**: `includes/workflow/class-workflow-handler.php`

#### Phase 1: Pre-Validation
- `arsol_before_project_conversion_validation` - Before any validation
- `arsol_after_project_conversion_validated` - After validation passes

#### Phase 2: Project Creation  
- `arsol_project_conversion_args` (FILTER) - Modify project arguments
- `arsol_before_project_conversion_project_creation` - Before project creation
- `arsol_after_project_conversion_project_created` - After project created
- `arsol_project_conversion_project_creation_failed` - On project creation failure

#### Phase 3: Metadata Copy
- `arsol_before_project_conversion_metadata_copy` - Before metadata copy
- `arsol_project_conversion_meta_mapping` (FILTER) - Modify metadata mapping
- `arsol_after_project_conversion_metadata_copied` - After metadata copied

#### Phase 4: Order Creation
- `arsol_before_project_conversion_order_creation` - Before WooCommerce order creation
- `arsol_after_project_conversion_order_creation_attempt` - After order creation attempt

#### Phase 5: Success/Rollback
- `arsol_before_project_conversion_rollback` - Before rollback on failure
- `arsol_after_project_conversion_rollback` - After rollback completed
- `arsol_before_project_conversion_proposal_deletion` - Before proposal deletion
- `arsol_after_project_conversion_complete` - Conversion complete
- `arsol_before_project_conversion_redirect` - Before redirect

### Proposal Conversion Hooks (Request → Proposal)
**Location**: `includes/workflow/class-workflow-handler.php`

#### Phase 1: Pre-Validation
- `arsol_before_proposal_conversion_validation` - Before any validation
- `arsol_after_proposal_conversion_validated` - After validation passes

#### Phase 2: Proposal Creation
- `arsol_proposal_conversion_args` (FILTER) - Modify proposal arguments
- `arsol_before_proposal_conversion_proposal_creation` - Before proposal creation
- `arsol_after_proposal_conversion_proposal_created` - After proposal created
- `arsol_proposal_conversion_proposal_creation_failed` - On proposal creation failure

#### Phase 3: Metadata Copy
- `arsol_before_proposal_conversion_metadata_copy` - Before metadata copy
- `arsol_proposal_conversion_meta_mapping` (FILTER) - Modify metadata mapping
- `arsol_after_proposal_conversion_metadata_copied` - After metadata copied

#### Phase 4: Cleanup & Success
- `arsol_before_proposal_conversion_request_deletion` - Before request deletion
- `arsol_after_proposal_conversion_complete` - Conversion complete
- `arsol_before_proposal_conversion_redirect` - Before redirect

## Context Data

### Direct Creation Context
```php
// Request/Proposal/Project Creation
$creation_data = array(
    'user_id' => int,
    'creation_method' => 'frontend_form',
    'timestamp' => int,
    'form_data' => array,  // $_POST data
    'request_id|proposal_id|project_id' => int  // Added after creation
);
```

### Conversion Context
```php
// Project Conversion Context
$conversion_data = array(
    'proposal_id' => int,
    'proposal_post' => WP_Post,
    'is_internal_call' => bool,
    'user_id' => int,
    'conversion_method' => 'customer_approval|admin_conversion'
);

// Proposal Conversion Context
$conversion_data = array(
    'request_id' => int,
    'request_post' => WP_Post,
    'user_id' => int,
    'conversion_method' => 'admin_conversion',
    'timestamp' => int,
    'request_status' => string,
    'new_proposal_id' => int  // Added after proposal creation
);
```

## Key Features

### 🔄 **Consistent Architecture**
- All systems follow identical patterns
- Same naming conventions throughout
- Similar hook placement strategies

### 🛡️ **Error Handling**
- Dedicated failure hooks for each creation/conversion type
- Rollback support (project conversion)
- Rich error context data

### 🎯 **Flexibility**
- Filter hooks for arguments and metadata mapping
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

## Usage Examples

### Logging All Activities
```php
// Log direct creations
add_action('arsol_after_request_creation_complete', function($request_id, $creation_data) {
    error_log("Request {$request_id} created by user {$creation_data['user_id']}");
});

add_action('arsol_after_proposal_creation_complete', function($proposal_id, $creation_data) {
    error_log("Proposal {$proposal_id} created by user {$creation_data['user_id']}");
});

add_action('arsol_after_project_creation_complete', function($project_id, $creation_data) {
    error_log("Project {$project_id} created by user {$creation_data['user_id']}");
});

// Log conversions
add_action('arsol_after_proposal_conversion_complete', function($proposal_id, $request_id, $conversion_data) {
    error_log("Proposal {$proposal_id} created from request {$request_id}");
});

add_action('arsol_after_project_conversion_complete', function($project_id, $proposal_id, $conversion_data) {
    error_log("Project {$project_id} created from proposal {$proposal_id}");
});
```

### Custom Validation Across All Workflows
```php
// Validate user limits
add_action('arsol_after_request_creation_validated', function($creation_data) {
    $user_requests = count_user_posts($creation_data['user_id'], 'arsol-pfw-request');
    if ($user_requests >= 10) {
        wc_add_notice('Maximum request limit reached', 'error');
        wp_safe_redirect(wc_get_account_endpoint_url('projects'));
        exit;
    }
});

// Budget validation for all creation types
add_filter('arsol_request_creation_args', function($args, $creation_data) {
    if (isset($creation_data['form_data']['request_budget'])) {
        $budget = floatval($creation_data['form_data']['request_budget']);
        if ($budget > 100000) {
            $args['post_status'] = 'pending'; // Require admin approval for high budgets
        }
    }
    return $args;
}, 10, 2);
```

### Integration with External Systems
```php
// CRM integration for all new items
add_action('arsol_after_request_creation_complete', function($request_id, $creation_data) {
    ExternalCRM::create_lead($request_id, 'request', $creation_data);
});

add_action('arsol_after_proposal_creation_complete', function($proposal_id, $creation_data) {
    ExternalCRM::create_lead($proposal_id, 'proposal', $creation_data);
});

add_action('arsol_after_project_creation_complete', function($project_id, $creation_data) {
    ExternalCRM::create_lead($project_id, 'project', $creation_data);
});

// Project management tool integration
add_action('arsol_after_project_conversion_complete', function($project_id, $proposal_id, $conversion_data) {
    ProjectManagementAPI::create_project($project_id, $conversion_data);
});
```

## Total Hook System

The plugin now provides **60 total hooks** across all workflows:
- **Request Creation**: 11 hooks
- **Proposal Creation**: 11 hooks
- **Project Creation**: 11 hooks
- **Proposal Conversion**: 12 hooks
- **Project Conversion**: 15 hooks

This comprehensive system provides maximum flexibility for developers while maintaining full compatibility with existing functionality and billing infrastructure.