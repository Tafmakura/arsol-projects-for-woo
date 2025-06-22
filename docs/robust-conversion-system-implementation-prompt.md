# WordPress Plugin Simple Conversion System with Rollback

## Overview

Implement a simple but robust conversion system for a WordPress/WooCommerce plugin that safely converts between project types with automatic rollback and basic user feedback.

## Problem Statement

The current conversion system has critical flaws:
- Uses `wp_die()` which kills script execution before rollback can occur
- Tracks created entities in memory (lost on PHP crashes)
- No user feedback after redirects
- Vulnerable to partial failures leaving orphaned data

## Simple Solution

Use the **source post's metadata** as a persistent transaction log. If anything fails, use this log to clean up all created entities.

### Core Concept
```
Request → Proposal → Project
   ↓         ↓         ↓
Record    Record    Record
entities  entities  entities
   ↓         ↓         ↓
Success   Success   Success
   OR        OR        OR
Rollback  Rollback  Rollback
```

## Custom Post Types Flow
```
Request (arsol-pfw-request) 
    → Proposal (arsol-pfw-proposal) 
        → Project (arsol-project)
```

## Implementation Requirements

### Classes That Need Changes

#### 1. `includes/workflow/class-workflow-handler.php` (MAJOR CHANGES)
**Existing Class**: Contains current conversion methods
**Required Changes**: Add complete transaction system with logging

#### 2. `includes/classes/class-admin-settings-advanced.php` (MINOR CHANGES)
**Existing Class**: Advanced settings page  
**Required Changes**: Add cleanup button

#### 3. `includes/classes/class-setup.php` (MINOR CHANGES)
**Existing Class**: Plugin setup
**Required Changes**: Add cron job scheduling

#### 4. `includes/classes/class-woocommerce-logs.php` (INTEGRATION)
**Existing Class**: Logging system
**Required Changes**: Use existing log_workflow() method for conversion tracking

### Simple Transaction System

#### Core Principle
Store created entity IDs in the source post metadata. If conversion fails, delete all recorded entities.

#### Complete Meta Keys System
```php
// Workflow Level
'_arsol_workflow_status'           // 'in_progress', 'completed', 'failed'
'_arsol_workflow_type'             // 'conversion'
'_arsol_workflow_started'          // timestamp

// Conversion Level  
'_arsol_conversion_type'           // 'request_to_proposal', 'proposal_to_project'
'_arsol_conversion_step'           // 'validation', 'creation', 'metadata_copy'

// Transaction Level
'_arsol_conversion_created_ids'    // Array of all created post IDs
'_arsol_conversion_rollback_reason' // Error message for failed conversions
```

#### Basic Admin Notice System
```php
// Simple transient notice system
'arsol_notice_{user_id}'           // Single notice per user
```

### Simple Implementation

#### A. Complete Transaction Methods (Add to Workflow_Handler)
```php
// Core workflow methods:
private function start_workflow_transaction($source_id, $workflow_type, $conversion_type)
private function record_transaction_entity($source_id, $entity_id)  
private function complete_workflow_transaction($source_id)
private function rollback_workflow_transaction($source_id, $reason)

// Rollback methods:
private function rollback_request_to_proposal($source_id)
private function rollback_proposal_to_project($source_id)

// Utility methods:
private function is_workflow_in_progress($source_id)
public static function cleanup_stuck_workflows($max_age_minutes = 30)
```

#### B. Enhanced Notice System (built into Workflow_Handler)
```php
// Notice management:
private function set_conversion_success_notice($from_type, $to_type, $from_id, $to_id, $title)
private function set_conversion_failure_notice($from_type, $to_type, $from_id, $title, $error)
private function set_admin_notice($type, $message, $details = array())
```

