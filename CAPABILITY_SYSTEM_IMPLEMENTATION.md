# WordPress-Native Capability System Implementation

## Overview
The Arsol Projects for Woo plugin now uses a **WordPress-native capability system** that works with existing WordPress roles instead of creating custom roles. This implementation follows WordPress and WooCommerce best practices.

## System Architecture

### 1. WordPress-Native Capabilities
The plugin defines granular capabilities for each custom post type:

#### Master Capability
- `arsol_pfw_manage` - Full plugin management access

#### Project Capabilities
- `edit_arsol_pfw_projects` - Edit own projects
- `edit_others_arsol_pfw_projects` - Edit others' projects
- `publish_arsol_pfw_projects` - Publish projects
- `read_private_arsol_pfw_projects` - Read private projects
- `delete_arsol_pfw_projects` - Delete own projects
- `delete_others_arsol_pfw_projects` - Delete others' projects
- `edit_private_arsol_pfw_projects` - Edit private projects
- `edit_published_arsol_pfw_projects` - Edit published projects

#### Proposal Capabilities (same pattern)
- `edit_arsol_pfw_proposals`
- `edit_others_arsol_pfw_proposals`
- `publish_arsol_pfw_proposals`
- `read_private_arsol_pfw_proposals`
- `delete_arsol_pfw_proposals`
- `delete_others_arsol_pfw_proposals`
- `edit_private_arsol_pfw_proposals`
- `edit_published_arsol_pfw_proposals`

#### Request Capabilities (same pattern)
- `edit_arsol_pfw_requests`
- `edit_others_arsol_pfw_requests`
- `publish_arsol_pfw_requests`
- `read_private_arsol_pfw_requests`
- `delete_arsol_pfw_requests`
- `delete_others_arsol_pfw_requests`
- `edit_private_arsol_pfw_requests`
- `edit_published_arsol_pfw_requests`

### 2. Custom Post Type Configuration
All CPTs use proper WordPress capability mapping:

```php
'capability_type' => array('arsol_pfw_project', 'arsol_pfw_projects'),
'map_meta_cap' => true,  // WordPress handles ownership automatically
'capabilities' => array(
    'edit_post' => 'edit_arsol_pfw_project',
    'read_post' => 'read_arsol_pfw_project',
    'delete_post' => 'delete_arsol_pfw_project',
    // ... etc
)
```

### 3. Role and Capability Management

#### Admin Settings Integration
- **Manager Roles**: Roles that get full PFW management access
- **Creator Roles**: Roles that get create/edit access for their own content
- Capabilities are assigned through **Settings → General**
- No custom roles - works with existing WordPress roles only

#### Automatic Administrator Access
- Administrators always get full PFW access
- Capabilities are added on plugin activation
- Clean removal on plugin deactivation

### 4. Helper Methods
The `Admin_Capabilities` class provides clean helper methods:

```php
// Check management access
Admin_Capabilities::can_manage_projects($user_id)

// Check creation access
Admin_Capabilities::can_create_projects($user_id)
Admin_Capabilities::can_create_project_requests($user_id)
Admin_Capabilities::can_create_project_proposals($user_id)

// Check specific item editing (WordPress handles ownership)
Admin_Capabilities::can_edit_project($user_id, $project_id)
Admin_Capabilities::can_edit_project_request($user_id, $request_id)
Admin_Capabilities::can_edit_project_proposal($user_id, $proposal_id)
```

## Key Benefits

### 1. WordPress-Native Security
- Uses `map_meta_cap = true` for proper ownership handling
- Leverages WordPress's built-in capability system
- No custom security logic needed

### 2. Role Flexibility
- Works with any existing WordPress role
- Easy integration with role management plugins
- No custom roles cluttering the system

### 3. Granular Control
- Separate capabilities for each post type
- Different permission levels (own vs others, private vs published)
- Proper WordPress ownership handling

### 4. Admin-Friendly
- Capabilities assigned through familiar settings interface
- Clear user management page showing current capabilities
- Easy to understand permission levels

## Implementation Files

### Core Classes
- `includes/classes/class-admin-capabilities.php` - Main capability management
- `includes/classes/class-admin-users.php` - User management interface
- `includes/classes/class-admin-settings-general.php` - Settings integration

### CPT Registration
- `includes/custom-post-types/project/class-project-cpt-setup.php`
- `includes/custom-post-types/project-proposal/class-project-proposal-cpt-setup.php`
- `includes/custom-post-types/project-request/class-project-request-cpt-setup.php`

## Usage Examples

### Checking Permissions in Code
```php
// Check if user can manage projects
if (Admin_Capabilities::can_manage_projects()) {
    // Show admin features
}

// Check if user can create requests
if (Admin_Capabilities::can_create_project_requests()) {
    // Show request creation form
}

// Check if user can edit specific project (ownership handled automatically)
if (current_user_can('edit_arsol_pfw_project', $project_id)) {
    // Show edit button
}
```

### Admin Settings Usage
1. Go to **Projects → Settings → General**
2. Set **Manager Roles** to roles that should have full access
3. Set **Creator Roles** to roles that should be able to create content
4. Save settings - capabilities are automatically assigned

### User Management
1. Go to **Projects → Users** to see all users with PFW capabilities
2. Edit individual users to see their current PFW capabilities
3. Change user roles to modify their PFW access

## Migration Notes

### From Previous System
- Old capabilities (`manage_projects`, `create_projects`, `request_projects`) are automatically removed
- New WordPress-native capabilities are assigned based on role settings
- No data loss - only capability system changes

### Plugin Activation Required
- Plugin must be deactivated and reactivated to properly set up new capability system
- Administrator role automatically gets full access
- Settings-based role assignments are preserved

## Testing Instructions

### 1. Basic Functionality
```bash
# Deactivate and reactivate plugin
wp plugin deactivate arsol-projects-for-woo
wp plugin activate arsol-projects-for-woo
```

### 2. Check Administrator Capabilities
```php
$admin = get_userdata(1);
var_dump($admin->has_cap('arsol_pfw_manage')); // Should be true
var_dump($admin->has_cap('edit_arsol_pfw_projects')); // Should be true
```

### 3. Test Role Assignment
1. Create test user with specific role
2. Assign that role PFW capabilities in settings
3. Verify user has proper capabilities
4. Test ownership-based editing

### 4. Verify Settings Integration
1. Go to Settings → General
2. Modify Manager and Creator role assignments
3. Save and verify capabilities are properly assigned/removed

## Security Notes

- Uses WordPress's built-in capability system
- Proper ownership handling via `map_meta_cap`
- No custom security code required
- Follows WordPress security best practices
- Compatible with security plugins

## Compatibility

- WordPress 5.8+
- WooCommerce 5.0+
- PHP 7.4+
- Compatible with role management plugins
- Follows WordPress coding standards 