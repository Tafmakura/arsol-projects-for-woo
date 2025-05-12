<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectPost;

if (!defined('ABSPATH')) {
    exit;
}

class Setup {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_for_projects'), 10, 2);
        add_action('do_meta_boxes', array($this, 'move_author_metabox_to_side'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
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
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-portfolio',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
            'has_archive'        => true,
            'rewrite'           => array('slug' => 'projects'),
            'show_in_rest'      => false, // Disable Gutenberg
        );

        register_post_type('project', $args);
    }

    /**
     * Disable Gutenberg for projects post type
     */
    public function disable_gutenberg_for_projects($use_block_editor, $post_type) {
        if ($post_type === 'project') {
            return false;
        }
        return $use_block_editor;
    }

    /**
     * Move author metabox to side
     */
    public function move_author_metabox_to_side() {
        remove_meta_box('authordiv', 'project', 'normal');
        add_meta_box(
            'authordiv',
            __('Customer', 'arsol-projects-for-woo'),
            array($this, 'render_customer_search'),
            'project',
            'side',
            'high'
        );
    }

    public function render_customer_search($post) {
        if (!class_exists('WooCommerce')) {
            post_author_meta_box($post);
            return;
        }

        $current_author = $post->post_author;
        $current_user = get_user_by('id', $current_author);
        ?>
        <div class="wc-customer-search">
            <select 
                class="wc-customer-search"
                name="post_author_override"
                data-placeholder="<?php esc_attr_e('Search for a customer...', 'arsol-projects-for-woo'); ?>"
                data-allow_clear="true">
                <?php if ($current_user): ?>
                    <option value="<?php echo esc_attr($current_user->ID); ?>" selected="selected">
                        <?php echo esc_html(sprintf(
                            '%s (#%s - %s)',
                            $current_user->display_name,
                            $current_user->ID,
                            $current_user->user_email
                        )); ?>
                    </option>
                <?php endif; ?>
            </select>
        </div>
        <?php
    }

    public function enqueue_admin_scripts($hook) {
        global $post_type;
        if ($post_type !== 'project') {
            return;
        }
        WC_Admin_Assets::load_scripts();
    }
}
