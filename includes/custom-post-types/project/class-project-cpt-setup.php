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
        add_action('do_meta_boxes', array($this, 'move_author_metabox_to_side'));
        add_action('do_meta_boxes', array($this, 'move_excerpt_metabox_to_top'));
        add_action('add_meta_boxes', array($this, 'add_project_status_meta_box'));
        add_action('save_post_arsol-project', array($this, 'save_project_status'));
        add_action('restrict_manage_posts', array($this, 'add_project_status_filters'));
        add_action('template_redirect', array($this, 'handle_project_template_redirect'));
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
            'rewrite'           => array('slug' => 'arsol-project', 'with_front' => false),
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
     * Modify the author dropdown to include WooCommerce customers
     */
    public function modify_author_dropdown($query_args, $r) {
        if (!is_admin()) {
            return $query_args;
        }

        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'arsol-project') {
            // Get customers who have made orders
            $customer_ids = get_users(array(
                'role'    => 'customer',
                'fields'  => 'ID',
            ));

            $query_args['include'] = $customer_ids;
            $query_args['orderby'] = 'display_name';
            $query_args['order'] = 'ASC';
        }
        return $query_args;
    }

    /**
     * Move author metabox to side
     */
    public function move_author_metabox_to_side() {
        remove_meta_box('authordiv', 'arsol-project', 'normal');
        add_meta_box(
            'authordiv',
            __('Customer', 'arsol-projects-for-woo'),
            'post_author_meta_box',
            'arsol-project',
            'side',
            'high'
        );
    }

    /**
     * Move excerpt metabox to the top of the editor
     */
    public function move_excerpt_metabox_to_top() {
        // Remove the default excerpt metabox
        remove_meta_box('postexcerpt', 'arsol-project', 'normal');
        
        // Add it back with a new title but in the same position
        add_meta_box(
            'postexcerpt',
            __('Project Summary', 'arsol-projects-for-woo'), // Changed name
            'post_excerpt_meta_box',
            'arsol-project',
            'normal', // Keep in normal position
            'default' // Use default priority
        );
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
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'project-status'),
            'show_in_rest'      => true,
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
     * Add project status meta box
     */
    public function add_project_status_meta_box() {
        add_meta_box(
            'project_status_meta_box',
            __('Project Status', 'arsol-projects-for-woo'),
            array($this, 'render_project_status_meta_box'),
            'arsol-project',
            'side',
            'high'
        );
    }

    /**
     * Render project status meta box
     */
    public function render_project_status_meta_box($post) {
        $current_status = wp_get_object_terms($post->ID, 'project_status', array('fields' => 'slugs'));
        $current_status = !empty($current_status) ? $current_status[0] : 'not-started';
        
        $statuses = get_terms(array(
            'taxonomy' => 'project_status',
            'hide_empty' => false,
        ));
        
        wp_nonce_field('project_status_meta_box', 'project_status_meta_box_nonce');
        ?>
        <select name="project_status" id="project_status">
            <?php foreach ($statuses as $status) : ?>
                <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($current_status, $status->slug); ?>>
                    <?php echo esc_html($status->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Save project status
     */
    public function save_project_status($post_id) {
        if (!isset($_POST['project_status_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['project_status_meta_box_nonce'], 'project_status_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['project_status'])) {
            $status = sanitize_text_field($_POST['project_status']);
            wp_set_object_terms($post_id, $status, 'project_status');
        }
    }

    /**
     * Add project status filters
     */
    public function add_project_status_filters() {
        global $typenow;
        if ($typenow === 'arsol-project') {
            $current_status = isset($_GET['project_status']) ? $_GET['project_status'] : '';
            $statuses = get_terms('project_status', array('hide_empty' => false));
            
            if (!empty($statuses) && !is_wp_error($statuses)) {
                echo '<select name="project_status">';
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
        }
    }
}
