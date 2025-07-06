# Frontend Features - Complete Reference

## Overview

The **Arsol Projects for WooCommerce** plugin provides comprehensive frontend features including template overrides, shortcodes, sidebar components, and customer portal functionality. This reference covers all frontend customization options and implementation details.

## Key Features

- ✅ **Template Override System** for complete visual customization
- ✅ **30+ Shortcodes** for content integration
- ✅ **Dynamic Sidebars** with meta information and action buttons
- ✅ **Customer Portal** with project management interface
- ✅ **WooCommerce Integration** with My Account pages
- ✅ **Responsive Design** with mobile-first approach

---

## Template Override System

### Template Hierarchy

```
your-theme/arsol-projects-for-woo/
├── woocommerce/
│   └── myaccount/
│       ├── projects.php
│       ├── project-overview.php
│       ├── project-files.php
│       ├── project-orders.php
│       ├── project-subscriptions.php
│       ├── project-create.php
│       ├── project-request.php
│       ├── project-view-proposal.php
│       ├── project-view-request.php
│       └── no-access.php
├── partials/
│   ├── project/
│   │   ├── project-header.php
│   │   ├── overview/
│   │   │   └── project-overview-content.php
│   │   ├── orders/
│   │   │   └── project-orders-content.php
│   │   └── subscriptions/
│   │       └── project-subscriptions-content.php
│   ├── projects/
│   │   ├── projects-header.php
│   │   ├── projects-listing-project.php
│   │   ├── projects-listing-proposals.php
│   │   ├── projects-listing-requests.php
│   │   └── projects-orders-table.php
│   ├── project-view-proposal/
│   │   └── project-view-proposal-header.php
│   └── project-view-request/
│       └── project-view-request-header.php
└── components/
    └── frontend/
        ├── action-button.php
        ├── endpoint-create-project.php
        ├── endpoint-create-request.php
        ├── endpoint-view-project.php
        ├── endpoint-view-proposal.php
        ├── endpoint-view-request.php
        ├── endpoint-view-project-orders.php
        └── endpoint-view-project-subscriptions.php
```

### Main Templates

#### Projects Dashboard (`projects.php`)
Main dashboard showing all user projects, proposals, and requests.

**Available Variables:**
```php
$projects          // Array of user projects
$proposals         // Array of user proposals  
$requests          // Array of user requests
$user_id           // Current user ID
$current_tab       // Active tab (projects/proposals/requests)
$can_create        // Boolean: user can create projects
$can_request       // Boolean: user can request projects
```

**Template Structure:**
```php
<?php
// Header with navigation tabs
get_template_part('arsol-projects-for-woo/partials/projects/projects-header', null, array(
    'current_tab' => $current_tab,
    'can_create' => $can_create,
    'can_request' => $can_request
));

// Content based on active tab
if ($current_tab === 'projects') {
    get_template_part('arsol-projects-for-woo/partials/projects/projects-listing-project', null, array(
        'projects' => $projects,
        'user_id' => $user_id
    ));
} elseif ($current_tab === 'proposals') {
    get_template_part('arsol-projects-for-woo/partials/projects/projects-listing-proposals', null, array(
        'proposals' => $proposals,
        'user_id' => $user_id
    ));
} elseif ($current_tab === 'requests') {
    get_template_part('arsol-projects-for-woo/partials/projects/projects-listing-requests', null, array(
        'requests' => $requests,
        'user_id' => $user_id
    ));
}
?>
```

#### Project Overview (`project-overview.php`)
Individual project overview with details, comments, and sidebar.

**Available Variables:**
```php
$project_id        // Project ID
$project           // Project object
$project_stage     // Current project stage
$project_meta      // Project meta data
$user_id           // Current user ID
$can_edit          // Boolean: user can edit project
$comments_enabled  // Boolean: comments are enabled
```

#### Project Creation (`project-create.php`)
Form for creating new projects.

**Available Variables:**
```php
$user_id           // Current user ID
$form_fields       // Array of form fields
$project_stages    // Available project stages
$form_errors       // Validation errors (if any)
```

### Template Variables Reference

#### Global Variables
Available in all templates:
```php
$user_id           // Current user ID
$current_user      // Current user object
$plugin_url        // Plugin URL
$template_url      // Template directory URL
$assets_url        // Assets directory URL
```

#### Entity-Specific Variables

