# Simplified Override System Implementation

## Overview

The simplified override system replaces the complex manager override functionality with a cleaner, more maintainable approach that uses **capabilities as the primary check** with overrides as a secondary consideration.

## Key Changes

### 1. Simplified Settings Structure

**Before:**
- Complex manager override settings with separate capability mappings
- Manager default behavior settings
- Multiple override capability arrays

**After:**
- Single `allow_manager_overrides` checkbox
- Direct capability checking with override logic
- Cleaner user meta storage

### 2. Simplified User Meta Storage

**Before:**
```php
// Complex array-based storage
$user_overrides = array('manage_stages', 'manage_workflows');
update_user_meta($user_id, 'arsol_pfw_manager_overrides', $user_overrides);
```

**After:**
```php
// Simple individual meta keys
update_user_meta($user_id, 'arsol_pfw_manager_override_manage_stages', '1'); // Explicitly enabled
update_user_meta($user_id, 'arsol_pfw_manager_override_manage_stages', '0'); // Explicitly disabled
// No meta = follow the capability
```

### 3. ✅ CORRECTED: Capability-First Logic

**Before:**
```php
// Complex logic with admin settings first
if ($user_override_enabled) {
    return self::has_manager_override_capability($user_id, $capability);
}
// Default behavior logic...
```

**After:**
```php
// ✅ CAPABILITIES FIRST: Check WordPress capabilities first
$user = get_user_by('id', $user_id);
$wp_capability = $capability_mappings[$capability];

if (!$user->has_cap($wp_capability)) {
    return false; // User doesn't have capability - no override checks happen
}

// ✅ THEN check overrides (only if user has capability)
if (!$allow_overrides) {
    return true; // Follow the capability
}

$user_override = get_user_meta($user_id, 'arsol_pfw_manager_override_' . $capability, true);
if ($user_override === '1') {
    return true; // Explicitly enabled
} elseif ($user_override === '0') {
    return false; // Explicitly disabled
}

return true; // No override exists - follow the capability
```

## Implementation Details

### 1. Settings Page Changes

**File:** `includes/admin/settings/permissions.php`

- Removed `manager_default_behavior` field
- Simplified `allow_manager_overrides` to single checkbox
- Removed complex override capability management
- Updated validation to handle simplified structure

### 2. Capabilities Handler Changes

**File:** `includes/core/capabilities-handler.php`

- ✅ **Updated `get_effective_manager_capability()` with capability-first logic**
- Updated `get_user_manager_overrides()` to work with individual meta keys
- Updated `has_manager_override_capability()` for new storage format

### 3. User Profile Changes

**File:** `includes/admin/users.php`

- Simplified user profile fields to show only enabled capabilities
- Updated save logic to handle individual override meta keys
- Removed complex capability arrays

## ✅ CORRECTED: Override Logic Flow

1. **Check if user is a manager** - If not, return false
2. **Check if user has the specific WordPress capability** - If not, return false (no override checks happen)
3. **If user has capability**, check if overrides are enabled globally
4. **If overrides are disabled globally**, follow the capability (return true)
5. **If overrides are enabled**, check user override state:
   - `'1'` = Explicitly enabled
   - `'0'` = Explicitly disabled  
   - **No meta = Follow the capability** (return true)
6. **Return effective capability**

## Key Principles

✅ **Capabilities are the primary check** - WordPress role capabilities are checked first  
✅ **Overrides only matter if user has capability** - If no capability exists, overrides are ignored  
✅ **If no overrides exist, follow the capability** - Default behavior uses the capability  
✅ **Capabilities are preferred** - Only overridden when explicitly set  

## User Meta Storage Format

Each capability has its own meta key:
- `arsol_pfw_manager_override_manage_stages`
- `arsol_pfw_manager_override_manage_workflows`
- `arsol_pfw_manager_override_manage_settings`
- `arsol_pfw_manager_override_manage_permissions`

Values:
- `'1'` = Explicitly enabled by user
- `'0'` = Explicitly disabled by user
- **No meta = Follow the capability**

## Benefits

1. **✅ Correct Logic** - Capabilities are checked first, overrides are secondary
2. **Better Performance** - Fewer database queries when user lacks capability
3. **Cleaner Code** - Straightforward capability-first logic
4. **Easier Debugging** - Clear separation between capability and override logic
5. **WordPress Standards** - Uses standard WordPress capability checking

## Migration Notes

- Existing complex override data will be ignored
- New system starts fresh with WordPress capabilities
- No data migration needed - clean slate approach
- Backward compatibility maintained for basic functionality

## Testing

Use the test file `tests/test-simplified-override-system.php` to verify:
- ✅ Capability checking happens first
- ✅ Overrides only matter if user has capability
- ✅ No override = follow capability
- ✅ Explicit overrides work correctly

Run tests by accessing: `?test_override_system=1` (admin only) 