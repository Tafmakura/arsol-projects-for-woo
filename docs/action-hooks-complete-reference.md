# Arsol Projects for Woo - Action Hooks Complete Reference

**Version**: 1.0  
**Date**: 2024  
**Purpose**: Complete documentation of all action hooks in the Arsol Projects for Woo plugin

---

## Table of Contents

1. [Overview](#overview)
2. [Hook Categories](#hook-categories)
3. [Direct Creation Hooks](#direct-creation-hooks)
4. [Conversion Workflow Hooks](#conversion-workflow-hooks)
5. [Status Change Hooks](#status-change-hooks)
6. [WooCommerce Integration Hooks](#woocommerce-integration-hooks)
7. [Admin & UI Hooks](#admin--ui-hooks)
8. [Email System Hooks](#email-system-hooks)
9. [Implementation Examples](#implementation-examples)
10. [Best Practices](#best-practices)

---

## Overview

The Arsol Projects for Woo plugin provides a comprehensive action hook system with **60+ hooks** across all workflows. All hooks follow WooCommerce's before/during/after pattern and provide rich context data for maximum customization flexibility.

### Hook Architecture Principles

- **Consistent Naming**: All hooks follow `arsol_` prefix pattern
- **Rich Context**: Every hook provides relevant data and objects
- **Workflow Coverage**: Hooks at every significant workflow step
- **Error Handling**: Dedicated failure and rollback hooks
- **Integration Ready**: Designed for external system integration

### Total Hook Count

| Category | Action Hooks | Description |
|----------|--------------|-------------|
| **Direct Creation** | 33 | Request/Proposal/Project creation from forms |
| **Conversion Workflows** | 27 | Request→Proposal, Proposal→Project conversions |
| **Status Changes** | 9 | Status updates across all post types |
| **WooCommerce Integration** | 6 | Order/subscription creation and management |
| **Admin & UI** | 8 | Admin interface and user interactions |
| **Email System** | 14 | Email notification triggers |
| **TOTAL** | **97** | Complete hook coverage |

---

## Hook Categories

### 1. Direct Creation Hooks
*33 total hooks*

For when users create requests, proposals, or projects directly through frontend forms.

### 2. Conversion Workflow Hooks  
*27 total hooks*

For admin-initiated conversions between post types (Request→Proposal→Project).

### 3. Status Change Hooks
*9 total hooks*

For status updates on requests, proposals, and projects.

### 4. WooCommerce Integration Hooks
*6 total hooks*

For order and subscription creation, billing setup.

### 5. Admin & UI Hooks
*8 total hooks*

For admin interface actions and user interactions.

### 6. Email System Hooks
*14 total hooks*

For email notification triggers throughout workflows.

---

## Direct Creation Hooks

### Request Creation (11 hooks)
*File: `includes/workflow/class-workflow-handler.php`*

#### Pre-Validation Phase
```php
do_action('arsol_before_request_creation_validation', $creation_data);
do_action('arsol_after_request_creation_validated', $creation_data);
```

#### Post Creation Phase
```php
do_action('arsol_before_request_creation_post_creation', $creation_data);
do_action('arsol_after_request_creation_post_created', $request_id, $creation_data);
do_action('arsol_request_creation_post_creation_failed', $error, $creation_data);
```

#### Status Assignment Phase
```php
do_action('arsol_before_request_creation_status_assignment', $request_id, $creation_data);
do_action('arsol_after_request_creation_status_assigned', $request_id, $status, $creation_data);
```

#### Metadata & Completion Phase
```php
do_action('arsol_before_request_creation_metadata_save', $request_id, $creation_data);
do_action('arsol_after_request_creation_metadata_saved', $request_id, $creation_data);
do_action('arsol_after_request_creation_complete', $request_id, $creation_data);
do_action('arsol_before_request_creation_redirect', $request_id, $creation_data);
```

#### Context Data Structure
```php
$creation_data = array(
    'user_id' => int,
    'creation_method' => 'frontend_form',
    'timestamp' => int,
    'form_data' => array,  // $_POST data
    'request_id' => int    // Added after creation
);
```

### Proposal Creation (11 hooks)
*File: `includes/custom-post-types/project-proposal/class-project-proposal-cpt-frontend-handler.php`*

#### Pre-Validation Phase
```php
do_action('arsol_before_proposal_creation_validation', $creation_data);
do_action('arsol_after_proposal_creation_validated', $creation_data);
```

#### Post Creation Phase
```php
do_action('arsol_before_proposal_creation_post_creation', $creation_data);
do_action('arsol_after_proposal_creation_post_created', $proposal_id, $creation_data);
do_action('arsol_proposal_creation_post_creation_failed', $error, $creation_data);
```

#### Status Assignment Phase
```php
do_action('arsol_before_proposal_creation_status_assignment', $proposal_id, $creation_data);
do_action('arsol_after_proposal_creation_status_assigned', $proposal_id, $status, $creation_data);
```

#### Metadata & Completion Phase
```php
do_action('arsol_before_proposal_creation_metadata_save', $proposal_id, $creation_data);
do_action('arsol_after_proposal_creation_metadata_saved', $proposal_id, $creation_data);
do_action('arsol_after_proposal_creation_complete', $proposal_id, $creation_data);
do_action('arsol_before_proposal_creation_redirect', $proposal_id, $creation_data);
```

### Project Creation (11 hooks)
*File: `includes/custom-post-types/project/class-project-cpt-frontend-handler.php`*

#### Pre-Validation Phase
```php
do_action('arsol_before_project_creation_validation', $creation_data);
do_action('arsol_after_project_creation_validated', $creation_data);
```

#### Post Creation Phase
```php
do_action('arsol_before_project_creation_post_creation', $creation_data);
do_action('arsol_after_project_creation_post_created', $project_id, $creation_data);
do_action('arsol_project_creation_post_creation_failed', $error, $creation_data);
```

#### Status Assignment Phase
```php
do_action('arsol_before_project_creation_status_assignment', $project_id, $creation_data);
do_action('arsol_after_project_creation_status_assigned', $project_id, $status, $creation_data);
```

#### Metadata & Completion Phase
```php
do_action('arsol_before_project_creation_metadata_save', $project_id, $creation_data);
do_action('arsol_after_project_creation_metadata_saved', $project_id, $creation_data);
do_action('arsol_after_project_creation_complete', $project_id, $creation_data);
do_action('arsol_before_project_creation_redirect', $project_id, $creation_data);
```

---

## Conversion Workflow Hooks

### Proposal Conversion (Request → Proposal) - 12 hooks
*File: `includes/workflow/class-workflow-handler.php`*

#### Pre-Validation Phase
```php
do_action('arsol_before_proposal_conversion_validation', $conversion_data);
do_action('arsol_after_proposal_conversion_validated', $conversion_data);
```

#### Proposal Creation Phase
```php
do_action('arsol_before_proposal_conversion_proposal_creation', $conversion_data);
do_action('arsol_after_proposal_conversion_proposal_created', $proposal_id, $request_id, $conversion_data);
do_action('arsol_proposal_conversion_proposal_creation_failed', $error, $conversion_data);
```

#### Metadata Copy Phase
```php
do_action('arsol_before_proposal_conversion_metadata_copy', $proposal_id, $request_id, $conversion_data);
do_action('arsol_after_proposal_conversion_metadata_copied', $proposal_id, $request_id, $conversion_data);
```

#### Cleanup & Success Phase
```php
do_action('arsol_before_proposal_conversion_request_deletion', $proposal_id, $request_id, $conversion_data);
do_action('arsol_after_proposal_conversion_complete', $proposal_id, $request_id, $conversion_data);
do_action('arsol_before_proposal_conversion_redirect', $proposal_id, $request_id, $conversion_data);
```

#### Context Data Structure
```php
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

### Project Conversion (Proposal → Project) - 15 hooks
*File: `includes/workflow/class-workflow-handler.php`*

#### Pre-Validation Phase
```php
do_action('arsol_before_project_conversion_validation', $conversion_data);
do_action('arsol_after_project_conversion_validated', $conversion_data);
```

#### Project Creation Phase
```php
do_action('arsol_before_project_conversion_project_creation', $conversion_data);
do_action('arsol_after_project_conversion_project_created', $project_id, $proposal_id, $conversion_data);
do_action('arsol_project_conversion_project_creation_failed', $error, $conversion_data);
```

#### Metadata Copy Phase
```php
do_action('arsol_before_project_conversion_metadata_copy', $project_id, $proposal_id, $conversion_data);
do_action('arsol_after_project_conversion_metadata_copied', $project_id, $proposal_id, $conversion_data);
```

#### Order Creation Phase
```php
do_action('arsol_before_project_conversion_order_creation', $project_id, $proposal_id, $conversion_data);
do_action('arsol_after_project_conversion_order_creation_attempt', $project_id, $order_result, $conversion_data);
```

#### Success/Rollback Phase
```php
do_action('arsol_before_project_conversion_rollback', $project_id, $proposal_id, $conversion_data);
do_action('arsol_after_project_conversion_rollback', $project_id, $proposal_id, $conversion_data);
do_action('arsol_before_project_conversion_proposal_deletion', $project_id, $proposal_id, $conversion_data);
do_action('arsol_after_project_conversion_complete', $project_id, $proposal_id, $conversion_data);
do_action('arsol_before_project_conversion_redirect', $project_id, $proposal_id, $conversion_data);
```

#### Context Data Structure
```php
$conversion_data = array(
    'proposal_id' => int,
    'proposal_post' => WP_Post,
    'is_internal_call' => bool,
    'user_id' => int,
    'conversion_method' => 'customer_approval|admin_conversion'
);
```

---

## Status Change Hooks

### Request Status Changes (3 hooks)

```php
// Before status change
do_action('arsol_before_request_status_change', $request_id, $old_status, $new_status, $user_id);

// Status changed
do_action('arsol_request_status_changed', $request_id, $old_status, $new_status, $user_id);

// After status change processing
do_action('arsol_after_request_status_change', $request_id, $old_status, $new_status, $user_id);
```

**Possible Status Values:**
- `pending-review` → `under-review` → `on-hold` → `approved`

### Proposal Status Changes (3 hooks)

```php
// Before status change
do_action('arsol_before_proposal_status_change', $proposal_id, $old_status, $new_status, $user_id);

// Status changed
do_action('arsol_proposal_status_changed', $proposal_id, $old_status, $new_status, $user_id);

// After status change processing
do_action('arsol_after_proposal_status_change', $proposal_id, $old_status, $new_status, $user_id);
```

**Possible Status Values:**
- `processing` → `pending-approval` → `approved` | `rejected`

### Project Status Changes (3 hooks)

```php
// Before status change
do_action('arsol_before_project_status_change', $project_id, $old_status, $new_status, $project_lead_id);

// Status changed
do_action('arsol_project_status_changed', $project_id, $old_status, $new_status, $project_lead_id);

// After status change processing
do_action('arsol_after_project_status_change', $project_id, $old_status, $new_status, $project_lead_id);
```

**Possible Status Values:**
- `not-started` → `in-progress` → `on-hold` → `completed` → `cancelled`

---

## WooCommerce Integration Hooks

### Order Creation Hooks (3 hooks)

```php
// Before order creation attempt
do_action('arsol_before_order_creation', $project_id, $order_data);

// Order creation success
do_action('arsol_order_created_successfully', $project_id, $order_id, $order_data);

// Order creation failure
do_action('arsol_order_creation_failed', $project_id, $error_message, $order_data);
```

### Subscription Management Hooks (3 hooks)

```php
// Before subscription creation
do_action('arsol_before_subscription_creation', $project_id, $subscription_data);

// Subscription created
do_action('arsol_subscription_created', $project_id, $subscription_id, $subscription_data);

// Subscription creation failed
do_action('arsol_subscription_creation_failed', $project_id, $error_message, $subscription_data);
```

---

## Admin & UI Hooks

### Admin Interface Hooks (4 hooks)

```php
// After admin settings save
do_action('arsol_admin_settings_saved', $settings_data);

// Before admin bulk actions
do_action('arsol_before_admin_bulk_action', $action, $post_ids);

// After admin bulk actions
do_action('arsol_after_admin_bulk_action', $action, $post_ids, $results);

// Admin notification display
do_action('arsol_admin_notice_displayed', $notice_type, $message);
```

### User Interaction Hooks (4 hooks)

```php
// User dashboard access
do_action('arsol_user_dashboard_accessed', $user_id, $dashboard_section);

// User form submission (before validation)
do_action('arsol_user_form_submitted', $form_type, $form_data, $user_id);

// User file upload
do_action('arsol_user_file_uploaded', $file_id, $post_id, $user_id);

// User account endpoint access
do_action('arsol_user_endpoint_accessed', $endpoint, $user_id, $parameters);
```

---

## Email System Hooks

### Primary Email Triggers (8 hooks)

```php
// New content created
do_action('arsol_new_request_created', $request_id, $customer_id);
do_action('arsol_new_proposal_created', $proposal_id, $customer_id, $project_lead_id);
do_action('arsol_new_project_created', $project_id, $proposal_id, $customer_id, $project_lead_id);

// Status changes (trigger emails)
do_action('arsol_request_status_changed', $request_id, $old_status, $new_status);
do_action('arsol_proposal_status_changed', $proposal_id, $old_status, $new_status);
do_action('arsol_project_status_changed', $project_id, $old_status, $new_status, $project_lead_id);

// Special workflow notifications
do_action('arsol_proposal_processing_started', $proposal_id, $customer_id, $project_lead_id);
do_action('arsol_proposal_approved_project_created', $project_id, $proposal_id, $customer_id, $project_lead_id);
```

### Email System Management (6 hooks)

```php
// Email system initialization
do_action('arsol_email_system_initialized');

// Before email sending
do_action('arsol_before_email_send', $email_type, $recipient, $data);

// Email sent successfully
do_action('arsol_email_sent_successfully', $email_type, $recipient, $data);

// Email sending failed
do_action('arsol_email_send_failed', $email_type, $recipient, $error, $data);

// Email template rendered
do_action('arsol_email_template_rendered', $template_name, $data);

// Email preferences updated
do_action('arsol_email_preferences_updated', $user_id, $preferences);
```

---

## Implementation Examples

### Complete Activity Logging

```php
// Log all major actions across workflows
add_action('arsol_after_request_creation_complete', function($request_id, $creation_data) {
    arsol_log_activity('request_created', $request_id, $creation_data['user_id']);
});

add_action('arsol_after_proposal_creation_complete', function($proposal_id, $creation_data) {
    arsol_log_activity('proposal_created', $proposal_id, $creation_data['user_id']);
});

add_action('arsol_after_project_creation_complete', function($project_id, $creation_data) {
    arsol_log_activity('project_created', $project_id, $creation_data['user_id']);
});

add_action('arsol_after_proposal_conversion_complete', function($proposal_id, $request_id, $conversion_data) {
    arsol_log_activity('proposal_converted_from_request', $proposal_id, $conversion_data['user_id'], array('source_request' => $request_id));
});

add_action('arsol_after_project_conversion_complete', function($project_id, $proposal_id, $conversion_data) {
    arsol_log_activity('project_converted_from_proposal', $project_id, $conversion_data['user_id'], array('source_proposal' => $proposal_id));
});
```

### External CRM Integration

```php
// Sync with external CRM on all major events
add_action('arsol_after_request_creation_complete', function($request_id, $creation_data) {
    ExternalCRM::create_lead($request_id, 'request', array(
        'customer_id' => $creation_data['user_id'],
        'source' => 'website_form',
        'status' => 'new_request'
    ));
});

add_action('arsol_request_status_changed', function($request_id, $old_status, $new_status) {
    ExternalCRM::update_lead_status($request_id, $new_status);
});

add_action('arsol_after_project_conversion_complete', function($project_id, $proposal_id, $conversion_data) {
    ExternalCRM::convert_lead_to_customer($project_id, array(
        'project_value' => get_project_total_value($project_id),
        'project_lead' => get_post_meta($project_id, '_arsol_pfw_project_lead', true)
    ));
});
```

### Advanced Business Logic

```php
// Automatic project lead assignment based on budget
add_action('arsol_after_proposal_creation_post_created', function($proposal_id, $creation_data) {
    $budget = get_post_meta($proposal_id, '_arsol_pfw_proposal_budget_onetime_amount', true);
    
    if (is_array($budget) && $budget['amount'] > 10000) {
        // High-value projects get senior leads
        $senior_leads = get_users(array('role' => 'senior_project_lead'));
        if (!empty($senior_leads)) {
            update_post_meta($proposal_id, '_arsol_pfw_proposal_project_lead', $senior_leads[0]->ID);
        }
    }
});

// Automatic approval for small budget requests
add_action('arsol_after_request_creation_complete', function($request_id, $creation_data) {
    $budget = get_post_meta($request_id, '_arsol_pfw_request_budget', true);
    
    if (is_array($budget) && $budget['amount'] <= 1000) {
        // Auto-approve small requests
        wp_update_post(array(
            'ID' => $request_id,
            'post_status' => 'approved'
        ));
        
        // Trigger auto-conversion to proposal
        do_action('arsol_auto_convert_request_to_proposal', $request_id);
    }
});
```

### Performance Monitoring

```php
// Track workflow performance
add_action('arsol_before_proposal_conversion_validation', function($conversion_data) {
    update_option('arsol_conversion_start_time', microtime(true));
});

add_action('arsol_after_proposal_conversion_complete', function($proposal_id, $request_id, $conversion_data) {
    $start_time = get_option('arsol_conversion_start_time');
    $duration = microtime(true) - $start_time;
    
    arsol_log_performance('proposal_conversion', $duration, array(
        'proposal_id' => $proposal_id,
        'request_id' => $request_id
    ));
});
```

### Email Customization

```php
// Custom email notifications for VIP customers
add_action('arsol_proposal_processing_started', function($proposal_id, $customer_id, $project_lead_id) {
    $customer = get_userdata($customer_id);
    
    if (in_array('vip_customer', $customer->roles)) {
        // Send special VIP notification
        arsol_send_vip_notification($proposal_id, $customer_id);
        
        // Notify management
        arsol_send_management_alert('VIP customer proposal processing started', array(
            'customer' => $customer->display_name,
            'proposal_id' => $proposal_id
        ));
    }
});
```

---

## Best Practices

### Hook Usage Guidelines

1. **Use Appropriate Hooks**: Choose the most specific hook for your needs
2. **Check Context**: Always validate the data passed to your hook functions
3. **Handle Errors**: Implement proper error handling in hook callbacks
4. **Performance**: Avoid heavy operations in frequently-fired hooks
5. **Priority**: Use appropriate priority values to control execution order

### Example Best Practices

```php
// ✅ Good: Specific hook usage with error handling
add_action('arsol_after_project_creation_complete', function($project_id, $creation_data) {
    try {
        if (empty($project_id) || empty($creation_data)) {
            error_log('Invalid data passed to project creation hook');
            return;
        }
        
        // Safe processing here
        external_system_notify($project_id, $creation_data);
        
    } catch (Exception $e) {
        error_log('Project creation hook error: ' . $e->getMessage());
    }
}, 10, 2);

// ❌ Avoid: Heavy operations in frequently-fired hooks
add_action('arsol_before_request_creation_validation', function($creation_data) {
    // Don't do this - heavy API call on every validation
    $external_data = expensive_api_call($creation_data);
});

// ✅ Better: Use completion hooks for heavy operations
add_action('arsol_after_request_creation_complete', function($request_id, $creation_data) {
    // Better - only runs after successful creation
    wp_schedule_single_event(time() + 10, 'arsol_process_external_sync', array($request_id));
});
```

### Hook Priority Examples

```php
// High priority (runs early)
add_action('arsol_after_request_creation_complete', 'critical_security_check', 5);

// Normal priority (default)
add_action('arsol_after_request_creation_complete', 'standard_processing', 10);

// Low priority (runs late)
add_action('arsol_after_request_creation_complete', 'cleanup_operations', 20);
```

This comprehensive action hook system provides maximum flexibility for developers while maintaining consistent patterns and rich context data throughout all plugin workflows. 