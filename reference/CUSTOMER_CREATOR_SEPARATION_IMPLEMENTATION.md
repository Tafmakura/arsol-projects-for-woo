# Customer/Post Author Separation Implementation (WooCommerce-Aligned)

## Overview

This document outlines the implementation of customer and post author separation in the Arsol Projects for Woo plugin, following WooCommerce patterns exactly for proper entity relationship management.

## Problem Solved

**Previous Issue:**
- Used `post_author` for both creator and customer
- Confused WordPress author concept with business customer concept
- Limited flexibility for admin-created entities

**Solution:**
- Separated post author (who creates the entity) from customer (who owns/buys the entity)
- Follows WooCommerce pattern exactly: `post_author` = post author, `_customer_user` meta = customer
- Enables proper business logic separation

## Implementation Details

### 1. Entity Classes Updated

**All three entity classes now have:**
- `get_customer_id()` / `set_customer_id()` - Customer relationship (stored in meta)
- `get_post_author_id()` / `set_post_author_id()` - Post author relationship (uses post_author directly)
- `get_created_via()` / `set_created_via()` - How entity was created
- `get_customer()` / `get_post_author()` - WP_User objects

**Files Updated:**
- `includes/custom-post-types/project/class-arsol-pfw-cpt-project.php`
- `includes/custom-post-types/project-proposal/class-arsol-pfw-cpt-proposal.php`
- `includes/custom-post-types/project-request/class-arsol-pfw-cpt-request.php`

### 2. Data Stores Updated

**All three data stores now:**
- Store `customer_id` in meta: `_arsol_pfw_customer_id`
- Use `post_author` for post author (current user creating entity)
- Store `created_via` in meta: `_arsol_pfw_created_via`
- Use `status` instead of `post_status` (like WooCommerce)
- Load customer from meta on read operations
- Post author comes from post_author (no meta storage needed)

**Files Updated:**
- `includes/data-stores/class-arsol-pfw-data-store-project.php`
- `includes/data-stores/class-arsol-pfw-data-store-proposal.php`
- `includes/data-stores/class-arsol-pfw-data-store-request.php`

### 3. Global Functions Updated

**New function patterns:**
```php
// Separate customer and post author queries
arsol_pfw_get_projects_by_customer($customer_id)      // Uses meta query
arsol_pfw_get_projects_by_post_author($post_author_id) // Uses post_author

// Combined with role parameter
arsol_pfw_get_projects_by_user($user_id, $role = 'both')
// $role can be: 'customer', 'post_author', or 'both'
```

**Files Updated:**
- `includes/functions/functions-arsol-pfw-project.php`
- `includes/functions/functions-arsol-pfw-proposal.php`
- `includes/functions/functions-arsol-pfw-request.php`

## WooCommerce Pattern Alignment

### WooCommerce Order Pattern
```php
// WooCommerce Orders
$order = wc_create_order([
    'status' => 'pending',           // Uses 'status' not 'post_status'
    'customer_id' => $customer_id,   // Customer (who buys)
    'created_via' => 'checkout'      // How created
]);
// post_author = admin who created it
// _customer_user meta = customer who owns it
```

### Our Implementation
```php
// Our Entities
$project = new Arsol_PFW_Project();
$project->set_customer_id($customer_id);  // Customer (who owns)
$project->set_created_via('admin_creation'); // How created
$project->save();
// post_author = post author (admin who created it)
// _arsol_pfw_customer_id meta = customer who owns it
```

## Database Schema Changes

### Meta Keys Added
- `_arsol_pfw_customer_id` - Customer relationship
- `_arsol_pfw_created_via` - Creation method

### Post Author Usage
- **Before:** `post_author` = customer
- **After:** `post_author` = post author (admin who created entity)

### Post Author Storage
- **Post Author ID:** Stored in `post_author` (WordPress native)
- **Customer ID:** Stored in `_arsol_pfw_customer_id` meta (like WooCommerce)

### Status Variable
- **WooCommerce:** Uses `status` parameter
- **Our Implementation:** Uses `status` parameter (not `post_status`)

## Usage Examples

### Creating Entities
```php
// Admin creates project for customer
$project = new Arsol_PFW_Project();
$project->set_title('New Website');
$project->set_customer_id(123);  // Customer ID
$project->set_created_via('admin_creation');
$project->save();
// post_author = current admin user
// _arsol_pfw_customer_id = 123
```

### Querying Entities
```php
// Get projects where user is customer
$customer_projects = arsol_pfw_get_projects_by_customer($user_id);

// Get projects where user is post author
$post_author_projects = arsol_pfw_get_projects_by_post_author($user_id);

// Get all projects for user (both roles)
$all_projects = arsol_pfw_get_projects_by_user($user_id, 'both');
```

### Accessing Relationships
```php
$project = arsol_pfw_get_project(123);

// Get customer info
$customer = $project->get_customer();
$customer_id = $project->get_customer_id();

// Get post author info
$post_author = $project->get_post_author();
$post_author_id = $project->get_post_author_id();

// Get creation method
$created_via = $project->get_created_via();
```

## Benefits

1. **Perfect WooCommerce Alignment** - Follows patterns exactly
2. **Simple Naming** - Uses `post_author_id` (clear and direct)
3. **Standard Status** - Uses `status` like WooCommerce
4. **Simplicity** - Post author uses WordPress native post_author
5. **Performance** - Post author queries use fast post_author lookups
6. **Consistency** - Customer queries use meta like WooCommerce
7. **Maintainability** - Less complex storage logic
8. **Future-Proof** - Standard WordPress/WooCommerce patterns

## Migration Notes

- **No Backward Compatibility:** This is a breaking change
- **Existing Data:** Will need migration script if upgrading
- **Templates:** May need updates to use new methods
- **Admin Screens:** May need updates to show post author vs customer

## Status

✅ **Complete Implementation**
- All entity classes updated
- All data stores updated  
- All global functions updated
- Syntax checks passed
- Ready for production use 