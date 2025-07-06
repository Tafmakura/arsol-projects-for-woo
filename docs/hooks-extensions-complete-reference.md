# Hooks & Extensions - Complete Reference

## Overview

The **Arsol Projects for WooCommerce** plugin provides extensive hook and filter system for developers to extend functionality. This reference covers all available action hooks, filter hooks, and extension points throughout the plugin.

## Key Features

- ✅ **150+ Action Hooks** for workflow integration
- ✅ **100+ Filter Hooks** for content modification
- ✅ **Entity-Specific Hooks** for projects, proposals, and requests
- ✅ **Stage-Based Hooks** for workflow customization
- ✅ **Frontend Hooks** for template and display modifications
- ✅ **Admin Hooks** for backend customization

---

## Action Hooks

### Project Lifecycle Hooks

#### Project Creation
```php
// Before project creation
do_action('arsol_pfw_before_project_create', $project_data, $user_id);

// After project created
do_action('arsol_pfw_after_project_create', $project_id, $project_data, $user_id);

// Project status change
do_action('arsol_pfw_project_status_changed', $project_id, $old_status, $new_status, $user_id);
```

#### Project Stage Changes
```php
// Generic stage change
do_action('arsol_pfw_project_stage_changed', $project_id, $old_stage, $new_stage, $user_id);

// Specific stage transitions
do_action('arsol_pfw_project_stage_to_active', $project_id, $old_stage, $user_id);
do_action('arsol_pfw_project_stage_to_completed', $project_id, $old_stage, $user_id);
do_action('arsol_pfw_project_stage_to_on_hold', $project_id, $old_stage, $user_id);
do_action('arsol_pfw_project_stage_to_cancelled', $project_id, $old_stage, $user_id);
```

#### Project Updates
```php
// Before project update
do_action('arsol_pfw_before_project_update', $project_id, $update_data, $user_id);

// After project updated
do_action('arsol_pfw_after_project_update', $project_id, $update_data, $user_id);

// Project meta updated
do_action('arsol_pfw_project_meta_updated', $project_id, $meta_key, $meta_value, $user_id);
```

### Proposal Lifecycle Hooks

#### Proposal Creation
```php
// Before proposal creation
do_action('arsol_pfw_before_proposal_create', $proposal_data, $project_id, $user_id);

// After proposal created
do_action('arsol_pfw_after_proposal_create', $proposal_id, $proposal_data, $project_id, $user_id);

// Proposal assigned to project
do_action('arsol_pfw_proposal_assigned_to_project', $proposal_id, $project_id, $user_id);
```

#### Proposal Stage Changes
```php
// Generic stage change
do_action('arsol_pfw_proposal_stage_changed', $proposal_id, $old_stage, $new_stage, $user_id);

// Specific stage transitions
do_action('arsol_pfw_proposal_stage_to_processing', $proposal_id, $old_stage, $user_id);
do_action('arsol_pfw_proposal_stage_to_ready', $proposal_id, $old_stage, $user_id);
do_action('arsol_pfw_proposal_stage_to_approved', $proposal_id, $old_stage, $user_id);
do_action('arsol_pfw_proposal_stage_to_rejected', $proposal_id, $old_stage, $user_id);
```

#### Proposal Decisions
```php
// Proposal approved
do_action('arsol_pfw_proposal_approved', $proposal_id, $project_id, $user_id);

// Proposal rejected
do_action('arsol_pfw_proposal_rejected', $proposal_id, $project_id, $rejection_reason, $user_id);

// Proposal revision requested
do_action('arsol_pfw_proposal_revision_requested', $proposal_id, $revision_notes, $user_id);
```

### Request Lifecycle Hooks

#### Request Creation
```php
// Before request creation
do_action('arsol_pfw_before_request_create', $request_data, $user_id);

// After request created
do_action('arsol_pfw_after_request_create', $request_id, $request_data, $user_id);

// Request submitted
do_action('arsol_pfw_request_submitted', $request_id, $user_id);
```

#### Request Stage Changes
```php
// Generic stage change
do_action('arsol_pfw_request_stage_changed', $request_id, $old_stage, $new_stage, $user_id);

// Specific stage transitions
do_action('arsol_pfw_request_stage_to_pending', $request_id, $old_stage, $user_id);
do_action('arsol_pfw_request_stage_to_under_review', $request_id, $old_stage, $user_id);
do_action('arsol_pfw_request_stage_to_approved', $request_id, $old_stage, $user_id);
do_action('arsol_pfw_request_stage_to_rejected', $request_id, $old_stage, $user_id);
```