**Project Templates:**
```php
$project_id        // Project ID
$project           // Project post object
$project_title     // Project title
$project_content   // Project content/description
$project_stage     // Current project stage
$project_meta      // All project meta data
$project_author    // Project author user object
$project_date      // Project creation date
$project_modified  // Last modified date
```

**Proposal Templates:**
```php
$proposal_id       // Proposal ID
$proposal          // Proposal post object
$proposal_title    // Proposal title
$proposal_content  // Proposal content
$proposal_stage    // Current proposal stage
$proposal_meta     // All proposal meta data
$parent_project    // Parent project ID
$proposal_budget   // Proposal budget information
```

**Request Templates:**
```php
$request_id        // Request ID
$request           // Request post object
$request_title     // Request title
$request_content   // Request description
$request_stage     // Current request stage
$request_meta      // All request meta data
$request_priority  // Request priority level
```

---

## Shortcode System

### Project Shortcodes

#### `[arsol_project_list]`
Display list of projects with filtering options.

**Attributes:**
```php
user_id="123"           // Show projects for specific user (default: current user)
stage="active"          // Filter by project stage
limit="10"              // Number of projects to show
order="DESC"            // Sort order (ASC/DESC)
orderby="date"          // Sort by (date/title/stage)
show_meta="true"        // Show project meta information
template="grid"         // Display template (list/grid/table)
```

**Usage Examples:**
```php
// Basic project list
[arsol_project_list]

// Active projects only, grid layout
[arsol_project_list stage="active" template="grid" limit="6"]

// All projects for specific user, table format
[arsol_project_list user_id="123" template="table" show_meta="true"]
```

#### `[arsol_project_details]`
Display detailed information about a specific project.

**Attributes:**
```php
project_id="123"        // Project ID (required)
show_title="true"       // Show project title
show_description="true" // Show project description
show_stage="true"       // Show current stage
show_meta="true"        // Show meta information
show_progress="false"   // Show progress bar
template="default"      // Display template
```

#### `[arsol_project_status]`
Display project status badge or information.

**Attributes:**
```php
project_id="123"        // Project ID (required)
format="badge"          // Display format (badge/text/icon)
show_date="false"       // Include last update date
```

### Proposal Shortcodes

#### `[arsol_proposal_list]`
Display list of proposals.

**Attributes:**
```php
user_id="123"           // Show proposals for specific user
project_id="456"        // Show proposals for specific project
stage="ready"           // Filter by proposal stage
limit="10"              // Number of proposals to show
template="list"         // Display template
```

#### `[arsol_proposal_decision]`
Display proposal decision interface (for proposal authors).

**Attributes:**
```php
proposal_id="123"       // Proposal ID (required)
show_approve="true"     // Show approve button
show_reject="true"      // Show reject button
show_request_changes="true" // Show request changes option
redirect_url=""         // Redirect after decision
```

### Request Shortcodes

#### `[arsol_request_list]`
Display list of project requests.

**Attributes:**
```php
user_id="123"           // Show requests for specific user
stage="pending"         // Filter by request stage
limit="10"              // Number of requests to show
template="list"         // Display template
```

#### `[arsol_request_form]`
Display project request form.

**Attributes:**
```php
redirect_url=""         // Redirect after submission
show_title="true"       // Show form title
required_fields=""      // Comma-separated required fields
template="default"      // Form template
```

### User Interface Shortcodes

#### `[arsol_user_dashboard]`
Display user dashboard with projects overview.

**Attributes:**
```php
user_id=""              // User ID (default: current user)
show_stats="true"       // Show statistics
show_recent="true"      // Show recent activity
template="default"      // Dashboard template
```

#### `[arsol_project_navigation]`
Display project navigation menu.

**Attributes:**
```php
project_id=""           // Project ID (auto-detected from URL)
show_overview="true"    // Show overview link
show_files="true"       // Show files link
show_orders="true"      // Show orders link
show_comments="true"    // Show comments link
template="tabs"         // Navigation template (tabs/menu/breadcrumb)
```

### Content Shortcodes

#### `[arsol_project_meta]`
Display specific project meta information.

**Attributes:**
```php
project_id=""           // Project ID (required)
meta_key=""             // Meta key to display (required)
format="text"           // Display format (text/date/currency/boolean)
default=""              // Default value if meta doesn't exist
```

