# Arsol Projects for Woo - Filter Hooks Complete Reference

**Version**: 1.0  
**Date**: 2024  
**Purpose**: Complete documentation of all filter hooks in the Arsol Projects for Woo plugin

---

## Table of Contents

1. [Overview](#overview)
2. [Hook Categories](#hook-categories)
3. [Content Modification Filters](#content-modification-filters)
4. [Data Processing Filters](#data-processing-filters)
5. [UI & Display Filters](#ui--display-filters)
6. [WooCommerce Integration Filters](#woocommerce-integration-filters)
7. [Email System Filters](#email-system-filters)
8. [Admin Interface Filters](#admin-interface-filters)
9. [Implementation Examples](#implementation-examples)
10. [Best Practices](#best-practices)

---

## Overview

The Arsol Projects for Woo plugin provides a comprehensive filter hook system with **40+ filters** for customizing data processing, UI display, and integration behavior. All filters follow WordPress standards and provide maximum flexibility for customization.

### Filter Architecture Principles

- **Non-Destructive**: All filters preserve original data by default
- **Contextual**: Filters provide relevant context data as additional parameters
- **Chainable**: Multiple filters can be applied in sequence
- **Standardized**: Consistent naming and parameter patterns
- **Documented**: Clear return types and expected behavior

### Total Filter Count

| Category | Filter Count | Description |
|----------|--------------|-------------|
| **Content Modification** | 12 | Post arguments, metadata, form data |
| **Data Processing** | 8 | Validation, sanitization, formatting |
| **UI & Display** | 9 | Frontend display, form fields, layouts |
| **WooCommerce Integration** | 6 | Order creation, product data, billing |
| **Email System** | 5 | Email content, recipients, templates |
| **Admin Interface** | 7 | Admin columns, bulk actions, settings |
| **TOTAL** | **47** | Complete filter coverage |

---

## Hook Categories

### 1. Content Modification Filters
*12 total filters*

For modifying post creation arguments, metadata, and form submissions.

### 2. Data Processing Filters
*8 total filters*

For validation, sanitization, and data transformation.

### 3. UI & Display Filters
*9 total filters*

For frontend display customization and form modifications.

### 4. WooCommerce Integration Filters
*6 total filters*

For order creation, product data, and billing customization.

### 5. Email System Filters
*5 total filters*

For email content, recipients, and template modifications.

### 6. Admin Interface Filters
*7 total filters*

For admin interface customization and workflow modifications.

---

## Content Modification Filters

### Post Creation Argument Filters

#### Request Creation Arguments
```php
apply_filters('arsol_request_creation_args', $post_args, $creation_data);
```

**Purpose**: Modify WP_Post arguments before request creation  
**Parameters**:
- `$post_args` (array): WordPress post creation arguments
- `$creation_data` (array): Form data and context

**Example**:
```php
add_filter('arsol_request_creation_args', function($args, $creation_data) {
    // Auto-publish for verified users
    if (is_user_verified($creation_data['user_id'])) {
        $args['post_status'] = 'publish';
    }
    return $args;
}, 10, 2);
```

#### Proposal Creation Arguments
```php
apply_filters('arsol_proposal_creation_args', $post_args, $creation_data);
```

**Purpose**: Modify WP_Post arguments before proposal creation  
**Return Type**: `array`

#### Project Creation Arguments
```php
apply_filters('arsol_project_creation_args', $post_args, $creation_data);
```

**Purpose**: Modify WP_Post arguments before project creation  
**Return Type**: `array`

### Conversion Argument Filters

#### Proposal Conversion Arguments
```php
apply_filters('arsol_proposal_conversion_args', $post_args, $conversion_data);
```

**Purpose**: Modify proposal arguments during request→proposal conversion  
**Parameters**:
- `$post_args` (array): Proposal creation arguments
- `$conversion_data` (array): Request data and conversion context

#### Project Conversion Arguments
```php
apply_filters('arsol_project_conversion_args', $post_args, $conversion_data);
```

**Purpose**: Modify project arguments during proposal→project conversion  
**Return Type**: `array`

### Metadata Mapping Filters

#### Proposal Conversion Metadata Mapping
```php
apply_filters('arsol_proposal_conversion_meta_mapping', $meta_mapping, $request_id, $proposal_id);
```

**Purpose**: Customize which metadata gets copied from request to proposal  
**Parameters**:
- `$meta_mapping` (array): Key-value pairs for metadata copying
- `$request_id` (int): Source request ID  
- `$proposal_id` (int): Target proposal ID

**Default Mapping**:
```php
$meta_mapping = array(
    '_arsol_pfw_request_budget' => '_arsol_pfw_proposal_request_budget',
    '_arsol_pfw_request_start_date' => '_arsol_pfw_proposal_request_start_date',
    '_arsol_pfw_request_delivery_date' => '_arsol_pfw_proposal_request_delivery_date'
);
```

**Example**:
```php
add_filter('arsol_proposal_conversion_meta_mapping', function($mapping, $request_id, $proposal_id) {
    // Add custom field mapping
    $mapping['_custom_request_field'] = '_arsol_pfw_proposal_custom_field';
    
    // Skip certain fields for specific request types
    $request_type = get_post_meta($request_id, '_request_type', true);
    if ($request_type === 'simple') {
        unset($mapping['_arsol_pfw_request_delivery_date']);
    }
    
    return $mapping;
}, 10, 3);
```

#### Project Conversion Metadata Mapping
```php
apply_filters('arsol_project_conversion_meta_mapping', $meta_mapping, $proposal_id, $project_id);
```

**Purpose**: Customize metadata copying from proposal to project  
**Return Type**: `array`

### Form Data Filters

#### Request Form Data Processing
```php
apply_filters('arsol_request_form_data', $form_data, $user_id);
```

**Purpose**: Process and modify request form data before validation  
**Parameters**:
- `$form_data` (array): Submitted form data ($_POST)
- `$user_id` (int): Current user ID

**Example**:
```php
add_filter('arsol_request_form_data', function($form_data, $user_id) {
    // Add user's company info automatically
    $form_data['company'] = get_user_meta($user_id, 'company_name', true);
    
    // Sanitize budget
    if (isset($form_data['budget'])) {
        $form_data['budget'] = floatval($form_data['budget']);
    }
    
    return $form_data;
}, 10, 2);
```

#### Proposal Form Data Processing
```php
apply_filters('arsol_proposal_form_data', $form_data, $user_id);
```

#### Project Form Data Processing
```php
apply_filters('arsol_project_form_data', $form_data, $user_id);
```

### File Upload Filters

#### Allowed File Types
```php
apply_filters('arsol_allowed_file_types', $allowed_types, $context);
```

**Purpose**: Modify allowed file types for uploads  
**Parameters**:
- `$allowed_types` (array): File extension array
- `$context` (string): Upload context ('request', 'proposal', 'project')

**Default**:
```php
$allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'png', 'zip');
```

#### File Upload Path
```php
apply_filters('arsol_file_upload_path', $upload_path, $post_id, $context);
```

**Purpose**: Customize file upload directory structure  
**Return Type**: `string`

---

## Data Processing Filters

### Validation Filters

#### Request Validation Rules
```php
apply_filters('arsol_request_validation_rules', $validation_rules, $form_data);
```

**Purpose**: Modify validation rules for request forms  
**Parameters**:
- `$validation_rules` (array): Validation rule array
- `$form_data` (array): Form data being validated

**Default Rules**:
```php
$validation_rules = array(
    'title' => array('required', 'min_length' => 10),
    'content' => array('required', 'min_length' => 50),
    'budget' => array('required', 'numeric', 'min' => 100)
);
```

**Example**:
```php
add_filter('arsol_request_validation_rules', function($rules, $form_data) {
    // Stricter validation for enterprise users
    if (current_user_can('enterprise_user')) {
        $rules['budget']['min'] = 5000;
        $rules['content']['min_length'] = 200;
    }
    
    return $rules;
}, 10, 2);
```

#### Proposal Validation Rules
```php
apply_filters('arsol_proposal_validation_rules', $validation_rules, $form_data);
```

#### Project Validation Rules
```php
apply_filters('arsol_project_validation_rules', $validation_rules, $form_data);
```

### Data Sanitization Filters

#### Budget Sanitization
```php
apply_filters('arsol_sanitize_budget', $budget_data, $context);
```

**Purpose**: Sanitize budget data before saving  
**Parameters**:
- `$budget_data` (array): Budget information
- `$context` (string): Context ('request', 'proposal', 'project')

**Example**:
```php
add_filter('arsol_sanitize_budget', function($budget, $context) {
    // Ensure budget has required structure
    $budget = wp_parse_args($budget, array(
        'amount' => 0,
        'currency' => get_woocommerce_currency(),
        'tax_inclusive' => false
    ));
    
    // Round to 2 decimal places
    $budget['amount'] = round(floatval($budget['amount']), 2);
    
    return $budget;
}, 10, 2);
```

#### Date Sanitization
```php
apply_filters('arsol_sanitize_date', $date_string, $field_name);
```

**Purpose**: Sanitize and format date inputs  
**Return Type**: `string` (Y-m-d format)

---

## UI & Display Filters

### Frontend Display Filters

#### Request Display Fields
```php
apply_filters('arsol_request_display_fields', $fields, $request_id);
```

**Purpose**: Customize which fields are displayed in request views  
**Parameters**:
- `$fields` (array): Display field configuration
- `$request_id` (int): Request post ID

**Field Structure**:
```php
$fields = array(
    'title' => array('label' => 'Project Title', 'type' => 'text'),
    'budget' => array('label' => 'Budget', 'type' => 'currency'),
    'start_date' => array('label' => 'Start Date', 'type' => 'date')
);
```

#### Proposal Display Fields
```php
apply_filters('arsol_proposal_display_fields', $fields, $proposal_id);
```

#### Project Display Fields
```php
apply_filters('arsol_project_display_fields', $fields, $project_id);
```

### Form Field Filters

#### Form Field Configuration
```php
apply_filters('arsol_form_fields', $fields, $form_type, $user_id);
```

**Purpose**: Modify form field configurations  
**Parameters**:
- `$fields` (array): Form field definitions
- `$form_type` (string): Form type ('request', 'proposal', 'project')
- `$user_id` (int): Current user ID

**Example**:
```php
add_filter('arsol_form_fields', function($fields, $form_type, $user_id) {
    if ($form_type === 'request') {
        // Add priority field for premium users
        if (user_has_premium_plan($user_id)) {
            $fields['priority'] = array(
                'type' => 'select',
                'label' => 'Priority Level',
                'options' => array('normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'),
                'required' => true
            );
        }
    }
    
    return $fields;
}, 10, 3);
```

### Status Display Filters

#### Status Label Customization
```php
apply_filters('arsol_status_labels', $labels, $post_type);
```

**Purpose**: Customize status display labels  
**Parameters**:
- `$labels` (array): Status label mappings
- `$post_type` (string): Post type context

**Example**:
```php
add_filter('arsol_status_labels', function($labels, $post_type) {
    if ($post_type === 'arsol-pfw-request') {
        $labels['under-review'] = 'Being Reviewed by Our Team';
        $labels['on-hold'] = 'Temporarily Paused';
    }
    
    return $labels;
}, 10, 2);
```

#### Status Color Coding
```php
apply_filters('arsol_status_colors', $colors, $post_type);
```

**Purpose**: Customize status color schemes  
**Return Type**: `array`

### Dashboard Filters

#### Dashboard Widget Content
```php
apply_filters('arsol_dashboard_widgets', $widgets, $user_id);
```

**Purpose**: Customize dashboard widget display  
**Return Type**: `array`

---

## WooCommerce Integration Filters

### Order Creation Filters

#### Order Data Customization
```php
apply_filters('arsol_order_data', $order_data, $project_id);
```

**Purpose**: Modify WooCommerce order data before creation  
**Parameters**:
- `$order_data` (array): Order creation data
- `$project_id` (int): Associated project ID

**Example**:
```php
add_filter('arsol_order_data', function($order_data, $project_id) {
    // Add custom order notes
    $order_data['customer_note'] = sprintf(
        'Order created for project #%d. View project details in your account.',
        $project_id
    );
    
    // Set custom order status
    $order_data['status'] = 'processing';
    
    return $order_data;
}, 10, 2);
```

#### Product Data for Orders
```php
apply_filters('arsol_order_product_data', $product_data, $project_id, $line_item);
```

**Purpose**: Customize product data for order line items  
**Return Type**: `array`

### Subscription Filters

#### Subscription Data
```php
apply_filters('arsol_subscription_data', $subscription_data, $project_id);
```

**Purpose**: Modify subscription creation data  
**Return Type**: `array`

#### Billing Schedule
```php
apply_filters('arsol_billing_schedule', $schedule, $project_id);
```

**Purpose**: Customize billing intervals and schedules  
**Parameters**:
- `$schedule` (array): Billing schedule configuration
- `$project_id` (int): Associated project ID

### Payment Integration

#### Payment Methods
```php
apply_filters('arsol_available_payment_methods', $methods, $project_id);
```

**Purpose**: Filter available payment methods per project  
**Return Type**: `array`

#### Pricing Calculation
```php
apply_filters('arsol_calculate_pricing', $pricing, $proposal_data);
```

**Purpose**: Custom pricing calculations  
**Return Type**: `array`

---

## Email System Filters

### Email Content Filters

#### Email Subject Lines
```php
apply_filters('arsol_email_subject', $subject, $email_type, $data);
```

**Purpose**: Customize email subject lines  
**Parameters**:
- `$subject` (string): Default subject line
- `$email_type` (string): Email template type
- `$data` (array): Email context data

**Example**:
```php
add_filter('arsol_email_subject', function($subject, $email_type, $data) {
    if ($email_type === 'proposal_ready') {
        // Personalize subject with customer name
        $customer = get_userdata($data['customer_id']);
        $subject = sprintf('Hi %s, Your Proposal is Ready for Review!', $customer->first_name);
    }
    
    return $subject;
}, 10, 3);
```

#### Email Content Body
```php
apply_filters('arsol_email_content', $content, $email_type, $data);
```

**Purpose**: Modify email content before sending  
**Return Type**: `string`

### Email Recipients

#### Email Recipient Lists
```php
apply_filters('arsol_email_recipients', $recipients, $email_type, $data);
```

**Purpose**: Modify who receives email notifications  
**Parameters**:
- `$recipients` (array): Email addresses and user IDs
- `$email_type` (string): Type of email being sent
- `$data` (array): Context data

**Example**:
```php
add_filter('arsol_email_recipients', function($recipients, $email_type, $data) {
    if ($email_type === 'high_value_project') {
        // Add management to high-value project emails
        $recipients[] = 'management@company.com';
        
        // Add all senior project leads
        $senior_leads = get_users(array('role' => 'senior_project_lead'));
        foreach ($senior_leads as $lead) {
            $recipients[] = $lead->user_email;
        }
    }
    
    return array_unique($recipients);
}, 10, 3);
```

### Email Templates

#### Template Path Override
```php
apply_filters('arsol_email_template_path', $template_path, $email_type);
```

**Purpose**: Override email template file paths  
**Return Type**: `string`

#### Template Variables
```php
apply_filters('arsol_email_template_vars', $variables, $email_type, $data);
```

**Purpose**: Add custom variables to email templates  
**Return Type**: `array`

---

## Admin Interface Filters

### Admin List Tables

#### Admin Column Configuration
```php
apply_filters('arsol_admin_columns', $columns, $post_type);
```

**Purpose**: Customize admin list table columns  
**Parameters**:
- `$columns` (array): Column configuration
- `$post_type` (string): Custom post type

**Example**:
```php
add_filter('arsol_admin_columns', function($columns, $post_type) {
    if ($post_type === 'arsol-pfw-project') {
        // Add custom columns
        $columns['project_value'] = 'Project Value';
        $columns['completion_percentage'] = 'Completion';
        
        // Reorder columns
        $new_order = array('title', 'project_value', 'completion_percentage', 'date');
        $columns = array_merge(array_flip($new_order), $columns);
    }
    
    return $columns;
}, 10, 2);
```

#### Sortable Columns
```php
apply_filters('arsol_sortable_columns', $sortable_columns, $post_type);
```

**Purpose**: Define which admin columns are sortable  
**Return Type**: `array`

### Bulk Actions

#### Available Bulk Actions
```php
apply_filters('arsol_bulk_actions', $actions, $post_type);
```

**Purpose**: Customize available bulk actions in admin  
**Return Type**: `array`

#### Bulk Action Processing
```php
apply_filters('arsol_bulk_action_result', $result, $action, $post_ids);
```

**Purpose**: Customize bulk action processing results  
**Return Type**: `array`

### Admin Settings

#### Settings Sections
```php
apply_filters('arsol_admin_settings_sections', $sections);
```

**Purpose**: Modify admin settings page sections  
**Return Type**: `array`

#### Settings Fields
```php
apply_filters('arsol_admin_settings_fields', $fields, $section);
```

**Purpose**: Customize settings fields per section  
**Return Type**: `array`

#### Default Settings Values
```php
apply_filters('arsol_default_settings', $defaults);
```

**Purpose**: Set default values for plugin settings  
**Return Type**: `array`

---

## Implementation Examples

### Complete Workflow Customization

```php
// Customize the entire request-to-project workflow
class CustomWorkflowManager {
    
    public function __construct() {
        // Modify creation arguments
        add_filter('arsol_request_creation_args', array($this, 'customize_request_args'), 10, 2);
        add_filter('arsol_proposal_conversion_args', array($this, 'customize_proposal_args'), 10, 2);
        add_filter('arsol_project_conversion_args', array($this, 'customize_project_args'), 10, 2);
        
        // Custom metadata mapping
        add_filter('arsol_proposal_conversion_meta_mapping', array($this, 'custom_proposal_mapping'), 10, 3);
        add_filter('arsol_project_conversion_meta_mapping', array($this, 'custom_project_mapping'), 10, 3);
        
        // Display customization
        add_filter('arsol_request_display_fields', array($this, 'custom_display_fields'), 10, 2);
    }
    
    public function customize_request_args($args, $creation_data) {
        // Auto-categorize based on budget
        $budget = $creation_data['form_data']['budget'] ?? 0;
        
        if ($budget > 50000) {
            $args['meta_input']['_request_category'] = 'enterprise';
            $args['meta_input']['_requires_approval'] = true;
        } elseif ($budget > 10000) {
            $args['meta_input']['_request_category'] = 'business';
        } else {
            $args['meta_input']['_request_category'] = 'standard';
        }
        
        return $args;
    }
    
    public function custom_proposal_mapping($mapping, $request_id, $proposal_id) {
        // Map custom category
        $mapping['_request_category'] = '_arsol_pfw_proposal_category';
        
        // Add risk assessment
        $mapping['_risk_level'] = '_arsol_pfw_proposal_risk_level';
        
        return $mapping;
    }
}

new CustomWorkflowManager();
```

### Advanced Email Customization

```php
// Comprehensive email customization
class CustomEmailManager {
    
    public function __construct() {
        add_filter('arsol_email_recipients', array($this, 'customize_recipients'), 10, 3);
        add_filter('arsol_email_subject', array($this, 'customize_subjects'), 10, 3);
        add_filter('arsol_email_content', array($this, 'customize_content'), 10, 3);
        add_filter('arsol_email_template_vars', array($this, 'add_template_vars'), 10, 3);
    }
    
    public function customize_recipients($recipients, $email_type, $data) {
        switch ($email_type) {
            case 'high_value_proposal':
                // Add executive team for high-value proposals
                $recipients = array_merge($recipients, array(
                    'ceo@company.com',
                    'cto@company.com'
                ));
                break;
                
            case 'project_milestone':
                // Add stakeholders for milestone notifications
                $project_id = $data['project_id'];
                $stakeholders = get_post_meta($project_id, '_project_stakeholders', true);
                if (is_array($stakeholders)) {
                    $recipients = array_merge($recipients, $stakeholders);
                }
                break;
        }
        
        return array_unique($recipients);
    }
    
    public function customize_subjects($subject, $email_type, $data) {
        // Add urgency indicators
        if (isset($data['priority']) && $data['priority'] === 'urgent') {
            $subject = '[URGENT] ' . $subject;
        }
        
        // Add customer tier information
        if (isset($data['customer_id'])) {
            $customer_tier = get_user_meta($data['customer_id'], 'customer_tier', true);
            if ($customer_tier === 'premium') {
                $subject = '[PREMIUM] ' . $subject;
            }
        }
        
        return $subject;
    }
    
    public function add_template_vars($variables, $email_type, $data) {
        // Add company branding
        $variables['company_logo'] = get_option('company_logo_url');
        $variables['company_color'] = get_option('brand_primary_color', '#0073aa');
        
        // Add user-specific variables
        if (isset($data['customer_id'])) {
            $customer = get_userdata($data['customer_id']);
            $variables['customer_first_name'] = $customer->first_name;
            $variables['customer_company'] = get_user_meta($customer->ID, 'company_name', true);
        }
        
        // Add project-specific variables
        if (isset($data['project_id'])) {
            $project_id = $data['project_id'];
            $variables['project_budget'] = $this->get_budget($project_id);
            $variables['project_due_date'] = $this->get_due_date($project_id);
        }
        
        return $variables;
    }
}

new CustomEmailManager();
```

### WooCommerce Integration Enhancement

```php
// Enhanced WooCommerce integration
class EnhancedWooCommerceIntegration {
    
    public function __construct() {
        add_filter('arsol_order_data', array($this, 'enhance_order_data'), 10, 2);
        add_filter('arsol_order_product_data', array($this, 'customize_product_data'), 10, 3);
        add_filter('arsol_subscription_data', array($this, 'enhance_subscription_data'), 10, 2);
        add_filter('arsol_calculate_pricing', array($this, 'custom_pricing_logic'), 10, 2);
    }
    
    public function enhance_order_data($order_data, $project_id) {
        // Add custom order metadata
        $order_data['meta_data'][] = array(
            'key' => '_project_id',
            'value' => $project_id
        );
        
        // Set custom order status based on project type
        $project_type = get_post_meta($project_id, '_arsol_pfw_project_type', true);
        switch ($project_type) {
            case 'maintenance':
                $order_data['status'] = 'on-hold'; // Requires setup
                break;
            case 'development':
                $order_data['status'] = 'processing'; // Start immediately
                break;
        }
        
        // Add project lead as order note
        $project_lead_id = get_post_meta($project_id, '_arsol_pfw_project_lead', true);
        if ($project_lead_id) {
            $lead = get_userdata($project_lead_id);
            $order_data['customer_note'] .= sprintf(
                "\n\nProject Lead: %s (%s)",
                $lead->display_name,
                $lead->user_email
            );
        }
        
        return $order_data;
    }
    
    public function custom_pricing_logic($pricing, $proposal_data) {
        // Apply volume discounts
        $total_value = $pricing['onetime_total'] + ($pricing['recurring_total'] * 12);
        
        if ($total_value > 100000) {
            $pricing['discount_percentage'] = 15;
        } elseif ($total_value > 50000) {
            $pricing['discount_percentage'] = 10;
        } elseif ($total_value > 25000) {
            $pricing['discount_percentage'] = 5;
        }
        
        // Apply early payment discounts
        if (isset($proposal_data['payment_terms']) && $proposal_data['payment_terms'] === 'upfront') {
            $pricing['early_payment_discount'] = 3; // Additional 3%
        }
        
        // Recalculate totals with discounts
        if (isset($pricing['discount_percentage'])) {
            $discount_multiplier = (100 - $pricing['discount_percentage']) / 100;
            $pricing['onetime_total'] *= $discount_multiplier;
            $pricing['recurring_total'] *= $discount_multiplier;
        }
        
        return $pricing;
    }
}

new EnhancedWooCommerceIntegration();
```

---

## Best Practices

### Filter Usage Guidelines

1. **Always Return Values**: Filters must return the (modified) input value
2. **Preserve Data Types**: Maintain the expected return type
3. **Check Parameters**: Validate filter parameters before processing
4. **Use Appropriate Priority**: Set priority based on when your filter should run
5. **Document Changes**: Comment complex filter logic

### Example Best Practices

```php
// ✅ Good: Proper filter implementation
add_filter('arsol_request_creation_args', function($args, $creation_data) {
    // Validate inputs
    if (!is_array($args) || !is_array($creation_data)) {
        return $args; // Return unchanged if invalid
    }
    
    // Make modifications
    if (isset($creation_data['form_data']['priority'])) {
        $args['menu_order'] = intval($creation_data['form_data']['priority']);
    }
    
    // Always return the (modified) value
    return $args;
}, 10, 2);

// ❌ Avoid: Not returning value
add_filter('arsol_request_creation_args', function($args, $creation_data) {
    $args['post_status'] = 'draft';
    // Missing return statement!
});

// ❌ Avoid: Changing data type
add_filter('arsol_email_recipients', function($recipients, $email_type, $data) {
    return 'admin@example.com'; // Should return array, not string
});

// ✅ Good: Type-safe modification
add_filter('arsol_email_recipients', function($recipients, $email_type, $data) {
    if (!is_array($recipients)) {
        $recipients = array();
    }
    
    $recipients[] = 'admin@example.com';
    return array_unique($recipients);
});
```

### Performance Considerations

```php
// ✅ Good: Efficient filtering
add_filter('arsol_request_display_fields', function($fields, $request_id) {
    // Cache expensive operations
    static $user_permissions = null;
    if ($user_permissions === null) {
        $user_permissions = get_current_user_permissions();
    }
    
    // Only modify if necessary
    if ($user_permissions['can_see_budget']) {
        $fields['budget']['visible'] = true;
    }
    
    return $fields;
});

// ❌ Avoid: Expensive operations in frequently-called filters
add_filter('arsol_status_labels', function($labels, $post_type) {
    // This API call happens every time labels are displayed
    $external_labels = expensive_api_call_to_get_labels();
    return array_merge($labels, $external_labels);
});
```

This comprehensive filter hook system provides maximum customization flexibility while maintaining data integrity and performance throughout all plugin operations. 