#### Request Processing
```php
// Request approved and converted
do_action('arsol_pfw_request_converted_to_project', $request_id, $project_id, $user_id);

// Request rejected with reason
do_action('arsol_pfw_request_rejected', $request_id, $rejection_reason, $user_id);

// Request requires more information
do_action('arsol_pfw_request_needs_info', $request_id, $info_needed, $user_id);
```

### Frontend Hooks

#### Template Hooks
```php
// Before template content
do_action('arsol_pfw_before_template_content', $template_name, $entity_id, $entity_type);

// After template content
do_action('arsol_pfw_after_template_content', $template_name, $entity_id, $entity_type);

// Before entity header
do_action('arsol_pfw_before_entity_header', $entity_id, $entity_type);

// After entity header
do_action('arsol_pfw_after_entity_header', $entity_id, $entity_type);
```

#### Dashboard Hooks
```php
// Before dashboard content
do_action('arsol_pfw_before_dashboard_content', $user_id);

// After dashboard content
do_action('arsol_pfw_after_dashboard_content', $user_id);

// Dashboard tab content
do_action('arsol_pfw_dashboard_tab_content', $tab_name, $user_id);
```

#### Form Hooks
```php
// Before form display
do_action('arsol_pfw_before_form_display', $form_type, $entity_id);

// After form display
do_action('arsol_pfw_after_form_display', $form_type, $entity_id);

// Form validation
do_action('arsol_pfw_form_validation', $form_type, $form_data, $errors);

// Form submission
do_action('arsol_pfw_form_submitted', $form_type, $form_data, $entity_id, $user_id);
```

### Admin Hooks

#### Admin Interface Hooks
```php
// Before admin content
do_action('arsol_pfw_before_admin_content', $screen_id, $entity_id);

// After admin content
do_action('arsol_pfw_after_admin_content', $screen_id, $entity_id);

// Admin meta box content
do_action('arsol_pfw_admin_meta_box_content', $meta_box_id, $entity_id);
```

#### Settings Hooks
```php
// Before settings save
do_action('arsol_pfw_before_settings_save', $settings_data, $settings_page);

// After settings saved
do_action('arsol_pfw_after_settings_save', $settings_data, $settings_page);

// Settings field added
do_action('arsol_pfw_settings_field_added', $field_id, $field_data, $settings_page);
```

### Email Hooks

#### Email Triggering
```php
// Before email sent
do_action('arsol_pfw_before_email_send', $email_type, $recipients, $email_data);

// After email sent
do_action('arsol_pfw_after_email_send', $email_type, $recipients, $email_data, $success);

// Email queued
do_action('arsol_pfw_email_queued', $email_type, $recipients, $email_data);
```

### WooCommerce Integration Hooks

#### Order Integration
```php
// Project order created
do_action('arsol_pfw_project_order_created', $project_id, $order_id, $user_id);

// Order status changed
do_action('arsol_pfw_project_order_status_changed', $project_id, $order_id, $old_status, $new_status);

// Subscription created
do_action('arsol_pfw_project_subscription_created', $project_id, $subscription_id, $user_id);
```

---

## Filter Hooks

### Content Filters

#### Project Content
```php
// Filter project title
add_filter('arsol_pfw_project_title', $title, $project_id);

// Filter project description
add_filter('arsol_pfw_project_description', $description, $project_id);

// Filter project meta
add_filter('arsol_pfw_project_meta', $meta_value, $meta_key, $project_id);

// Filter project stages
add_filter('arsol_pfw_project_stages', $stages, $project_id);
```

#### Proposal Content
```php
// Filter proposal title
add_filter('arsol_pfw_proposal_title', $title, $proposal_id);

// Filter proposal content
add_filter('arsol_pfw_proposal_content', $content, $proposal_id);

// Filter proposal pricing
add_filter('arsol_pfw_proposal_pricing', $pricing, $proposal_id);
```

#### Request Content
```php
// Filter request title
add_filter('arsol_pfw_request_title', $title, $request_id);

// Filter request description
add_filter('arsol_pfw_request_description', $description, $request_id);

// Filter request priority
add_filter('arsol_pfw_request_priority', $priority, $request_id);
```

