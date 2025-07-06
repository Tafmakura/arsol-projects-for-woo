# Arsol Projects for WooCommerce - Template Overrides Complete Reference

## Overview

The Template Override System allows you to replace default plugin templates with custom shortcodes, providing ultimate flexibility in how your project content is displayed. This system works seamlessly with the plugin's context-aware shortcode architecture.

## Core Concepts

### 1. Template Override Philosophy
- **Non-Destructive**: Original templates remain intact
- **Flexible**: Override entire pages or just content areas
- **Shortcode-Powered**: Uses the plugin's own shortcode system
- **Context-Aware**: Respects different display contexts

### 2. Override Types
- **Complete Replacement**: Replace entire template with shortcode
- **Content-Only Override**: Replace just the main content, keep sidebars
- **Conditional Override**: Different overrides for different contexts

### 3. Validation System
- **Shortcode Format Validation**: Ensures proper shortcode syntax
- **Fallback Protection**: Invalid overrides fall back to default templates
- **Debug Support**: Detailed logging for troubleshooting

---

## Configuration Interface

Navigate to **WooCommerce → Settings → Advanced → Arsol Projects** to configure template overrides.

### Available Override Fields

#### Project Overview Templates
1. **Active Project Overview**: Override for active project display
2. **Proposal Project Overview**: Override for proposal display  
3. **Request Project Overview**: Override for request display

#### Form Templates
4. **Create Project Form**: Override for project creation form
5. **Create Request Form**: Override for request creation form
6. **Edit Request Form**: Override for request editing form

#### Listing Templates
7. **Active Projects Listing**: Override for active projects list
8. **Proposals Listing**: Override for proposals list
9. **Requests Listing**: Override for requests list

#### Error Templates
10. **Access Denied**: Override for permission error pages

### Field Configuration

Each override field includes:
- **Input Field**: Enter your custom shortcode
- **Default Reference**: Shows the equivalent internal shortcode
- **Validation**: Real-time shortcode format checking
- **Help Text**: Usage examples and tips

---

## Shortcode Integration

### Internal Shortcodes (Defaults)

The plugin uses these shortcodes internally. You can reference them when creating overrides:

```php
// Content Areas
[arsol_pfw_project_overview]
[arsol_pfw_proposal_overview]
[arsol_pfw_request_overview]

// Listings
[arsol_pfw_projects_list]
[arsol_pfw_proposals_list]
[arsol_pfw_requests_list]

// Forms
[arsol_pfw_project_form]
[arsol_pfw_request_form]
```

### Custom Override Examples

#### 1. Enhanced Project Overview
```php
[arsol_pfw_project_overview]
<div class="project-extras">
    <h3>Related Orders</h3>
    [arsol_pfw_project_orders]
    
    <h3>Subscriptions</h3>
    [arsol_pfw_project_subscriptions]
</div>
```

#### 2. Filtered Project Listing
```php
<div class="projects-header">
    <h2>My Active Projects</h2>
    <p>Showing projects sorted by title</p>
</div>
[arsol_pfw_projects_list orderby="title" order="ASC"]
```

#### 3. Custom Form Layout
```php
<div class="custom-form-wrapper">
    <div class="form-instructions">
        <h3>Create Your Project</h3>
        <p>Fill out the form below to get started.</p>
    </div>
    [arsol_pfw_project_form form_id="custom-project-form"]
</div>
```

#### 4. Multi-Column Layout
```php
<div class="row">
    <div class="col-md-8">
        [arsol_pfw_project_overview]
    </div>
    <div class="col-md-4">
        <h4>Project Stats</h4>
        [arsol_pfw_project_orders]
        [arsol_pfw_project_subscriptions]
    </div>
</div>
```

---

## Advanced Configuration

### Context-Aware Overrides

You can create different overrides for different contexts using conditional shortcodes:

```php
[arsol_pfw_template_override_demo type="active" title="Custom Active Project" style="success"]

<!-- Or combine multiple shortcodes -->
[arsol_pfw_project_overview]
[arsol_pfw_user_projects_count] total projects
```

### Dynamic Content Integration

Combine static content with dynamic shortcodes:

```php
<div class="project-dashboard">
    <div class="stats-bar">
        Total Projects: [arsol_pfw_user_projects_count]
        | Active Projects: [arsol_pfw_projects_count]
    </div>
    
    [arsol_pfw_projects_list per_page="5"]
    
    <div class="quick-actions">
        <a href="/my-account/create-project/" class="button">Create New Project</a>
    </div>
</div>
```

### WooCommerce Integration

Leverage WooCommerce data in your overrides:

