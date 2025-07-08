# CRUD Infrastructure Testing Guide

This guide explains how to test the CRUD infrastructure (Phase 1) implementation.

## Test Files Created

1. **crud-test.php** - Browser-based test for WordPress admin
2. **wp-cli-test.php** - Command line test using WP-CLI

## Running Tests

### Method 1: Browser Test (Recommended)

1. Make sure your WordPress site is running
2. Login to WordPress admin
3. Navigate to any admin page and add `?crud_test=1` to the URL
4. Example: `https://yoursite.com/wp-admin/index.php?crud_test=1`
5. The test results will display at the top of the page

### Method 2: WP-CLI Test

1. Make sure WP-CLI is installed
2. Navigate to your WordPress root directory
3. Run: `wp eval-file wp-cli-test.php`
4. Test results will display in the terminal

### Method 3: Direct PHP Test

1. Navigate to your WordPress root directory
2. Run: `php -f crud-test.php`
3. Note: This may require additional setup for WordPress constants

## What the Tests Check

### 1. Class Loading
- ✅ ARSOL_PFW_Request class exists
- ✅ ARSOL_PFW_Data_Stores class exists  
- ✅ ARSOL_PFW_Request_Data_Store class exists

### 2. WooCommerce Integration
- ✅ Data store registered with WooCommerce
- ✅ WC_Data_Store::load() works correctly
- ✅ Proper inheritance from WC_Data

### 3. CRUD Operations
- ✅ CREATE: Can create new requests
- ✅ READ: Can load existing requests
- ✅ UPDATE: Can modify request data
- ✅ DELETE: Can remove requests

### 4. Stage Management
- ✅ Initial stage set correctly
- ✅ Stage transitions work
- ✅ Business logic methods (approve, reject) work

## Expected Results

If all tests pass, you should see:
```
🎉 ALL TESTS PASSED!
CRUD infrastructure is working correctly. Ready for Phase 2.
```

## Troubleshooting

### Common Issues

1. **"Class not found" errors**
   - Check that all CRUD files are properly loaded
   - Verify namespace declarations
   - Ensure proper file inclusion order

2. **"WooCommerce integration failed"**
   - Make sure WooCommerce is active
   - Check data store registration
   - Verify WC_Data inheritance

3. **"CRUD operation failed"**
   - Check database permissions
   - Verify post type registration
   - Check taxonomy setup

### Debug Steps

1. Enable WordPress debug mode:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

2. Check error logs in `/wp-content/debug.log`

3. Test individual components:
   ```php
   // Test class loading
   var_dump(class_exists('Arsol_Projects_For_Woo\ARSOL_PFW_Request'));
   
   // Test data store
   $store = WC_Data_Store::load('arsol-pfw-request');
   var_dump($store);
   
   // Test basic instantiation
   $request = new Arsol_Projects_For_Woo\ARSOL_PFW_Request();
   var_dump($request);
   ```

## Next Steps

Once all tests pass:
1. ✅ Phase 1 (CRUD Infrastructure) is complete
2. ⏭️ Ready to proceed to Phase 2 (Wrapper Implementation)
3. 🚀 Can begin updating existing CPT classes to use CRUD entities

## Support

If tests fail, check:
- File permissions
- WordPress/WooCommerce versions
- Plugin activation status
- Database connectivity
- PHP version compatibility (7.4+)