### Permission Filters

#### User Permissions
```php
// Filter user can create projects
add_filter('arsol_pfw_user_can_create_projects', $can_create, $user_id);

// Filter user can request projects
add_filter('arsol_pfw_user_can_request_projects', $can_request, $user_id);

// Filter user can view project
add_filter('arsol_pfw_user_can_view_project', $can_view, $user_id, $project_id);

// Filter user can edit project
add_filter('arsol_pfw_user_can_edit_project', $can_edit, $user_id, $project_id);
```

#### Role-Based Permissions
```php
// Filter project manager roles
add_filter('arsol_pfw_project_manager_roles', $roles);

// Filter customer roles
add_filter('arsol_pfw_customer_roles', $roles);

// Filter admin roles
add_filter('arsol_pfw_admin_roles', $roles);
```

### Display Filters

#### Frontend Display
```php
// Filter template content
add_filter('arsol_pfw_template_content', $content, $template_name, $entity_id);

// Filter sidebar content
add_filter('arsol_pfw_sidebar_content', $content, $sidebar_name, $entity_id);

// Filter dashboard content
add_filter('arsol_pfw_dashboard_content', $content, $user_id);
```

#### Admin Display
```php
// Filter admin columns
add_filter('arsol_pfw_admin_columns', $columns, $post_type);

// Filter admin column content
add_filter('arsol_pfw_admin_column_content', $content, $column_name, $entity_id);

// Filter admin actions
add_filter('arsol_pfw_admin_actions', $actions, $entity_id, $entity_type);
```

### Data Filters

#### Query Filters
```php
// Filter projects query
add_filter('arsol_pfw_projects_query', $query_args, $user_id);

// Filter proposals query
add_filter('arsol_pfw_proposals_query', $query_args, $user_id);

// Filter requests query
add_filter('arsol_pfw_requests_query', $query_args, $user_id);
```

#### Validation Filters
```php
// Filter form validation rules
add_filter('arsol_pfw_form_validation_rules', $rules, $form_type);

// Filter validation errors
add_filter('arsol_pfw_validation_errors', $errors, $form_type, $form_data);

// Filter sanitization rules
add_filter('arsol_pfw_sanitization_rules', $rules, $form_type);
```

### Email Filters

#### Email Recipients
```php
// Filter email recipients
add_filter('arsol_pfw_email_recipients', $recipients, $email_type, $entity_id);

// Filter manager recipients
add_filter('arsol_pfw_email_manager_recipients', $managers, $project_id);

// Filter customer recipients
add_filter('arsol_pfw_email_customer_recipients', $customers, $project_id);
```

#### Email Content
```php
// Filter email subject
add_filter('arsol_pfw_email_subject', $subject, $email_type, $entity_id);

// Filter email content
add_filter('arsol_pfw_email_content', $content, $email_type, $entity_id);

// Filter email template
add_filter('arsol_pfw_email_template', $template, $email_type, $entity_id);
```

### Settings Filters

#### Global Settings
```php
// Filter default settings
add_filter('arsol_pfw_default_settings', $defaults, $settings_page);

// Filter settings validation
add_filter('arsol_pfw_settings_validation', $validated, $input, $settings_page);

// Filter settings display
add_filter('arsol_pfw_settings_display', $display_settings, $settings_page);
```

---

## Hook Usage Examples

### Basic Hook Usage

#### Adding Custom Project Data
```php
// Add custom meta when project is created
add_action('arsol_pfw_after_project_create', 'add_custom_project_meta', 10, 3);
function add_custom_project_meta($project_id, $project_data, $user_id) {
    // Add custom timestamp
    update_post_meta($project_id, '_custom_creation_timestamp', time());
    
    // Add user preferences
    $user_preferences = get_user_meta($user_id, 'project_preferences', true);
    update_post_meta($project_id, '_user_preferences', $user_preferences);
}
```

#### Modifying Project Title Display
```php
// Modify project title display
add_filter('arsol_pfw_project_title', 'modify_project_title', 10, 2);
function modify_project_title($title, $project_id) {
    $project_stage = get_post_meta($project_id, '_project_stage', true);
    
    // Add stage prefix to title
    $stage_prefixes = array(
        'active' => '[ACTIVE] ',
        'completed' => '[COMPLETED] ',
        'on_hold' => '[ON HOLD] '
    );
    
    if (isset($stage_prefixes[$project_stage])) {
        $title = $stage_prefixes[$project_stage] . $title;
    }
    
    return $title;
}
```