```php
<div class="project-commerce">
    [arsol_pfw_project_overview]
    
    <div class="commerce-section">
        <h3>Project Orders</h3>
        [arsol_pfw_project_orders limit="5"]
        
        <h3>Subscriptions</h3>
        [arsol_pfw_project_subscriptions limit="3"]
    </div>
</div>
```

---

## Template Override Mapping

### Default Template → Override Field Mapping

| Template File | Override Field | Default Shortcode |
|---------------|----------------|-------------------|
| `section-project-content-active.php` | Active Project Overview | `[arsol_pfw_project_overview]` |
| `section-project-content-proposal.php` | Proposal Project Overview | `[arsol_pfw_proposal_overview]` |
| `section-project-content-request.php` | Request Project Overview | `[arsol_pfw_request_overview]` |
| `form-project-create-active.php` | Create Project Form | `[arsol_pfw_project_form]` |
| `form-project-request-create.php` | Create Request Form | `[arsol_pfw_request_form]` |
| `form-project-request-edit.php` | Edit Request Form | `[arsol_pfw_request_form is_edit="true"]` |
| `projects-listing-active.php` | Active Projects Listing | `[arsol_pfw_projects_list]` |
| `projects-listing-proposals.php` | Proposals Listing | `[arsol_pfw_proposals_list]` |
| `projects-listing-requests.php` | Requests Listing | `[arsol_pfw_requests_list]` |
| `page-access-denied.php` | Access Denied | Custom HTML/shortcode |

### Context Integration Points

The override system integrates at these key points:

1. **My Account Endpoints**: `/my-account/project-overview/`, `/my-account/projects/`, etc.
2. **Single Post Pages**: Individual project, proposal, and request pages
3. **Custom Pages**: Any page using project shortcodes
4. **Widget Areas**: Shortcodes work in widgets and page builders

---

## Validation and Error Handling

### Shortcode Validation Rules

The system validates shortcodes using these criteria:

1. **Format Check**: Must start with `[` and end with `]`
2. **Shortcode Existence**: Must be a registered shortcode
3. **Parameter Validation**: Parameters must follow WordPress standards
4. **Security Check**: No malicious code injection

### Validation Examples

✅ **Valid Overrides:**
```php
[arsol_pfw_project_overview]
[arsol_pfw_projects_list per_page="10"]
[arsol_pfw_project_form form_id="custom-form"]
<div class="wrapper">[arsol_pfw_project_overview]</div>
```

❌ **Invalid Overrides:**
```php
[invalid_shortcode]
[arsol_pfw_project_overview malicious_param="<script>"]
arsol_pfw_project_overview (missing brackets)
[arsol_pfw_project_overview (unclosed bracket)
```

### Error Handling Behavior

When validation fails:

1. **Warning Display**: Admin users see validation warnings
2. **Fallback Mode**: System uses default template
3. **Debug Logging**: Detailed error logged for troubleshooting
4. **User Experience**: End users see normal content (no errors)

---

## Best Practices

### 1. Start Simple
Begin with basic shortcode replacements before adding complex layouts:

```php
<!-- Start with this -->
[arsol_pfw_project_overview]

<!-- Then enhance -->
<div class="enhanced-project">
    [arsol_pfw_project_overview]
    <div class="project-meta">
        [arsol_pfw_project_orders limit="3"]
    </div>
</div>
```

### 2. Maintain Consistency
Use consistent styling and structure across all overrides:

```php
<div class="arsol-override-wrapper">
    <div class="arsol-content">
        [arsol_pfw_project_overview]
    </div>
    <div class="arsol-sidebar">
        <!-- Additional content -->
    </div>
</div>
```

### 3. Test Across Contexts
Ensure your overrides work in all contexts:
- My Account pages
- Single post pages
- Public listing pages
- Mobile devices

### 4. Use Semantic HTML
Structure your overrides with proper semantic HTML:

```php
<article class="project-overview">
    <header class="project-header">
        <h1>Project Details</h1>
    </header>
    <main class="project-content">
        [arsol_pfw_project_overview]
    </main>
    <aside class="project-sidebar">
        [arsol_pfw_project_orders]
    </aside>
</article>
```

---

## Support and Resources

### Getting Help
- **Documentation**: This reference and shortcode documentation
- **Community**: WordPress.org plugin forums
- **Support**: Premium support for license holders
- **GitHub**: Issue tracking and feature requests

### Additional Resources
- **Video Tutorials**: Step-by-step override creation
- **Code Examples**: Ready-to-use override templates
- **Best Practices Guide**: Advanced implementation strategies
- **Theme Integration**: Specific guidance for popular themes

---

*Documentation for Arsol Projects for WooCommerce* 