<?php
/**
 * Shortcodes Class
 *
 * Handles all shortcodes functionality for the Arsol Projects plugin.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */


namespace Arsol_Projects_For_Woo;

use Arsol_Projects_For_Woo\Woo\AdminOrders;
use Arsol_Projects_For_Woo\Woocommerce;
use Arsol_Projects_For_Woo\Woocommerce_Subscriptions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class responsible for managing shortcodes
 */
class Shortcodes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register your existing shortcodes
		add_shortcode('arsol_projects', array($this, 'render_projects'));
		add_shortcode('arsol_project', array($this, 'render_single_project'));
		add_shortcode('arsol_project_categories', array($this, 'render_project_categories'));
		add_shortcode('arsol_project_orders', array($this, 'project_orders_shortcode'));
		
		// Only register subscription shortcode if WooCommerce Subscriptions is active
		if (Woocommerce_Subscriptions::is_plugin_active()) {
			add_shortcode('arsol_project_subscriptions', array($this, 'project_subscriptions_shortcode'));
		}
		
		add_shortcode('arsol_user_projects', array($this, 'user_projects_shortcode'));
		add_shortcode('arsol_user_projects_count', array($this, 'user_projects_count_shortcode'));
		add_shortcode('arsol_projects_count', array($this, 'projects_count_shortcode'));
		
		// Template override example/demo shortcode
		add_shortcode('arsol_template_override_demo', array($this, 'template_override_demo_shortcode'));
	}

	/**
	 * Displays a list of projects.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_projects( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'      => 10,
				'category'   => '',
				'columns'    => 3,
				'orderby'    => 'date',
				'order'      => 'DESC',
				'pagination' => 'yes',
			),
			$atts,
			'arsol_projects'
		);

		ob_start();
		// Code to query and display projects based on attributes.
		// This would typically use WP_Query to fetch projects.
		
		echo '<div class="arsol-projects-grid columns-' . esc_attr( $atts['columns'] ) . '">';
		// Loop through projects and display them
		echo '</div>';

		if ( 'yes' === $atts['pagination'] ) {
			// Add pagination code
		}

		return ob_get_clean();
	}

	/**
	 * Displays a single project.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_single_project( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'arsol_project'
		);

		if ( empty( $atts['id'] ) ) {
			return '<p>' . esc_html__( 'Please specify a project ID.', 'arsol-pfw' ) . '</p>';
		}

		ob_start();
		// Code to fetch and display a single project.
		
		return ob_get_clean();
	}

	/**
	 * Displays project categories.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_project_categories( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'    => -1,
				'orderby'  => 'name',
				'order'    => 'ASC',
				'parent'   => '',
				'hide_empty' => 'no',
			),
			$atts,
			'arsol_project_categories'
		);

		ob_start();
		// Code to display project categories
		
		return ob_get_clean();
	}

	/**
	 * Shortcode to display orders associated with a project
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function project_orders_shortcode($atts) {
		if (!is_user_logged_in()) {
			return '<p>' . __('Please log in to view project orders.', 'arsol-pfw') . '</p>';
		}

		$atts = shortcode_atts(array(
			'id' => 0,
		), $atts, 'project_orders');

		$project_id = intval($atts['id']);
		if (!$project_id) {
			return '<p>' . __('No project specified.', 'arsol-pfw') . '</p>';
		}

		if (!current_user_can('read_post', $project_id)) {
			return '<p>' . __('You do not have permission to view orders for this project.', 'arsol-pfw') . '</p>';
		}

		ob_start();
		// Get current user
		$current_user_id = get_current_user_id();
		
		// Get current page for pagination
		$current_page = max(1, (int) $atts['paged']);
		$per_page = max(1, (int) $atts['per_page']);

		// Get project orders using the admin orders class
		$project_orders = Woocommerce::get_project_orders(
			$project_id, 
			$current_user_id, 
			$current_page, 
			$per_page
		);
		
		// Prepare template variables
		$has_orders = !empty($project_orders->orders);
		$customer_orders = $project_orders;
		$wp_button_class = wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

		// Load component template - UPDATED PATH
		include(plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/ui/components/frontend/section-project-orders-table.php');
		
		// Return buffered content
		return ob_get_clean();
	}

	/**
	 * Shortcode to display subscriptions associated with a project
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function project_subscriptions_shortcode($atts) {
		if (!is_user_logged_in()) {
			return '<p>' . __('Please log in to view project subscriptions.', 'arsol-pfw') . '</p>';
		}

		$atts = shortcode_atts(array(
			'id' => 0,
		), $atts, 'project_subscriptions');

		$project_id = intval($atts['id']);
		if (!$project_id) {
			return '<p>' . __('No project specified.', 'arsol-pfw') . '</p>';
		}

		if (!current_user_can('read_post', $project_id)) {
			return '<p>' . __('You do not have permission to view subscriptions for this project.', 'arsol-pfw') . '</p>';
		}

		// Use centralized subscription handling
		if (!Woocommerce_Subscriptions::ensure_plugin_active()) {
			return '<p>' . __('WooCommerce Subscriptions plugin is required to display subscription information.', 'arsol-pfw') . '</p>';
		}

		ob_start();
		
		// Get current user
		$current_user_id = get_current_user_id();
		
		// Get current page for pagination
		$current_page = max(1, (int) $atts['paged']);
		$per_page = max(1, (int) $atts['per_page']);

		// Get project subscriptions using the centralized class
		$project_subscriptions = Woocommerce_Subscriptions::get_project_subscriptions(
			$project_id, 
			$current_user_id, 
			$current_page, 
			$per_page
		);
		
		// Prepare template variables
		$has_subscriptions = !empty($project_subscriptions->subscriptions);
		$customer_subscriptions = $project_subscriptions;
		$wp_button_class = wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

		// Load component template - UPDATED PATH
		include(plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/ui/components/frontend/section-project-subscriptions-table.php');
		
		// Return buffered content
		return ob_get_clean();
	}

	/**
	 * Shortcode to display a list of user's projects
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function user_projects_shortcode($atts) {
		if (!is_user_logged_in()) {
			return '<p>' . __('Please log in to view your projects.', 'arsol-pfw') . '</p>';
		}

		$atts = shortcode_atts(array(
			'status' => 'any',
			'per_page' => 10,
			'paged' => 1,
		), $atts);

		// Get current user
		$current_user_id = get_current_user_id();
		
		// Get current page for pagination
		$current_page = max(1, (int) $atts['paged']);
		$per_page = max(1, (int) $atts['per_page']);
		
		// Get user's projects - using author parameter instead of meta query
		$args = array(
			'post_type' => 'arsol-project',
			'posts_per_page' => $per_page,
			'paged' => $current_page,
			'orderby' => 'title',
			'order' => 'ASC',
			'post_status' => 'publish',  // Only published projects
			'author' => $current_user_id // Using the author parameter to match the logged-in user
		);
		
		// Apply additional filtering if needed
		$args = apply_filters('arsol_projects_user_projects_query_args', $args, $current_user_id);
		
		$projects_query = new \WP_Query($args);
		
		// Prepare template variables
		$has_projects = $projects_query->have_posts();
		$total_pages = $projects_query->max_num_pages;
		$wp_button_class = wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
		
		// Load component template - UPDATED PATH
		include(plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/ui/components/frontend/section-projects-table.php');
		
		// Return buffered content
		return ob_get_clean();
	}

	/**
	 * Shortcode to display the count of user's projects
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function user_projects_count_shortcode($atts) {
		// Get current user
		$current_user_id = get_current_user_id();
		
		// If no user is logged in, return 0
		if (!$current_user_id) {
			return '0';
		}

		// Get user's projects count
		$args = array(
			'post_type' => 'arsol-project',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'post_status' => 'publish',
			'author' => $current_user_id
		);
		
		// Apply additional filtering if needed
		$args = apply_filters('arsol_projects_user_projects_count_query_args', $args, $current_user_id);
		
		$projects_query = new \WP_Query($args);
		$count = $projects_query->found_posts;
		
		return (string) $count;
	}

	/**
	 * Shortcode to display the total count of all projects
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function projects_count_shortcode($atts) {
		// Get total projects count
		$args = array(
			'post_type' => 'arsol-project',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'post_status' => 'publish'
		);
		
		// Apply additional filtering if needed
		$args = apply_filters('arsol_projects_count_query_args', $args);
		
		$projects_query = new \WP_Query($args);
		$count = $projects_query->found_posts;
		
		return (string) $count;
	}

	/**
	 * Get projects for the current user
	 * 
	 * @return array Array of project post objects
	 */
	private function get_user_projects() {
		if (!is_user_logged_in()) {
			return [];
		}

		$user_id = get_current_user_id();
		return get_posts([
			'post_type'      => 'arsol-project',
			'post_status'    => 'publish',
			'author'         => $user_id,
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		]);
	}

	/**
	 * Get orders for a specific project
	 *
	 * @param int $project_id Project ID
	 * @param int $user_id User ID
	 * @return array Array of order IDs
	 */
	private function get_project_orders($project_id, $user_id) {
		$args = array(
			'customer_id' => $user_id,
			'meta_key'   => self::PROJECT_META_KEY,
			'meta_value' => $project_id,
			'return'     => 'ids',
			'limit'      => -1
		);
		
		return wc_get_orders($args);
	}

	/**
	 * Demo shortcode for testing template overrides
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function template_override_demo_shortcode($atts) {
		$atts = shortcode_atts(array(
			'title' => __('Custom Template Override', 'arsol-pfw'),
			'message' => __('This content is being displayed using a shortcode override instead of the default template.', 'arsol-pfw'),
			'style' => 'default',
			'type' => 'active'
		), $atts);

		$style_class = '';
		switch ($atts['style']) {
			case 'success':
				$style_class = 'notice-success';
				break;
			case 'warning':
				$style_class = 'notice-warning';
				break;
			case 'error':
				$style_class = 'notice-error';
				break;
			default:
				$style_class = 'notice-info';
				break;
		}

		ob_start();
		?>
		<div class="arsol-template-override-demo <?php echo esc_attr($style_class); ?>" style="padding: 20px; margin: 20px 0; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
			<h3 style="margin-top: 0; color: #333;"><?php echo esc_html($atts['title']); ?></h3>
			<p style="margin-bottom: 0; color: #666;"><?php echo esc_html($atts['message']); ?></p>
			<p style="font-size: 11px; color: #999; margin: 10px 0 0 0;">
				<strong><?php _e('Project Type:', 'arsol-pfw'); ?></strong> <?php echo esc_html(ucfirst($atts['type'])); ?><br>
				<strong><?php _e('Demo Shortcode:', 'arsol-pfw'); ?></strong> 
				[arsol_template_override_demo title="<?php echo esc_attr($atts['title']); ?>" message="<?php echo esc_attr($atts['message']); ?>" type="<?php echo esc_attr($atts['type']); ?>" style="<?php echo esc_attr($atts['style']); ?>"]
			</p>
		</div>
		<?php
		return ob_get_clean();
	}
}