# Proposal to Project Conversion Hooks Reference

This document provides a comprehensive reference for all action hooks and filters available during the proposal to project conversion process in the Arsol Projects for WooCommerce plugin. The hook system follows WooCommerce's proven before/during/after pattern for maximum compatibility and developer familiarity.

## Hook Execution Order

The hooks are fired in this exact order during the conversion process:

### 1. Pre-Validation Phase
```php
// Before any validation checks
do_action('arsol_before_project_proposal_conversion_validation', $proposal_id, $conversion_data);

// After validation passes, before project creation
do_action('arsol_after_project_proposal_conversion_validated', $proposal_id, $proposal_post, $conversion_data);
```

### 2. Project Creation Phase
```php
// Filter to modify project arguments before creation
$project_args = apply_filters('arsol_project_proposal_conversion_args', $project_args, $proposal_id, $proposal_post, $conversion_data);

// Before project post is created
do_action('arsol_before_project_proposal_conversion_project_creation', $project_args, $proposal_id, $conversion_data);

// After project is created (success path)
do_action('arsol_after_project_proposal_conversion_project_created', $project_id, $proposal_id, $proposal_post, $conversion_data);

// If project creation fails (error path)
do_action('arsol_project_proposal_conversion_project_creation_failed', $error, $proposal_id, $project_args, $conversion_data);
```

### 3. Metadata Copy Phase
```php
// Before copying metadata
do_action('arsol_before_project_proposal_conversion_metadata_copy', $project_id, $proposal_id, $conversion_data);

// Filter to modify metadata mapping
$meta_mapping = apply_filters('arsol_project_proposal_conversion_meta_mapping', $meta_to_copy, $project_id, $proposal_id, $conversion_data);

// After metadata is copied
do_action('arsol_after_project_proposal_conversion_metadata_copied', $project_id, $proposal_id, $meta_to_copy, $conversion_data);
```

### 4. Order Creation Phase
```php
// Before order/subscription creation
do_action('arsol_before_project_conversion_order_creation', $project_id, $proposal_id, $conversion_data);

// Legacy main conversion hook (where billing happens)
do_action('arsol_proposal_converted_to_project', $project_id, $proposal_id);

// After order creation attempt
do_action('arsol_after_project_conversion_order_creation_attempt', $project_id, $proposal_id, $conversion_data);
```

### 5. Success/Rollback Phase
```php
// Success Path:
do_action('arsol_before_project_conversion_proposal_deletion', $proposal_id, $project_id, $conversion_data);
do_action('arsol_after_project_conversion_complete', $project_id, $proposal_id, $conversion_data);
do_action('arsol_before_project_conversion_redirect', $project_id, $proposal_id, $conversion_data);

// Rollback Path (if order creation fails):
do_action('arsol_before_project_conversion_rollback', $project_id, $proposal_id, $order_errors, $order_ids, $conversion_data);
do_action('arsol_after_project_conversion_rollback', $proposal_id, $order_errors, $conversion_data);
```

---

## Detailed Hook Reference

### Pre-Validation Hooks

#### `arsol_before_project_proposal_conversion_validation`
**Type:** Action Hook  
**Timing:** Before any validation checks are performed  
**Parameters:**
- `$proposal_id` (int) - The proposal ID
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Add custom validation checks
- Log conversion attempts
- Set up external API connections
- Prepare third-party integrations

**Example:**
```php
add_action('arsol_before_project_proposal_conversion_validation', 'log_conversion_attempt', 10, 2);
function log_conversion_attempt($proposal_id, $conversion_data) {
    error_log(sprintf('Conversion attempt for proposal #%d by user #%d', 
        $proposal_id, $conversion_data['user_id']));
}
```

#### `arsol_after_project_proposal_conversion_validated`
**Type:** Action Hook  
**Timing:** After all validation checks pass, before project creation  
**Parameters:**
- `$proposal_id` (int) - The proposal ID
- `$proposal_post` (WP_Post) - The proposal post object
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Final preparation before conversion
- Set conversion flags
- Initialize tracking systems
- Send notifications

**Example:**
```php
add_action('arsol_after_project_proposal_conversion_validated', 'prepare_external_systems', 10, 3);
function prepare_external_systems($proposal_id, $proposal_post, $conversion_data) {
    // Initialize CRM integration
    update_post_meta($proposal_id, '_conversion_started', current_time('mysql'));
}
```

### Project Creation Hooks

#### `arsol_project_proposal_conversion_args` (Filter)
**Type:** Filter Hook  
**Timing:** Before project post creation  
**Parameters:**
- `$project_args` (array) - The project arguments
- `$proposal_id` (int) - The proposal ID
- `$proposal_post` (WP_Post) - The proposal post
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Modify project title, content, or status
- Set custom post author
- Add custom post fields

