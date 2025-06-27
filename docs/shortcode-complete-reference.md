# Arsol Projects for WooCommerce - Complete Shortcode Reference

## Overview

This plugin provides a comprehensive set of shortcodes for displaying project content, listings, and forms. All shortcodes use the **arsol_pfw_** naming convention and are **context-aware**.

## Naming Convention

- **Shortcodes**: Use `arsol_pfw_*` prefix (e.g., `[arsol_pfw_project_form]`)
- **Post Types**: `arsol-project`, `arsol-pfw-proposal`, `arsol-pfw-request`
- **Frontend Names**: Always include "Project" (e.g., "Project Form", "Project Request Form")

---

## Content Shortcodes

### `[arsol_pfw_project_content_active]`
Displays active project content.

### `[arsol_pfw_project_content_proposal]`
Displays project proposal content.

### `[arsol_pfw_project_content_proposal_processing]`
Displays processing message for project proposals regardless of content.

**Parameters:**
- `title` - Custom title (default: "Proposal Status")
- `message` - Custom processing message (default: uses configured processing message)

**Example:**
```php
[arsol_pfw_project_content_proposal_processing]
[arsol_pfw_project_content_proposal_processing title="Working on Your Proposal" message="Our team is crafting your custom proposal..."]
```

### `[arsol_pfw_project_content_proposal_pending_approval]`
Displays pending approval message for project proposals regardless of content.

**Parameters:**
- `title` - Custom title (default: "Proposal Status")
- `message` - Custom pending approval message (default: uses configured pending approval message)

**Example:**
```php
[arsol_pfw_project_content_proposal_pending_approval]
[arsol_pfw_project_content_proposal_pending_approval title="Ready for Review" message="Your proposal is ready for your review and approval..."]
```

### `[arsol_pfw_project_content_request]`
Displays project request content.

---

## Listing Shortcodes

### `[arsol_pfw_projects_listing_active]`
Lists active projects with context-aware behavior and dynamic URL parameters.

### `[arsol_pfw_projects_listing_proposals]`
Lists project proposals with filtering options.

### `[arsol_pfw_projects_listing_requests]`
Lists project requests with search capabilities.

---

## Form Shortcodes

### `[arsol_pfw_project_form]`
Displays the **Project Form** for creating projects.

### `[arsol_pfw_project_request_form]`
Displays the **Project Request Form** for creating/editing requests.

---

## Utility Shortcodes

### `[arsol_pfw_access_denied]`
Displays access denied notice with customizable title and message.

**Parameters:**
- `title` - Custom title (default: "Access Denied")
- `message` - Custom message (default: standard access denied text)

**Example:**
```php
[arsol_pfw_access_denied]
[arsol_pfw_access_denied title="Restricted Area" message="This content is for members only."]
```

---

## Context-Aware Behavior

- **My Account**: Work natively using URL context
- **Public Pages**: Show public content with optional customer filtering
- **Dynamic Parameters**: Support URL-based parameter overrides

## Examples

```php
// Public project gallery with customer filter
[arsol_pfw_projects_listing_active customer_id="123"]

// Dynamic filtering via URL
[arsol_pfw_projects_listing_active]
// URL: /projects/?category=web-dev&search=wordpress

// Project forms
[arsol_pfw_project_form]
[arsol_pfw_project_request_form]
```

*For complete documentation, see the full reference guide.* 