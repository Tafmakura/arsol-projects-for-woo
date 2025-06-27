# Sidebar System Complete Reference

The Arsol Projects for Woo plugin provides a flexible, extensible sidebar system for displaying project information, interactive forms, and action buttons. The system uses WordPress's action and filter hooks with filterable arrays, following WordPress/WooCommerce best practices.

## Overview

The sidebar system consists of three main components:

1. **Metadata Display** - Shows project information (budget, dates, status, etc.)
2. **Unified Form** - Single form with filterable fields for user input
3. **Actions** - Direct action hooks for outputting buttons and links

## Architecture

### Template Structure

All sidebar templates use the same simple structure:

```php
<div class="project-sidebar sidebar-{type}">
    <?php
    // Metadata section
    do_action('arsol_pfw_sidebar_meta', $post_type, $current_status, $post_id);
    
    // Form section
    do_action('arsol_pfw_sidebar_form', $post_type, $current_status, $post_id);
    
    // Actions section
    do_action('arsol_pfw_sidebar_actions', $post_type, $current_status, $post_id);
    ?>
</div>
```

### Classes

- **`Frontend_Template_Sidebar_Meta`** - Handles metadata display
- **`Frontend_Template_Sidebar_Fields`** - Handles unified form with filterable fields
- **`Frontend_Template_Sidebar_Actions`** - Handles actions using direct action hooks

## 1. Metadata System

### Hook: `arsol_pfw_sidebar_meta`

Displays project metadata using filterable arrays.

**Parameters:**
- `$post_type` (string) - Post type: 'active', 'proposal', 'request'
- `$current_status` (string) - Current status slug
- `$post_id` (int) - Post ID

### Filter: `arsol_pfw_sidebar_metadata`

Filters the metadata array before display.

**Parameters:**
- `$metadata` (array) - Array of metadata items
- `$post_type` (string) - Post type
- `$current_status` (string) - Current status
- `$post_id` (int) - Post ID

### Metadata Item Structure

```php
$metadata['key'] = array(
    'label' => __('Display Label', 'arsol-pfw'),
    'value' => 'The actual value',
    'type' => 'currency', // See supported types below
    'class' => 'custom-css-class',
    'format' => 'F j, Y', // For date types
    'suffix' => 'weeks', // For text types
    'show_if' => array('status' => array('active', 'completed')),
    'url' => 'https://example.com', // For link types
    'target' => '_blank' // For link types
);
```

### Supported Metadata Types

| Type | Description | Example Value |
|------|-------------|---------------|
| `text` | Plain text display | `"Client Name"` |
| `currency` | Formatted currency | `1500.00` |
| `currency_array` | Currency range | `array('min' => 1000, 'max' => 2000)` |
| `date` | Formatted date | `"2024-01-15"` or timestamp |
| `badge` | Styled status badge | `"active"` |
| `link` | Clickable link | `"View Details"` |
| `list` | Array as bulleted list | `array('Item 1', 'Item 2')` |

### Example: Adding Custom Metadata

```php
add_filter('arsol_pfw_sidebar_metadata', function($metadata, $post_type, $status, $post_id) {
    if ($post_type === 'proposal') {
        $metadata['custom_field'] = array(
            'label' => __('Custom Field', 'my-plugin'),
            'value' => get_post_meta($post_id, 'my_custom_field', true),
            'type' => 'text',
            'show_if' => array('status' => array('sent', 'pending-approval'))
        );
    }
    return $metadata;
}, 10, 4);
```

## 2. Form System

### Hook: `arsol_pfw_sidebar_form`

Displays a unified form with filterable fields and submit button.

**Parameters:**
- `$post_type` (string) - Post type: 'active', 'proposal', 'request'
- `$current_status` (string) - Current status slug
- `$post_id` (int) - Post ID

### Filter: `arsol_pfw_form_fields`

Filters the form fields array before rendering.

**Parameters:**
- `$fields` (array) - Array of field configurations
- `$post_type` (string) - Post type
- `$current_status` (string) - Current status
- `$post_id` (int) - Post ID

