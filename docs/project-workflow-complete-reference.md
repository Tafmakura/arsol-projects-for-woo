# Project Workflow - Complete Reference

## Overview

The **Arsol Projects for WooCommerce** plugin implements a comprehensive project workflow system covering the complete lifecycle from initial request to project completion. This reference covers all workflow stages, conversions, and management processes.

## Key Features

- ✅ **3-Stage Workflow**: Request → Proposal → Project
- ✅ **Dynamic Stage Management** with custom taxonomies
- ✅ **Automatic Conversions** between workflow stages  
- ✅ **WooCommerce Integration** for billing and subscriptions
- ✅ **Email Notifications** at every workflow step
- ✅ **Role-Based Access Control** for workflow management

---

## Workflow Overview

### Complete Project Lifecycle

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   REQUEST       │───▶│    PROPOSAL      │───▶│    PROJECT      │
│                 │    │                  │    │                 │
│ • Pending       │    │ • Processing     │    │ • Active        │
│ • Under Review  │    │ • Ready          │    │ • In Progress   │
│ • Approved      │    │ • Approved       │    │ • Completed     │
│ • Rejected      │    │ • Rejected       │    │ • On Hold       │
│ • On Hold       │    │ • Revision       │    │ • Cancelled     │
└─────────────────┘    └──────────────────┘    └─────────────────┘
```

### Phase Management System

The plugin uses a 4-phase taxonomy system:

1. **Request Phase** - Initial project requests
2. **Proposal Phase** - Proposal development and approval
3. **Project Phase** - Active project execution
4. **Archive Phase** - Completed and cancelled projects

---

## Stage 1: Project Requests

### Request Stages (Taxonomy: `arsol-pfw-request-stage`)

#### 1. Pending Review
**Description**: Initial state when request is submitted
**Who Can Change**: Project managers, administrators
**Email Triggers**: New request confirmation to customer, admin notification

```php
// Create new request in pending stage
$request_id = wp_insert_post(array(
    'post_type' => 'arsol-pfw-request',
    'post_title' => $request_title,
    'post_content' => $request_description,
    'post_status' => 'publish',
    'post_author' => $user_id
));

// Set initial stage
wp_set_post_terms($request_id, 'pending-review', 'arsol-pfw-request-stage');
```

#### 2. Under Review
**Description**: Request is being actively reviewed by management
**Who Can Change**: Project managers, administrators
**Email Triggers**: Status update to customer

```php
// Change request stage
wp_set_post_terms($request_id, 'under-review', 'arsol-pfw-request-stage');

// Trigger email notification
do_action('arsol_pfw_request_stage_changed', $request_id, 'pending-review', 'under-review', $user_id);
```

#### 3. Approved
**Description**: Request approved, ready for proposal creation
**Who Can Change**: Project managers, administrators
**Email Triggers**: Admin notification for proposal creation
**Next Step**: Convert to proposal

```php
// Approve request
wp_set_post_terms($request_id, 'approved', 'arsol-pfw-request-stage');

// Auto-convert to proposal (if enabled)
if (get_option('arsol_pfw_auto_convert_requests', false)) {
    $proposal_id = arsol_pfw_convert_request_to_proposal($request_id);
}
```

#### 4. Rejected
**Description**: Request declined with reason
**Who Can Change**: Project managers, administrators
**Email Triggers**: Rejection notification with reason

```php
// Reject request with reason
wp_set_post_terms($request_id, 'rejected', 'arsol-pfw-request-stage');
update_post_meta($request_id, '_rejection_reason', $rejection_reason);

// Trigger rejection email
do_action('arsol_pfw_request_rejected', $request_id, $rejection_reason, $user_id);
```

#### 5. On Hold
**Description**: Request temporarily paused pending information
**Who Can Change**: Project managers, administrators
**Email Triggers**: On hold notification with details

### Request Management Functions

#### Request Creation
```php
/**
 * Create new project request
 */
