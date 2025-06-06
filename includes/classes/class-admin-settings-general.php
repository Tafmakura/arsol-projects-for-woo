<?php
/**
 * Admin General Settings Class
 *
 * Handles the general settings page functionality.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Settings_General {
    /**
     * Constructor
     */
    public function __construct() {
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add scripts for admin page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add filter for post type supports
        add_filter('post_type_supports', array($this, 'filter_post_type_supports'), 10, 2);

        // Update capabilities when settings are saved
        add_action('update_option_arsol_projects_settings', array($this, 'update_capabilities'), 10, 2);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('arsol_projects_settings', 'arsol_projects_settings');

        // General Settings Section
        add_settings_section(
            'arsol_projects_general_settings',
            __('General Settings', 'arsol-pfw'),
            array($this, 'render_general_settings_section'),
            'arsol_projects_settings'
        );

        // Product Settings Section
        add_settings_section(
            'arsol_projects_product_settings',
            __('Product Settings', 'arsol-pfw'),
            array($this, 'render_product_settings_section'),
            'arsol_projects_settings'
        );

        add_settings_field(
            'project_products',
            __('Project Products', 'arsol-pfw'),
            array($this, 'render_products_select_field'),
            'arsol_projects_settings',
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
            'arsol_projects_settings',
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
            'arsol_projects_settings',
            'arsol_projects_product_settings',
            array(
                'field' => 'require_project_selection',
                'label' => __('Make project selection mandatory when the field is displayed at checkout.', 'arsol-pfw'),
                'description' => __('If unchecked, customers can proceed without selecting a project.', 'arsol-pfw'),
                'class' => 'arsol-pfw-require-selection'
            )
        );

        // Proposal Invoice Settings Section
        add_settings_section(
            'arsol_projects_proposal_invoice_settings',
            __('Proposal Invoice Settings', 'arsol-pfw'),
            array($this, 'render_proposal_invoice_settings_section'),
            'arsol_projects_settings'
        );

        add_settings_field(
            'proposal_invoice_product',
            __('Proposal Invoice Product', 'arsol-pfw'),
            array($this, 'render_single_product_select_field'),
            'arsol_projects_settings',
            'arsol_projects_proposal_invoice_settings',
            array(
                'field' => 'proposal_invoice_product',
                'description' => __('Select a product to be used for single proposal invoices.', 'arsol-pfw'),
                'class' => 'arsol-pfw-proposal-invoice-product',
                'product_type' => 'simple'
            )
        );

        add_settings_field(
            'proposal_recurring_invoice_product',
            __('Proposal Recurring Invoice Product', 'arsol-pfw'),
            array($this, 'render_single_product_select_field'),
            'arsol_projects_settings',
            'arsol_projects_proposal_invoice_settings',
            array(
                'field' => 'proposal_recurring_invoice_product',
                'description' => __('Select a subscription product to be used for recurring proposal invoices.', 'arsol-pfw'),
                'class' => 'arsol-pfw-proposal-recurring-invoice-product',
                'product_type' => 'subscription'
            )
        );

        // User Permissions Section
        add_settings_section(
            'arsol_projects_user_permissions',
            __('User Project Permissions', 'arsol-pfw'),
            array($this, 'render_user_permissions_section'),
            'arsol_projects_settings'
        );

        add_settings_field(
            'manage_roles',
            __('Project Manager Roles', 'arsol-pfw'),
            array($this, 'render_roles_field'),
            'arsol_projects_settings',
            'arsol_projects_user_permissions',
            array(
                'field' => 'manage_roles',
                'description' => __('Users with these roles can manage all projects, proposals, and requests.', 'arsol-pfw'),
                'class' => 'arsol-pfw-manage-roles'
            )
        );

        add_settings_field(
            'create_roles',
            __('Project User Roles', 'arsol-pfw'),
            array($this, 'render_roles_field'),
            'arsol_projects_settings',
            'arsol_projects_user_permissions',
            array(
                'field' => 'create_roles',
                'description' => __('Users with these roles can create new projects and requests.', 'arsol-pfw'),
                'class' => 'arsol-pfw-create-roles'
            )
        );

        add_settings_field(
            'user_project_permissions',
            __('Frontend Permissions', 'arsol-pfw'),
            array($this, 'render_select_field'),
            'arsol_projects_settings',
            'arsol_projects_user_permissions',
            array(
                'description' => __('Controls how user project permissions are handled globally', 'arsol-pfw'),
                'options' => array(
                    'none' => __('None', 'arsol-pfw'),
                    'request' => __('Users can request projects', 'arsol-pfw'),
                    'create' => __('Users can create projects', 'arsol-pfw'),
                    'user_specific' => __('Set per user', 'arsol-pfw')
                ),
                'class' => 'arsol-pfw-frontend-permissions'
            )
        );

        add_settings_field(
            'default_user_permission',
            __('New User Permissions', 'arsol-pfw'),
            array($this, 'render_conditional_select_field'),
            'arsol_projects_settings',
            'arsol_projects_user_permissions',
            array(
                'description' => __('Default permission level assigned to new users (only applies when "User Specific" is selected above)', 'arsol-pfw'),
                'condition_field' => 'user_project_permissions',
                'condition_value' => 'user_specific',
                'options' => array(
                    'none' => __('None', 'arsol-pfw'),
                    'request' => __('Can request projects', 'arsol-pfw'),
                    'create' => __('Can create projects', 'arsol-pfw')
                ),
                'class' => 'arsol-conditional-field arsol-pfw-new-user-permissions',
                'data-condition-field' => 'user_project_permissions',
                'data-condition-value' => 'user_specific'
            )
        );

        // Comments Settings Section
        add_settings_section(
            'arsol_projects_comments_settings',
            __('Comments Settings', 'arsol-pfw'),
            array($this, 'render_comments_settings_section'),
            'arsol_projects_settings'
        );

        add_settings_field(
            'comment_permissions',
            __('Comments permissions', 'arsol-pfw'),
            array($this, 'render_comment_permissions_group'),
            'arsol_projects_settings',
            'arsol_projects_comments_settings',
            array(
                'class' => 'arsol-pfw-comment-permissions'
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

        wp_enqueue_script('wc-enhanced-select');
        wp_enqueue_script('wc-product-search');
    }

    /**
     * Render general settings section description
     */
    public function render_general_settings_section() {
        echo '<p>' . esc_html__('Configure general settings for Arsol Projects For Woo.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render product settings section description
     */
    public function render_product_settings_section() {
        echo '<p>' . esc_html__('Configure which products or categories should show the project selector during checkout.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render proposal invoice settings section description
     */
    public function render_proposal_invoice_settings_section() {
        echo '<p>' . esc_html__('Configure which products should be added to the project invoice when a proposal is accepted by the customer.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render products select field
     */
    public function render_products_select_field($args) {
        $settings = get_option('arsol_projects_settings', array());
        $product_ids = isset($settings['project_products']) ? $settings['project_products'] : array();
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        ?>
        <div class="<?php echo $class; ?>">
            <select class="wc-product-search"
                    multiple="multiple"
                    style="width: 50%;"
                    name="arsol_projects_settings[project_products][]"
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
        $settings = get_option('arsol_projects_settings', array());
        $category_ids = isset($settings['project_categories']) ? $settings['project_categories'] : array();
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        ?>
        <div class="<?php echo $class; ?>">
            <select class="wc-enhanced-select"
                    multiple="multiple"
                    style="width: 50%;"
                    name="arsol_projects_settings[project_categories][]"
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
        $settings = get_option('arsol_projects_settings', array());
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
                       name="arsol_projects_settings[<?php echo esc_attr($field); ?>]"
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
        $settings = get_option('arsol_projects_settings', array());
        $field = isset($args['field']) ? $args['field'] : $args['label_for'] ?? '';
        $value = isset($settings[$field]) ? $settings[$field] : 0;
        $label = isset($args['label']) ? $args['label'] : '';
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        ?>
        <div class="<?php echo $class; ?>">
            <label for="<?php echo esc_attr($field); ?>">
                <input type="checkbox"
                       id="<?php echo esc_attr($field); ?>"
                       name="arsol_projects_settings[<?php echo esc_attr($field); ?>]"
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
        $settings = get_option('arsol_projects_settings', array());
        $value = isset($settings['user_project_permissions']) ? $settings['user_project_permissions'] : 'none';
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        ?>
        <div class="<?php echo $class; ?>">
            <select id="user_project_permissions"
                    name="arsol_projects_settings[user_project_permissions]">
                <?php foreach ($args['options'] as $option => $label): ?>
                    <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">
                <?php echo esc_html($args['description']); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render conditional select field
     */
    public function render_conditional_select_field($args) {
        $settings = get_option('arsol_projects_settings', array());
        $value = isset($settings['default_user_permission']) ? $settings['default_user_permission'] : 'none';
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');
        ?>
        <div class="<?php echo $class; ?>">
            <select id="default_user_permission"
                    name="arsol_projects_settings[default_user_permission]">
                <?php foreach ($args['options'] as $option => $label): ?>
                    <option value="<?php echo esc_attr($option); ?>" <?php selected($value, $option); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">
                <?php echo esc_html($args['description']); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render user permissions section description
     */
    public function render_user_permissions_section() {
        echo '<p>' . esc_html__('Configure user project permissions for Arsol Projects For Woo.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render comments settings section description
     */
    public function render_comments_settings_section() {
        echo '<p>' . esc_html__('Configure comment settings for Arsol Projects For Woo.', 'arsol-pfw') . '</p>';
    }

    /**
     * Render roles field
     */
    public function render_roles_field($args) {
        $field_name = $args['field'];
        $settings = get_option('arsol_projects_settings', array());
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
            echo '<input type="checkbox" name="arsol_projects_settings[' . esc_attr($field_name) . '][]" value="' . esc_attr($role) . '" ' . $checked . ' ' . $disabled . '> ';
            echo esc_html($details['name']);
            if ($is_admin) {
                echo ' <em>(' . esc_html__('always enabled', 'arsol-pfw') . ')</em>';
                // Add a hidden input to ensure the administrator role is always submitted
                echo '<input type="hidden" name="arsol_projects_settings[' . esc_attr($field_name) . '][]" value="administrator">';
            }
            echo '</label><br>';
        }

        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
        echo '</div>';
    }

    /**
     * Render single product select field
     */
    public function render_single_product_select_field($args) {
        $field_name = $args['field'];
        $product_type = isset($args['product_type']) ? $args['product_type'] : 'any';

        if ($product_type === 'subscription' && !class_exists('WC_Subscriptions')) {
            echo '<p class="description">' . esc_html__('WooCommerce Subscriptions plugin is not active.', 'arsol-pfw') . '</p>';
            return;
        }

        $settings = get_option('arsol_projects_settings', array());
        $product_id = isset($settings[$field_name]) ? $settings[$field_name] : '';
        $class = 'arsol-pfw-setting-field ' . (isset($args['class']) ? esc_attr($args['class']) : '');

        $custom_attributes = '';
        if ($product_type === 'simple') {
            $custom_attributes = 'data-exclude-type="subscription,variable-subscription"';
        } elseif ($product_type === 'subscription') {
            $custom_attributes = 'data-include-type="subscription,variable-subscription"';
        }

        ?>
        <div class="<?php echo esc_attr($class); ?>">
            <select class="wc-product-search"
                    style="width: 50%;"
                    id="<?php echo esc_attr($field_name); ?>"
                    name="arsol_projects_settings[<?php echo esc_attr($field_name); ?>]"
                    data-placeholder="<?php esc_attr_e('Search for a product…', 'arsol-pfw'); ?>"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-allow_clear="true"
                    <?php echo $custom_attributes; ?>>
                <?php
                if (!empty($product_id)) {
                    $product = wc_get_product($product_id);
                    if (is_object($product)) {
                        echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . wp_kses_post($product->get_formatted_name()) . '</option>';
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
     * Check if comments are enabled for a specific post type
     *
     * @param string $post_type The post type to check
     * @return bool Whether comments are enabled for the post type
     */
    public static function is_comments_enabled_for_post_type($post_type) {
        $settings = get_option('arsol_projects_settings', array());
        
        switch ($post_type) {
            case 'arsol-project':
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
        if (in_array($post_type, array('arsol-project', 'arsol-pfw-request', 'arsol-pfw-proposal'))) {
            if (!self::is_comments_enabled_for_post_type($post_type)) {
                $supports = array_diff($supports, array('comments'));
            }
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
        $manage_roles = isset($new_value['manage_roles']) ? array_unique($new_value['manage_roles']) : array('administrator');
        $create_roles = isset($new_value['create_roles']) ? array_unique($new_value['create_roles']) : array('administrator');

        if (!in_array('administrator', $manage_roles)) {
            $manage_roles[] = 'administrator';
        }

        $all_roles = wp_roles()->get_names();

        foreach ($all_roles as $role_slug => $role_name) {
            $role = get_role($role_slug);
            if (!$role) {
                continue;
            }
            
            if (in_array($role_slug, $manage_roles)) {
                $role->add_cap('manage_projects');
                $role->add_cap('create_projects');
                $role->add_cap('request_projects');
            } else {
                $role->remove_cap('manage_projects');
                if (!in_array($role_slug, $create_roles)) {
                    $role->remove_cap('create_projects');
                    $role->remove_cap('request_projects');
                }
            }

            if (in_array($role_slug, $create_roles)) {
                $role->add_cap('create_projects');
                $role->add_cap('request_projects');
            } else {
                if (!in_array($role_slug, $manage_roles)) {
                    $role->remove_cap('create_projects');
                    $role->remove_cap('request_projects');
                }
            }
        }
    }
}