# Shortcode Complete Reference

This document provides a comprehensive reference for all available shortcodes in the Arsol Projects for WooCommerce plugin.

## Overview

All shortcodes follow WordPress naming standards with underscores and use the `arsol_pfw_` prefix for consistency and to avoid conflicts with other plugins.

## Core Shortcodes

### `[arsol_pfw_projects]`
Displays a grid/list of projects with customizable parameters.

**Parameters:**
- `limit` - Number of projects to display (default: 10)
- `category` - Filter by project category slug
- `columns` - Number of columns for grid display (default: 3)
- `orderby` - Sort field: date, title, menu_order, author, modified (default: date)
- `order` - Sort direction: ASC, DESC (default: DESC)
- `pagination` - Show pagination: yes, no (default: yes)

**Example:**
```php
[arsol_pfw_projects limit="6" columns="2" orderby="title" order="ASC"]
[arsol_pfw_projects category="web-development" pagination="no"]
```

### `[arsol_pfw_project]`
Displays a single project by ID.

**Parameters:**
- `id` - Project ID (required)

**Example:**
```php
[arsol_pfw_project id="123"]
```

### `[arsol_pfw_project_categories]`
Displays project categories.

**Parameters:**
- `limit` - Number of categories to show (default: -1 for all)
- `orderby` - Sort field: name, count, slug (default: name)
- `order` - Sort direction: ASC, DESC (default: ASC)
- `parent` - Show only child categories of specified parent
- `hide_empty` - Hide categories with no projects: yes, no (default: no)

**Example:**
```php
[arsol_pfw_project_categories limit="5" orderby="count" order="DESC"]
```

### `[arsol_pfw_user_projects]`
Displays projects for the current logged-in user.

**Parameters:**
- `status` - Filter by project status (default: any)
- `per_page` - Projects per page (default: 10)
- `paged` - Current page number (default: 1)

**Example:**
```php
[arsol_pfw_user_projects status="active" per_page="5"]
```

### `[arsol_pfw_user_projects_count]`
Displays the count of projects for the current user.

**Example:**
```php
You have [arsol_pfw_user_projects_count] active projects.
```

### `[arsol_pfw_projects_count]`
Displays the total count of all published projects.

**Example:**
```php
Total projects: [arsol_pfw_projects_count]
```

### `[arsol_pfw_project_orders]`
Displays WooCommerce orders associated with a specific project.

**Parameters:**
- `id` - Project ID (required)

**Example:**
```php
[arsol_pfw_project_orders id="123"]
```

### `[arsol_pfw_project_subscriptions]`
Displays WooCommerce subscriptions associated with a specific project.

**Parameters:**
- `id` - Project ID (required)

**Note:** Requires WooCommerce Subscriptions plugin to be active.

**Example:**
```php
[arsol_pfw_project_subscriptions id="123"]
```

---

## Template Override Shortcodes

These shortcodes are used for displaying specific content sections and can be overridden in admin settings.

### `[arsol_pfw_project_overview]`
Displays the overview content for active projects.

**Parameters:**
- `project_id` - Project ID (optional in My Account context)

**Context:**
- **My Account**: Auto-detects project ID from URL
- **Public Pages**: Requires `project_id` parameter

**Example:**
```php
[arsol_pfw_project_overview]
[arsol_pfw_project_overview project_id="123"]
```

### `[arsol_pfw_proposal_overview]`
Displays the overview content for project proposals.

**Parameters:**
- `project_id` - Proposal ID (optional in My Account context)

**Example:**
```php
[arsol_pfw_proposal_overview]
[arsol_pfw_proposal_overview project_id="456"]
```

### `[arsol_pfw_request_overview]`
Displays the overview content for project requests.

**Parameters:**
- `project_id` - Request ID (optional in My Account context)

**Example:**
```php
[arsol_pfw_request_overview]
[arsol_pfw_request_overview project_id="789"]
```

---

## Listing Shortcodes

### `[arsol_pfw_projects_list]`
Displays a list of active projects with advanced filtering.

**Parameters:**
- `per_page` - Projects per page (default: 10)
- `paged` - Current page number (default: 1)
- `customer_id` - Filter by specific customer (default: 0)
- `status` - Filter by project status (default: active)
- `category` - Filter by category slug
- `orderby` - Sort field (default: date)
- `order` - Sort direction (default: DESC)
- `search` - Search term for project titles/content

**Context-Aware Behavior:**
- **My Account**: Shows user's own projects automatically
- **Public Pages**: Shows public projects with optional customer filtering

**Example:**
```php
[arsol_pfw_projects_list per_page="5" orderby="title"]
[arsol_pfw_projects_list customer_id="123" category="web-dev"]
```

### `[arsol_pfw_proposals_list]`
Displays a list of project proposals.

**Parameters:** Same as `arsol_pfw_projects_list` but for proposals

**Example:**
```php
[arsol_pfw_proposals_list]
[arsol_pfw_proposals_list per_page="8" status="pending"]
```

### `[arsol_pfw_requests_list]`
Displays a list of project requests.

**Parameters:** Same as `arsol_pfw_projects_list` but for requests

**Example:**
```php
[arsol_pfw_requests_list]
[arsol_pfw_requests_list orderby="date" order="ASC"]
```

---

## Form Shortcodes

### `[arsol_pfw_project_form]`
Displays the project creation/editing form.