function arsol_pfw_create_request($request_data, $user_id = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Validate user permissions
    if (!arsol_pfw_user_can_request_projects($user_id)) {
        return new WP_Error('permission_denied', 'User cannot create requests');
    }
    
    // Create request post
    $request_id = wp_insert_post(array(
        'post_type' => 'arsol-pfw-request',
        'post_title' => sanitize_text_field($request_data['title']),
        'post_content' => wp_kses_post($request_data['description']),
        'post_status' => 'publish',
        'post_author' => $user_id
    ));
    
    if (is_wp_error($request_id)) {
        return $request_id;
    }
    
    // Set initial stage
    wp_set_post_terms($request_id, 'pending-review', 'arsol-pfw-request-stage');
    
    // Save additional meta
    if (isset($request_data['priority'])) {
        update_post_meta($request_id, '_request_priority', $request_data['priority']);
    }
    
    if (isset($request_data['budget_range'])) {
        update_post_meta($request_id, '_estimated_budget', $request_data['budget_range']);
    }
    
    // Trigger creation hooks
    do_action('arsol_pfw_request_created', $request_id, $request_data, $user_id);
    
    return $request_id;
}
```

#### Request to Proposal Conversion
```php
/**
 * Convert request to proposal
 */
function arsol_pfw_convert_request_to_proposal($request_id, $assign_to_user = 0) {
    // Validate request
    $request = get_post($request_id);
    if (!$request || $request->post_type !== 'arsol-pfw-request') {
        return new WP_Error('invalid_request', 'Invalid request ID');
    }
    
    // Check if already converted
    $existing_proposal = get_post_meta($request_id, '_converted_to_proposal', true);
    if ($existing_proposal) {
        return new WP_Error('already_converted', 'Request already converted');
    }
    
    // Create proposal
    $proposal_id = wp_insert_post(array(
        'post_type' => 'arsol-pfw-proposal',
        'post_title' => $request->post_title,
        'post_content' => $request->post_content,
        'post_status' => 'publish',
        'post_author' => $assign_to_user ?: get_current_user_id()
    ));
    
    if (is_wp_error($proposal_id)) {
        return $proposal_id;
    }
    
    // Set initial proposal stage
    wp_set_post_terms($proposal_id, 'processing', 'arsol-pfw-proposal-stage');
    
    // Link request to proposal
    update_post_meta($request_id, '_converted_to_proposal', $proposal_id);
    update_post_meta($proposal_id, '_source_request', $request_id);
    
    // Copy relevant meta data
    $budget = get_post_meta($request_id, '_estimated_budget', true);
    if ($budget) {
        update_post_meta($proposal_id, '_proposal_budget', $budget);
    }
    
    // Trigger conversion hooks
    do_action('arsol_pfw_request_converted_to_proposal', $request_id, $proposal_id, $assign_to_user);
    
    return $proposal_id;
}
```

---

## Stage 2: Project Proposals

### Proposal Stages (Taxonomy: `arsol-pfw-proposal-stage`)

#### 1. Processing
**Description**: Proposal is being developed by assigned team member
**Who Can Change**: Proposal author, project managers
**Email Triggers**: Assignment notification, processing started

```php
// Assign proposal to team member
$proposal_id = arsol_pfw_convert_request_to_proposal($request_id, $team_member_id);

// Trigger processing emails
do_action('arsol_pfw_proposal_processing_started', $proposal_id, $customer_id, $team_member_id);
```

#### 2. Ready for Review
**Description**: Proposal completed, ready for customer review
**Who Can Change**: Proposal author, project managers
**Email Triggers**: Customer review request, internal notification

```php
// Mark proposal as ready
wp_set_post_terms($proposal_id, 'ready', 'arsol-pfw-proposal-stage');

