<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_project_status_taxonomy'));
        add_action('init', array($this, 'add_default_project_statuses'));
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_projects'), 10, 2);
        add_filter('wp_dropdown_users_args', array($this, 'modify_author_dropdown'), 10, 2);
        add_action('add_meta_boxes', array($this, 'add_project_details_meta_box'));
        add_action('save_post_arsol-project', array($this, 'save_project_details'));
        add_action('restrict_manage_posts', array($this, 'add_project_status_filters'));
        add_action('pre_get_posts', array($this, 'filter_projects_by_date_range'));
        add_action('template_redirect', array($this, 'handle_project_template_redirect'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_wc_admin_styles'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Projects', 'arsol-projects-for-woo'),
            'singular_name'      => __('Project', 'arsol-projects-for-woo'),
            'add_new'           => __('Add New', 'arsol-projects-for-woo'),
            'add_new_item'      => __('Add New Project', 'arsol-projects-for-woo'),
            'edit_item'         => __('Edit Project', 'arsol-projects-for-woo'),
            'new_item'          => __('New Project', 'arsol-projects-for-woo'),
            'view_item'         => __('View Project', 'arsol-projects-for-woo'),
            'search_items'      => __('Search Projects', 'arsol-projects-for-woo'),
            'not_found'         => __('No projects found', 'arsol-projects-for-woo'),
            'not_found_in_trash'=> __('No projects found in trash', 'arsol-projects-for-woo'),
            'menu_name'         => __('Projects', 'arsol-projects-for-woo'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true, // Enable for comment handling
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-portfolio',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'excerpt', 'author', 'comments'),
            'has_archive'        => false,
            'rewrite'           => array('slug' => 'project', 'with_front' => false),
            'show_in_rest'      => false,
        );

        register_post_type('arsol-project', $args);
    }

    /**
     * Disable Gutenberg for projects post type
     */
    public function disable_gutenberg_for_projects($use_block_editor, $post_type) {
        if ($post_type === 'arsol-project') {
            return false;
        }
        return $use_block_editor;
    }

    /**
     * Modify the author dropdown to include only WooCommerce customers
     */
    public function modify_author_dropdown($query_args, $r) {
        if (!is_admin()) {
            return $query_args;
        }

        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'arsol-project') {
            // Get all users who can make purchases
            $query_args['role__in'] = array('customer', 'subscriber');
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Handle template redirect for project pages
     */
    public function handle_project_template_redirect() {
        if (is_singular('arsol-project')) {
            $project_id = get_the_ID();
            $user_id = get_current_user_id();
            
            // Check if user can view the project
            if (!\Arsol_Projects_For_Woo\Endpoints::user_can_view_project($user_id, $project_id)) {
                // Simple redirect to projects list
                wp_redirect(wc_get_account_endpoint_url('projects'));
                exit;
            }
            
            // Redirect to the project overview page in the account area
            wp_redirect(wc_get_account_endpoint_url('project-overview/' . $project_id));
            exit;
        }
    }

    /**
     * Register project status taxonomy
     */
    public function register_project_status_taxonomy() {
        $labels = array(
            'name'              => __('Project Statuses', 'arsol-projects-for-woo'),
            'singular_name'     => __('Project Status', 'arsol-projects-for-woo'),
            'search_items'      => __('Search Project Statuses', 'arsol-projects-for-woo'),
            'all_items'         => __('All Project Statuses', 'arsol-projects-for-woo'),
            'edit_item'         => __('Edit Project Status', 'arsol-projects-for-woo'),
            'update_item'       => __('Update Project Status', 'arsol-projects-for-woo'),
            'add_new_item'      => __('Add New Project Status', 'arsol-projects-for-woo'),
            'new_item_name'     => __('New Project Status Name', 'arsol-projects-for-woo'),
            'menu_name'         => __('Status', 'arsol-projects-for-woo'),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'project-status'),
            'show_in_rest'      => true,
            'meta_box_cb'       => false,
        );

        register_taxonomy('project_status', 'arsol-project', $args);
    }

    /**
     * Add default project statuses
     */
    public function add_default_project_statuses() {
        $default_statuses = array(
            'not-started' => 'Not Started',
            'in-progress' => 'In Progress',
            'on-hold'     => 'On Hold',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled'
        );

        foreach ($default_statuses as $slug => $name) {
            if (!term_exists($slug, 'project_status')) {
                wp_insert_term($name, 'project_status', array('slug' => $slug));
            }
        }
    }

    /**
     * Add project details meta box
     */
    public function add_project_details_meta_box() {
        add_meta_box(
            'project_details_meta_box',
            __('Project Details', 'arsol-projects-for-woo'),
            array($this, 'render_project_details_meta_box'),
            'arsol-project',
            'side',
            'default'
        );
    }

    /**
     * Render project details meta box
     */
    public function render_project_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('project_details_meta_box', 'project_details_meta_box_nonce');

        // Get current values
        $current_status = wp_get_object_terms($post->ID, 'project_status', array('fields' => 'slugs'));
        $current_status = !empty($current_status) ? $current_status[0] : 'not-started';
        $due_date = get_post_meta($post->ID, '_project_due_date', true);
        $start_date = get_post_meta($post->ID, '_project_start_date', true);
        $project_lead = get_post_meta($post->ID, '_project_lead', true);
        
        // Get statuses
        $statuses = get_terms(array(
            'taxonomy' => 'project_status',
            'hide_empty' => false,
        ));

        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'widefat'
        ));

        // Get project leads (administrators and shop managers)
        $project_leads = get_users(array(
            'role__in' => array('administrator', 'shop_manager'),
            'orderby' => 'display_name',
            'fields' => array('ID', 'display_name')
        ));

        // Check if current user is admin
        $is_admin = current_user_can('administrator');
        ?>
        <p>
            <label for="project_code"><?php _e('Project Code:', 'arsol-projects-for-woo'); ?></label>
            <input type="text" 
                   id="project_code" 
                   value="<?php echo esc_attr($post->ID); ?>"
                   disabled
                   style="width:100%">
        </p>

        <p>
            <label for="post_author_override"><?php _e('Customer:', 'arsol-projects-for-woo'); ?></label>
            <?php echo $author_dropdown; ?>
        </p>

        <p>
            <label for="project_lead"><?php _e('Project Lead:', 'arsol-projects-for-woo'); ?></label>
            <select name="project_lead" id="project_lead" style="width:100%">
                <option value=""><?php _e('Select Project Lead', 'arsol-projects-for-woo'); ?></option>
                <?php foreach ($project_leads as $lead) : ?>
                    <option value="<?php echo esc_attr($lead->ID); ?>" <?php selected($project_lead, $lead->ID); ?>>
                        <?php echo esc_html($lead->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="project_status"><?php _e('Project Status:', 'arsol-projects-for-woo'); ?></label>
            <select name="project_status" id="project_status" style="width:100%">
                <?php foreach ($statuses as $status) : ?>
                    <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($current_status, $status->slug); ?>>
                        <?php echo esc_html($status->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="project_start_date"><?php _e('Start Date:', 'arsol-projects-for-woo'); ?></label>
            <input type="date" 
                   id="project_start_date" 
                   name="project_start_date" 
                   value="<?php echo esc_attr($start_date); ?>"
                   style="width:100%"
                   <?php echo $is_admin ? '' : 'readonly'; ?>>
        </p>

        <p>
            <label for="project_due_date"><?php _e('Due Date:', 'arsol-projects-for-woo'); ?></label>
            <input type="date" 
                   id="project_due_date" 
                   name="project_due_date" 
                   value="<?php echo esc_attr($due_date); ?>"
                   style="width:100%">
        </p>
        <?php
    }

    /**
     * Save project details
     */
    public function save_project_details($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['project_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['project_details_meta_box_nonce'], 'project_details_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save project status
        if (isset($_POST['project_status'])) {
            $new_status = sanitize_text_field($_POST['project_status']);
            $current_status = wp_get_object_terms($post_id, 'project_status', array('fields' => 'slugs'));
            $current_status = !empty($current_status) ? $current_status[0] : '';
            
            // If status is changing to 'in-progress' and there's no start date, set it
            if ($new_status === 'in-progress' && $current_status !== 'in-progress') {
                $start_date = get_post_meta($post_id, '_project_start_date', true);
                if (empty($start_date)) {
                    update_post_meta($post_id, '_project_start_date', current_time('Y-m-d'));
                }
            }
            
            wp_set_object_terms($post_id, $new_status, 'project_status', false);
        }

        // Save project lead
        if (isset($_POST['project_lead'])) {
            update_post_meta($post_id, '_project_lead', sanitize_text_field($_POST['project_lead']));
        }

        // Save start date (only if user is admin)
        if (isset($_POST['project_start_date']) && current_user_can('administrator')) {
            update_post_meta($post_id, '_project_start_date', sanitize_text_field($_POST['project_start_date']));
        }

        // Save due date
        if (isset($_POST['project_due_date'])) {
            update_post_meta($post_id, '_project_due_date', sanitize_text_field($_POST['project_due_date']));
        }
    }

    /**
     * Add project filters
     */
    public function add_project_status_filters() {
        global $typenow;
        if ($typenow === 'arsol-project') {
            echo '<div class="alignleft actions">';

            // Status filter
            $current_status = isset($_GET['project_status']) ? $_GET['project_status'] : '';
            $statuses = get_terms('project_status', array('hide_empty' => false));
            if (!empty($statuses) && !is_wp_error($statuses)) {
                echo '<select name="project_status" id="filter-by-project-status" class="select2-enhanced" data-placeholder="' . esc_attr__('All Statuses', 'arsol-projects-for-woo') . '">';
                echo '<option value="">' . __('All Statuses', 'arsol-projects-for-woo') . '</option>';
                foreach ($statuses as $status) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($status->slug),
                        selected($current_status, $status->slug, false),
                        esc_html($status->name)
                    );
                }
                echo '</select>';
            }

            // Project Lead filter (manual select for Select2)
            $current_lead = isset($_GET['project_lead']) ? $_GET['project_lead'] : '';
            $project_leads = get_users(array(
                'role__in' => array('administrator', 'shop_manager'),
                'orderby' => 'display_name',
                'order' => 'ASC',
                'fields' => array('ID', 'display_name')
            ));
            echo '<select name="project_lead" id="filter-by-project-lead" class="wc-customer-search select2-hidden-accessible enhanced" data-placeholder="' . esc_attr__('Filter by project lead', 'arsol-projects-for-woo') . '" data-allow_clear="true">';
            echo '<option value="">' . __('Filter by project lead', 'arsol-projects-for-woo') . '</option>';
            foreach ($project_leads as $lead) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($lead->ID),
                    selected($current_lead, $lead->ID, false),
                    esc_html($lead->display_name)
                );
            }
            echo '</select>';

            // Customer filter (manual select for Select2)
            $current_customer = isset($_GET['customer']) ? $_GET['customer'] : '';
            $customers = get_users(array(
                'role__in' => array('customer', 'subscriber'),
                'orderby' => 'display_name',
                'order' => 'ASC',
                'fields' => array('ID', 'display_name')
            ));
            echo '<select name="customer" id="filter-by-customer" class="wc-customer-search select2-hidden-accessible enhanced" data-placeholder="' . esc_attr__('Filter by registered customer', 'arsol-projects-for-woo') . '" data-allow_clear="true">';
            echo '<option value="">' . __('Filter by registered customer', 'arsol-projects-for-woo') . '</option>';
            foreach ($customers as $customer) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($customer->ID),
                    selected($current_customer, $customer->ID, false),
                    esc_html($customer->display_name)
                );
            }
            echo '</select>';

            // Filter and Reset buttons
            echo '<button type="submit" name="filter_action" id="project-query-submit" class="button">' . __('Filter', 'arsol-projects-for-woo') . '</button>';
            echo '<a href="' . esc_url(admin_url('edit.php?post_type=arsol-project')) . '" class="button">' . __('Reset Filters', 'arsol-projects-for-woo') . '</a>';

            echo '</div>';

            // Enqueue WooCommerce select2
            wp_enqueue_script('select2');
            wp_enqueue_style('select2');
            
            // Add inline script to initialize select2
            add_action('admin_footer', function() {
                ?>
                <script type="text/javascript">
                jQuery(function($) {
                    $('.wc-customer-search, .select2-enhanced').select2({
                        allowClear: true,
                        placeholder: function(){
                            return $(this).data('placeholder');
                        }
                    });
                });
                </script>
                <?php
            });
        }
    }

    /**
     * Filter projects by date range
     */
    public function filter_projects_by_date_range($query) {
        global $pagenow, $typenow;

        if ($pagenow === 'edit.php' && $typenow === 'arsol-project' && $query->is_main_query()) {
            // Filter by project lead
            if (!empty($_GET['project_lead'])) {
                $query->set('meta_query', array(
                    array(
                        'key' => '_project_lead',
                        'value' => sanitize_text_field($_GET['project_lead']),
                        'compare' => '='
                    )
                ));
            }

            // Filter by customer (author)
            if (!empty($_GET['customer'])) {
                $query->set('author', sanitize_text_field($_GET['customer']));
            }
        }
    }

    /**
     * Enqueue WooCommerce admin styles for arsol-project post type
     */
    public function enqueue_wc_admin_styles($hook) {
        global $typenow;
        if ($typenow === 'arsol-project' || (isset($_GET['post_type']) && $_GET['post_type'] === 'arsol-project')) {
            // WooCommerce admin styles
            wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
        }
    }
}
