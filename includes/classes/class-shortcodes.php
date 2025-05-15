<?php
/**
 * Shortcodes Class
 *
 * Handles all shortcodes functionality for the Arsol Projects plugin.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class responsible for managing shortcodes
 */
class Arsol_Projects_Shortcodes {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register shortcodes.
		add_shortcode( 'arsol_projects', array( $this, 'render_projects' ) );
		add_shortcode( 'arsol_project', array( $this, 'render_single_project' ) );
		add_shortcode( 'arsol_project_categories', array( $this, 'render_project_categories' ) );
	}

	/**
	 * Initialize shortcodes
	 */
	public static function init() {
		add_shortcode('project_orders', array(__CLASS__, 'project_orders_shortcode'));
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
			return '<p>' . esc_html__( 'Please specify a project ID.', 'arsol-projects-for-woo' ) . '</p>';
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
	public static function project_orders_shortcode($atts) {
		// Start output buffering
		ob_start();

		// Normalize attributes
		$atts = shortcode_atts(array(
			'project_id' => 0,
			'per_page' => 10,
			'page' => 1,
		), $atts);

		// Get current user
		$current_user_id = get_current_user_id();
		
		// If no user is logged in, show login message
		if (!$current_user_id) {
			return '<p>' . __('Please log in to view project orders.', 'arsol-projects-for-woo') . '</p>';
		}

		// Get project ID (from attribute or current page)
		$project_id = (int) $atts['project_id'];
		if (!$project_id) {
			// Try to get from current page if it's a project
			global $post;
			if ($post && $post->post_type === 'project') {
				$project_id = $post->ID;
			}
		}

		if (!$project_id) {
			return '<p>' . __('No project specified.', 'arsol-projects-for-woo') . '</p>';
		}

		// Verify user has access to this project
		if (!self::user_can_view_project($current_user_id, $project_id)) {
			return '<p>' . __('You do not have permission to view orders for this project.', 'arsol-projects-for-woo') . '</p>';
		}

		// Get current page for pagination
		$current_page = max(1, (int) $atts['page']);
		$per_page = max(1, (int) $atts['per_page']);

		// Get project orders
		$project_orders = self::get_project_orders($project_id, $current_user_id, $current_page, $per_page);
		
		// Prepare template variables
		$has_orders = !empty($project_orders->orders);
		$customer_orders = $project_orders;
		$wp_button_class = wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';

		// Load template
		include(plugin_dir_path(dirname(dirname(__FILE__))) . 'includes/ui/templates/frontend/project-orders.php');
		
		// Return buffered content
		return ob_get_clean();
	}

	/**
	 * Check if user can view the project
	 *
	 * @param int $user_id User ID
	 * @param int $project_id Project ID
	 * @return bool
	 */
	private static function user_can_view_project($user_id, $project_id) {
		// Check if user is admin
		if (user_can($user_id, 'manage_options')) {
			return true;
		}

		// Check if user is project owner or collaborator
		$project_owner = get_post_field('post_author', $project_id);
		if ($project_owner == $user_id) {
			return true;
		}

		// Check if user is a collaborator (implement your own logic here)
		$collaborators = get_post_meta($project_id, '_project_collaborators', true);
		if (is_array($collaborators) && in_array($user_id, $collaborators)) {
			return true;
		}

		return false;
	}

	/**
	 * Get orders associated with a project
	 *
	 * @param int $project_id Project ID
	 * @param int $user_id User ID
	 * @param int $current_page Current page number
	 * @param int $per_page Orders per page
	 * @return object Orders object with pagination data
	 */
	private static function get_project_orders($project_id, $user_id, $current_page = 1, $per_page = 10) {
		// Query orders that have meta data connecting them to this project
		$args = array(
			'customer_id' => $user_id,
			'limit' => $per_page,
			'page' => $current_page,
			'meta_key' => '_project_id',
			'meta_value' => $project_id,
			'return' => 'ids',
		);

		// Get orders
		$orders = wc_get_orders($args);
		$total_orders = wc_get_orders(array_merge($args, array('limit' => -1, 'return' => 'ids')));
		
		// Create result object similar to WooCommerce customer orders
		$result = new stdClass();
		$result->orders = $orders;
		$result->total = count($total_orders);
		$result->max_num_pages = ceil($result->total / $per_page);

		return $result;
	}
}

// Initialize the shortcodes.
new Arsol_Projects_Shortcodes();
add_action('init', array('Arsol_Projects_Shortcodes', 'init'));