// Trigger customer review email
do_action('arsol_pfw_proposal_ready_for_review', $proposal_id, $customer_id, $team_member_id);
```

#### 3. Approved
**Description**: Customer approved proposal, ready for project creation
**Who Can Change**: Customer (proposal decision), project managers
**Email Triggers**: Approval confirmation, project creation notification
**Next Step**: Convert to active project

```php
// Customer approves proposal
arsol_pfw_approve_proposal($proposal_id, $customer_id);

// Auto-convert to project
if (get_option('arsol_pfw_auto_convert_proposals', true)) {
    $project_id = arsol_pfw_convert_proposal_to_project($proposal_id);
}
```

#### 4. Rejected
**Description**: Customer rejected proposal with feedback
**Who Can Change**: Customer (proposal decision), project managers
**Email Triggers**: Rejection notification with feedback

```php
// Customer rejects proposal
arsol_pfw_reject_proposal($proposal_id, $customer_id, $rejection_reason);
```

#### 5. Revision Needed
**Description**: Customer requested changes to proposal
**Who Can Change**: Customer, project managers
**Email Triggers**: Revision request notification

### Proposal Management Functions

#### Proposal Decision Handling
```php
/**
 * Handle proposal decision (approve/reject/request changes)
 */
function arsol_pfw_process_proposal_decision($proposal_id, $decision, $user_id, $notes = '') {
    $proposal = get_post($proposal_id);
    if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
        return new WP_Error('invalid_proposal', 'Invalid proposal ID');
    }
    
    // Validate user can make decision
    if (!arsol_pfw_user_can_decide_proposal($user_id, $proposal_id)) {
        return new WP_Error('permission_denied', 'User cannot decide on this proposal');
    }
    
    switch ($decision) {
        case 'approve':
            wp_set_post_terms($proposal_id, 'approved', 'arsol-pfw-proposal-stage');
            update_post_meta($proposal_id, '_approval_date', current_time('timestamp'));
            update_post_meta($proposal_id, '_approved_by', $user_id);
            
            // Auto-convert to project if enabled
            if (get_option('arsol_pfw_auto_convert_proposals', true)) {
                $project_id = arsol_pfw_convert_proposal_to_project($proposal_id);
            }
            
            do_action('arsol_pfw_proposal_approved', $proposal_id, $user_id, $notes);
            break;
            
        case 'reject':
            wp_set_post_terms($proposal_id, 'rejected', 'arsol-pfw-proposal-stage');
            update_post_meta($proposal_id, '_rejection_date', current_time('timestamp'));
            update_post_meta($proposal_id, '_rejected_by', $user_id);
            update_post_meta($proposal_id, '_rejection_reason', $notes);
            
            do_action('arsol_pfw_proposal_rejected', $proposal_id, $user_id, $notes);
            break;
            
        case 'request_changes':
            wp_set_post_terms($proposal_id, 'revision', 'arsol-pfw-proposal-stage');
            update_post_meta($proposal_id, '_revision_requested_date', current_time('timestamp'));
            update_post_meta($proposal_id, '_revision_requested_by', $user_id);
            update_post_meta($proposal_id, '_revision_notes', $notes);
            
            do_action('arsol_pfw_proposal_revision_requested', $proposal_id, $user_id, $notes);
            break;
    }
    
    return true;
}
```

#### Proposal to Project Conversion
```php
/**
 * Convert approved proposal to active project
 */
