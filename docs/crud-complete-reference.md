# CRUD System Complete Reference - Arsol Projects for WooCommerce

## Table of Contents

1. [Overview](#overview)
2. [Entity Classes](#entity-classes)
3. [Data Store Classes](#data-store-classes)
4. [Factory Functions](#factory-functions)
5. [Stage Management](#stage-management)
6. [Hooks and Filters](#hooks-and-filters)
7. [Usage Examples](#usage-examples)
8. [Migration Guide](#migration-guide)
9. [API Reference](#api-reference)

## Overview

The CRUD system provides a clean, object-oriented API for managing Projects, Proposals, and Requests. It follows WooCommerce patterns exactly, ensuring familiarity for developers already working with WooCommerce.

### Key Components

- **Entity Classes**: `ARSOL_PFW_Project`, `ARSOL_PFW_Proposal`, `ARSOL_PFW_Request`
- **Data Store Classes**: Handle database operations
- **Factory Functions**: `arsol_pfw_get_project()`, `arsol_pfw_get_proposal()`, `arsol_pfw_get_request()`
- **Stage Management**: WooCommerce-inspired status system

## Entity Classes

### ARSOL_PFW_Project

**File**: `includes/custom-post-types/project/class-arsol-pfw-project.php`

```php
class ARSOL_PFW_Project extends WC_Data implements ARSOL_PFW_Stage_Interface {
    
    protected $data = array(
        'name'        => '',
        'stage'       => 'not-started',
        'lead_id'     => 0,
        'customer_id' => 0,
        'start_date'  => null,
        'due_date'    => null,
    );
    
    protected $object_type = 'arsol-pfw-project';
    
    // Basic getters/setters
    public function get_name($context = 'view') { return $this->get_prop('name', $context); }
    public function set_name($name) { $this->set_prop('name', $name); }
    
    public function get_stage($context = 'view') { return $this->get_prop('stage', $context); }
    public function set_stage($stage) { $this->set_prop('stage', $stage); }
    
    public function get_lead_id($context = 'view') { return $this->get_prop('lead_id', $context); }
    public function set_lead_id($lead_id) { $this->set_prop('lead_id', absint($lead_id)); }
    
    public function get_customer_id($context = 'view') { return $this->get_prop('customer_id', $context); }
    public function set_customer_id($customer_id) { $this->set_prop('customer_id', absint($customer_id)); }
    
    public function get_start_date($context = 'view') { return $this->get_prop('start_date', $context); }
    public function set_start_date($date) { $this->set_prop('start_date', $date); }
    
    public function get_due_date($context = 'view') { return $this->get_prop('due_date', $context); }
    public function set_due_date($date) { $this->set_prop('due_date', $date); }
    
    // Stage management
    public function update_stage($new_stage, $note = '') {
        $old_stage = $this->get_stage();
        $this->set_stage($new_stage);
        $this->save();
        
        do_action('arsol_pfw_project_stage_changed', $this->get_id(), $old_stage, $new_stage, $this);
        do_action("arsol_pfw_project_stage_{$old_stage}_to_{$new_stage}", $this->get_id(), $this);
    }
    
    public function get_available_stages() {
        return array(
            'not-started' => 'Not Started',
            'in-progress' => 'In Progress',
            'on-hold'     => 'On Hold',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
        );
    }
    
    // WooCommerce integration
    public function get_orders() {
        return wc_get_orders(array(
            'meta_key'   => '_arsol_pfw_project_id',
            'meta_value' => $this->get_id(),
        ));
    }
    
    public function get_subscriptions() {
        if (class_exists('WC_Subscriptions')) {
            return wcs_get_subscriptions(array(
                'meta_key'   => '_arsol_pfw_project_id',
                'meta_value' => $this->get_id(),
            ));
        }
        return array();
    }
}
```

### ARSOL_PFW_Proposal

**File**: `includes/custom-post-types/project-proposal/class-arsol-pfw-proposal.php`

```php
class ARSOL_PFW_Proposal extends WC_Data implements ARSOL_PFW_Stage_Interface {
    
    protected $data = array(
        'name'         => '',
        'stage'        => 'draft',
        'project_id'   => 0,
        'request_id'   => 0,
        'customer_id'  => 0,
        'costing_type' => 'fixed',
        'quotation'    => array(),
        'total_amount' => 0,
    );
    
    protected $object_type = 'arsol-pfw-proposal';
    
    // Basic getters/setters
    public function get_name($context = 'view') { return $this->get_prop('name', $context); }
    public function set_name($name) { $this->set_prop('name', $name); }
    
    public function get_stage($context = 'view') { return $this->get_prop('stage', $context); }
    public function set_stage($stage) { $this->set_prop('stage', $stage); }
    
    public function get_project_id($context = 'view') { return $this->get_prop('project_id', $context); }
    public function set_project_id($project_id) { $this->set_prop('project_id', absint($project_id)); }
    
    public function get_request_id($context = 'view') { return $this->get_prop('request_id', $context); }
    public function set_request_id($request_id) { $this->set_prop('request_id', absint($request_id)); }
    
    public function get_customer_id($context = 'view') { return $this->get_prop('customer_id', $context); }
    public function set_customer_id($customer_id) { $this->set_prop('customer_id', absint($customer_id)); }
    
    public function get_costing_type($context = 'view') { return $this->get_prop('costing_type', $context); }
    public function set_costing_type($type) { $this->set_prop('costing_type', $type); }
    
    public function get_quotation($context = 'view') { return $this->get_prop('quotation', $context); }
    public function set_quotation($quotation) { $this->set_prop('quotation', $quotation); }
    
    public function get_total_amount($context = 'view') { return $this->get_prop('total_amount', $context); }
    public function set_total_amount($amount) { $this->set_prop('total_amount', floatval($amount)); }
    
    // Stage management
    public function update_stage($new_stage, $note = '') {
        $old_stage = $this->get_stage();
        $this->set_stage($new_stage);
        $this->save();
        
        do_action('arsol_pfw_proposal_stage_changed', $this->get_id(), $old_stage, $new_stage, $this);
        do_action("arsol_pfw_proposal_stage_{$old_stage}_to_{$new_stage}", $this->get_id(), $this);
    }
    
    public function get_available_stages() {
        return array(
            'draft'      => 'Draft',
            'sent'       => 'Sent to Customer',
            'approved'   => 'Approved',
            'rejected'   => 'Rejected',
            'expired'    => 'Expired',
        );
    }
    
    // Proposal-specific methods
    public function approve() {
        $this->update_stage('approved');
        
        // Trigger project creation if approved
        do_action('arsol_pfw_proposal_approved', $this->get_id(), $this);
    }
    
    public function reject($reason = '') {
        $this->update_stage('rejected');
        
        if ($reason) {
            update_post_meta($this->get_id(), '_arsol_pfw_rejection_reason', sanitize_text_field($reason));
        }
        
        do_action('arsol_pfw_proposal_rejected', $this->get_id(), $this, $reason);
    }
}
```

### ARSOL_PFW_Request

**File**: `includes/custom-post-types/project-request/class-arsol-pfw-request.php`

```php
class ARSOL_PFW_Request extends WC_Data implements ARSOL_PFW_Stage_Interface {
    
    protected $data = array(
        'name'        => '',
        'stage'       => 'new',
        'customer_id' => 0,
        'project_id'  => 0,
        'budget'      => array(),
        'deadline'    => null,
        'description' => '',
    );
    
    protected $object_type = 'arsol-pfw-request';
    
    // Basic getters/setters
    public function get_name($context = 'view') { return $this->get_prop('name', $context); }
    public function set_name($name) { $this->set_prop('name', $name); }
    
    public function get_stage($context = 'view') { return $this->get_prop('stage', $context); }
    public function set_stage($stage) { $this->set_prop('stage', $stage); }
    
    public function get_customer_id($context = 'view') { return $this->get_prop('customer_id', $context); }
    public function set_customer_id($customer_id) { $this->set_prop('customer_id', absint($customer_id)); }
    
    public function get_project_id($context = 'view') { return $this->get_prop('project_id', $context); }
    public function set_project_id($project_id) { $this->set_prop('project_id', absint($project_id)); }
    
    public function get_budget($context = 'view') { return $this->get_prop('budget', $context); }
    public function set_budget($budget) { $this->set_prop('budget', $budget); }
    
    public function get_deadline($context = 'view') { return $this->get_prop('deadline', $context); }
    public function set_deadline($deadline) { $this->set_prop('deadline', $deadline); }
    
    public function get_description($context = 'view') { return $this->get_prop('description', $context); }
    public function set_description($description) { $this->set_prop('description', $description); }
    
    // Stage management
    public function update_stage($new_stage, $note = '') {
        $old_stage = $this->get_stage();
        $this->set_stage($new_stage);
        $this->save();
        
        do_action('arsol_pfw_request_stage_changed', $this->get_id(), $old_stage, $new_stage, $this);
        do_action("arsol_pfw_request_stage_{$old_stage}_to_{$new_stage}", $this->get_id(), $this);
    }
    
    public function get_available_stages() {
        return array(
            'new'        => 'New',
            'reviewing'  => 'Under Review',
            'approved'   => 'Approved',
            'rejected'   => 'Rejected',
            'on-hold'    => 'On Hold',
        );
    }
    
    // Request-specific methods
    public function approve() {
        $this->update_stage('approved');
        
        // Trigger proposal creation if approved
        do_action('arsol_pfw_request_approved', $this->get_id(), $this);
    }
    
    public function reject($reason = '') {
        $this->update_stage('rejected');
        
        if ($reason) {
            update_post_meta($this->get_id(), '_arsol_pfw_rejection_reason', sanitize_text_field($reason));
        }
        
        do_action('arsol_pfw_request_rejected', $this->get_id(), $this, $reason);
    }
}
```

## Data Store Classes

### ARSOL_PFW_Project_Data_Store

**File**: `includes/data-stores/class-arsol-pfw-project-data-store.php`

```php
class ARSOL_PFW_Project_Data_Store extends ARSOL_PFW_Data_Store_WP implements ARSOL_PFW_Project_Data_Store_Interface {
    
    protected $internal_meta_keys = array(
        '_arsol_pfw_project_stage',
        '_arsol_pfw_project_lead_id',
        '_arsol_pfw_project_customer_id',
        '_arsol_pfw_project_start_date',
        '_arsol_pfw_project_due_date',
    );
    
    public function create(&$project) {
        $project->set_date_created(current_time('timestamp', true));
        
        $id = wp_insert_post(
            apply_filters('arsol_pfw_new_project_data', array(
                'post_type'   => 'arsol-pfw-project',
                'post_status' => 'publish',
                'post_title'  => $project->get_name(),
                'post_author' => get_current_user_id(),
                'meta_input'  => $this->get_wp_meta_data($project),
            ))
        );
        
        if ($id && !is_wp_error($id)) {
            $project->set_id($id);
            $this->update_post_meta($project);
            $this->set_stage_term($project);
            
            do_action('arsol_pfw_project_created', $id, $project);
        }
    }
    
    public function read(&$project) {
        $post_object = get_post($project->get_id());
        
        if (!$post_object || 'arsol-pfw-project' !== $post_object->post_type) {
            throw new Exception('Invalid project.');
        }
        
        $project->set_props(array(
            'name'        => $post_object->post_title,
            'stage'       => $this->get_stage_from_terms($project->get_id()),
            'lead_id'     => get_post_meta($project->get_id(), '_arsol_pfw_project_lead_id', true),
            'customer_id' => get_post_meta($project->get_id(), '_arsol_pfw_project_customer_id', true),
            'start_date'  => get_post_meta($project->get_id(), '_arsol_pfw_project_start_date', true),
            'due_date'    => get_post_meta($project->get_id(), '_arsol_pfw_project_due_date', true),
        ));
        
        $project->set_object_read(true);
    }
    
    public function update(&$project) {
        $changes = $project->get_changes();
        
        if (array_intersect(array('name'), array_keys($changes))) {
            wp_update_post(array(
                'ID'         => $project->get_id(),
                'post_title' => $project->get_name(),
            ));
        }
        
        $this->update_post_meta($project);
        $this->set_stage_term($project);
        
        do_action('arsol_pfw_project_updated', $project->get_id(), $project);
    }
    
    public function delete(&$project, $args = array()) {
        $id = $project->get_id();
        
        if (!$id) {
            return false;
        }
        
        wp_delete_post($id, true);
        
        do_action('arsol_pfw_project_deleted', $id, $project);
        
        return true;
    }
    
    public function get_projects_by_stage($stage, $args = array()) {
        $args = array_merge(array(
            'post_type'      => 'arsol-pfw-project',
            'posts_per_page' => -1,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'arsol-pfw-project-stage',
                    'field'    => 'slug',
                    'terms'    => $stage,
                ),
            ),
        ), $args);
        
        return get_posts($args);
    }
    
    public function get_available_stages() {
        $terms = get_terms(array(
            'taxonomy'   => 'arsol-pfw-project-stage',
            'hide_empty' => false,
        ));
        
        $stages = array();
        foreach ($terms as $term) {
            $stages[$term->slug] = $term->name;
        }
        
        return $stages;
    }
    
    public function get_stage_counts() {
        $counts = wp_count_terms(array(
            'taxonomy' => 'arsol-pfw-project-stage',
        ));
        
        return $counts;
    }
    
    protected function set_stage_term($project) {
        wp_set_object_terms($project->get_id(), $project->get_stage(), 'arsol-pfw-project-stage');
    }
    
    protected function get_stage_from_terms($project_id) {
        $terms = wp_get_object_terms($project_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
        
        return !empty($terms) ? $terms[0] : 'not-started';
    }
}
```

## Factory Functions

### Core Factory Functions

**File**: `includes/arsol-pfw-core-functions.php`

```php
/**
 * Get project by ID
 *
 * @param int $project_id Project ID
 * @return ARSOL_PFW_Project|false
 */
function arsol_pfw_get_project($project_id) {
    return arsol_pfw_get_project_factory()->get_project($project_id);
}

/**
 * Get proposal by ID
 *
 * @param int $proposal_id Proposal ID
 * @return ARSOL_PFW_Proposal|false
 */
function arsol_pfw_get_proposal($proposal_id) {
    return arsol_pfw_get_proposal_factory()->get_proposal($proposal_id);
}

/**
 * Get request by ID
 *
 * @param int $request_id Request ID
 * @return ARSOL_PFW_Request|false
 */
function arsol_pfw_get_request($request_id) {
    return arsol_pfw_get_request_factory()->get_request($request_id);
}

/**
 * Create new project
 *
 * @param array $args Project data
 * @return ARSOL_PFW_Project
 */
function arsol_pfw_create_project($args = array()) {
    $project = new ARSOL_PFW_Project();
    
    foreach ($args as $key => $value) {
        if (is_callable(array($project, "set_{$key}"))) {
            $project->{"set_{$key}"}($value);
        }
    }
    
    return $project;
}

/**
 * Create new proposal
 *
 * @param array $args Proposal data
 * @return ARSOL_PFW_Proposal
 */
function arsol_pfw_create_proposal($args = array()) {
    $proposal = new ARSOL_PFW_Proposal();
    
    foreach ($args as $key => $value) {
        if (is_callable(array($proposal, "set_{$key}"))) {
            $proposal->{"set_{$key}"}($value);
        }
    }
    
    return $proposal;
}

/**
 * Create new request
 *
 * @param array $args Request data
 * @return ARSOL_PFW_Request
 */
function arsol_pfw_create_request($args = array()) {
    $request = new ARSOL_PFW_Request();
    
    foreach ($args as $key => $value) {
        if (is_callable(array($request, "set_{$key}"))) {
            $request->{"set_{$key}"}($value);
        }
    }
    
    return $request;
}
```

## Stage Management

### Stage Transitions

All entities support consistent stage management:

```php
// Get current stage
$current_stage = $project->get_stage();

// Set stage (no save)
$project->set_stage('in-progress');

// Update stage (with hooks and save)
$project->update_stage('completed');

// Get available stages
$stages = $project->get_available_stages();
```

### Stage Hooks

```php
// General stage change hooks
add_action('arsol_pfw_project_stage_changed', 'handle_project_stage_change', 10, 4);
add_action('arsol_pfw_proposal_stage_changed', 'handle_proposal_stage_change', 10, 4);
add_action('arsol_pfw_request_stage_changed', 'handle_request_stage_change', 10, 4);

// Specific transition hooks
add_action('arsol_pfw_project_stage_not_started_to_in_progress', 'project_started', 10, 2);
add_action('arsol_pfw_project_stage_in_progress_to_completed', 'project_completed', 10, 2);
add_action('arsol_pfw_proposal_stage_draft_to_sent', 'proposal_sent', 10, 2);
add_action('arsol_pfw_proposal_stage_sent_to_approved', 'proposal_approved', 10, 2);
```

## Hooks and Filters

### Entity Hooks

```php
// Creation hooks
do_action('arsol_pfw_project_created', $project_id, $project);
do_action('arsol_pfw_proposal_created', $proposal_id, $proposal);
do_action('arsol_pfw_request_created', $request_id, $request);

// Update hooks
do_action('arsol_pfw_project_updated', $project_id, $project);
do_action('arsol_pfw_proposal_updated', $proposal_id, $proposal);
do_action('arsol_pfw_request_updated', $request_id, $request);

// Deletion hooks
do_action('arsol_pfw_project_deleted', $project_id, $project);
do_action('arsol_pfw_proposal_deleted', $proposal_id, $proposal);
do_action('arsol_pfw_request_deleted', $request_id, $request);
```

### Data Filters

```php
// Filter data before saving
add_filter('arsol_pfw_new_project_data', 'modify_project_data');
add_filter('arsol_pfw_new_proposal_data', 'modify_proposal_data');
add_filter('arsol_pfw_new_request_data', 'modify_request_data');

// Filter available stages
add_filter('arsol_pfw_project_available_stages', 'modify_project_stages');
add_filter('arsol_pfw_proposal_available_stages', 'modify_proposal_stages');
add_filter('arsol_pfw_request_available_stages', 'modify_request_stages');
```

## Usage Examples

### Creating Entities

```php
// Create a new project
$project = arsol_pfw_create_project(array(
    'name'        => 'Website Redesign',
    'lead_id'     => 123,
    'customer_id' => 456,
    'start_date'  => '2024-01-01',
    'due_date'    => '2024-03-01',
));
$project->save();

// Create a new proposal
$proposal = arsol_pfw_create_proposal(array(
    'name'         => 'Website Redesign Proposal',
    'project_id'   => $project->get_id(),
    'customer_id'  => 456,
    'costing_type' => 'fixed',
    'total_amount' => 5000,
));
$proposal->save();

// Create a new request
$request = arsol_pfw_create_request(array(
    'name'        => 'Website Redesign Request',
    'customer_id' => 456,
    'budget'      => array('amount' => 5000, 'currency' => 'USD'),
    'deadline'    => '2024-02-15',
));
$request->save();
```

### Reading Entities

```php
// Load existing entities
$project = arsol_pfw_get_project($project_id);
$proposal = arsol_pfw_get_proposal($proposal_id);
$request = arsol_pfw_get_request($request_id);

// Get properties
$project_name = $project->get_name();
$project_stage = $project->get_stage();
$lead_id = $project->get_lead_id();
```

### Updating Entities

```php
// Update properties
$project->set_name('Updated Project Name');
$project->set_stage('in-progress');
$project->save();

// Update with stage transitions
$project->update_stage('completed');
$proposal->approve();
$request->reject('Budget too high');
```

### Querying Entities

```php
// Get projects by stage
$data_store = new ARSOL_PFW_Project_Data_Store();
$active_projects = $data_store->get_projects_by_stage('in-progress');

// Get stage counts
$stage_counts = $data_store->get_stage_counts();

// Get available stages
$stages = $project->get_available_stages();
```

## Migration Guide

### From Old Pattern to New CRUD

**Old Pattern:**
```php
// Old way - scattered calls
$project_id = wp_insert_post(array(
    'post_type'  => 'arsol-pfw-project',
    'post_title' => $name,
));

update_post_meta($project_id, '_arsol_pfw_project_lead_id', $lead_id);
update_post_meta($project_id, '_arsol_pfw_project_customer_id', $customer_id);
wp_set_object_terms($project_id, 'in-progress', 'arsol-pfw-project-stage');
```

**New CRUD Pattern:**
```php
// New way - clean OOP
$project = arsol_pfw_create_project(array(
    'name'        => $name,
    'lead_id'     => $lead_id,
    'customer_id' => $customer_id,
    'stage'       => 'in-progress',
));
$project->save();
```

### Migration Steps

1. **Identify current patterns** in your code
2. **Replace with CRUD methods** using the examples above
3. **Test thoroughly** to ensure data integrity
4. **Remove old helper functions** once migration is complete

## API Reference

### Entity Methods

**Common Methods (All Entities)**
- `get_id()` - Get entity ID
- `get_name()` - Get entity name
- `set_name($name)` - Set entity name
- `get_stage()` - Get current stage
- `set_stage($stage)` - Set stage
- `update_stage($stage)` - Update stage with hooks
- `get_available_stages()` - Get available stages
- `save()` - Save entity
- `delete()` - Delete entity

**Project-Specific Methods**
- `get_lead_id()` / `set_lead_id($id)`
- `get_customer_id()` / `set_customer_id($id)`
- `get_start_date()` / `set_start_date($date)`
- `get_due_date()` / `set_due_date($date)`
- `get_orders()` - Get related WooCommerce orders
- `get_subscriptions()` - Get related WooCommerce subscriptions

**Proposal-Specific Methods**
- `get_project_id()` / `set_project_id($id)`
- `get_request_id()` / `set_request_id($id)`
- `get_costing_type()` / `set_costing_type($type)`
- `get_quotation()` / `set_quotation($quotation)`
- `get_total_amount()` / `set_total_amount($amount)`
- `approve()` - Approve proposal
- `reject($reason)` - Reject proposal

**Request-Specific Methods**
- `get_customer_id()` / `set_customer_id($id)`
- `get_project_id()` / `set_project_id($id)`
- `get_budget()` / `set_budget($budget)`
- `get_deadline()` / `set_deadline($deadline)`
- `get_description()` / `set_description($description)`
- `approve()` - Approve request
- `reject($reason)` - Reject request

### Data Store Methods

**Common Methods (All Data Stores)**
- `create(&$entity)` - Create entity
- `read(&$entity)` - Read entity
- `update(&$entity)` - Update entity
- `delete(&$entity)` - Delete entity
- `get_available_stages()` - Get available stages
- `get_stage_counts()` - Get stage counts

**Query Methods**
- `get_projects_by_stage($stage, $args)`
- `get_proposals_by_stage($stage, $args)`
- `get_requests_by_stage($stage, $args)`

### Factory Functions

- `arsol_pfw_get_project($id)` - Get project by ID
- `arsol_pfw_get_proposal($id)` - Get proposal by ID
- `arsol_pfw_get_request($id)` - Get request by ID
- `arsol_pfw_create_project($args)` - Create new project
- `arsol_pfw_create_proposal($args)` - Create new proposal
- `arsol_pfw_create_request($args)` - Create new request

---

This complete reference provides everything needed to implement and use the CRUD system effectively. The API is designed to be familiar to WooCommerce developers while providing the specific functionality needed for project management workflows. 