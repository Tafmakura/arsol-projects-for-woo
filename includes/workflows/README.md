# Arsol Projects For WooCommerce - Workflows

This directory contains the workflow management system for the Arsol Projects For WooCommerce plugin, organized following WordPress/WooCommerce best practices.

## 📁 Directory Structure

```
includes/workflows/
├── class-arsol-pfw-workflows-setup.php    # Main workflows manager
├── default/                               # Default workflow implementation
│   ├── class-arsol-pfw-workflow-default-setup.php      # Setup & configuration
│   ├── class-arsol-pfw-workflow-default.php            # Main workflow coordinator
│   ├── class-arsol-pfw-workflow-default-transitions.php # Conversions & actions
│   └── class-arsol-pfw-workflow-default-stages.php     # Permissions & transactions
└── README.md                              # This documentation
```

## 🔧 Architecture Overview

### **Main Manager**
- **File**: `class-arsol-pfw-workflows-setup.php`
- **Namespace**: `Arsol_Projects_For_Woo\Setup\Workflows`
- **Purpose**: Initializes and manages all workflow systems

### **Default Workflow**
- **Namespace**: `Arsol_Projects_For_Woo\Workflows\Default`
- **Components**:
  - **Setup**: Configuration and initialization
  - **Workflow**: Main coordination and entry point
  - **Transitions**: Conversion logic and customer actions
  - **Stages**: Permission checking and transaction management

## 🚀 Core Functionality

### **Workflow Transitions**
- **Request → Proposal**: Admin conversion with validation
- **Proposal → Project**: Customer approval or admin conversion
- **Customer Actions**: Approve, reject, cancel operations

### **Stage Management**
- **Permission Checking**: User access validation
- **Transaction Handling**: Rollback and cleanup support
- **Workflow Monitoring**: Progress tracking and stuck workflow detection

### **Safety Features**
- **Concurrent Protection**: Prevents multiple simultaneous conversions
- **Stuck Workflow Detection**: Automatic cleanup of aged workflows
- **Transaction Rollback**: Complete rollback on failure
- **Emergency Cleanup**: Force clear all stuck workflows

## 📋 Key Features

### **Conversion System**
```php
// Request to Proposal conversion
do_action('admin_post_arsol_convert_to_proposal');

// Proposal to Project conversion  
do_action('admin_post_arsol_convert_to_project');
```

### **Customer Actions**
```php
// Customer workflow actions
do_action('admin_post_arsol_approve_proposal');
do_action('admin_post_arsol_reject_proposal');
do_action('admin_post_arsol_cancel_request');
```

### **Workflow Hooks**
```php
// Before validation
do_action('arsol_before_proposal_conversion_validation', $request_id, $data);

// After creation
do_action('arsol_after_proposal_conversion_proposal_created', $proposal_id, $request_id, $data);

// Before redirect
do_action('arsol_before_proposal_conversion_redirect', $proposal_id, $url, $data);
```

## 🔒 Security Features

### **Nonce Verification**
- All workflow actions require valid nonces
- Prevents CSRF attacks

### **Permission Checks**
- User capability validation
- Post ownership verification
- Admin capability fallback

### **Input Sanitization**
- All form inputs sanitized
- SQL injection prevention
- XSS protection

## 🛠️ Usage Examples

### **Initialize Workflows**
```php
// Get workflow manager
$workflows = \Arsol_Projects_For_Woo\Setup\Workflows::get_instance();

// Get default workflow
$default_workflow = $workflows->get_workflow('default');

// Check workflow status
$status = $default_workflow->get_status();
```

### **Check User Permissions**
```php
$stages = \Arsol_Projects_For_Woo\Workflows\Default\Stages::get_instance();
$can_view = $stages->user_can_view_post($user_id, $post_id);
```

### **Manual Workflow Cleanup**
```php
// Cleanup stuck workflows
$stages->cleanup_stuck_workflows(30); // 30 minutes max age

// Emergency cleanup all
$stages->emergency_cleanup_all_stuck_workflows();
```

## 📊 Monitoring & Debugging

### **Workflow Statistics**
```php
$stats = $stages->get_workflow_statistics();
// Returns: active_workflows, completed_workflows, failed_workflows, stuck_workflows
```

### **Workflow History**
```php
$history = $stages->get_workflow_history($post_id);
// Returns: All workflow metadata for a post
```

### **Log Integration**
- Uses `\Arsol_Projects_For_Woo\Woocommerce_Logs` for logging
- Tracks conversions, errors, and cleanup operations
- Provides audit trail for workflow operations

## 🔄 Workflow States

### **Request Stages**
- `pending-review` - Initial state
- `under-review` - Being evaluated
- `approved` - Ready for proposal conversion
- `rejected` - Declined
- `cancelled` - Cancelled by customer

### **Proposal Stages**
- `pending-approval` - Awaiting customer decision
- `approved` - Accepted by customer
- `rejected` - Declined by customer
- `expired` - Expired proposal

### **Project Stages**
- `active` - In progress
- `on-hold` - Temporarily suspended
- `completed` - Finished
- `cancelled` - Cancelled

## 🧹 Maintenance

### **Scheduled Cleanup**
- Hourly cleanup of stuck workflows
- Configurable maximum age (default: 30 minutes)
- Automatic logging of cleanup operations

### **Emergency Procedures**
- Force clear all stuck workflows
- Complete transaction rollback
- Entity deletion and cleanup

## 🎯 Extension Points

### **Custom Workflows**
```php
// Register custom workflow
$workflows->register_workflow('custom', $custom_workflow_instance);

// Check if workflow is active
$is_active = $workflows->is_workflow_active('custom');
```

### **Workflow Filters**
```php
// Modify proposal creation arguments
add_filter('arsol_proposal_conversion_args', function($args, $request_id) {
    // Custom logic here
    return $args;
});

// Modify request creation arguments
add_filter('arsol_request_creation_args', function($args, $data) {
    // Custom logic here
    return $args;
});
```

## 🔧 Configuration

### **Workflow Settings**
- Stuck workflow detection threshold
- Cleanup schedule frequency
- Transaction timeout limits
- Rollback strategies

### **Debugging Options**
- Detailed workflow logging
- Transaction tracking
- Performance monitoring
- Error reporting

---

This workflows system provides a robust, secure, and extensible foundation for managing project workflows in the Arsol Projects For WooCommerce plugin, following WordPress and WooCommerce best practices. 