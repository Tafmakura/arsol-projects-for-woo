<?php
/**
 * Admin General Settings Class
 *
 * Handles the general settings page functionality.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin\Settings;

if (!defined('ABSPATH')) {
    exit;
}

class General {
    /**
     * Constructor
     */
    public function __construct() {
        // Register settings after init to ensure text domain is loaded
        add_action('init', array($this, 'setup_settings'), 20);
        
        // Add scripts for admin page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add filter for post type supports
        add_filter('post_type_supports', array($this, 'filter_post_type_supports'), 10, 2);

        // Update capabilities when settings are saved
        add_action('update_option_arsol_pfw_general_settings', array($this, 'update_capabilities'), 10, 2);
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
        register_setting('arsol_pfw_general_settings', 'arsol_pfw_general_settings', array($this, 'validate_settings'));

        // General Settings Section
        add_settings_section(
            'arsol_projects_general_settings',
            null,
            null,
            'arsol_pfw_general_settings'
        );

        // Product Settings Section
        add_settings_section(
            'arsol_projects_product_settings',
            __('Product Settings', 'arsol-pfw'),
            array($this, 'render_product_settings_section'),
            'arsol_pfw_general_settings'
        );

        add_settings_field(
            'project_products',
            __('Project Products', 'arsol-pfw'),
            array($this, 'render_products_select_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_product_settings',
            array(
                'description' => __('Show the project selector at checkout only if these products are in the cart. Leave empty to show for all products.', 'arsol-pfw'),
                'class' => 'arsol-pfw-project-products'
            )
        );

        add_settings_field(
            'project_categories',
            __('Project Categories', 'arsol-pfw'),
            array($this, 'render_categories_select_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_product_settings',
            array(
                'description' => __('Show the project selector at checkout if a product from these categories is in the cart. Leave empty to show for all products.', 'arsol-pfw'),
                'class' => 'arsol-pfw-project-categories'
            )
        );

        add_settings_field(
            'require_project_selection',
            __('Require Project Selection', 'arsol-pfw'),
            array($this, 'render_checkbox_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_product_settings',
            array(
                'field' => 'require_project_selection',
                'label' => __('Make project selection mandatory on frontend.', 'arsol-pfw'),
             'class' => 'arsol-pfw-require-selection'
            )
        );

        add_settings_field(
            'mixed_cart_behavior',
            __('Mixed Cart Behavior', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_product_settings',
            array(
                'field' => 'mixed_cart_behavior',
                'description' => __('How to handle carts containing both project and non-project items.', 'arsol-pfw'),
                'options' => array(
                    'add_all' => __('Add all items to project', 'arsol-pfw'),
                    'purge_non_project' => __('Remove non-project items from cart', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-mixed-cart-behavior'
            )
        );

        // User Permissions Section
        add_settings_section(
            'arsol_projects_user_permissions',
            __('Project Permissions', 'arsol-pfw'),
            array($this, 'render_user_permissions_section'),
            'arsol_pfw_general_settings'
        );

        // Project Manager Roles
        add_settings_field(
            'project_manager_roles',
            __('Project Manager Permissions', 'arsol-pfw'),
            array($this, 'render_manager_roles_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_user_permissions',
            array(
                'field' => 'project_manager_roles',
                'description' => __('Select roles that can manage all projects, proposals, and requests.', 'arsol-pfw'),
                'class' => 'arsol-pfw-manager-roles-field'
            )
        );

        // Project Manager Capabilities
        add_settings_field(
            'project_manager_capabilities',
            '',
            array($this, 'render_manager_capabilities_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_user_permissions',
            array(
                'description' => __('Additional capabilities for project managers. Administrators always have all permissions regardless of these settings.', 'arsol-pfw'),
                'class' => 'arsol-pfw-manager-roles-field'
            )
        );

        // Project User Roles
        add_settings_field(
            'project_user_roles',
            __('Customer Permissions', 'arsol-pfw'),
            array($this, 'render_roles_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_user_permissions',
            array(
                'field' => 'project_user_roles',
                'description' => __('Select roles that can create and manage their own projects as WooCommerce customers.', 'arsol-pfw'),
                'class' => 'arsol-pfw-user-roles-field'
            )
        );

        add_settings_field(
            'arsol-pfw-user-project-permissions',
            __('Frontend Permissions', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_user_permissions',
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

        add_settings_field(
            'arsol-pfw-default-user-permission',
            __('New User Permissions', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_user_permissions',
            array(
                'description' => __('Default permission level assigned to new users (only applies when "User Specific" is selected above)', 'arsol-pfw'),
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

        // Comments Settings Section
        add_settings_section(
            'arsol_projects_comments_settings',
            __('Comments Settings', 'arsol-pfw'),
            array($this, 'render_comments_settings_section'),
            'arsol_pfw_general_settings'
        );

        add_settings_field(
            'comment_permissions',
            __('Comments permissions', 'arsol-pfw'),
            array($this, 'render_comment_permissions_group'),
            'arsol_pfw_general_settings',
            'arsol_projects_comments_settings',
            array(
                'class' => 'arsol-pfw-comment-permissions'
            )
        );

        add_settings_field(
            'comment_max_depth',
            __('Comment Reply Depth', 'arsol-pfw'),
            array($this, 'render_number_field'),
            'arsol_pfw_general_settings',
            'arsol_projects_comments_settings',
            array(
                'field' => 'comment_max_depth',
                'description' => __('Maximum reply depth for comments (0 = no replies, 1 = one level of replies, etc.). Reply buttons are hidden when depth limit is reached, but existing replies remain visible.', 'arsol-pfw'),
                'min' => 0,
                'max' => 5,
                'step' => 1,
                'default' => 5,
                'class' => 'arsol-pfw-comment-max-depth'
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

        // Enqueue WooCommerce scripts for the settings page (for product/category selectors)
        wp_enqueue_script('wc-enhanced-select');
        wp_enqueue_script('wc-product-search');
        
        // Note: arsol-pfw-admin script and style are now handled by the Assets class
        // which includes the settings page in its enqueue logic
    }

    /**
     * Render general settings section description
     */
    public function render_general_settings_section() {
        // This section is intentionally left blank.
    }

    /**
     * Render product settings section description
     */
    public function render_product_settings_section() {
        echo '<p>' . esc_html__('Configure which products or categories should show the project selector during checkout.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render products select field
     */
    public function render_products_select_field($args) {
        $settings = get_option('arsol_pfw_general_settings', array());
        $product_ids = isset($settings['project_products']) ? $settings['project_products'] : array();
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        ?>
        <div class="<?php echo $class; ?>">
            <select class="wc-product-search arsol-pfw-admin-multi-select arsol-settings-wide-field arsol-pfw-multiselect2"
                    multiple="multiple"
                    name="arsol_pfw_general_settings[project_products][]"
                    data-placeholder="<?php esc_attr_e('Search for a product…', 'arsol-pfw'); ?>"
                    data-action="woocommerce_json_search_products_and_variations">
                <?php
                if (!empty($product_ids)) {
                    foreach ($product_ids as $product_id) {
                        $product = wc_get_product($product_id);
                        if (is_object($product)) {
                            echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . wp_kses_post($product->get_formatted_name()) . '</option>';
                        }
                    }
                }
                ?>
            </select>
            <?php if (!empty($args['description'])): ?>
                <p class="description"><?php echo esc_html($args['description']); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render categories select field
     */
    public function render_categories_select_field($args) {
        $settings = get_option('arsol_pfw_general_settings', array());
        $category_ids = isset($settings['project_categories']) ? $settings['project_categories'] : array();
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        ?>
        <div class="<?php echo $class; ?>">
            <select class="wc-enhanced-select arsol-pfw-admin-multi-select arsol-settings-wide-field arsol-pfw-multiselect2"
                    multiple="multiple"
                    name="arsol_pfw_general_settings[project_categories][]"
                    data-placeholder="<?php esc_attr_e('Search for a category…', 'arsol-pfw'); ?>">
                <?php
                $categories = get_terms('product_cat', array('hide_empty' => false));
                if (!empty($categories)) {
                    foreach ($categories as $category) {
                        echo '<option value="' . esc_attr($category->term_id) . '"' . selected(in_array($category->term_id, $category_ids), true, false) . '>' . esc_html($category->name) . '</option>';
                    }
                }
                ?>
            </select>
            <?php if (!empty($args['description'])): ?>
                <p class="description"><?php echo esc_html($args['description']); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render comment permissions checkbox group
     */
    public function render_comment_permissions_group($args) {
        $settings = get_option('arsol_pfw_general_settings', array());
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        $comment_options = array(
            'enable_project_comments' => __('Allow project comments', 'arsol-pfw'),
            'enable_project_request_comments' => __('Allow project request comments', 'arsol-pfw'),
            'enable_project_proposal_comments' => __('Allow project proposal comments', 'arsol-pfw'),
        );

        echo "<div class='{$class}'>";
        foreach ($comment_options as $field => $label) {
            $value = isset($settings[$field]) ? $settings[$field] : 0;
            ?>
            <label for="<?php echo esc_attr($field); ?>">
                <input type="checkbox"
                       id="<?php echo esc_attr($field); ?>"
                       name="arsol_pfw_general_settings[<?php echo esc_attr($field); ?>]"
                       value="1"
                       <?php checked(1, $value); ?>>
                <?php echo esc_html($label); ?>
            </label><br>
            <?php
        }
        echo "</div>";
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $settings = get_option('arsol_pfw_general_settings', array());
        $field = isset($args['field']) ? $args['field'] : $args['label_for'] ?? '';
        $value = isset($settings[$field]) ? $settings[$field] : 0;
        $label = isset($args['label']) ? $args['label'] : '';
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        ?>
        <div class="<?php echo $class; ?>">
        <label for="<?php echo esc_attr($field); ?>">
            <input type="checkbox"
                   id="<?php echo esc_attr($field); ?>"
                   name="arsol_pfw_general_settings[<?php echo esc_attr($field); ?>]"
                   value="1"
                   <?php checked(1, $value); ?>>
            <?php echo esc_html($label); ?>
        </label>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render select field
     */
    public function render_select_field($args) {
        $settings = get_option('arsol_pfw_general_settings', array());
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
                name="arsol_pfw_general_settings[<?php echo esc_attr($field_name); ?>]">
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
     * Render user permissions section description
     */
    public function render_user_permissions_section() {
        echo '<p>' . esc_html__('Configure project permissions: Project managers need WooCommerce admin knowledge, while regular users just need to be WooCommerce customers.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render comments settings section description
     */
    public function render_comments_settings_section() {
        echo '<p>' . esc_html__('Configure comment settings for Arsol Projects For Woo.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render manager roles field (only roles with admin access)
     */
    public function render_manager_roles_field($args) {
        $field_name = $args['field'];
        $settings = get_option('arsol_pfw_general_settings', array());
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
                'publish_shop_orders',
                'edit_others_shop_orders',
                'read_private_shop_orders',
                'delete_others_shop_orders'
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
        
        $role_order = array('administrator', 'shop_manager', 'editor', 'author', 'contributor', 'subscriber');
        
        uksort($admin_roles, function ($a, $b) use ($role_order) {
            $a_pos = array_search($a, $role_order);
            $b_pos = array_search($b, $role_order);
            if ($a_pos === false && $b_pos === false) return 0;
            if ($a_pos === false) return 1;
            if ($b_pos === false) return -1;
            return $a_pos - $b_pos;
        });

        echo "<div class='{$class}'>";
        foreach ($admin_roles as $role => $details) {
            $is_admin = ($role === 'administrator');
            $checked = ($is_admin || in_array($role, $selected_roles)) ? 'checked' : '';
            $disabled = $is_admin ? 'disabled' : '';

            echo '<label>';
            echo '<input type="checkbox" name="arsol_pfw_general_settings[' . esc_attr($field_name) . '][]" value="' . esc_attr($role) . '" ' . $checked . ' ' . $disabled . '> ';
            echo esc_html($details['name']);
            if ($is_admin) {
                echo ' <em>(' . esc_html__('always enabled', 'arsol-pfw') . ')</em>';
                // Add a hidden input to ensure the administrator role is always submitted
                echo '<input type="hidden" name="arsol_pfw_general_settings[' . esc_attr($field_name) . '][]" value="administrator">';
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
        $settings = get_option('arsol_pfw_general_settings', array());
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
            'manage_permissions' => __('Can manage permissions settings', 'arsol-pfw'),
        );

        echo "<div class='{$class}'>";
        foreach ($all_capabilities as $cap_key => $cap_label) {
            $checked = in_array($cap_key, $capabilities) ? 'checked' : '';
            ?>
            <label for="arsol-pfw-project-manager-capability-<?php echo esc_attr($cap_key); ?>">
                <input type="checkbox"
                       id="arsol-pfw-project-manager-capability-<?php echo esc_attr($cap_key); ?>"
                       name="arsol_pfw_general_settings[project_manager_capabilities][]"
                       value="<?php echo esc_attr($cap_key); ?>"
                       <?php echo esc_attr($checked); ?>>
                <?php echo esc_html($cap_label); ?>
            </label><br>
            <?php
        }
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
        $settings = get_option('arsol_pfw_general_settings', array());
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
            echo '<input type="checkbox" name="arsol_pfw_general_settings[' . esc_attr($field_name) . '][]" value="' . esc_attr($role) . '" ' . $checked . ' ' . $disabled . '> ';
            echo esc_html($details['name']);
            if ($is_admin) {
                echo ' <em>(' . esc_html__('always enabled', 'arsol-pfw') . ')</em>';
                // Add a hidden input to ensure the administrator role is always submitted
                echo '<input type="hidden" name="arsol_pfw_general_settings[' . esc_attr($field_name) . '][]" value="administrator">';
            }
            echo '</label><br>';
        }

        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
        echo '</div>';
    }

    /**
     * Check if comments are enabled for a specific post type
     *
     * @param string $post_type The post type to check
     * @return bool Whether comments are enabled for the post type
     */
    public static function is_comments_enabled_for_post_type($post_type) {
        $settings = get_option('arsol_pfw_general_settings', array());
        
        switch ($post_type) {
            case 'arsol-pfw-project':
                return isset($settings['enable_project_comments']) && $settings['enable_project_comments'];
            case 'arsol-pfw-request':
                return isset($settings['enable_project_request_comments']) && $settings['enable_project_request_comments'];
            case 'arsol-pfw-proposal':
                return isset($settings['enable_project_proposal_comments']) && $settings['enable_project_proposal_comments'];
            default:
                return false;
        }
    }

    /**
     * Filter comment support for custom post types
     *
     * @param array $supports The post type supports
     * @param string $post_type The post type
     * @return array Modified supports array
     */
    public static function filter_post_type_supports($supports, $post_type) {
        // Check if comments are enabled for this post type
        if (self::is_comments_enabled_for_post_type($post_type)) {
            $supports[] = 'comments';
        }
        
        return $supports;
    }

    /**
     * Update capabilities when settings are saved
     *
     * @param mixed $old_value Old settings value
     * @param mixed $new_value New settings value
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
    }

    /**
     * Generic field renderer using WordPress Settings API patterns
     * 
     * @param array $args Field arguments
     */
    public function render_generic_field($args) {
        $settings = get_option('arsol_pfw_general_settings', array());
        $field_name = isset($args['field']) ? $args['field'] : $args['label_for'] ?? '';
        $field_type = isset($args['type']) ? $args['type'] : 'text';
        $value = isset($settings[$field_name]) ? $settings[$field_name] : ($args['default'] ?? '');
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        echo '<div class="' . esc_attr($class) . '">';
        
        switch ($field_type) {
            case 'checkbox':
                printf(
                    '<label for="%1$s"><input type="checkbox" id="%1$s" name="arsol_pfw_general_settings[%1$s]" value="1" %2$s> %3$s</label>',
                    esc_attr($field_name),
                    checked(1, $value, false),
                    esc_html($args['label'] ?? '')
                );
                break;
                
            case 'select':
                printf('<select id="%s" name="arsol_pfw_general_settings[%s]">', esc_attr($field_name), esc_attr($field_name));
                foreach ($args['options'] as $option_value => $option_label) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($option_value),
                        selected($value, $option_value, false),
                        esc_html($option_label)
                    );
                }
                echo '</select>';
                break;
                
            case 'text':
            case 'email':
            case 'url':
            case 'number':
            default:
                printf(
                    '<input type="%s" id="%s" name="arsol_pfw_general_settings[%s]" value="%s" class="regular-text" %s>',
                    esc_attr($field_type),
                    esc_attr($field_name),
                    esc_attr($field_name),
                    esc_attr($value),
                    isset($args['attributes']) ? $args['attributes'] : ''
                );
                break;
        }
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
        
        echo '</div>';
    }

    /**
     * Enhanced WooCommerce field renderer - leverages WC's enhanced select
     * 
     * @param array $args Field arguments
     */
    public function render_wc_enhanced_field($args) {
        $settings = get_option('arsol_pfw_general_settings', array());
        $field_name = isset($args['field']) ? $args['field'] : $args['label_for'] ?? '';
        $field_type = isset($args['wc_type']) ? $args['wc_type'] : 'product';
        $value = isset($settings[$field_name]) ? $settings[$field_name] : array();
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        if (!is_array($value)) {
            $value = array($value);
        }
        
        echo '<div class="' . esc_attr($class) . '">';
        
        $select_class = 'arsol-pfw-admin-multi-select arsol-settings-wide-field';
        $data_action = '';
        
        switch ($field_type) {
            case 'product':
                $select_class .= ' wc-product-search';
                $data_action = 'woocommerce_json_search_products_and_variations';
                break;
            case 'customer':
                $select_class .= ' wc-customer-search';
                $data_action = 'woocommerce_json_search_customers';
                break;
            case 'category':
                $select_class .= ' wc-enhanced-select';
                break;
        }
        
        $multiple = isset($args['multiple']) && $args['multiple'] ? 'multiple="multiple"' : '';
        $field_name_attr = $multiple ? $field_name . '[]' : $field_name;
        
        printf(
            '<select class="%s" %s name="arsol_pfw_general_settings[%s]" data-placeholder="%s" %s>',
            esc_attr($select_class),
            $multiple,
            esc_attr($field_name_attr),
            esc_attr($args['placeholder'] ?? __('Select an option…', 'arsol-pfw')),
            $data_action ? 'data-action="' . esc_attr($data_action) . '"' : ''
        );
        
        // Handle different field types
        if ($field_type === 'product' && !empty($value)) {
            foreach ($value as $product_id) {
                $product = wc_get_product($product_id);
                if (is_object($product)) {
                    printf(
                        '<option value="%s" selected="selected">%s</option>',
                        esc_attr($product_id),
                        wp_kses_post($product->get_formatted_name())
                    );
                }
            }
        } elseif ($field_type === 'category') {
            $categories = get_terms('product_cat', array('hide_empty' => false));
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($category->term_id),
                        selected(in_array($category->term_id, $value), true, false),
                        esc_html($category->name)
                    );
                }
            }
        }
        
        echo '</select>';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
        
        echo '</div>';
    }

    /**
     * Render number field
     */
    public function render_number_field($args) {
        $settings = get_option('arsol_pfw_general_settings', array());
        $field_name = isset($args['field']) ? $args['field'] : $args['label_for'] ?? '';
        $value = isset($settings[$field_name]) ? $settings[$field_name] : ($args['default'] ?? '');
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        
        // Build attributes for number field
        $attributes = '';
        if (isset($args['min'])) {
            $attributes .= ' min="' . esc_attr($args['min']) . '"';
        }
        if (isset($args['max'])) {
            $attributes .= ' max="' . esc_attr($args['max']) . '"';
        }
        if (isset($args['step'])) {
            $attributes .= ' step="' . esc_attr($args['step']) . '"';
        }
        if (isset($args['attributes'])) {
            $attributes .= ' ' . $args['attributes'];
        }
        
        echo '<div class="' . esc_attr($class) . '">';
        
        printf(
            '<input type="number" id="%s" name="arsol_pfw_general_settings[%s]" value="%s" %s>',
            esc_attr($field_name),
            esc_attr($field_name),
            esc_attr($value),
            $attributes
        );
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
        
        echo '</div>';
    }

    /**
     * Validate settings
     *
     * @param mixed $input The input to validate
     * @return mixed Validated input
     */
    public function validate_settings($input) {
        // Validate comment max depth
        $input['comment_max_depth'] = intval($input['comment_max_depth']);
        if ($input['comment_max_depth'] < 0 || $input['comment_max_depth'] > 5) {
            $input['comment_max_depth'] = 5; // Default to maximum if out of range
        }
        
        // Validate manager capabilities
        if (isset($input['project_manager_capabilities'])) {
            $valid_capabilities = array('manage_stages', 'manage_workflows', 'manage_settings', 'manage_permissions');
            $input['project_manager_capabilities'] = array_intersect($input['project_manager_capabilities'], $valid_capabilities);
        }
        
        return $input;
    }
}