### Filter: `arsol_pfw_submit_button`

Filters the submit button configuration.

**Parameters:**
- `$submit_button` (array) - Submit button configuration
- `$post_type` (string) - Post type
- `$current_status` (string) - Current status
- `$post_id` (int) - Post ID

### Filter: `arsol_pfw_has_form_fields`

Controls whether the form is displayed (useful for conditional forms).

**Parameters:**
- `$has_fields` (bool) - Whether form has fields
- `$post_type` (string) - Post type
- `$current_status` (string) - Current status
- `$post_id` (int) - Post ID

### Field Configuration Structure

```php
$fields['field_key'] = array(
    'type' => 'select', // See supported types below
    'label' => __('Field Label', 'arsol-pfw'),
    'placeholder' => __('Placeholder text', 'arsol-pfw'),
    'required' => true,
    'class' => 'custom-css-class',
    'options' => array( // For select/radio types
        'value1' => __('Option 1', 'arsol-pfw'),
        'value2' => __('Option 2', 'arsol-pfw')
    ),
    'rows' => 4, // For textarea type
    'show_if' => array('status' => array('pending', 'under-review'))
);
```

### Supported Field Types

| Type | Description | Additional Options |
|------|-------------|-------------------|
| `text` | Single line text input | `placeholder` |
| `textarea` | Multi-line text input | `placeholder`, `rows` |
| `select` | Dropdown selection | `options` |
| `radio` | Radio button group | `options` |
| `checkbox` | Single checkbox | - |

### Submit Button Configuration

```php
$submit_button = array(
    'label' => __('Submit Decision', 'arsol-pfw'),
    'class' => 'button button-primary'
);
```

### Example: Adding Custom Form Fields

```php
add_filter('arsol_pfw_form_fields', function($fields, $post_type, $status, $post_id) {
    if ($post_type === 'request' && $status === 'pending') {
        $fields['priority'] = array(
            'type' => 'select',
            'label' => __('Priority Level', 'my-plugin'),
            'options' => array(
                '' => __('Select priority...', 'my-plugin'),
                'low' => __('Low', 'my-plugin'),
                'medium' => __('Medium', 'my-plugin'),
                'high' => __('High', 'my-plugin')
            ),
            'required' => true
        );
        
        $fields['notes'] = array(
            'type' => 'textarea',
            'label' => __('Additional Notes', 'my-plugin'),
            'placeholder' => __('Add any additional notes...', 'my-plugin'),
            'rows' => 3,
            'required' => false
        );
    }
    return $fields;
}, 10, 4);
```

### Example: Customizing Submit Button

```php
add_filter('arsol_pfw_submit_button', function($button, $post_type, $status, $post_id) {
    if ($post_type === 'proposal' && $status === 'pending-approval') {
        return array(
            'label' => __('Approve & Continue', 'my-plugin'),
            'class' => 'button button-primary large-button'
        );
    }
    return $button;
}, 10, 4);
```

### Form Processing Hook: `arsol_pfw_process_sidebar_form`

Handle form submissions with this action hook.

**Parameters:**
- `$post_id` (int) - Post ID
- `$post_type` (string) - Post type
- `$current_status` (string) - Current status
- `$form_data` (array) - Submitted form data ($_POST)

### Example: Processing Form Submissions

```php
add_action('arsol_pfw_process_sidebar_form', function($post_id, $post_type, $current_status, $form_data) {
    if ($post_type === 'proposal' && isset($form_data['agree_terms'])) {
        // Process proposal approval
        if ($form_data['agree_terms'] === '1') {
            // Update proposal status
            wp_set_post_terms($post_id, 'accepted', 'arsol-proposal-status');
            
            // Save approval notes if provided
            if (!empty($form_data['approval_notes'])) {
                update_post_meta($post_id, '_arsol_approval_notes', sanitize_textarea_field($form_data['approval_notes']));
            }
            
            // Trigger any additional actions
            do_action('arsol_proposal_approved', $post_id, $form_data);
        }
    }
}, 10, 4);
```