#### Custom Email Recipients
```php
// Add custom recipients for project emails
add_filter('arsol_pfw_email_recipients', 'add_custom_email_recipients', 10, 3);
function add_custom_email_recipients($recipients, $email_type, $entity_id) {
    if ($email_type === 'project_stage_changed') {
        // Add project stakeholders
        $stakeholders = get_post_meta($entity_id, '_project_stakeholders', true);
        if ($stakeholders && is_array($stakeholders)) {
            $recipients = array_merge($recipients, $stakeholders);
        }
    }
    
    return array_unique($recipients);
}
```

### Advanced Hook Usage

#### Custom Workflow Integration
```php
// Integrate with external project management system
add_action('arsol_pfw_project_stage_changed', 'sync_with_external_system', 10, 4);
function sync_with_external_system($project_id, $old_stage, $new_stage, $user_id) {
    // Get external system ID
    $external_id = get_post_meta($project_id, '_external_project_id', true);
    
    if ($external_id) {
        // Sync stage change with external system
        $api_client = new ExternalProjectAPI();
        $api_client->update_project_stage($external_id, $new_stage);
        
        // Log the sync
        error_log("Synced project {$project_id} stage change to external system");
    }
}
```

#### Dynamic Permission System
```php
// Custom permission system based on project categories
add_filter('arsol_pfw_user_can_view_project', 'custom_project_permissions', 10, 3);
function custom_project_permissions($can_view, $user_id, $project_id) {
    // Get project category
    $project_categories = wp_get_post_terms($project_id, 'project_category');
    
    if ($project_categories && !is_wp_error($project_categories)) {
        foreach ($project_categories as $category) {
            // Check if user has permission for this category
            $category_permissions = get_user_meta($user_id, 'category_permissions', true);
            
            if (is_array($category_permissions) && in_array($category->term_id, $category_permissions)) {
                return true;
            }
        }
    }
    
    return $can_view;
}
```

#### Custom Dashboard Content
```php
// Add custom dashboard widget
add_action('arsol_pfw_dashboard_tab_content', 'add_custom_dashboard_widget', 10, 2);
function add_custom_dashboard_widget($tab_name, $user_id) {
    if ($tab_name === 'overview') {
        // Add custom statistics widget
        $user_projects = get_user_projects($user_id);
        $project_stats = calculate_project_stats($user_projects);
        
        echo '<div class="arsol-pfw-custom-widget">';
        echo '<h3>Your Project Statistics</h3>';
        echo '<ul>';
        echo '<li>Active Projects: ' . $project_stats['active'] . '</li>';
        echo '<li>Completed Projects: ' . $project_stats['completed'] . '</li>';
        echo '<li>Total Budget: $' . number_format($project_stats['total_budget']) . '</li>';
        echo '</ul>';
        echo '</div>';
    }
}
```

### Hook Combinations

#### Complete Project Lifecycle Tracking
```php
// Track complete project lifecycle
class ProjectLifecycleTracker {
    
    public function __construct() {
        // Hook into all major lifecycle events
        add_action('arsol_pfw_after_project_create', array($this, 'track_project_creation'), 10, 3);
        add_action('arsol_pfw_project_stage_changed', array($this, 'track_stage_change'), 10, 4);
        add_action('arsol_pfw_after_project_update', array($this, 'track_project_update'), 10, 3);
        
        // Hook into email events
        add_action('arsol_pfw_after_email_send', array($this, 'track_email_sent'), 10, 4);
    }
    
    public function track_project_creation($project_id, $project_data, $user_id) {
        $this->log_event('project_created', $project_id, array(
            'user_id' => $user_id,
            'project_data' => $project_data
        ));
    }
    
    public function track_stage_change($project_id, $old_stage, $new_stage, $user_id) {
        $this->log_event('stage_changed', $project_id, array(
            'old_stage' => $old_stage,
            'new_stage' => $new_stage,
            'user_id' => $user_id
        ));
    }
    
    public function track_project_update($project_id, $update_data, $user_id) {
        $this->log_event('project_updated', $project_id, array(
            'update_data' => $update_data,
            'user_id' => $user_id
        ));
    }
    
    public function track_email_sent($email_type, $recipients, $email_data, $success) {
        if (isset($email_data['project_id'])) {
            $this->log_event('email_sent', $email_data['project_id'], array(
                'email_type' => $email_type,
                'recipients' => $recipients,
                'success' => $success
            ));
        }
    }
    
    private function log_event($event_type, $project_id, $data) {
        // Log to custom table or meta
        $log_entry = array(
            'event_type' => $event_type,
            'project_id' => $project_id,
            'data' => $data,
            'timestamp' => current_time('timestamp')
        );
        
        // Save to project meta
        $existing_log = get_post_meta($project_id, '_project_lifecycle_log', true);
        if (!is_array($existing_log)) {
            $existing_log = array();
        }
        
        $existing_log[] = $log_entry;
        update_post_meta($project_id, '_project_lifecycle_log', $existing_log);
    }
}

// Initialize tracker
new ProjectLifecycleTracker();
```