**Parameters:**
- `form_id` - Custom form ID (default: project-form)
- `is_edit` - Edit mode: true, false (default: false)
- `post_id` - Project ID for editing (required if is_edit=true)

**Example:**
```php
[arsol_pfw_project_form]
[arsol_pfw_project_form is_edit="true" post_id="123"]
```

### `[arsol_pfw_request_form]`
Displays the project request creation/editing form.

**Parameters:**
- `form_id` - Custom form ID (default: create-request-form)
- `is_edit` - Edit mode: true, false (default: false)
- `post_id` - Request ID for editing (required if is_edit=true)

**Example:**
```php
[arsol_pfw_request_form]
[arsol_pfw_request_form is_edit="true" post_id="456"]
```

### `[arsol_pfw_edit_project_form]`
Simplified edit form shortcode that automatically sets edit mode.

**Parameters:**
- `form_id` - Custom form ID (default: edit-project-form)
- `post_id` - Project ID (required)

**Example:**
```php
[arsol_pfw_edit_project_form post_id="123"]
```

### `[arsol_pfw_edit_request_form]`
Simplified edit form shortcode for requests that automatically sets edit mode.

**Parameters:**
- `form_id` - Custom form ID (default: edit-request-form)
- `post_id` - Request ID (required)

**Example:**
```php
[arsol_pfw_edit_request_form post_id="456"]
```

#### **Implementation Notes:**
- **Default behavior**: Our plugin uses the main shortcodes with `is_edit` parameter
- **Custom implementations**: Can override with dedicated edit shortcodes if needed
- **Template overrides**: Both approaches available in admin settings under Display tab

---

## File Management Shortcodes

### `[arsol_pfw_proposal_files]`
Displays files associated with a project proposal, with download capability.

**Parameters:**
- `id` - Proposal ID (optional in My Account context)

**Context:**
- **My Account**: Auto-detects proposal ID from URL
- **Public Pages**: Requires `id` parameter

**Example:**
```php
[arsol_pfw_proposal_files]
[arsol_pfw_proposal_files id="123"]
```

### `[arsol_pfw_request_file_upload]`
Displays file upload form for project requests.

**Parameters:**
- `id` - Request ID (optional in My Account context)

**Context:**
- **My Account**: Auto-detects request ID from URL
- **Public Pages**: Requires `id` parameter

**File Settings:**
- All file upload settings (formats, sizes, etc.) configured in Admin → Files Settings
- Uses clean implementation with WordPress file handling

**Example:**
```php
[arsol_pfw_request_file_upload]
[arsol_pfw_request_file_upload id="456"]
```

### `[arsol_pfw_project_files_list]`
Displays a listing of all files associated with an active project.

**Parameters:**
- `id` - Project ID (optional in My Account context)

**Context:**
- **My Account**: Auto-detects project ID from URL
- **Public Pages**: Requires `id` parameter

**Features:**
- File download capability
- Integrated with conditional display system
- HTML classes only (no additional CSS rules)

**Example:**
```php
[arsol_pfw_project_files_list]
[arsol_pfw_project_files_list id="789"]
```

---

## Utility Shortcodes

### `[arsol_pfw_no_access]`
Displays access denied notice with customizable title and message.

**Parameters:**
- `title` - Custom title (default: "Access Denied")
- `message` - Custom message (default: standard access denied text)

**Example:**
```php
[arsol_pfw_no_access]
[arsol_pfw_no_access title="Restricted Area" message="This content is for members only."]
```

### `[arsol_pfw_template_override_demo]`
Demo shortcode for testing template overrides.

**Parameters:**
- `title` - Demo title
- `message` - Demo message  
- `style` - Style class: default, success, warning, error
- `type` - Demo type for context

**Example:**
```php
[arsol_pfw_template_override_demo title="Test Override" style="success"]
```

---

## Context-Aware Behavior

- **My Account**: Work natively using URL context
- **Public Pages**: Show public content with optional customer filtering
- **Dynamic Parameters**: Support URL-based parameter overrides

## Examples

```php
// Public project gallery with customer filter
[arsol_pfw_projects_list customer_id="123"]

// Dynamic filtering via URL
[arsol_pfw_projects_list]
// URL: /projects/?category=web-dev&search=wordpress

// Project forms
[arsol_pfw_project_form]
[arsol_pfw_request_form]
```

*For complete documentation, see the full reference guide.* 

### Template Override System Enhancement

**NEW: WordPress Coding Standards Compliance**
- ✅ **Enhanced Validation**: All shortcode inputs are validated for proper format
- ✅ **Error Handling**: Invalid shortcodes show clear error messages
- ✅ **Security**: All inputs are properly sanitized and escaped
- ✅ **Known Shortcode Detection**: System validates against registered shortcodes
- ✅ **WordPress Best Practices**: Follows all WP coding standards

**Validation Rules:**
- Shortcode format: `[shortcode_name]` or `[shortcode_name param="value"]`
- Must match regex: `/^\[[\w\s_=-]+\]$/`
- Must be either a registered WordPress shortcode or one of our known plugin shortcodes
- Empty values are accepted (will use default templates)

**Error Messages:**
If you enter an invalid shortcode format, you'll see a clear error message:
> "Invalid shortcode format for Project Form: "[invalid_shortcode]". Please use the format [shortcode_name]." 