## 3. Actions System

### Hook: `arsol_pfw_sidebar_actions`

Displays the actions container and calls `arsol_pfw_add_sidebar_actions`.

**Parameters:**
- `$post_type` (string) - Post type: 'active', 'proposal', 'request'
- `$current_status` (string) - Current status slug
- `$post_id` (int) - Post ID

### Hook: `arsol_pfw_add_sidebar_actions`

This is where you add your custom action buttons by echoing HTML directly.

**Parameters:**
- `$post_type` (string) - Post type
- `$current_status` (string) - Current status
- `$post_id` (int) - Post ID

### Example: Adding Custom Actions

```php
add_action('arsol_pfw_add_sidebar_actions', function($post_type, $status, $post_id) {
    if ($post_type === 'active' && $status === 'completed') {
        $invoice_url = wp_nonce_url(
            add_query_arg(array('action' => 'generate_invoice', 'project_id' => $post_id)),
            'generate_invoice_' . $post_id
        );
        
        echo '<div class="action-item action-generate-invoice">';
        echo '<a href="' . esc_url($invoice_url) . '" class="button button-primary" ';
        echo 'onclick="return confirm(\'' . esc_js__('Generate invoice for this project?', 'my-plugin') . '\')">';
        echo '<span class="dashicons dashicons-media-spreadsheet"></span> ';
        echo esc_html__('Generate Invoice', 'my-plugin');
        echo '</a>';
        echo '</div>';
    }
    
    // Add custom client portal link for active projects
    if ($post_type === 'active') {
        echo '<div class="action-item action-client-portal">';
        echo '<a href="' . esc_url(home_url('/client-portal/?project=' . $post_id)) . '" class="button secondary-button" target="_blank">';
        echo '<span class="dashicons dashicons-admin-users"></span> ';
        echo esc_html__('Client Portal', 'my-plugin');
        echo '</a>';
        echo '</div>';
    }
}, 10, 3);
```

### Example: Conditional Actions by Status

```php
add_action('arsol_pfw_add_sidebar_actions', function($post_type, $status, $post_id) {
    if ($post_type === 'proposal') {
        // Show different actions based on status
        switch ($status) {
            case 'draft':
                echo '<div class="action-item">';
                echo '<a href="' . esc_url(get_edit_post_link($post_id)) . '" class="button button-primary">';
                echo esc_html__('Continue Editing', 'my-plugin');
                echo '</a>';
                echo '</div>';
                break;
                
            case 'sent':
                echo '<div class="action-item">';
                echo '<a href="#" class="button secondary-button" id="send-reminder">';
                echo esc_html__('Send Reminder', 'my-plugin');
                echo '</a>';
                echo '</div>';
                break;
                
            case 'accepted':
                $project_url = wp_nonce_url(
                    add_query_arg(array('action' => 'create_project_from_proposal', 'proposal_id' => $post_id)),
                    'create_project_' . $post_id
                );
                
                echo '<div class="action-item">';
                echo '<a href="' . esc_url($project_url) . '" class="button button-primary">';
                echo esc_html__('Create Project', 'my-plugin');
                echo '</a>';
                echo '</div>';
                break;
        }
    }
}, 15, 3);
```

### Example: Adding JavaScript-Powered Actions

```php
add_action('arsol_pfw_add_sidebar_actions', function($post_type, $status, $post_id) {
    if ($post_type === 'request' && $status === 'pending') {
        // Quick approval buttons with AJAX
        echo '<div class="action-item action-quick-approve">';
        echo '<button type="button" class="button button-primary quick-approve-btn" data-post-id="' . esc_attr($post_id) . '">';
        echo esc_html__('Quick Approve', 'my-plugin');
        echo '</button>';
        echo '</div>';
        
        echo '<div class="action-item action-quick-reject">';
        echo '<button type="button" class="button button-secondary quick-reject-btn" data-post-id="' . esc_attr($post_id) . '">';
        echo esc_html__('Quick Reject', 'my-plugin');
        echo '</button>';
        echo '</div>';
        
        // Add the JavaScript inline
        echo '<script>
        jQuery(document).ready(function($) {
            $(".quick-approve-btn").click(function() {
                var postId = $(this).data("post-id");
                // Your AJAX call here
                console.log("Quick approve for post:", postId);
            });
            
            $(".quick-reject-btn").click(function() {
                var postId = $(this).data("post-id");
                // Your AJAX call here
                console.log("Quick reject for post:", postId);
            });
        });
        </script>';
    }
}, 20, 3);
```

