# Sidebar System Complete Reference

The Arsol Projects for Woo plugin provides a flexible, extensible sidebar system for displaying project information, interactive forms, and action buttons. The system uses WordPress's action and filter hooks with filterable arrays, following WordPress/WooCommerce best practices.

## Overview

The sidebar system consists of three main components:

1. **Metadata Display** - Shows project information (budget, dates, status, etc.)
2. **Unified Form** - Single form with filterable fields for user input
3. **Secondary Actions** - Action buttons and links that don't require form input

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
    
    // Secondary actions section
    do_action('arsol_pfw_sidebar_actions', $post_type, $current_status, $post_id);
    ?>
</div>
```

### Classes

- **`Frontend_Template_Sidebar_Meta`** - Handles metadata display
- **`Frontend_Template_Sidebar_Fields`** - Handles unified form with filterable fields
- **`Frontend_Template_Sidebar_Actions`** - Handles secondary actions/buttons

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

## 3. Secondary Actions System

### Hook: `arsol_pfw_sidebar_actions`

Displays secondary action buttons and links.

**Parameters:**
- `$post_type` (string) - Post type: 'active', 'proposal', 'request'
- `$current_status` (string) - Current status slug
- `$post_id` (int) - Post ID

### Filter: `arsol_pfw_sidebar_actions`

Filters the secondary actions array before display.

**Parameters:**
- `$actions` (array) - Array of action items
- `$post_type` (string) - Post type
- `$current_status` (string) - Current status
- `$post_id` (int) - Post ID

### Action Item Structure

```php
$actions['action_key'] = array(
    'label' => __('Action Label', 'arsol-pfw'),
    'url' => 'https://example.com/action',
    'class' => 'button secondary-button',
    'icon' => 'dashicons-download',
    'confirm' => __('Are you sure?', 'arsol-pfw'),
    'target' => '_blank',
    'show_if' => array('status' => array('active', 'completed'))
);
```

### Action Item Properties

| Property | Required | Description | Example |
|----------|----------|-------------|---------|
| `label` | Yes | Button text | `"Download Files"` |
| `url` | Yes | Action URL | `get_permalink($post_id)` |
| `class` | No | CSS classes | `"button button-primary"` |
| `icon` | No | Dashicons class | `"dashicons-download"` |
| `confirm` | No | Confirmation message | `"Are you sure?"` |
| `target` | No | Link target | `"_blank"` |
| `show_if` | No | Conditional display | `array('status' => array('active'))` |

### Example: Adding Custom Actions

```php
add_filter('arsol_pfw_sidebar_actions', function($actions, $post_type, $status, $post_id) {
    if ($post_type === 'active' && $status === 'completed') {
        $actions['generate_invoice'] = array(
            'label' => __('Generate Invoice', 'my-plugin'),
            'url' => wp_nonce_url(
                add_query_arg(array('action' => 'generate_invoice', 'project_id' => $post_id)),
                'generate_invoice_' . $post_id
            ),
            'class' => 'button button-primary',
            'icon' => 'dashicons-media-spreadsheet',
            'confirm' => __('Generate invoice for this project?', 'my-plugin')
        );
    }
    return $actions;
}, 10, 4);
```

## Status-Based Conditional Display

All three systems support conditional display based on current status using the `show_if` parameter:

```php
'show_if' => array(
    'status' => array('active', 'completed', 'on-hold')
)
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

## Complete Examples

### Adding a Complete Custom Sidebar Section

```php
// Add custom metadata
add_filter('arsol_pfw_sidebar_metadata', function($metadata, $post_type, $status, $post_id) {
    if ($post_type === 'active') {
        $metadata['project_manager'] = array(
            'label' => __('Project Manager', 'my-plugin'),
            'value' => get_post_meta($post_id, '_project_manager', true),
            'type' => 'text'
        );
        
        $metadata['completion_percentage'] = array(
            'label' => __('Completion', 'my-plugin'),
            'value' => get_post_meta($post_id, '_completion_percentage', true),
            'type' => 'text',
            'suffix' => '%',
            'show_if' => array('status' => array('active'))
        );
    }
    return $metadata;
}, 10, 4);

// Add custom form fields
add_filter('arsol_pfw_form_fields', function($fields, $post_type, $status, $post_id) {
    if ($post_type === 'active' && $status === 'active') {
        $fields['completion_update'] = array(
            'type' => 'select',
            'label' => __('Update Completion', 'my-plugin'),
            'options' => array(
                '' => __('No change', 'my-plugin'),
                '25' => __('25% Complete', 'my-plugin'),
                '50' => __('50% Complete', 'my-plugin'),
                '75' => __('75% Complete', 'my-plugin'),
                '100' => __('100% Complete', 'my-plugin')
            )
        );
        
        $fields['progress_notes'] = array(
            'type' => 'textarea',
            'label' => __('Progress Notes', 'my-plugin'),
            'placeholder' => __('Describe recent progress...', 'my-plugin'),
            'rows' => 3
        );
    }
    return $fields;
}, 10, 4);

// Customize submit button
add_filter('arsol_pfw_submit_button', function($button, $post_type, $status, $post_id) {
    if ($post_type === 'active' && $status === 'active') {
        return array(
            'label' => __('Update Progress', 'my-plugin'),
            'class' => 'button button-primary'
        );
    }
    return $button;
}, 10, 4);

// Add custom actions
add_filter('arsol_pfw_sidebar_actions', function($actions, $post_type, $status, $post_id) {
    if ($post_type === 'active') {
        $actions['project_timeline'] = array(
            'label' => __('View Timeline', 'my-plugin'),
            'url' => add_query_arg(array('view' => 'timeline'), get_permalink($post_id)),
            'class' => 'button secondary-button',
            'icon' => 'dashicons-calendar-alt'
        );
        
        $actions['client_portal'] = array(
            'label' => __('Client Portal', 'my-plugin'),
            'url' => home_url('/client-portal/?project=' . $post_id),
            'class' => 'button secondary-button',
            'icon' => 'dashicons-admin-users',
            'target' => '_blank'
        );
    }
    return $actions;
}, 10, 4);

// Process form submissions
add_action('arsol_pfw_process_sidebar_form', function($post_id, $post_type, $current_status, $form_data) {
    if ($post_type === 'active' && !empty($form_data['completion_update'])) {
        $completion = intval($form_data['completion_update']);
        update_post_meta($post_id, '_completion_percentage', $completion);
        
        if (!empty($form_data['progress_notes'])) {
            update_post_meta($post_id, '_progress_notes', sanitize_textarea_field($form_data['progress_notes']));
        }
        
        // If 100% complete, change status
        if ($completion === 100) {
            wp_set_post_terms($post_id, 'completed', 'arsol-project-status');
        }
    }
}, 10, 4);
```

## Best Practices

1. **Always Escape Output** - Use `esc_html()`, `esc_attr()`, `esc_url()` appropriately
2. **Sanitize Input** - Use `sanitize_text_field()`, `sanitize_textarea_field()` for form data
3. **Use Nonces** - Include nonces in action URLs for security
4. **Check Permissions** - Verify user capabilities before processing actions
5. **Conditional Display** - Use `show_if` to show/hide based on status
6. **Consistent Styling** - Use WordPress button classes for consistent UI
7. **Internationalization** - Wrap all text strings in `__()` for translation
8. **Error Handling** - Check for empty values and provide fallbacks

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
- `.action-{key}` - Specific action by key

This system provides maximum flexibility while maintaining WordPress/WooCommerce standards and best practices.