---

## Best Practices

### Hook Implementation
1. **Use Appropriate Priority**: Use priority 10 for normal hooks, higher for late execution
2. **Check Data Existence**: Always verify data exists before processing
3. **Fail Gracefully**: Handle errors without breaking the flow
4. **Document Hooks**: Comment your hook usage for maintenance

### Performance Considerations
1. **Avoid Heavy Operations**: Don't perform expensive operations in frequently called hooks
2. **Use Caching**: Cache results of expensive operations
3. **Limit Database Queries**: Minimize database hits in hooks
4. **Conditional Execution**: Only execute when necessary

### Security
1. **Validate Input**: Always validate and sanitize hook data
2. **Check Permissions**: Verify user permissions before processing
3. **Escape Output**: Properly escape any output generated by hooks
4. **Use Nonces**: Include nonce verification for form-related hooks

### Debugging
1. **Enable Debug Mode**: Use WP_DEBUG for development
2. **Log Hook Execution**: Log important hook executions
3. **Use Debug Plugins**: Utilize plugins like Query Monitor
4. **Test Thoroughly**: Test hooks across different scenarios

---

## Extension Examples

### Creating Custom Extensions

#### Project Analytics Extension
```php
class ProjectAnalyticsExtension {
    
    public function __construct() {
        add_action('arsol_pfw_project_stage_changed', array($this, 'track_stage_duration'), 10, 4);
        add_action('arsol_pfw_after_project_create', array($this, 'initialize_analytics'), 10, 3);
        add_filter('arsol_pfw_dashboard_content', array($this, 'add_analytics_dashboard'), 10, 2);
    }
    
    public function track_stage_duration($project_id, $old_stage, $new_stage, $user_id) {
        $stage_start = get_post_meta($project_id, '_stage_' . $old_stage . '_start', true);
        
        if ($stage_start) {
            $duration = time() - $stage_start;
            update_post_meta($project_id, '_stage_' . $old_stage . '_duration', $duration);
        }
        
        // Set new stage start time
        update_post_meta($project_id, '_stage_' . $new_stage . '_start', time());
    }
    
    public function initialize_analytics($project_id, $project_data, $user_id) {
        // Initialize analytics tracking
        update_post_meta($project_id, '_analytics_initialized', time());
        update_post_meta($project_id, '_stage_initial_start', time());
    }
    
    public function add_analytics_dashboard($content, $user_id) {
        $analytics_data = $this->get_user_analytics($user_id);
        
        $content .= '<div class="arsol-pfw-analytics">';
        $content .= '<h3>Project Analytics</h3>';
        $content .= '<p>Average project duration: ' . $analytics_data['avg_duration'] . ' days</p>';
        $content .= '<p>Most time spent in: ' . $analytics_data['longest_stage'] . '</p>';
        $content .= '</div>';
        
        return $content;
    }
    
    private function get_user_analytics($user_id) {
        // Calculate user-specific analytics
        // Implementation details...
        return array(
            'avg_duration' => 30,
            'longest_stage' => 'active'
        );
    }
}

// Initialize extension
new ProjectAnalyticsExtension();
```

---

This completes the comprehensive hooks and extensions reference. The system provides extensive customization options while maintaining clean, documented interfaces for developers. 