## Status-Based Conditional Display

All systems support conditional display based on current status. For actions, use simple PHP conditionals:

```php
add_action('arsol_pfw_add_sidebar_actions', function($post_type, $status, $post_id) {
    // Only show for specific statuses
    if (in_array($status, array('active', 'completed', 'on-hold'))) {
        echo '<div class="action-item">';
        echo '<a href="#" class="button">My Action</a>';
        echo '</div>';
    }
    
    // Different actions for different statuses
    if ($status === 'active') {
        // Active project actions
    } elseif ($status === 'completed') {
        // Completed project actions
    }
}, 10, 3);
```

### Available Statuses

**Projects (`active` post type):**
- `active` - Currently active
- `completed` - Finished projects
- `on-hold` - Temporarily paused
- `cancelled` - Cancelled projects

**Proposals (`proposal` post type):**
- `draft` - Draft proposals
- `sent` - Sent to client
- `pending-approval` - Awaiting client approval
- `accepted` - Approved by client
- `rejected` - Rejected by client
- `expired` - Expired proposals

**Requests (`request` post type):**
- `pending` - New requests
- `under-review` - Being reviewed
- `approved` - Approved requests
- `rejected` - Rejected requests
- `on-hold` - Temporarily paused

## Best Practices

1. **Always Escape Output** - Use `esc_html()`, `esc_attr()`, `esc_url()` appropriately
2. **Sanitize Input** - Use `sanitize_text_field()`, `sanitize_textarea_field()` for form data
3. **Use Nonces** - Include nonces in action URLs for security
4. **Check Permissions** - Verify user capabilities before processing actions
5. **Conditional Display** - Use PHP conditionals for status-based display
6. **Consistent Styling** - Use WordPress button classes for consistent UI
7. **Internationalization** - Wrap all text strings in `__()` for translation
8. **Error Handling** - Check for empty values and provide fallbacks
9. **Wrap Actions in Divs** - Use `.action-item` class for consistent styling

## CSS Classes

The system provides CSS classes for styling:

### Metadata
- `.sidebar-metadata` - Container for all metadata
- `.metadata-item` - Individual metadata item
- `.metadata-{key}` - Specific metadata item by key
- `.metadata-label` - Metadata labels
- `.metadata-value` - Metadata values

### Form
- `.sidebar-form` - Form container
- `.arsol-sidebar-form` - The actual form element
- `.form-fields` - Fields container
- `.form-field` - Individual field wrapper
- `.field-{key}` - Specific field by key
- `.form-submit` - Submit button container

### Actions
- `.sidebar-actions` - Actions container
- `.action-item` - Individual action wrapper
- `.action-{key}` - Specific action by key (if using consistent naming)

## Action Hooks Summary

### Metadata System
- `arsol_pfw_sidebar_meta` - Displays metadata container
- Filter: `arsol_pfw_sidebar_metadata` - Filters metadata array

### Form System  
- `arsol_pfw_sidebar_form` - Displays form container
- Filter: `arsol_pfw_form_fields` - Filters form fields array
- Filter: `arsol_pfw_submit_button` - Filters submit button config
- Filter: `arsol_pfw_has_form_fields` - Controls form display
- `arsol_pfw_process_sidebar_form` - Processes form submissions

### Actions System
- `arsol_pfw_sidebar_actions` - Displays actions container  
- `arsol_pfw_add_sidebar_actions` - **This is where you add your buttons**

This system provides maximum flexibility while maintaining WordPress/WooCommerce standards and best practices.
