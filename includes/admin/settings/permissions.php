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
            __('Admin Capabilities', 'arsol-pfw'),
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
            'allow_manager_overrides',
            __('Allow Individual Overrides', 'arsol-pfw'),
            array($this, 'render_manager_override_field'),
            'arsol_pfw_permissions_settings',
            'arsol_project_manager_permissions',
            array(
                'description' => __('Allow individual project managers to override admin settings for enabled capabilities.', 'arsol-pfw'),
                'class' => 'arsol-pfw-manager-override-field'
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
            __('New User Permissions', 'arsol-pfw'),
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
        
        // Dummy checkbox for administrator (always enabled)
        if (isset($admin_roles['administrator'])) {
            echo '<label>';
            echo '<input type="checkbox" checked disabled> ';
            echo esc_html($admin_roles['administrator']['name']);
            echo '</label><br>';
        }
        
        // Actual configurable roles (excluding administrator)
        foreach ($admin_roles as $role => $details) {
            if ($role === 'administrator') {
                continue; // Skip administrator as it's handled above
            }
            
            $checked = in_array($role, $selected_roles) ? 'checked' : '';
            
            echo '<label>';
            echo '<input type="checkbox" name="arsol_pfw_permissions_settings[' . esc_attr($field_name) . '][]" value="' . esc_attr($role) . '" ' . $checked . '> ';
            echo esc_html($details['name']);
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
            $capabilities = array(
                'manage_assigned_projects', 'manage_all_requests', 'manage_all_proposals', 'manage_all_projects',
                'manage_stages', 'manage_workflows', 'manage_settings', 'manage_permissions'
            );
        }
        
        echo "<div class='{$class}'>";
        
        // Dummy checkbox for assigned projects (always enabled)
        ?>
        <label for="arsol-pfw-project-manager-capability-manage_assigned_projects">
            <input type="checkbox"
                   id="arsol-pfw-project-manager-capability-manage_assigned_projects"
                   checked disabled>
            <?php echo esc_html__('Can manage assigned projects', 'arsol-pfw'); ?>
        </label><br>
        <?php
        
        // Actual configurable capabilities
        $configurable_capabilities = array(
            'manage_all_requests' => __('Can manage all requests', 'arsol-pfw'),
            'manage_all_proposals' => __('Can manage all proposals', 'arsol-pfw'),
            'manage_all_projects' => __('Can manage all projects', 'arsol-pfw'),
            'manage_stages' => __('Can manage stages', 'arsol-pfw'),
            'manage_workflows' => __('Can manage workflows', 'arsol-pfw'),
            'manage_settings' => __('Can manage settings', 'arsol-pfw'),
            'manage_permissions' => __('Can manage permissions', 'arsol-pfw'),
        );

        foreach ($configurable_capabilities as $cap_key => $cap_label) {
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
            <?php echo esc_html__('Allow individual project managers to override admin settings', 'arsol-pfw'); ?>
        </label>
        <?php
        
        echo "</div>";
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
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
        
        // Dummy checkbox for administrator (always enabled)
        if (isset($editable_roles['administrator'])) {
            echo '<label>';
            echo '<input type="checkbox" checked disabled> ';
            echo esc_html($editable_roles['administrator']['name']);
            echo '</label><br>';
        }
        
        // Actual configurable roles (excluding administrator)
        foreach ($editable_roles as $role => $details) {
            if ($role === 'administrator') {
                continue; // Skip administrator as it's handled above
            }
            
            $checked = in_array($role, $selected_roles) ? 'checked' : '';

            echo '<label>';
            echo '<input type="checkbox" name="arsol_pfw_permissions_settings[' . esc_attr($field_name) . '][]" value="' . esc_attr($role) . '" ' . $checked . '> ';
            echo esc_html($details['name']);
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
        // Use Capabilities_Handler to update capabilities from settings
        \Arsol_Projects_For_Woo\Core\Capabilities_Handler::update_capabilities_from_settings($new_value);
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
            $valid_capabilities = array(
                'manage_assigned_projects', 'manage_all_requests', 'manage_all_proposals', 'manage_all_projects',
                'manage_stages', 'manage_workflows', 'manage_settings', 'manage_permissions'
            );
            $input['project_manager_capabilities'] = array_intersect($input['project_manager_capabilities'], $valid_capabilities);
            
            // If manage_settings is not checked, remove manage_permissions
            if (!in_array('manage_settings', $input['project_manager_capabilities'])) {
                $input['project_manager_capabilities'] = array_diff($input['project_manager_capabilities'], array('manage_permissions'));
            }
        }
        
        // ✅ SIMPLIFIED: Validate manager override setting
        if (isset($input['allow_manager_overrides'])) {
            $input['allow_manager_overrides'] = (bool) $input['allow_manager_overrides'];
        }
        
        return $input;
    }
}
