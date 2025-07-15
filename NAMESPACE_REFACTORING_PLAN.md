# Namespace Refactoring Plan

## Overview
This document outlines the comprehensive namespace reorganization for the Arsol Projects for Woo plugin to improve consistency, clarity, and maintainability.

## Current → New Namespace Mapping

### 1. Custom Post Types

#### Project CPT
| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Project` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Project\Admin\Single` | `Project` | Single project admin |
| `Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Projects` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Project\Admin\List` | `Projects` | Project list admin |
| `Arsol_Projects_For_Woo\Custom_Post_Types\Project\Admin\Setup` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Project\Admin\Setup` | `Setup` | Project setup |
| `Arsol_Projects_For_Woo\Custom_Post_Types\Project\Frontend_Handler` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Project\Frontend\Handler` | `Frontend_Handler` | Project frontend |

#### Proposal CPT
| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal\Admin\Single` | `Proposal` | Single proposal admin |
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposals` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal\Admin\List` | `Proposals` | Proposal list admin |
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Setup` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal\Admin\Setup` | `Setup` | Proposal setup |
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Budget` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal\Admin\Budget` | `Proposal_Budget` | Budget handling |
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin\Proposal_Quotation` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal\Admin\Quotation` | `Proposal_Quotation` | Quotation handling |
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Proposal_Conversion` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal\Conversion` | `Proposal_Conversion` | Conversion handling |

#### Request CPT
| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Request` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request\Admin\Single` | `Request` | Single request admin |
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Requests` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request\Admin\List` | `Requests` | Request list admin |
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Admin\Setup` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request\Admin\Setup` | `Setup` | Request setup |
| `Arsol_Projects_For_Woo\Custom_Post_Types\ProjectRequest\Request_Conversion` | `Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request\Conversion` | `Request_Conversion` | Conversion handling |

### 2. Data Stores

| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Data_Stores\Project_Data_Store` | `Arsol_Projects_For_Woo\Data_Stores\Project` | `Project_Data_Store` | Project data store |
| `Arsol_Projects_For_Woo\Data_Stores\Proposal_Data_Store` | `Arsol_Projects_For_Woo\Data_Stores\Proposal` | `Proposal_Data_Store` | Proposal data store |
| `Arsol_Projects_For_Woo\Data_Stores\Request_Data_Store` | `Arsol_Projects_For_Woo\Data_Stores\Request` | `Request_Data_Store` | Request data store |

### 3. Admin Settings

| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Admin\Settings_General` | `Arsol_Projects_For_Woo\Admin\Settings\General` | `Settings_General` | General settings |
| `Arsol_Projects_For_Woo\Admin\Settings_Display` | `Arsol_Projects_For_Woo\Admin\Settings\Display` | `Settings_Display` | Display settings |
| `Arsol_Projects_For_Woo\Admin\Settings_Files` | `Arsol_Projects_For_Woo\Admin\Settings\Files` | `Settings_Files` | File settings |
| `Arsol_Projects_For_Woo\Admin\Settings_Integrations` | `Arsol_Projects_For_Woo\Admin\Settings\Integrations` | `Settings_Integrations` | Integration settings |
| `Arsol_Projects_For_Woo\Admin\Settings_Advanced` | `Arsol_Projects_For_Woo\Admin\Settings\Advanced` | `Settings_Advanced` | Advanced settings |
| `Arsol_Projects_For_Woo\Admin\Settings_Tools` | `Arsol_Projects_For_Woo\Admin\Settings\Tools` | `Settings_Tools` | Tools settings |

### 4. Frontend Classes

| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Frontend\Frontend_Handler` | `Arsol_Projects_For_Woo\Frontend\Handler` | `Frontend_Handler` | Base frontend handler |
| `Arsol_Projects_For_Woo\Frontend\Proposal_Frontend` | `Arsol_Projects_For_Woo\Frontend\Proposal` | `Proposal_Frontend` | Proposal frontend |
| `Arsol_Projects_For_Woo\Frontend\Request_Frontend` | `Arsol_Projects_For_Woo\Frontend\Request` | `Request_Frontend` | Request frontend |
| `Arsol_Projects_For_Woo\Frontend_Comments` | `Arsol_Projects_For_Woo\Frontend\Comments` | `Frontend_Comments` | Comments handling |
| `Arsol_Projects_For_Woo\Frontend_Template_Sidebar_Buttons` | `Arsol_Projects_For_Woo\Frontend\Template\Sidebar\Buttons` | `Frontend_Template_Sidebar_Buttons` | Sidebar buttons |
| `Arsol_Projects_For_Woo\Frontend_Template_Sidebar_Meta` | `Arsol_Projects_For_Woo\Frontend\Template\Sidebar\Meta` | `Frontend_Template_Sidebar_Meta` | Sidebar metadata |
| `Arsol_Projects_For_Woo\Frontend_Template_Overrides` | `Arsol_Projects_For_Woo\Frontend\Template\Overrides` | `Frontend_Template_Overrides` | Template overrides |
| `Arsol_Projects_For_Woo\Frontend_Woocommerce_Checkout` | `Arsol_Projects_For_Woo\Frontend\Woocommerce\Checkout` | `Frontend_Woocommerce_Checkout` | WooCommerce checkout |
| `Arsol_Projects_For_Woo\Woocommerce\Frontend_Endpoints` | `Arsol_Projects_For_Woo\Frontend\Woocommerce\Endpoints` | `Frontend_Endpoints` | WooCommerce endpoints |

### 5. Taxonomies

| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Taxonomies\Taxonomies_Setup` | `Arsol_Projects_For_Woo\Taxonomies\Setup` | `Taxonomies_Setup` | Taxonomy setup |
| `Arsol_Projects_For_Woo\Taxonomies\ProjectStage\Taxonomies_Project_Stage_Setup` | `Arsol_Projects_For_Woo\Taxonomies\Project_Stage\Setup` | `Taxonomies_Project_Stage_Setup` | Project stage setup |
| `Arsol_Projects_For_Woo\Taxonomies\ProjectStage\Taxonomies_Project_Stage_Admin` | `Arsol_Projects_For_Woo\Taxonomies\Project_Stage\Admin` | `Taxonomies_Project_Stage_Admin` | Project stage admin |
| `Arsol_Projects_For_Woo\Taxonomies\ProposalStage\Taxonomies_Proposal_Stage_Setup` | `Arsol_Projects_For_Woo\Taxonomies\Proposal_Stage\Setup` | `Taxonomies_Proposal_Stage_Setup` | Proposal stage setup |
| `Arsol_Projects_For_Woo\Taxonomies\ProposalStage\Taxonomies_Proposal_Stage_Admin` | `Arsol_Projects_For_Woo\Taxonomies\Proposal_Stage\Admin` | `Taxonomies_Proposal_Stage_Admin` | Proposal stage admin |
| `Arsol_Projects_For_Woo\Taxonomies\RequestStage\Taxonomies_Request_Stage_Setup` | `Arsol_Projects_For_Woo\Taxonomies\Request_Stage\Setup` | `Taxonomies_Request_Stage_Setup` | Request stage setup |
| `Arsol_Projects_For_Woo\Taxonomies\RequestStage\Taxonomies_Request_Stage_Admin` | `Arsol_Projects_For_Woo\Taxonomies\Request_Stage\Admin` | `Taxonomies_Request_Stage_Admin` | Request stage admin |

### 6. WooCommerce Integration

| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Workflow\Workflow_Handler` | `Arsol_Projects_For_Woo\Workflow\Handler` | `Workflow_Handler` | Workflow handler |
| `Arsol_Projects_For_Woo\Woocommerce_Biller` | `Arsol_Projects_For_Woo\Woocommerce\Biller` | `Woocommerce_Biller` | Billing and invoicing |
| `Arsol_Projects_For_Woo\Woocommerce_Logs` | `Arsol_Projects_For_Woo\Woocommerce\Logs` | `Woocommerce_Logs` | Logging system |
| `Arsol_Projects_For_Woo\Woocommerce_Subscriptions` | `Arsol_Projects_For_Woo\Woocommerce\Subscriptions` | `Woocommerce_Subscriptions` | Subscription handling |

### 7. Email System

| Current Namespace | New Namespace | Class Name | Purpose |
|------------------|---------------|------------|---------|
| `Arsol_Projects_For_Woo\Arsol_Email_Manager` | `Arsol_Projects_For_Woo\WooCommerce\Email\Handler` | `Arsol_Email_Manager` | Email manager |
| `WC_Email_New_Request` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\New_Request` | `WC_Email_New_Request` | New request email |
| `WC_Email_Admin_New_Request` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Admin_New_Request` | `WC_Email_Admin_New_Request` | Admin new request email |
| `WC_Email_Request_Stage` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Request_Stage` | `WC_Email_Request_Stage` | Request stage email |
| `WC_Email_Proposal_Processing` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Proposal_Processing` | `WC_Email_Proposal_Processing` | Proposal processing email |
| `WC_Email_Proposal_Ready` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Proposal_Ready` | `WC_Email_Proposal_Ready` | Proposal ready email |
| `WC_Email_Proposal_Decision` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Proposal_Decision` | `WC_Email_Proposal_Decision` | Proposal decision email |
| `WC_Email_Project_Creation` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Project_Creation` | `WC_Email_Project_Creation` | Project creation email |
| `WC_Email_Project_Stage` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Project_Stage` | `WC_Email_Project_Stage` | Project stage email |
| `WC_Email_Project_Completion` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Project_Completion` | `WC_Email_Project_Completion` | Project completion email |
| `WC_Email_Admin_New_Project` | `Arsol_Projects_For_Woo\WooCommerce\Email\Templates\Admin_New_Project` | `WC_Email_Admin_New_Project` | Admin new project email |

## Implementation Steps

### Phase 1: File Structure Reorganization
1. Create new directory structure
2. Move files to new locations
3. Update file paths in autoloader

### Phase 2: Namespace Updates
1. Update namespace declarations in all files
2. Update use statements
3. Update class references

### Phase 3: Class Name Updates
1. Update class names where needed
2. Update instantiation calls
3. Update inheritance references

### Phase 4: Testing and Validation
1. Syntax checking
2. Functionality testing
3. Integration testing

## Files to be Modified

### Core Files
- `arsol-projects-for-woo.php` (main plugin file)
- `class-arsol-pfw-setup.php` (setup class)

### Custom Post Types
- All files in `includes/custom-post-types/`
- All admin classes
- All setup classes
- All conversion classes

### Data Stores
- `includes/data-stores/class-arsol-pfw-data-store-project.php`
- `includes/data-stores/class-arsol-pfw-data-store-proposal.php`
- `includes/data-stores/class-arsol-pfw-data-store-request.php`

### Admin Settings
- All files in `includes/admin/class-arsol-pfw-admin-settings-*.php`

### Frontend
- All files in `includes/frontend/`
- All template classes
- All WooCommerce integration classes

### Taxonomies
- All files in `includes/taxonomies/`

### WooCommerce Integration
- All files in `includes/integrations/woocommerce/`
- All email template classes

### Functions
- All files in `includes/functions/`

## Impact Assessment

### High Impact
- All class instantiation calls
- All use statements
- All inheritance references
- All autoloader configurations

### Medium Impact
- Template files that reference classes
- JavaScript files that may reference PHP classes
- Configuration files

### Low Impact
- CSS files
- Documentation files
- Asset files

## Rollback Plan

1. Keep backup of current structure
2. Use Git branches for testing
3. Maintain backward compatibility during transition
4. Create migration scripts if needed

## Testing Strategy

1. **Unit Testing**: Test each class individually
2. **Integration Testing**: Test class interactions
3. **Functional Testing**: Test complete workflows
4. **Regression Testing**: Ensure no functionality is broken

## Timeline Estimate

- **Phase 1**: 2-3 hours
- **Phase 2**: 4-6 hours
- **Phase 3**: 2-3 hours
- **Phase 4**: 2-4 hours

**Total Estimated Time**: 10-16 hours

## Risk Assessment

### High Risk
- Breaking existing functionality
- Incompatibility with existing installations
- Performance impact

### Medium Risk
- Development workflow disruption
- Testing complexity
- Documentation updates

### Low Risk
- File organization
- Code readability improvements

## Success Criteria

1. All classes load correctly
2. All functionality works as expected
3. No breaking changes for end users
4. Improved code organization
5. Better maintainability
6. Consistent naming conventions

## Notes

- This refactoring will require careful testing
- Consider implementing in stages
- Maintain detailed change logs
- Update all documentation
- Consider impact on third-party integrations 