function arsol_pfw_convert_proposal_to_project($proposal_id) {
    $proposal = get_post($proposal_id);
    if (!$proposal || $proposal->post_type !== 'arsol-pfw-proposal') {
        return new WP_Error('invalid_proposal', 'Invalid proposal ID');
    }
    
    // Check proposal is approved
    $current_stage = wp_get_post_terms($proposal_id, 'arsol-pfw-proposal-stage', array('fields' => 'slugs'));
    if (!in_array('approved', $current_stage)) {
        return new WP_Error('not_approved', 'Proposal must be approved before conversion');
    }
    
    // Check if already converted
    $existing_project = get_post_meta($proposal_id, '_converted_to_project', true);
    if ($existing_project) {
        return new WP_Error('already_converted', 'Proposal already converted');
    }
    
    // Get original customer (from source request)
    $source_request = get_post_meta($proposal_id, '_source_request', true);
    $customer_id = $source_request ? get_post($source_request)->post_author : $proposal->post_author;
    
    // Create project
    $project_id = wp_insert_post(array(
        'post_type' => 'arsol-pfw-project',
        'post_title' => $proposal->post_title,
        'post_content' => $proposal->post_content,
        'post_status' => 'publish',
        'post_author' => $customer_id
    ));
    
    if (is_wp_error($project_id)) {
        return $project_id;
    }
    
    // Set initial project stage
    wp_set_post_terms($project_id, 'active', 'arsol-pfw-project-stage');
    
    // Link proposal to project
    update_post_meta($proposal_id, '_converted_to_project', $project_id);
    update_post_meta($project_id, '_source_proposal', $proposal_id);
    update_post_meta($project_id, '_source_request', $source_request);
    
    // Copy proposal data to project
    $proposal_budget = get_post_meta($proposal_id, '_proposal_budget', true);
    if ($proposal_budget) {
        update_post_meta($project_id, '_project_budget', $proposal_budget);
    }
    
    $proposal_timeline = get_post_meta($proposal_id, '_proposal_timeline', true);
    if ($proposal_timeline) {
        update_post_meta($project_id, '_project_timeline', $proposal_timeline);
    }
    
    // Assign project manager (proposal author)
    update_post_meta($project_id, '_project_manager', $proposal->post_author);
    
    // Trigger conversion hooks
    do_action('arsol_pfw_proposal_converted_to_project', $proposal_id, $project_id, $customer_id);
    
    return $project_id;
}
```

---

## Stage 3: Active Projects

### Project Stages (Taxonomy: `arsol-pfw-project-stage`)

#### 1. Active
**Description**: Project officially started and in progress
**Who Can Change**: Project manager, administrators
**Email Triggers**: Project kickoff notification

```php
// Project becomes active (from proposal conversion)
wp_set_post_terms($project_id, 'active', 'arsol-pfw-project-stage');

// Trigger kickoff notifications
do_action('arsol_pfw_project_activated', $project_id, $customer_id, $project_manager_id);
```

#### 2. In Progress
**Description**: Active work being performed on project
**Who Can Change**: Project manager, team members
**Email Triggers**: Progress update notifications

#### 3. Review
**Description**: Project work completed, under customer review
**Who Can Change**: Project manager
**Email Triggers**: Review request to customer

#### 4. Completed
**Description**: Project successfully completed and delivered
**Who Can Change**: Project manager, customer confirmation
**Email Triggers**: Completion notification, delivery confirmation

```php
// Mark project as completed
wp_set_post_terms($project_id, 'completed', 'arsol-pfw-project-stage');
update_post_meta($project_id, '_completion_date', current_time('timestamp'));

// Trigger completion notifications
do_action('arsol_pfw_project_completed', $project_id, $customer_id, $project_manager_id);
```

#### 5. On Hold
**Description**: Project temporarily paused
**Who Can Change**: Project manager, customer, administrators
**Email Triggers**: On hold notification with reason

#### 6. Cancelled
**Description**: Project cancelled before completion
**Who Can Change**: Project manager, administrators
**Email Triggers**: Cancellation notification

### Project Management Functions

#### Project Stage Management
```php
/**
 * Change project stage with validation and notifications
 */