#### C. Complete Conversion Pattern with Logging
```php
public function convert_request_to_proposal() {
    $request_id = intval($_GET['request_id']);
    $request_post = null;
    
    try {
        // Get source details
        $request_post = get_post($request_id);
        if (!$request_post || $request_post->post_type !== 'arsol-pfw-request') {
            throw new Exception(__('Invalid request.', 'arsol-pfw'));
        }
        
        // Prevent concurrent conversions
        if ($this->is_workflow_in_progress($request_id)) {
            throw new Exception(__('Conversion already in progress.', 'arsol-pfw'));
        }
        
        // Start transaction with logging
        $this->start_workflow_transaction($request_id, 'conversion', 'request_to_proposal');
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
            "Starting conversion: Request #{$request_id} to Proposal");
        
        // Validation step
        update_post_meta($request_id, '_arsol_conversion_step', 'validation');
        // ... existing validation logic
        
        // Creation step
        update_post_meta($request_id, '_arsol_conversion_step', 'creation');
        $new_proposal_id = wp_insert_post($proposal_args);
        if (is_wp_error($new_proposal_id)) {
            throw new Exception($new_proposal_id->get_error_message());
        }
        
        // Record created entity
        $this->record_transaction_entity($request_id, $new_proposal_id);
        
        // Metadata copy step
        update_post_meta($request_id, '_arsol_conversion_step', 'metadata_copy');
        $this->copy_request_metadata_to_proposal($request_id, $new_proposal_id);
        
        // Complete transaction
        $this->complete_workflow_transaction($request_id);
        
        // Log success
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('success', 
            "Conversion completed: Request #{$request_id} → Proposal #{$new_proposal_id}");
        
        // Set success notice
        $this->set_conversion_success_notice('request', 'proposal', $request_id, $new_proposal_id, $request_post->post_title);
        
        // Redirect
        $this->safe_redirect(admin_url('post.php?post=' . $new_proposal_id . '&action=edit'));
        
    } catch (Exception $e) {
        // Log error
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('error', 
            "Conversion failed: Request #{$request_id} - " . $e->getMessage());
        
        // Rollback everything
        $this->rollback_workflow_transaction($request_id, $e->getMessage());
        
        // Set failure notice
        if ($request_post) {
            $this->set_conversion_failure_notice('request', 'proposal', $request_id, $request_post->post_title, $e->getMessage());
        }
        
        // Redirect back
        $this->safe_redirect(admin_url('edit.php?post_type=arsol-pfw-request'));
    }
}
```

### Complete Rollback Implementation with Error Handling

#### Request → Proposal Rollback
```php
private function rollback_request_to_proposal($source_id) {
    $deleted_count = 0;
    
    // Delete created proposal
    $created_ids = get_post_meta($source_id, '_arsol_conversion_created_ids', true) ?: array();
    foreach ($created_ids as $entity_id) {
        if (wp_delete_post($entity_id, true)) {
            $deleted_count++;
            \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                "Rollback: Deleted proposal #{$entity_id}");
        }
    }
    
    return $deleted_count;
}
```

#### Proposal → Project Rollback (Complex)
```php
private function rollback_proposal_to_project($source_id) {
    $deleted_count = 0;
    $created_ids = get_post_meta($source_id, '_arsol_conversion_created_ids', true) ?: array();
    
    foreach ($created_ids as $entity_id) {
        $post = get_post($entity_id);
        if (!$post) continue;
        
        // Handle different entity types
        switch ($post->post_type) {
            case 'shop_order':
            case 'shop_subscription':
                // WooCommerce entities
                if (wp_delete_post($entity_id, true)) {
                    $deleted_count++;
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Rollback: Deleted {$post->post_type} #{$entity_id}");
                }
                break;
            case 'arsol-project':
                // Project entity
                if (wp_delete_post($entity_id, true)) {
                    $deleted_count++;
                    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
                        "Rollback: Deleted project #{$entity_id}");
                }
                break;
        }
    }
    
    return $deleted_count;
}
```

#### Main Rollback Method
```php
private function rollback_workflow_transaction($source_id, $reason) {
    // Store rollback reason
    update_post_meta($source_id, '_arsol_conversion_rollback_reason', $reason);
    update_post_meta($source_id, '_arsol_workflow_status', 'failed');
    
    // Get conversion type for specific rollback
    $conversion_type = get_post_meta($source_id, '_arsol_conversion_type', true);
    
    $deleted_count = 0;
    switch ($conversion_type) {
        case 'request_to_proposal':
            $deleted_count = $this->rollback_request_to_proposal($source_id);
            break;
        case 'proposal_to_project':
            $deleted_count = $this->rollback_proposal_to_project($source_id);
            break;
    }
    
    // Log rollback completion
    \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
        "Rollback completed for {$conversion_type}: {$deleted_count} entities deleted. Reason: {$reason}");
    
    // Clean up transaction metadata
    delete_post_meta($source_id, '_arsol_conversion_created_ids');
    delete_post_meta($source_id, '_arsol_conversion_step');
}
```

### Automatic Cleanup System

#### Cron Job Implementation (Add to class-setup.php)
```php
// In constructor:
add_action('wp', array($this, 'schedule_conversion_cleanup'));
add_action('arsol_cleanup_stuck_conversions', array($this, 'cleanup_stuck_conversions'));

public function schedule_conversion_cleanup() {
    if (!wp_next_scheduled('arsol_cleanup_stuck_conversions')) {
        wp_schedule_event(time(), 'hourly', 'arsol_cleanup_stuck_conversions');
    }
}

public function cleanup_stuck_conversions() {
    $cleaned = \Arsol_Projects_For_Woo\Workflow\Workflow_Handler::cleanup_stuck_workflows(30);
    if ($cleaned > 0) {
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('info', 
            "Automatic cleanup: removed {$cleaned} stuck conversions");
    }
}
```

