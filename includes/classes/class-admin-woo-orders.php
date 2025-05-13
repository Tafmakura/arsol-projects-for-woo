<?php

namespace Arsol_Projects_For_Woo\Woo;

if (!defined('ABSPATH')) {
    exit;
}

class AdminOrders {
    public function __construct() {
        // Initialize hooks
        add_action('init', array($this, 'init'));
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'add_project_selector_to_order'));
        add_action('woocommerce_process_shop_order_meta', array($this, 'save_project_field'));
        
        // Add new column management
        add_filter('manage_edit-shop_order_columns', array($this, 'add_project_column'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'display_project_column_content'), 10, 2);

        // Add AJAX and scripts
        add_action('wp_ajax_search_projects', array($this, 'ajax_search_projects'));
        add_action('admin_footer', array($this, 'add_select2_scripts'));
    }

    public function init() {
        // Add your initialization code here
    }

    /**
     * Adds a project selector dropdown to the order details page
     *
     * @param \WC_Order $order The order object
     */
    public function add_project_selector_to_order($order) {
        $selected_project = $order->get_meta('arsol_project', true);
        $selected_project_title = '';
        
        if ($selected_project) {
            $project = get_post($selected_project);
            if ($project) {
                $selected_project_title = $project->post_title;
            }
        }
        ?>
        <p class="form-field form-field-wide">
            <label for="project_selector">Project:</label>
            <select name="assigned_project" id="project_selector" class="wc-enhanced-select-nostd" style="width: 100%;">
                <?php if ($selected_project) : ?>
                    <option value="<?php echo esc_attr($selected_project); ?>" selected>
                        <?php echo esc_html($selected_project_title); ?>
                    </option>
                <?php endif; ?>
            </select>
        </p>
        <?php
    }

    /**
     * Save the selected project ID to order meta
     *
     * @param int $order_id The order ID
     */
    public function save_project_field($order_id) {
        // Verify nonce
        if (!isset($_POST['arsol_project_nonce']) || !wp_verify_nonce($_POST['arsol_project_nonce'], 'arsol_save_project_data')) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        if (isset($_POST['assigned_project'])) {
            $project_id = sanitize_text_field($_POST['assigned_project']);
            $order->update_meta_data('arsol_project', $project_id);
            $order->save();
        }
    }

    /**
     * Add a custom column to the orders list
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_project_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            if ($key === 'order_status') {
                $new_columns['project'] = __('Project', 'arsol-projects-for-woo');
            }
        }
        return $new_columns;
    }

    /**
     * Display content for the custom project column
     *
     * @param string $column Column name
     * @param int $order_id Order ID
     */
    public function display_project_column_content($column, $order_id) {
        if ($column === 'project') {
            $order = wc_get_order($order_id);
            if (!$order) {
                return;
            }
            
            $project_id = $order->get_meta('arsol_project', true);
            if ($project_id) {
                $project = get_post($project_id);
                if ($project) {
                    echo esc_html($project->post_title);
                }
            } else {
                echo '—';
            }
        }
    }

    /**
     * Handle AJAX search for projects
     */
    public function ajax_search_projects() {
        check_ajax_referer('search-projects', 'security');

        $term = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';
        
        $projects = get_posts([
            'post_type' => 'project',
            'posts_per_page' => 10,
            's' => $term,
        ]);

        $results = array();
        foreach ($projects as $project) {
            $results[] = array(
                'id' => $project->ID,
                'text' => $project->post_title
            );
        }

        wp_send_json([
            'results' => $results
        ]);
    }

    /**
     * Add JavaScript for select2 with AJAX
     */
    public function add_select2_scripts() {
        global $pagenow;
        if (!in_array($pagenow, array('post.php', 'post-new.php')) || get_post_type() !== 'shop_order') {
            return;
        }
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $('#project_selector').select2({
                    ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term,
                                action: 'search_projects',
                                security: '<?php echo wp_create_nonce("search-projects"); ?>'
                            };
                        },
                        processResults: function(data) {
                            return data;
                        },
                        cache: true
                    },
                    minimumInputLength: 1,
                    placeholder: 'Search for a project...',
                    allowClear: true
                });
            });
        </script>
        <?php
    }
}