function arsol_pfw_change_project_stage($project_id, $new_stage, $user_id = 0, $notes = '') {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $project = get_post($project_id);
    if (!$project || $project->post_type !== 'arsol-pfw-project') {
        return new WP_Error('invalid_project', 'Invalid project ID');
    }
    
    // Get current stage
    $current_stages = wp_get_post_terms($project_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
    $old_stage = !empty($current_stages) ? $current_stages[0] : '';
    
    // Validate stage transition
    if (!arsol_pfw_validate_stage_transition($old_stage, $new_stage, 'project')) {
        return new WP_Error('invalid_transition', 'Invalid stage transition');
    }
    
    // Validate user permissions
    if (!arsol_pfw_user_can_change_project_stage($user_id, $project_id, $new_stage)) {
        return new WP_Error('permission_denied', 'User cannot change project stage');
    }
    
    // Update stage
    wp_set_post_terms($project_id, $new_stage, 'arsol-pfw-project-stage');
    
    // Log stage change
    $stage_log = get_post_meta($project_id, '_stage_log', true);
    if (!is_array($stage_log)) {
        $stage_log = array();
    }
    
    $stage_log[] = array(
        'from' => $old_stage,
        'to' => $new_stage,
        'user_id' => $user_id,
        'date' => current_time('timestamp'),
        'notes' => $notes
    );
    
    update_post_meta($project_id, '_stage_log', $stage_log);
    
    // Handle stage-specific actions
    switch ($new_stage) {
        case 'completed':
            update_post_meta($project_id, '_completion_date', current_time('timestamp'));
            break;
            
        case 'cancelled':
            update_post_meta($project_id, '_cancellation_date', current_time('timestamp'));
            update_post_meta($project_id, '_cancellation_reason', $notes);
            break;
            
        case 'on_hold':
            update_post_meta($project_id, '_hold_reason', $notes);
            break;
    }
    
    // Trigger stage change hooks
    do_action('arsol_pfw_project_stage_changed', $project_id, $old_stage, $new_stage, $user_id);
    
    return true;
}
```

---

## WooCommerce Integration

### Order Creation from Projects

```php
/**
 * Create WooCommerce order from project
 */
function arsol_pfw_create_project_order($project_id, $order_data = array()) {
    $project = get_post($project_id);
    if (!$project || $project->post_type !== 'arsol-pfw-project') {
        return new WP_Error('invalid_project', 'Invalid project ID');
    }
    
    // Get project details
    $customer_id = $project->post_author;
    $project_budget = get_post_meta($project_id, '_project_budget', true);
    
    // Create order
    $order = wc_create_order(array(
        'customer_id' => $customer_id,
        'status' => 'pending'
    ));
    
    if (is_wp_error($order)) {
        return $order;
    }
    
    // Add project as order item
    $item = new WC_Order_Item_Product();
    $item->set_name($project->post_title);
    $item->set_quantity(1);
    $item->set_total($project_budget);
    $item->add_meta_data('_arsol_pfw_project_id', $project_id);
    
    $order->add_item($item);
    $order->calculate_totals();
    $order->save();
    
    // Link order to project
    update_post_meta($project_id, '_woocommerce_order_id', $order->get_id());
    
    // Trigger order creation hooks
    do_action('arsol_pfw_project_order_created', $project_id, $order->get_id(), $customer_id);
    
    return $order->get_id();
}
```

### Subscription Integration

```php
/**
 * Create subscription for ongoing project
 */
function arsol_pfw_create_project_subscription($project_id, $subscription_data) {
    if (!class_exists('WC_Subscriptions')) {
        return new WP_Error('missing_plugin', 'WooCommerce Subscriptions required');
    }
    
    $project = get_post($project_id);
    if (!$project || $project->post_type !== 'arsol-pfw-project') {
        return new WP_Error('invalid_project', 'Invalid project ID');
    }
    
    // Create subscription order
    $subscription = wcs_create_subscription(array(
        'customer_id' => $project->post_author,
        'billing_period' => $subscription_data['period'],
        'billing_interval' => $subscription_data['interval']
    ));
    
    if (is_wp_error($subscription)) {
        return $subscription;
    }
    
    // Add project service as subscription item
    $item = new WC_Order_Item_Product();
    $item->set_name($project->post_title . ' - Ongoing Service');
    $item->set_quantity(1);
    $item->set_total($subscription_data['amount']);
    $item->add_meta_data('_arsol_pfw_project_id', $project_id);
    
    $subscription->add_item($item);
    $subscription->calculate_totals();
    $subscription->save();
    
    // Link subscription to project
    update_post_meta($project_id, '_woocommerce_subscription_id', $subscription->get_id());
    
    return $subscription->get_id();
}
```

---

## Workflow Automation

### Auto-Conversion Settings

```php
// Enable auto-conversion of requests to proposals
add_filter('arsol_pfw_auto_convert_requests', '__return_true');

// Enable auto-conversion of proposals to projects
add_filter('arsol_pfw_auto_convert_proposals', '__return_true');

// Customize conversion conditions
add_filter('arsol_pfw_should_auto_convert_request', function($should_convert, $request_id) {
    // Only auto-convert high-priority requests
    $priority = get_post_meta($request_id, '_request_priority', true);
    return $priority === 'high';
}, 10, 2);
```

### Workflow Rules Engine

```php
/**
 * Define workflow rules
 */
function arsol_pfw_define_workflow_rules() {
    // Stage transition rules
    $stage_rules = array(
        'request' => array(
            'pending-review' => array('under-review', 'approved', 'rejected', 'on-hold'),
            'under-review' => array('approved', 'rejected', 'on-hold'),
            'approved' => array(), // Terminal state
            'rejected' => array(), // Terminal state
            'on-hold' => array('under-review', 'approved', 'rejected')
        ),
        'proposal' => array(
            'processing' => array('ready', 'revision'),
            'ready' => array('approved', 'rejected', 'revision'),
            'approved' => array(), // Terminal state
            'rejected' => array('revision'),
            'revision' => array('processing', 'ready')
        ),
        'project' => array(
            'active' => array('in-progress', 'on-hold', 'cancelled'),
            'in-progress' => array('review', 'completed', 'on-hold', 'cancelled'),
            'review' => array('in-progress', 'completed'),
            'completed' => array(), // Terminal state
            'on-hold' => array('active', 'in-progress', 'cancelled'),
            'cancelled' => array() // Terminal state
        )
    );
    
    return apply_filters('arsol_pfw_workflow_stage_rules', $stage_rules);
}

/**
 * Validate stage transition
 */
function arsol_pfw_validate_stage_transition($from_stage, $to_stage, $entity_type) {
    $rules = arsol_pfw_define_workflow_rules();
    
    if (!isset($rules[$entity_type][$from_stage])) {
        return false;
    }
    
    return in_array($to_stage, $rules[$entity_type][$from_stage]);
}
```

### Automated Notifications

```php
/**
 * Set up automated workflow notifications
 */
function arsol_pfw_setup_workflow_notifications() {
    // Request stage notifications
    add_action('arsol_pfw_request_stage_changed', 'arsol_pfw_send_request_notification', 10, 4);
    
    // Proposal stage notifications
    add_action('arsol_pfw_proposal_stage_changed', 'arsol_pfw_send_proposal_notification', 10, 4);
    
    // Project stage notifications
    add_action('arsol_pfw_project_stage_changed', 'arsol_pfw_send_project_notification', 10, 4);
    
    // Conversion notifications
    add_action('arsol_pfw_request_converted_to_proposal', 'arsol_pfw_send_conversion_notification', 10, 3);
    add_action('arsol_pfw_proposal_converted_to_project', 'arsol_pfw_send_conversion_notification', 10, 3);
}
```

---

## Workflow Customization

### Custom Stage Creation

```php
/**
 * Add custom project stages
 */
function add_custom_project_stages() {
    // Add custom stages to project stage taxonomy
    $custom_stages = array(
        'design-phase' => 'Design Phase',
        'development-phase' => 'Development Phase',
        'testing-phase' => 'Testing Phase',
        'deployment-phase' => 'Deployment Phase'
    );
    
    foreach ($custom_stages as $slug => $name) {
        if (!term_exists($slug, 'arsol-pfw-project-stage')) {
            wp_insert_term($name, 'arsol-pfw-project-stage', array(
                'slug' => $slug,
                'description' => 'Custom stage: ' . $name
            ));
        }
    }
}
add_action('init', 'add_custom_project_stages');
```

### Workflow Event Logging

```php
/**
 * Comprehensive workflow event logging
 */
class Arsol_Workflow_Logger {
    
    public function __construct() {
        // Hook into all workflow events
        add_action('arsol_pfw_request_created', array($this, 'log_request_created'), 10, 3);
        add_action('arsol_pfw_request_stage_changed', array($this, 'log_stage_change'), 10, 4);
        add_action('arsol_pfw_request_converted_to_proposal', array($this, 'log_conversion'), 10, 3);
        add_action('arsol_pfw_proposal_stage_changed', array($this, 'log_stage_change'), 10, 4);
        add_action('arsol_pfw_proposal_converted_to_project', array($this, 'log_conversion'), 10, 3);
        add_action('arsol_pfw_project_stage_changed', array($this, 'log_stage_change'), 10, 4);
    }
    
    public function log_request_created($request_id, $request_data, $user_id) {
        $this->log_event('request_created', $request_id, array(
            'user_id' => $user_id,
            'title' => $request_data['title']
        ));
    }
    
    public function log_stage_change($entity_id, $old_stage, $new_stage, $user_id) {
        $entity_type = get_post_type($entity_id);
        
        $this->log_event('stage_changed', $entity_id, array(
            'entity_type' => $entity_type,
            'old_stage' => $old_stage,
            'new_stage' => $new_stage,
            'user_id' => $user_id
        ));
    }
    
    public function log_conversion($source_id, $target_id, $user_id) {
        $source_type = get_post_type($source_id);
        $target_type = get_post_type($target_id);
        
        $this->log_event('entity_converted', $source_id, array(
            'source_type' => $source_type,
            'target_id' => $target_id,
            'target_type' => $target_type,
            'user_id' => $user_id
        ));
    }
    
    private function log_event($event_type, $entity_id, $data) {
        $log_entry = array(
            'event_type' => $event_type,
            'entity_id' => $entity_id,
            'data' => $data,
            'timestamp' => current_time('timestamp'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        );
        
        // Store in custom table or use existing options
        $workflow_log = get_option('arsol_pfw_workflow_log', array());
        $workflow_log[] = $log_entry;
        
        // Keep only last 1000 entries
        if (count($workflow_log) > 1000) {
            $workflow_log = array_slice($workflow_log, -1000);
        }
        
        update_option('arsol_pfw_workflow_log', $workflow_log);
        
        // Also trigger hook for external systems
        do_action('arsol_pfw_workflow_event_logged', $log_entry);
    }
}

// Initialize logger
new Arsol_Workflow_Logger();
```

---

## Performance Optimization

### Workflow Caching
```php
// Cache workflow queries
add_filter('arsol_pfw_cache_workflow_queries', '__return_true');

// Cache stage transitions
add_filter('arsol_pfw_cache_stage_transitions', '__return_true');
```

### Batch Operations
```php
/**
 * Bulk stage changes
 */
function arsol_pfw_bulk_change_stages($entity_ids, $new_stage, $entity_type = 'project') {
    foreach ($entity_ids as $entity_id) {
        arsol_pfw_change_project_stage($entity_id, $new_stage);
    }
    
    // Clear relevant caches
    wp_cache_delete('arsol_pfw_workflow_stats');
}
```

---

This completes the comprehensive project workflow reference. The system provides powerful workflow management with complete automation, customization options, and detailed tracking throughout the entire project lifecycle. 