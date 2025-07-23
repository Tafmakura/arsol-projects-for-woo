# Native WordPress Capabilities Refactoring

## Overview

Refactored the `Capabilities_Handler` class to use native WordPress capability functions as much as possible, removing wrapper methods that duplicated native functionality.

## Changes Made

### 1. **Removed Wrapper Methods**

**Before:**
```php
// Wrapper methods that duplicated native WordPress functions
public static function can_manage_projects($user_id = null) {
    return current_user_can('arsol_pfw_manage') || current_user_can('manage_options');
}

public static function can_create_projects($user_id = null) {
    return current_user_can('edit_arsol_pfw_projects') || current_user_can('arsol_pfw_manage');
}

public static function can_create_project_requests($user_id = null) {
    return current_user_can('edit_arsol_pfw_requests') || current_user_can('arsol_pfw_manage');
}
```

**After:**
```php
// Use native WordPress functions directly
$user = get_user_by('id', $user_id);
$can_manage = $user && ($user->has_cap('arsol_pfw_manage') || $user->has_cap('manage_options'));
$can_create = $user && ($user->has_cap('edit_arsol_pfw_projects') || $user->has_cap('arsol_pfw_manage'));
$can_request = $user && ($user->has_cap('edit_arsol_pfw_requests') || $user->has_cap('arsol_pfw_manage'));
```

### 2. **Kept Custom Logic Methods**

**Methods that add custom logic (kept):**
- `get_effective_manager_capability()` - Core override logic
- `can_manage_stages()` - Has override support
- `can_manage_workflows()` - Has override support
- `can_manage_settings()` - Has override support
- `can_manage_permissions()` - Has override support
- `get_user_permission_level()` - Custom permission level logic
- `get_user_manager_overrides()` - Override management
- `has_manager_override_capability()` - Override checking

### 3. **Updated Files**

**Files updated to use native WordPress capabilities:**

1. **`includes/core/access-handler.php`**
   - Replaced `Capabilities_Handler::can_create_projects()` with native `$user->has_cap()`
   - Replaced `Capabilities_Handler::can_manage_projects()` with native `$user->has_cap()`
   - Replaced `Capabilities_Handler::can_create_project_requests()` with native `$user->has_cap()`

2. **`includes/frontend/request/request.php`**
   - Replaced `Capabilities_Handler::can_create_project_requests()` with native capability checking

3. **`includes/frontend/proposal/proposal.php`**
   - Replaced `Capabilities_Handler::can_create_project_proposals()` with native capability checking

4. **`includes/custom-post-types/project/frontend/handler.php`**
   - Replaced `Capabilities_Handler::can_create_projects()` with native capability checking

5. **`ui/components/frontend/endpoint-create-project.php`**
   - Replaced `Capabilities_Handler::can_create_projects()` with native capability checking

6. **`ui/components/frontend/endpoint-create-request.php`**
   - Replaced `Capabilities_Handler::can_create_project_requests()` with native capability checking

## Benefits

### 1. **Reduced Code Duplication**
- Eliminated wrapper methods that just called native WordPress functions
- Direct use of `current_user_can()` and `$user->has_cap()`

### 2. **Better Performance**
- Fewer method calls
- Direct WordPress capability checking
- No unnecessary abstraction layers

### 3. **WordPress Standards Compliance**
- Uses native WordPress capability system directly
- Follows WordPress coding standards
- More maintainable and familiar to WordPress developers

### 4. **Cleaner Architecture**
- `Capabilities_Handler` now focuses only on custom logic (overrides)
- Native capabilities handled by WordPress core
- Clear separation of concerns

## Native WordPress Capability Usage

### **Basic Capability Checking:**
```php
// Instead of wrapper methods, use native WordPress functions
$user = get_user_by('id', $user_id);
$can_manage = $user && ($user->has_cap('arsol_pfw_manage') || $user->has_cap('manage_options'));
$can_create = $user && ($user->has_cap('edit_arsol_pfw_projects') || $user->has_cap('arsol_pfw_manage'));
```

### **Object-Specific Capability Checking:**
```php
// For specific posts/objects
$can_edit = $user && ($user->has_cap('edit_arsol_pfw_project', $project_id) || $user->has_cap('arsol_pfw_manage'));
$can_delete = $user && ($user->has_cap('delete_arsol_pfw_project', $project_id) || $user->has_cap('arsol_pfw_manage'));
```

### **Current User Capability Checking:**
```php
// For current user
$can_manage = current_user_can('arsol_pfw_manage') || current_user_can('manage_options');
$can_create = current_user_can('edit_arsol_pfw_projects') || current_user_can('arsol_pfw_manage');
```

## Migration Notes

- **No breaking changes** - All functionality preserved
- **Override system unchanged** - Custom logic methods still work
- **Settings system unchanged** - Capability assignment still works
- **Performance improved** - Fewer method calls and better efficiency

## Testing

The refactoring maintains all existing functionality while improving performance and reducing code complexity. All capability checking now uses native WordPress functions where possible, with custom logic only where necessary (override system). 