**Example:**
```php
add_filter('arsol_project_proposal_conversion_args', 'customize_project_creation', 10, 4);
function customize_project_creation($project_args, $proposal_id, $proposal_post, $conversion_data) {
    // Add priority indicator to title
    $priority = get_post_meta($proposal_id, '_proposal_priority', true);
    if ($priority === 'high') {
        $project_args['post_title'] = '[HIGH PRIORITY] ' . $project_args['post_title'];
    }
    return $project_args;
}
```

#### `arsol_before_project_proposal_conversion_project_creation`
**Type:** Action Hook  
**Timing:** Immediately before the project post is created  
**Parameters:**
- `$project_args` (array) - The final project arguments that will be used
- `$proposal_id` (int) - The proposal ID
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Final validation of project arguments
- Set up database connections
- Prepare external services

#### `arsol_after_project_proposal_conversion_project_created`
**Type:** Action Hook  
**Timing:** Immediately after project is created, before any metadata  
**Parameters:**
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID
- `$proposal_post` (WP_Post) - The proposal post object
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Send notifications
- Create related records
- Initialize project tracking
- Set up project directories

**Example:**
```php
add_action('arsol_after_project_proposal_conversion_project_created', 'initialize_project_tracking', 10, 4);
function initialize_project_tracking($project_id, $proposal_id, $proposal_post, $conversion_data) {
    // Create project timeline entry
    add_post_meta($project_id, '_project_timeline', array(
        'created' => current_time('mysql'),
        'created_from' => 'proposal',
        'original_proposal' => $proposal_id
    ));
}
```

#### `arsol_project_proposal_conversion_project_creation_failed`
**Type:** Action Hook  
**Timing:** When project creation fails  
**Parameters:**
- `$error` (WP_Error) - The error object
- `$proposal_id` (int) - The proposal ID
- `$project_args` (array) - The project arguments that failed
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Error logging
- Failure notifications
- Cleanup operations
- Retry mechanisms

### Metadata Copy Hooks

#### `arsol_before_project_proposal_conversion_metadata_copy`
**Type:** Action Hook  
**Timing:** Before copying metadata from proposal to project  
**Parameters:**
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID
- `$conversion_data` (array) - Conversion context data

#### `arsol_project_proposal_conversion_meta_mapping` (Filter)
**Type:** Filter Hook  
**Timing:** Before metadata is copied  
**Parameters:**
- `$meta_to_copy` (array) - Array of `proposal_key => project_key` mappings
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Add custom metadata mappings
- Skip certain metadata
- Transform metadata keys

**Example:**
```php
add_filter('arsol_project_proposal_conversion_meta_mapping', 'add_custom_meta_mapping', 10, 4);
function add_custom_meta_mapping($meta_to_copy, $project_id, $proposal_id, $conversion_data) {
    // Add custom mapping for priority field
    $meta_to_copy['_proposal_priority'] = '_project_priority';
    
    // Skip internal proposal tracking
    unset($meta_to_copy['_proposal_internal_notes']);
    
    return $meta_to_copy;
}
```

#### `arsol_after_project_proposal_conversion_metadata_copied`
**Type:** Action Hook  
**Timing:** After all metadata is copied from proposal to project  
**Parameters:**
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID
- `$meta_to_copy` (array) - The metadata that was copied
- `$conversion_data` (array) - Conversion context data

### Order Creation Hooks

#### `arsol_before_project_conversion_order_creation`
**Type:** Action Hook  
**Timing:** Before order/subscription creation from proposal  
**Parameters:**
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Prepare billing systems
- Set up payment processors
- Initialize order tracking
- Custom pricing calculations

#### `arsol_proposal_converted_to_project` (Legacy)
**Type:** Action Hook  
**Timing:** Main conversion hook where billing systems operate  
**Parameters:**
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID

**Note:** This is the legacy hook that billing systems use. It's maintained for backward compatibility.

#### `arsol_after_project_conversion_order_creation_attempt`
**Type:** Action Hook  
**Timing:** After order creation attempt, before error checking  
**Parameters:**
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID
- `$conversion_data` (array) - Conversion context data

### Success/Rollback Hooks

#### `arsol_before_project_conversion_rollback`
**Type:** Action Hook  
**Timing:** Before rollback due to order creation failure  
**Parameters:**
- `$project_id` (int) - The project ID that will be deleted
- `$proposal_id` (int) - The original proposal ID
- `$order_errors` (array) - Array of error messages
- `$order_ids` (array) - Array of order IDs to be deleted
- `$conversion_data` (array) - Conversion context data

#### `arsol_after_project_conversion_rollback`
**Type:** Action Hook  
**Timing:** After rollback is completed  
**Parameters:**
- `$proposal_id` (int) - The original proposal ID (still exists)
- `$order_errors` (array) - Array of error messages
- `$conversion_data` (array) - Conversion context data