#### Cleanup Method (Add to Workflow_Handler)
```php
public static function cleanup_stuck_workflows($max_age_minutes = 30) {
    global $wpdb;
    
    $cutoff_time = date('Y-m-d H:i:s', strtotime("-{$max_age_minutes} minutes"));
    
    // Find stuck conversions
    $stuck_posts = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, pm1.meta_value as conversion_type, pm2.meta_value as started_time
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_arsol_workflow_status'
        INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_arsol_workflow_started'
        WHERE pm1.meta_value = 'in_progress'
        AND pm2.meta_value < %s
    ", $cutoff_time));
    
    $cleaned = 0;
    foreach ($stuck_posts as $post) {
        // Force rollback stuck conversion
        $workflow_handler = new self();
        $workflow_handler->rollback_workflow_transaction($post->ID, 'Automatic cleanup - stuck for over ' . $max_age_minutes . ' minutes');
        $cleaned++;
        
        \Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('warning', 
            "Cleaned up stuck conversion: Post #{$post->ID}, Type: {$post->conversion_type}");
    }
    
    return $cleaned;
}
```

### Admin Interface Integration

#### Advanced Settings Addition
```php
// Add to render_page() method in class-admin-settings-advanced.php:

echo '<div class="arsol-settings-section">';
echo '<h3>' . __('Conversion Management', 'arsol-pfw') . '</h3>';
echo '<table class="form-table">';
echo '<tr><th scope="row">' . __('Maintenance', 'arsol-pfw') . '</th>';
echo '<td>';
echo '<button type="button" id="cleanup-conversions" class="button">' 
     . __('Clean Up Stuck Conversions', 'arsol-pfw') . '</button>';
echo '<p class="description">' . __('Remove conversion data for processes stuck for more than 30 minutes.', 'arsol-pfw') . '</p>';
echo '</td></tr>';
echo '</table>';
echo '</div>';

// JavaScript with AJAX:
echo '<script>
jQuery("#cleanup-conversions").click(function() {
    if (confirm("Clean up stuck conversions?")) {
        const button = jQuery(this);
        button.prop("disabled", true).text("Cleaning...");
        
        jQuery.post(ajaxurl, {
            action: "arsol_cleanup_conversions",
            nonce: "' . wp_create_nonce('arsol_admin') . '"
        }, function(response) {
            if (response.success) {
                alert("Cleanup completed: " + response.data.cleaned + " conversions cleaned");
            } else {
                alert("Cleanup failed: " + response.data);
            }
            button.prop("disabled", false).text("Clean Up Stuck Conversions");
            location.reload();
        });
    }
});
</script>';

// Add AJAX handler:
add_action('wp_ajax_arsol_cleanup_conversions', array($this, 'handle_cleanup_ajax'));

public function handle_cleanup_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'arsol_admin') || !current_user_can('manage_options')) {
        wp_send_json_error('Security check failed');
    }
    
    $cleaned = \Arsol_Projects_For_Woo\Workflow\Workflow_Handler::cleanup_stuck_workflows(30);
    
    wp_send_json_success(array(
        'cleaned' => $cleaned,
        'message' => sprintf(__('%d stuck conversions cleaned up.', 'arsol-pfw'), $cleaned)
    ));
}
```

### Error Handling Principles

#### Complete Try-Catch Wrapping
- **Never use `wp_die()` during conversions**
- **Always redirect with notices instead of killing script**
- **Rollback before redirecting on any error**
- **Log all conversion steps and failures**

#### Persistent Error Storage
```php
// Store error details in source post metadata for debugging
update_post_meta($source_id, '_arsol_conversion_rollback_reason', $error_message);
update_post_meta($source_id, '_arsol_workflow_status', 'failed');

// Log to WooCommerce logs for admin review
\Arsol_Projects_For_Woo\Woocommerce_Logs::log_workflow('error', 
    "Conversion failed: {$conversion_type} - {$error_message}");
```

#### User Experience Features

##### Success Notice Example
```php
$this->set_conversion_success_notice('request', 'proposal', $request_id, $new_proposal_id, $request_post->post_title);

// Results in:
// ✅ Request "Website Redesign Project" successfully converted to Proposal #123.
```

##### Error Notice Example  
```php
$this->set_conversion_failure_notice('proposal', 'project', $proposal_id, $proposal_post->post_title, $e->getMessage());

// Results in:
// ❌ Failed to convert Proposal "Website Redesign Project" to Project. 
// Error: WooCommerce order creation failed.
```

### Core Benefits

1. **🛡️ Safe Rollback**: Survives PHP crashes and timeouts with persistent metadata
2. **👤 User Feedback**: Detailed success/failure notices after redirects
3. **📝 Complete Logging**: All conversion steps logged via existing WooCommerce_Logs class
4. **🔄 Automatic Cleanup**: Hourly cron job removes stuck conversions
5. **🔧 Admin Maintenance**: One-button cleanup for manual intervention
6. **⚛️ Atomic Operations**: All-or-nothing conversions with step tracking

### Success Criteria

After implementation:
- ✅ No orphaned data from failed conversions
- ✅ Users see clear success/failure messages
- ✅ Simple admin cleanup for stuck conversions
- ✅ Complete rollback on any failure

This simplified system provides robust conversion safety without complexity.