**Usage Examples:**
```php
// Display project budget
[arsol_project_meta project_id="123" meta_key="budget" format="currency"]

// Display project deadline
[arsol_project_meta project_id="123" meta_key="deadline" format="date"]

// Display custom field with default
[arsol_project_meta project_id="123" meta_key="priority" default="Normal"]
```

#### `[arsol_project_comments]`
Display project comments section.

**Attributes:**
```php
project_id=""           // Project ID (required)
show_form="true"        // Show comment form
max_depth="5"           // Maximum reply depth
order="ASC"             // Comment order (ASC/DESC)
template="default"      // Comments template
```

### Utility Shortcodes

#### `[arsol_user_can]`
Conditional content based on user permissions.

**Attributes:**
```php
capability=""           // Required capability
user_id=""              // User ID (default: current user)
project_id=""           // Project ID for project-specific permissions
```

**Usage Example:**
```php
[arsol_user_can capability="edit_projects"]
    <p>You can edit projects!</p>
[/arsol_user_can]
```

#### `[arsol_date_format]`
Format dates consistently across the plugin.

**Attributes:**
```php
date=""                 // Date to format (required)
format="F j, Y"         // PHP date format
relative="false"        // Show relative dates (e.g., "2 days ago")
```

### Action Shortcodes

#### `[arsol_action_button]`
Display action buttons for projects/proposals/requests.

**Attributes:**
```php
action=""               // Action to perform (required)
entity_id=""            // Entity ID (required)
entity_type=""          // Entity type (project/proposal/request)
text=""                 // Button text
class=""                // Additional CSS classes
confirm=""              // Confirmation message
redirect_url=""         // Redirect after action
```

**Usage Examples:**
```php
// Approve proposal button
[arsol_action_button action="approve" entity_id="123" entity_type="proposal" text="Approve Proposal" confirm="Are you sure?"]

// Delete project button (with permission check)
[arsol_user_can capability="delete_projects"]
    [arsol_action_button action="delete" entity_id="456" entity_type="project" text="Delete Project" class="danger"]
[/arsol_user_can]
```

---

## Sidebar System

### Sidebar Components

#### Project Sidebar (`section-sidebar-project.php`)
Displays project-specific information and actions.

**Components:**
- Project meta information
- Stage indicator
- Action buttons
- Related files
- Recent activity

#### Proposal Sidebar (`section-sidebar-proposal.php`)
Displays proposal-specific information and actions.

**Components:**
- Proposal meta information
- Decision buttons (approve/reject)
- Budget information
- Timeline
- Related project link

#### Request Sidebar (`section-sidebar-request.php`)
Displays request-specific information and actions.

**Components:**
- Request meta information
- Status indicator
- Priority level
- Submission date
- Admin actions (if applicable)

### Sidebar Meta Information

#### Meta Display Component (`class-frontend-template-sidebar-meta.php`)
Handles dynamic meta information display.

**Available Meta Fields:**
```php
// Project Meta
'project_stage'         // Current project stage
'project_budget'        // Project budget
'project_deadline'      // Project deadline
'project_priority'      // Project priority
'project_progress'      // Project progress percentage
'project_manager'       // Assigned project manager
'estimated_hours'       // Estimated work hours
'actual_hours'          // Actual work hours

// Proposal Meta
'proposal_stage'        // Current proposal stage
'proposal_budget'       // Proposed budget
'proposal_timeline'     // Proposed timeline
'proposal_deliverables' // Proposed deliverables
'proposal_notes'        // Internal notes

// Request Meta
'request_stage'         // Current request stage
'request_priority'      // Request priority
'request_type'          // Type of request
'request_category'      // Request category
'estimated_budget'      // Estimated budget range
```

#### Meta Display Customization
```php
// Filter meta display format
add_filter('arsol_pfw_sidebar_meta_format', function($format, $meta_key, $meta_value) {
    if ($meta_key === 'project_budget') {
        return '$' . number_format($meta_value, 2);
    }
    return $format;
}, 10, 3);

// Add custom meta fields
add_filter('arsol_pfw_sidebar_meta_fields', function($fields, $entity_type, $entity_id) {
    if ($entity_type === 'project') {
        $fields['custom_field'] = array(
            'label' => 'Custom Field',
            'value' => get_post_meta($entity_id, '_custom_field', true),
            'format' => 'text'
        );
    }
    return $fields;
}, 10, 3);
```

### Sidebar Action Buttons

