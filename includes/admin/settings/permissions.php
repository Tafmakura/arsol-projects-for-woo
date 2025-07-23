<?php
/**
 * Admin Permissions Settings Class
 *
 * Handles the permissions settings page functionality.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin\Settings;

if (!defined('ABSPATH')) {
    exit;
}

class Permissions {
    /**
     * Constructor
     */
    public function __construct() {
        // Register settings after init to ensure text domain is loaded
        add_action('init', array($this, 'setup_settings'), 20);
        
        // Add scripts for admin page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Update capabilities when settings are saved
        add_action('update_option_arsol_pfw_permissions_settings', array($this, 'update_capabilities'), 10, 2);
    }

    /**
     * Setup settings after init
     */
    public function setup_settings() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('arsol_pfw_permissions_settings', 'arsol_pfw_permissions_settings', array($this, 'validate_settings'));

        // Project Manager Permissions Section
        add_settings_section(
            'arsol_project_manager_permissions',
            __('Project Manager Permissions', 'arsol-pfw'),
            array($this, 'render_project_manager_section'),
            'arsol_pfw_permissions_settings'
        );

        // Project Manager Roles
        add_settings_field(
            'project_manager_roles',
            __('Project Manager Roles', 'arsol-pfw'),
            array($this, 'render_manager_roles_field'),
            'arsol_pfw_permissions_settings',
            'arsol_project_manager_permissions',
            array(
                'field' => 'project_manager_roles',
                'description' => __('Select roles that can manage all projects, proposals, and requests.', 'arsol-pfw'),
                'class' => 'arsol-pfw-manager-roles-field'
            )
        );

        // Project Manager Capabilities
        add_settings_field(
            'project_manager_capabilities',
            __('Capabilities', 'arsol-pfw'),
            array($this, 'render_manager_capabilities_field'),
            'arsol_pfw_permissions_settings',
            'arsol_project_manager_permissions',
            array(
                'description' => __('Additional capabilities for project managers. Administrators always have all permissions regardless of these settings.', 'arsol-pfw'),
                'class' => 'arsol-pfw-manager-roles-field'
            )
        );

        // Manager Override Settings
        add_settings_field(
            'manager_override_settings',
            '',
            array($this, 'render_manager_override_field'),
            'arsol_pfw_permissions_settings',
            'arsol_project_manager_permissions',
            array(
                'description' => __('Allow individual project managers to override admin settings for enabled capabilities.', 'arsol-pfw'),
                'class' => 'arsol-pfw-manager-override-field'
            )
        );

        // Manager Default Behavior
        add_settings_field(
            'manager_default_behavior',
            __('New User Capabilities', 'arsol-pfw'),
            array($this, 'render_manager_default_behavior_field'),
            'arsol_pfw_permissions_settings',
            'arsol_project_manager_permissions',
            array(
                'description' => __('Default permission level assigned to new project managers.', 'arsol-pfw'),
                'class' => 'arsol-pfw-show-if-arsol-pfw-allow-manager-overrides-is-checked arsol-pfw-manager-default-behavior'
            )
        );

        // Customer Permissions Section
        add_settings_section(
            'arsol_customer_permissions',
            __('Customer Permissions', 'arsol-pfw'),
            array($this, 'render_customer_permissions_section'),
            'arsol_pfw_permissions_settings'
        );

        // Project User Roles
        add_settings_field(
            'project_user_roles',
            __('Customer Roles', 'arsol-pfw'),
            array($this, 'render_roles_field'),
            'arsol_pfw_permissions_settings',
            'arsol_customer_permissions',
            array(
                'field' => 'project_user_roles',
                'description' => __('Select roles that can create and manage their own projects as WooCommerce customers.', 'arsol-pfw'),
                'class' => 'arsol-pfw-user-roles-field'
            )
        );

        // Frontend Permissions
        add_settings_field(
            'arsol-pfw-user-project-permissions',
            __('Frontend Permissions', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_pfw_permissions_settings',
            'arsol_customer_permissions',
            array(
                'description' => __('Controls what users can do from the frontend', 'arsol-pfw'),
                'field' => 'user_project_permissions',
                'options' => array(
                    'none' => __('None', 'arsol-pfw'),
                    'request_projects' => __('All users can request projects', 'arsol-pfw'),
                    'create_projects' => __('All users can create projects', 'arsol-pfw'),
                    'can_do_both' => __('All users can do both', 'arsol-pfw'),
                    'user_specific' => __('Set per user', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-frontend-permissions'
            )
        );

        // Default User Permissions
        add_settings_field(
            'arsol-pfw-default-user-permission',
            __('New User Capabilities', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_pfw_permissions_settings',
            'arsol_customer_permissions',
            array(
                'description' => __('Default permission level assigned to new customers.', 'arsol-pfw'),
                'field' => 'default_user_permission',
                'options' => array(
                    'none' => __('None', 'arsol-pfw'),
                    'request_projects' => __('Can request projects', 'arsol-pfw'),
                    'create_projects' => __('Can create projects', 'arsol-pfw'),
                    'can_do_both' => __('Can do both', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user_specific arsol-pfw-new-user-permissions'
            )
        );
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our settings page
        if ($hook !== 'toplevel_page_arsol-projects') {
            return;
        }

        // Note: arsol-pfw-admin script and style are now handled by the Assets class
        // which includes the settings page in its enqueue logic
        // Checkbox conditional visibility is now handled in arsol-pfw-admin.js
    }

    /**
     * Render permissions section description
     */
    public function render_permissions_section() {
        echo '<p>' . esc_html__('Configure project permissions: Project managers need WooCommerce admin knowledge, while regular users just need to be WooCommerce customers.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render project manager section description
     */
    public function render_project_manager_section() {
        echo '<div class="arsol-pfw-project-manager-permissions">';
        echo '<div id="project-manager-permissions-description"><p>' . esc_html__('Configure permissions for users who manage projects (Project Managers).', 'arsol-pfw') . '</p></div>';
        echo '</div>';
    }

    /**
     * Render customer permissions section description
     */
    public function render_customer_permissions_section() {
        echo '<div class="arsol-pfw-customer-permissions">';
        echo '<div id="customer-permissions-description"><p>' . esc_html__('Configure permissions for users who create and manage their own projects (Customers).', 'arsol-pfw') . '</p></div>';
        echo '</div>';
    }

    /**
     * Render manager roles field (only roles with admin access)
     */
    public function render_manager_roles_field($args) {
        $field_name = $args['field'];
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $selected_roles = isset($settings[$field_name]) ? $settings[$field_name] : array('administrator');
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        $editable_roles = get_editable_roles();
        
        // Filter roles to include those with admin access (WordPress admin OR WooCommerce admin)
        $admin_roles = array();
        foreach ($editable_roles as $role => $details) {
            $has_admin_access = false;
            
            // Check for WordPress admin capabilities
            if (isset($details['capabilities']['manage_options']) && $details['capabilities']['manage_options']) {
                $has_admin_access = true;
            }
            
            // Check for WooCommerce admin capabilities
            $woocommerce_admin_caps = array(
                'manage_woocommerce',
                'view_woocommerce_reports',
                'edit_shop_orders',
                'read_shop_orders',
                'delete_shop_orders',
                'edit_shop_orders',
                'read_shop_orders',
                'delete_shop_orders',
                'edit_products',
                'read_products',
                'delete_products',
                'edit_shop_coupons',
                'read_shop_coupons',
                'delete_shop_coupons',
                'edit_shop_webhooks',
                'read_shop_webhooks',
                'delete_shop_webhooks'
            );
            
            foreach ($woocommerce_admin_caps as $cap) {
                if (isset($details['capabilities'][$cap]) && $details['capabilities'][$cap]) {
                    $has_admin_access = true;
                    break;
                }
            }
            
            if ($has_admin_access) {
                $admin_roles[$role] = $details;
            }
        }
        
        echo "<div class='{$class}'>";
        foreach ($admin_roles as $role => $details) {
            $is_admin = ($role === 'administrator');
            $checked = ($is_admin || in_array($role, $selected_roles)) ? 'checked' : '';
            $disabled = $is_admin ? 'disabled' : '';
            
            echo '<label>';
            echo '<input type="checkbox" name="arsol_pfw_permissions_settings[' . esc_attr($field_name) . '][]" value="' . esc_attr($role) . '" ' . $checked . ' ' . $disabled . '> ';
            echo esc_html($details['name']);
            if ($is_admin) {
                echo ' <em>(' . esc_html__('always enabled', 'arsol-pfw') . ')</em>';
                // Add a hidden input to ensure the administrator role is always submitted
                echo '<input type="hidden" name="arsol_pfw_permissions_settings[' . esc_attr($field_name) . '][]" value="administrator">';
            }
            echo '</label><br>';
        }
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
        echo '</div>';
    }

    /**
     * Render manager capabilities field
     */
    public function render_manager_capabilities_field($args) {
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $capabilities = isset($settings['project_manager_capabilities']) ? $settings['project_manager_capabilities'] : array();
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        // Default all capabilities to checked if not set
        if (empty($capabilities)) {
            $capabilities = array('manage_stages', 'manage_workflows', 'manage_settings', 'manage_permissions');
        }
        
        $all_capabilities = array(
            'manage_stages' => __('Can manage stages', 'arsol-pfw'),
            'manage_workflows' => __('Can manage workflows', 'arsol-pfw'),
            'manage_settings' => __('Can manage settings', 'arsol-pfw'),
            'manage_permissions' => __('Can manage permissions', 'arsol-pfw'),
        );

        echo "<div class='{$class}'>";
        foreach ($all_capabilities as $cap_key => $cap_label) {
            $checked = in_array($cap_key, $capabilities) ? 'checked' : '';
            
            // Add conditional class for permissions checkbox
            $conditional_class = '';
            if ($cap_key === 'manage_permissions') {
                $conditional_class = ' arsol-pfw-show-if-arsol-pfw-project-manager-capability-manage_settings-is-checked';
            }
            
            ?>
            <label for="arsol-pfw-project-manager-capability-<?php echo esc_attr($cap_key); ?>"<?php echo $conditional_class ? ' class="' . esc_attr($conditional_class) . '"' : ''; ?>>
                <input type="checkbox"
                       id="arsol-pfw-project-manager-capability-<?php echo esc_attr($cap_key); ?>"
                       name="arsol_pfw_permissions_settings[project_manager_capabilities][]"
                       value="<?php echo esc_attr($cap_key); ?>"
                       <?php echo esc_attr($checked); ?>>
                <?php echo esc_html($cap_label); ?><?php echo $conditional_class ? '<br>' : ''; ?>
            </label><?php echo $conditional_class ? '' : '<br>'; ?>
            <?php
        }
        echo "</div>";
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render manager override field
     */
    public function render_manager_override_field($args) {
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $allow_overrides = isset($settings['allow_manager_overrides']) ? $settings['allow_manager_overrides'] : false;
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        echo "<div class='{$class}'>";
        
        // Main checkbox for allowing overrides
        ?>
        <label for="arsol-pfw-allow-manager-overrides">
            <input type="checkbox"
                   id="arsol-pfw-allow-manager-overrides"
                   name="arsol_pfw_permissions_settings[allow_manager_overrides]"
                   value="1"
                   <?php echo $allow_overrides ? 'checked' : ''; ?>>
            <?php echo esc_html__('Allow overrides per project manager', 'arsol-pfw'); ?>
        </label>
        <?php
        
        echo "</div>";
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Render manager default behavior field
     */
    public function render_manager_default_behavior_field($args) {
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $default_behavior = isset($settings['manager_default_behavior']) ? $settings['manager_default_behavior'] : 'enable_all';
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        $valid_behaviors = array('enable_all', 'disable_all');
        
        echo "<div class='{$class}'>";
        
        ?>
        <select id="arsol-pfw-manager-default-behavior"
                name="arsol_pfw_permissions_settings[manager_default_behavior]">
            <?php foreach ($valid_behaviors as $behavior): ?>
                <option value="<?php echo esc_attr($behavior); ?>" <?php selected($default_behavior, $behavior); ?>>
                    <?php echo esc_html($behavior === 'enable_all' ? __('Enable all available permissions', 'arsol-pfw') : __('Disable all available permissions', 'arsol-pfw')); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
        echo '</div>';
    }

    /**
     * Render roles field
     */
    public function render_roles_field($args) {
        $field_name = $args['field'];
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $selected_roles = isset($settings[$field_name]) ? $settings[$field_name] : array('administrator');
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        $editable_roles = get_editable_roles();
        $role_order = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        
        uksort($editable_roles, function ($a, $b) use ($role_order) {
            $a_pos = array_search($a, $role_order);
            $b_pos = array_search($b, $role_order);
            if ($a_pos === false && $b_pos === false) return 0;
            if ($a_pos === false) return 1;
            if ($b_pos === false) return -1;
            return $a_pos - $b_pos;
        });

        echo "<div class='{$class}'>";
        foreach ($editable_roles as $role => $details) {
            $is_admin = ($role === 'administrator');
            $checked = ($is_admin || in_array($role, $selected_roles)) ? 'checked' : '';
            $disabled = $is_admin ? 'disabled' : '';

            echo '<label>';
            echo '<input type="checkbox" name="arsol_pfw_permissions_settings[' . esc_attr($field_name) . '][]" value="' . esc_attr($role) . '" ' . $checked . ' ' . $disabled . '> ';
            echo esc_html($details['name']);
            if ($is_admin) {
                echo ' <em>(' . esc_html__('always enabled', 'arsol-pfw') . ')</em>';
                // Add a hidden input to ensure the administrator role is always submitted
                echo '<input type="hidden" name="arsol_pfw_permissions_settings[' . esc_attr($field_name) . '][]" value="administrator">';
            }
            echo '</label><br>';
        }

        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
        echo '</div>';
    }

    /**
     * Render select field
     */
    public function render_select_field($args) {
        $settings = get_option('arsol_pfw_permissions_settings', array());
        $field_name = isset($args['field']) ? $args['field'] : 'user_project_permissions';
        $default_value = ($field_name === 'mixed_cart_behavior') ? 'add_all' : 'none';
        $value = isset($settings[$field_name]) ? $settings[$field_name] : $default_value;
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        // Use the registration ID (kebab-case) for the HTML id attribute
        // Get the ID from the current add_settings_field call context
        $field_id = isset($args['label_for']) ? $args['label_for'] : $field_name;
        
        // Convert field_name to kebab-case for ID if it contains underscores
        if (strpos($field_name, '_') !== false) {
            $field_id = 'arsol-pfw-' . str_replace('_', '-', $field_name);
        }
        ?>
        <div class="<?php echo $class; ?>">
        <select id="<?php echo esc_attr($field_id); ?>"
                name="arsol_pfw_permissions_settings[<?php echo esc_attr($field_name); ?>]">
            <?php foreach ($args['options'] as $option => $label): ?>
                <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Update capabilities when settings are saved
     */
    public function update_capabilities($old_value, $new_value) {
        $manage_roles = isset($new_value["project_manager_roles"]) ? array_unique($new_value["project_manager_roles"]) : array("administrator");
        $create_roles = isset($new_value["project_user_roles"]) ? array_unique($new_value["project_user_roles"]) : array("administrator");

        if (!in_array("administrator", $manage_roles)) {
            $manage_roles[] = "administrator";
        }

        $all_roles = wp_roles()->get_names();

        // ✅ SIMPLIFIED: Use standard WordPress capabilities
        $manage_capabilities = array(
            "edit_posts",           // Can edit their own posts
            "edit_others_posts",    // Can edit others" posts
            "edit_private_posts",   // Can edit private posts
            "edit_published_posts", // Can edit published posts
            "publish_posts",        // Can publish posts
            "delete_posts",         // Can delete their own posts
            "delete_others_posts",  // Can delete others" posts
            "delete_private_posts", // Can delete private posts
            "delete_published_posts", // Can delete published posts
            "read_private_posts",   // Can read private posts
        );

        $create_capabilities = array(
            "edit_posts",           // Can edit their own posts
            "publish_posts",        // Can publish posts
            "delete_posts",         // Can delete their own posts
            "read_private_posts",   // Can read private posts
        );

        // Clean up old custom capabilities first
        $old_custom_capabilities = array(
            "arsol_pfw_manage",
            "edit_arsol_pfw_projects", "edit_others_arsol_pfw_projects", "publish_arsol_pfw_projects",
            "read_private_arsol_pfw_projects", "delete_arsol_pfw_projects", "delete_others_arsol_pfw_projects",
            "edit_arsol_pfw_proposals", "edit_others_arsol_pfw_proposals", "publish_arsol_pfw_proposals",
            "read_private_arsol_pfw_proposals", "delete_arsol_pfw_proposals", "delete_others_arsol_pfw_proposals",
            "edit_arsol_pfw_requests", "edit_others_arsol_pfw_requests", "publish_arsol_pfw_requests",
            "read_private_arsol_pfw_requests", "delete_arsol_pfw_requests", "delete_others_arsol_pfw_requests",
            "manage_projects", "create_projects", "request_projects"
        );

        foreach ($all_roles as $role_slug => $role_name) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }

            // Remove old custom capabilities
            foreach ($old_custom_capabilities as $old_cap) {
                $role->remove_cap($old_cap);
            }

            // Don not mess with Administrator, Editor, Author default capabilities
            if (in_array($role_slug, array("administrator", "editor", "author"))) {
                continue;
            }

            // For other roles, remove standard capabilities first
            foreach ($manage_capabilities as $cap) {
                $role->remove_cap($cap);
            }

            // Add capabilities based on role assignment
            if (in_array($role_slug, $manage_roles)) {
                // Add all management capabilities
                foreach ($manage_capabilities as $cap) {
                    $role->add_cap($cap);
                }
            } elseif (in_array($role_slug, $create_roles)) {
                // Add only create capabilities
                foreach ($create_capabilities as $cap) {
                    $role->add_cap($cap);
                }
            }
        }

        // Frontend permissions capability management
        $frontend_permission = isset($new_value['user_project_permissions']) ? $new_value['user_project_permissions'] : 'none';
        
        // Get the customer role
        $customer_role = get_role('customer');
        if ($customer_role) {
            // Remove all frontend capabilities first
            $frontend_caps = array(
                'arsol_pfw_frontend_create_own_projects',
                'arsol_pfw_frontend_view_own_projects',
                'arsol_pfw_frontend_edit_own_projects',
                'arsol_pfw_frontend_create_own_requests',
                'arsol_pfw_frontend_view_own_requests'
            );
            
            foreach ($frontend_caps as $cap) {
                $customer_role->remove_cap($cap);
            }
            
            // Grant capabilities based on selected permission level
            switch ($frontend_permission) {
                case 'request_projects':
                    $customer_role->add_cap('arsol_pfw_frontend_create_own_requests');
                    $customer_role->add_cap('arsol_pfw_frontend_view_own_requests');
                    break;
                    
                case 'create_projects':
                    $customer_role->add_cap('arsol_pfw_frontend_create_own_projects');
                    $customer_role->add_cap('arsol_pfw_frontend_view_own_projects');
                    $customer_role->add_cap('arsol_pfw_frontend_edit_own_projects');
                    break;
                    
                case 'can_do_both':
                    $customer_role->add_cap('arsol_pfw_frontend_create_own_projects');
                    $customer_role->add_cap('arsol_pfw_frontend_view_own_projects');
                    $customer_role->add_cap('arsol_pfw_frontend_edit_own_projects');
                    $customer_role->add_cap('arsol_pfw_frontend_create_own_requests');
                    $customer_role->add_cap('arsol_pfw_frontend_view_own_requests');
                    break;
                    
                case 'user_specific':
                    // Don't grant any global capabilities - let individual user settings handle it
                    break;
                    
                case 'none':
                default:
                    // No capabilities granted
                    break;
            }
        }

        // Manager capabilities management
        $manager_capabilities = isset($new_value['project_manager_capabilities']) ? $new_value['project_manager_capabilities'] : array();
        
        // Define the capability mappings
        $capability_mappings = array(
            'manage_stages' => 'arsol_pfw_manage_stages',
            'manage_workflows' => 'arsol_pfw_manage_workflows',
            'manage_settings' => 'arsol_pfw_manage_settings',
            'manage_permissions' => 'arsol_pfw_manage_permissions'
        );
        
        // Remove all manager capabilities from all roles first
        foreach ($all_roles as $role_slug => $role_name) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }
            
            // Skip administrator - they always have all permissions
            if ($role_slug === 'administrator') {
                continue;
            }
            
            // Remove all manager capabilities
            foreach ($capability_mappings as $cap) {
                $role->remove_cap($cap);
            }
        }
        
        // Grant capabilities to selected manager roles
        foreach ($manage_roles as $role_slug) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }
            
            // Skip administrator - they always have all permissions
            if ($role_slug === 'administrator') {
                continue;
            }
            
            // Grant selected capabilities
            foreach ($manager_capabilities as $cap_key) {
                if (isset($capability_mappings[$cap_key])) {
                    $role->add_cap($capability_mappings[$cap_key]);
                }
            }
        }

        // Manager override settings management
        $manager_override_settings = isset($new_value['manager_override_settings']) ? $new_value['manager_override_settings'] : array();
        
        // Define the capability mappings for overrides
        $override_capability_mappings = array(
            'manage_stages' => 'arsol_pfw_manage_stages_override',
            'manage_workflows' => 'arsol_pfw_manage_workflows_override',
            'manage_settings' => 'arsol_pfw_manage_settings_override',
            'manage_permissions' => 'arsol_pfw_manage_permissions_override'
        );

        // Remove all override capabilities from all roles first
        foreach ($all_roles as $role_slug => $role_name) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }
            
            // Skip administrator - they always have all permissions
            if ($role_slug === 'administrator') {
                continue;
            }
            
            // Remove all override capabilities
            foreach ($override_capability_mappings as $cap) {
                $role->remove_cap($cap);
            }
        }
        
        // Grant override capabilities to selected manager roles
        foreach ($manage_roles as $role_slug) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }
            
            // Skip administrator - they always have all permissions
            if ($role_slug === 'administrator') {
                continue;
            }
            
            // Grant selected override capabilities
            foreach ($manager_override_settings as $cap_key) {
                if (isset($override_capability_mappings[$cap_key])) {
                    $role->add_cap($override_capability_mappings[$cap_key]);
                }
            }
        }
    }

    /**
     * Validate settings
     *
     * @param mixed $input The input to validate
     * @return mixed Validated input
     */
    public function validate_settings($input) {
        // Validate manager capabilities
        if (isset($input['project_manager_capabilities'])) {
            $valid_capabilities = array('manage_stages', 'manage_workflows', 'manage_settings', 'manage_permissions');
            $input['project_manager_capabilities'] = array_intersect($input['project_manager_capabilities'], $valid_capabilities);
            
            // If manage_settings is not checked, remove manage_permissions
            if (!in_array('manage_settings', $input['project_manager_capabilities'])) {
                $input['project_manager_capabilities'] = array_diff($input['project_manager_capabilities'], array('manage_permissions'));
            }
        }
        
        // Validate manager override settings
        if (isset($input['allow_manager_overrides'])) {
            $input['allow_manager_overrides'] = (bool) $input['allow_manager_overrides'];
        }
        
        // Validate manager default behavior
        if (isset($input['manager_default_behavior'])) {
            $valid_behaviors = array('enable_all', 'disable_all');
            if (!in_array($input['manager_default_behavior'], $valid_behaviors)) {
                $input['manager_default_behavior'] = 'enable_all';
            }
        }
        
        return $input;
    }
}