#### `arsol_before_project_conversion_proposal_deletion`
**Type:** Action Hook  
**Timing:** Before the original proposal is deleted (conversion successful)  
**Parameters:**
- `$proposal_id` (int) - The proposal ID that will be deleted
- `$project_id` (int) - The new project ID
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Final proposal data backup
- Archive proposal information
- Send final notifications
- Update external systems

#### `arsol_after_project_conversion_complete`
**Type:** Action Hook  
**Timing:** After successful conversion completion (proposal deleted, project active)  
**Parameters:**
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID (now deleted)
- `$conversion_data` (array) - Conversion context data

**Use Cases:**
- Success notifications
- Project initialization
- External system updates
- Analytics tracking

#### `arsol_before_project_conversion_redirect`
**Type:** Action Hook  
**Timing:** Before redirect after successful conversion  
**Parameters:**
- `$project_id` (int) - The new project ID
- `$proposal_id` (int) - The original proposal ID (deleted)
- `$conversion_data` (array) - Conversion context data

---

## Conversion Data Array Structure

The `$conversion_data` array contains contextual information about the conversion:

```php
$conversion_data = array(
    'proposal_id' => 123,                    // The proposal ID
    'proposal_post' => $proposal_post,       // WP_Post object
    'is_internal_call' => false,             // true if customer-initiated
    'user_id' => 1,                          // User performing conversion
    'conversion_method' => 'admin_conversion' // 'admin_conversion' or 'customer_approval'
);
```

---

## Common Use Cases and Examples

### 1. Custom Project Initialization
```php
add_action('arsol_after_project_proposal_conversion_project_created', 'setup_custom_project_structure', 10, 4);
function setup_custom_project_structure($project_id, $proposal_id, $proposal_post, $conversion_data) {
    // Create project phases
    $phases = get_post_meta($proposal_id, '_proposal_phases', true);
    if ($phases) {
        update_post_meta($project_id, '_project_phases', $phases);
    }
    
    // Set project manager
    $manager_id = get_post_meta($proposal_id, '_assigned_manager', true);
    if ($manager_id) {
        update_post_meta($project_id, '_project_manager', $manager_id);
    }
}
```

### 2. External System Integration
```php
add_action('arsol_after_project_conversion_complete', 'sync_with_crm', 10, 3);
function sync_with_crm($project_id, $proposal_id, $conversion_data) {
    // Send project data to external CRM
    $project_data = array(
        'project_id' => $project_id,
        'customer_id' => $conversion_data['proposal_post']->post_author,
        'status' => 'active',
        'created_date' => current_time('c')
    );
    
    wp_remote_post('https://crm.example.com/api/projects', array(
        'body' => json_encode($project_data),
        'headers' => array('Content-Type' => 'application/json')
    ));
}
```

### 3. Conditional Processing
```php
add_action('arsol_before_project_conversion_order_creation', 'handle_special_pricing', 10, 3);
function handle_special_pricing($project_id, $proposal_id, $conversion_data) {
    // Only for high-value proposals
    $budget = get_post_meta($proposal_id, '_proposal_budget', true);
    if ($budget['amount'] > 10000) {
        // Apply enterprise discount
        update_post_meta($project_id, '_enterprise_discount', true);
    }
}
```

### 4. Error Handling and Recovery
```php
add_action('arsol_after_project_conversion_rollback', 'handle_conversion_failure', 10, 3);
function handle_conversion_failure($proposal_id, $errors, $conversion_data) {
    // Log detailed error information
    error_log(sprintf('Conversion failed for proposal #%d: %s', 
        $proposal_id, implode(', ', $errors)));
    
    // Send notification to admin
    wp_mail(
        get_option('admin_email'),
        'Project Conversion Failed',
        sprintf('Proposal #%d failed to convert: %s', $proposal_id, implode(', ', $errors))
    );
    
    // Set proposal status for retry
    update_post_meta($proposal_id, '_conversion_failed', current_time('mysql'));
}
```

---

## Best Practices

### 1. Hook Priority Management
- Use priority 5 for early modifications
- Use priority 10 (default) for normal processing
- Use priority 15+ for late processing that depends on other hooks

### 2. Error Handling
- Always check if objects exist before using them
- Use `is_wp_error()` to check for WordPress errors
- Implement proper fallbacks for failed operations

### 3. Performance Considerations
- Avoid heavy database operations in high-frequency hooks
- Use transients for caching when appropriate
- Consider using `wp_defer_term_counting()` for bulk operations

### 4. Data Validation
- Always validate data passed to hooks
- Use WordPress sanitization functions
- Check user permissions when appropriate

---

This comprehensive hook system provides maximum flexibility for developers while maintaining compatibility with existing code through the legacy `arsol_proposal_converted_to_project` hook. 