#### Button Component (`class-frontend-template-sidebar-buttons.php`)
Handles dynamic action button generation.

**Available Actions:**
```php
// Project Actions
'edit_project'          // Edit project details
'delete_project'        // Delete project
'duplicate_project'     // Duplicate project
'archive_project'       // Archive project
'change_stage'          // Change project stage
'add_comment'           // Add project comment
'upload_file'           // Upload project file
'create_order'          // Create WooCommerce order

// Proposal Actions
'approve_proposal'      // Approve proposal
'reject_proposal'       // Reject proposal
'request_changes'       // Request proposal changes
'download_proposal'     // Download proposal PDF
'duplicate_proposal'    // Duplicate proposal

// Request Actions
'approve_request'       // Approve request
'reject_request'        // Reject request
'convert_to_project'    // Convert request to project
'add_notes'             // Add request notes
```

#### Button Customization
```php
// Add custom action button
add_filter('arsol_pfw_sidebar_action_buttons', function($buttons, $entity_type, $entity_id) {
    if ($entity_type === 'project') {
        $buttons['custom_action'] = array(
            'text' => 'Custom Action',
            'url' => '#',
            'class' => 'arsol-pfw-button arsol-pfw-custom',
            'icon' => 'dashicons-admin-generic',
            'confirm' => 'Are you sure?',
            'permission' => 'edit_projects'
        );
    }
    return $buttons;
}, 10, 3);

// Modify existing button
add_filter('arsol_pfw_sidebar_button_edit_project', function($button, $entity_id) {
    $button['text'] = 'Customize Project';
    $button['class'] .= ' custom-edit-button';
    return $button;
}, 10, 2);
```

---

## Customer Portal Integration

### WooCommerce My Account Integration

#### Endpoint Registration
```php
// Custom endpoints are automatically registered
'projects'                  // Main projects dashboard
'project-overview'          // Individual project overview
'project-files'             // Project files management
'project-orders'            // Project-related orders
'project-subscriptions'     // Project subscriptions
'project-create'            // Create new project
'project-request'           // Create project request
'project-view-proposal'     // View proposal details
'project-view-request'      // View request details
```

#### Navigation Integration
The plugin automatically adds navigation items to the WooCommerce My Account menu:

```php
// Menu items added automatically
'Projects'                  // Link to projects dashboard
'Create Project'            // Link to project creation (if permitted)
'Request Project'           // Link to project request (if permitted)
```

#### Menu Customization
```php
// Customize My Account menu items
add_filter('arsol_pfw_account_menu_items', function($items) {
    // Rename menu item
    $items['projects']['title'] = 'My Projects';
    
    // Add custom menu item
    $items['custom-page'] = array(
        'title' => 'Custom Page',
        'url' => wc_get_account_endpoint_url('custom-page'),
        'permission' => 'read'
    );
    
    return $items;
});
```

### Access Control

#### Permission-Based Display
```php
// Check user permissions for various actions
$can_create_projects = arsol_pfw_user_can_create_projects($user_id);
$can_request_projects = arsol_pfw_user_can_request_projects($user_id);
$can_view_project = arsol_pfw_user_can_view_project($user_id, $project_id);
$can_edit_project = arsol_pfw_user_can_edit_project($user_id, $project_id);
```

#### Access Control in Templates
```php
<?php if (arsol_pfw_user_can_create_projects(get_current_user_id())): ?>
    <a href="<?php echo wc_get_account_endpoint_url('project-create'); ?>" class="button">
        Create New Project
    </a>
<?php endif; ?>

<?php if (arsol_pfw_user_can_edit_project(get_current_user_id(), $project_id)): ?>
    <a href="<?php echo admin_url('post.php?post=' . $project_id . '&action=edit'); ?>" class="button">
        Edit Project
    </a>
<?php endif; ?>
```

---

## Responsive Design & Styling

### CSS Classes Reference

#### Layout Classes
```css
.arsol-pfw-container        /* Main container */
.arsol-pfw-grid            /* Grid layout */
.arsol-pfw-flex            /* Flexbox layout */
.arsol-pfw-sidebar         /* Sidebar container */
.arsol-pfw-content         /* Main content area */
```

#### Component Classes
```css
.arsol-pfw-project-card     /* Project card component */
.arsol-pfw-proposal-card    /* Proposal card component */
.arsol-pfw-request-card     /* Request card component */
.arsol-pfw-meta-box         /* Meta information box */
.arsol-pfw-action-buttons   /* Action buttons container */
.arsol-pfw-status-badge     /* Status badge */
.arsol-pfw-progress-bar     /* Progress bar */
```

