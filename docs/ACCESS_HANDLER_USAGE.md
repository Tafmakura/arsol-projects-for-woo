# Access Handler Usage Guide

## Overview

The `Access_Handler` class provides centralized access control and no-access template display for the Arsol Projects for Woo plugin.

## Basic Usage

### 1. Simple No Access Display

```php
// Display no access template with default context
\Arsol_Projects_For_Woo\Core\Access_Handler::display_no_access_template();

// Display with specific context
\Arsol_Projects_For_Woo\Core\Access_Handler::display_no_access_template('proposal');
```

### 2. Get Context-Specific Messages

```php
// Get message for proposal context
$message = \Arsol_Projects_For_Woo\Core\Access_Handler::get_no_access_message('proposal');
// Returns: "You do not have permission to view this proposal."

// Get title for proposal context
$title = \Arsol_Projects_For_Woo\Core\Access_Handler::get_no_access_title('proposal');
// Returns: "Proposal Access Denied"
```

### 3. Handle No Access with Automatic Response Type

```php
// Automatically chooses template or redirect based on context
\Arsol_Projects_For_Woo\Core\Access_Handler::handle_no_access('proposal', array(
    'proposal_id' => 123
));
```

### 4. Check Access and Handle Automatically

```php
// Check permission and handle no access if denied
$has_access = \Arsol_Projects_For_Woo\Core\Access_Handler::check_access_and_handle(
    function() {
        return current_user_can('edit_arsol_pfw_proposals');
    },
    'proposal',
    array('proposal_id' => 123)
);
```

## Migration Examples

### Before (Frontend Handler)
```php
if ($current_user_id != $customer_id) {
    return '<div class="arsol-pfw-error"><p>You do not have permission to view this proposal.</p></div>';
}
```

### After (Using Access Handler)
```php
if ($current_user_id != $customer_id) {
    ob_start();
    \Arsol_Projects_For_Woo\Core\Access_Handler::display_no_access_template('proposal', array(
        'proposal_id' => $post->ID
    ));
    return ob_get_clean();
}
```

### Before (Shortcode)
```php
return '<p>' . __('You do not have permission to view this proposal.', 'arsol-pfw') . '</p>';
```

### After (Using Access Handler)
```php
ob_start();
\Arsol_Projects_For_Woo\Core\Access_Handler::display_no_access_template('proposal', array(
    'proposal_id' => $proposal_id
));
return ob_get_clean();
```

## Available Contexts

- `proposal` - Proposal access denied
- `request` - Request access denied
- `project` - Project access denied
- `create` - Create project access denied
- `request_projects` - Request project access denied
- `edit` - Edit access denied
- `delete` - Delete access denied
- `files` - File access denied
- `upload` - Upload access denied
- `general` - General access denied (default)

## Response Types

- `template` - Display full no-access template
- `html` - Return HTML string for shortcodes
- `redirect` - Redirect with WooCommerce notice
- `auto` - Automatically choose based on context

## Benefits

1. **Consistency** - All no-access scenarios use the same template
2. **Maintainability** - Single source of truth for access logic
3. **Flexibility** - Context-aware messages and titles
4. **User Experience** - Professional, consistent error pages
5. **Hook Compatibility** - Maintains existing hook system