#### State Classes
```css
.arsol-pfw-stage-active     /* Active project stage */
.arsol-pfw-stage-completed  /* Completed project stage */
.arsol-pfw-stage-on-hold    /* On hold project stage */
.arsol-pfw-priority-high    /* High priority */
.arsol-pfw-priority-medium  /* Medium priority */
.arsol-pfw-priority-low     /* Low priority */
```

#### Button Classes
```css
.arsol-pfw-button           /* Base button class */
.arsol-pfw-button-primary   /* Primary action button */
.arsol-pfw-button-secondary /* Secondary action button */
.arsol-pfw-button-danger    /* Danger/delete button */
.arsol-pfw-button-small     /* Small button size */
.arsol-pfw-button-large     /* Large button size */
```

### Mobile Responsiveness

#### Breakpoints
```css
/* Mobile First Approach */
@media (min-width: 768px) {  /* Tablet */
    .arsol-pfw-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) { /* Desktop */
    .arsol-pfw-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .arsol-pfw-sidebar {
        position: sticky;
        top: 20px;
    }
}
```

#### Mobile Optimizations
- Touch-friendly buttons (minimum 44px)
- Collapsible sidebar on mobile
- Horizontal scrolling for tables
- Optimized image sizes
- Reduced animation on mobile

---

## Performance Optimization

### Template Caching
```php
// Cache template parts for better performance
add_filter('arsol_pfw_cache_template_parts', '__return_true');

// Set cache duration (in seconds)
add_filter('arsol_pfw_template_cache_duration', function() {
    return 3600; // 1 hour
});
```

### Lazy Loading
```php
// Enable lazy loading for project lists
add_filter('arsol_pfw_enable_lazy_loading', '__return_true');

// Set lazy loading threshold
add_filter('arsol_pfw_lazy_loading_threshold', function() {
    return 10; // Load 10 items initially
});
```

### Asset Optimization
```php
// Minify frontend assets
add_filter('arsol_pfw_minify_assets', '__return_true');

// Combine CSS files
add_filter('arsol_pfw_combine_css', '__return_true');

// Load assets only on plugin pages
add_filter('arsol_pfw_conditional_asset_loading', '__return_true');
```

---

## Customization Examples

### Custom Template Override
```php
// In your theme: arsol-projects-for-woo/woocommerce/myaccount/projects.php
<?php
// Custom projects dashboard template
get_header('projects'); // Custom header for projects

// Custom navigation
echo '<nav class="custom-project-nav">';
echo '<a href="#" class="nav-item active">All Projects</a>';
echo '<a href="#" class="nav-item">Active</a>';
echo '<a href="#" class="nav-item">Completed</a>';
echo '</nav>';

// Custom project display
foreach ($projects as $project) {
    echo '<div class="custom-project-card">';
    echo '<h3>' . get_the_title($project->ID) . '</h3>';
    echo '<div class="project-meta">';
    echo '<span class="stage">' . get_post_meta($project->ID, '_project_stage', true) . '</span>';
    echo '<span class="date">' . get_the_date('M j, Y', $project->ID) . '</span>';
    echo '</div>';
    echo '</div>';
}

get_footer('projects'); // Custom footer for projects
?>
```

### Custom Shortcode Implementation
```php
// Add custom project statistics shortcode
add_shortcode('arsol_project_stats', 'arsol_project_stats_shortcode');
function arsol_project_stats_shortcode($atts) {
    $atts = shortcode_atts(array(
        'user_id' => get_current_user_id(),
        'period' => 'month',
        'template' => 'default'
    ), $atts);
    
    $stats = calculate_user_project_stats($atts['user_id'], $atts['period']);
    
    ob_start();
    ?>
    <div class="arsol-pfw-project-stats">
        <div class="stat-item">
            <span class="stat-number"><?php echo $stats['total']; ?></span>
            <span class="stat-label">Total Projects</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $stats['active']; ?></span>
            <span class="stat-label">Active Projects</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $stats['completed']; ?></span>
            <span class="stat-label">Completed</span>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
```

---

This completes the comprehensive frontend features reference. The system provides extensive customization options while maintaining clean, maintainable code and excellent user experience. 