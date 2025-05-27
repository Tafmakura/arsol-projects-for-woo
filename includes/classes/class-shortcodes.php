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
		add_shortcode('arsol_project_subscriptions', array($this, 'project_subscriptions_shortcode'));
		add_shortcode('arsol_user_projects', array($this, 'user_projects_shortcode'));
		add_shortcode('arsol_user_projects_count', array($this, 'user_projects_count_shortcode'));
		add_shortcode('arsol_projects_count', array($this, 'projects_count_shortcode'));
		add_shortcode('arsol_comment_form', array($this, 'render_comment_form'));
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
	public function project_orders_shortcode($atts) {
		// Start output buffering
		ob_start();

		// Normalize attributes
		$atts = shortcode_atts(array(
			'project_id' => 0,
			'per_page' => 10,
			'paged' => 1,
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
		if (!AdminOrders::user_can_view_project($current_user_id, $project_id)) {
			return '<p>' . __('You do not have permission to view orders for this project.', 'arsol-projects-for-woo') . '</p>';
		}

		// Get current page for pagination
		$current_page = max(1, (int) $atts['paged']);
		$per_page = max(1, (int) $atts['per_page']);

		// Get project orders using the admin orders class
		$project_orders = AdminOrders::get_project_orders(
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
		// Start output buffering
		ob_start();

		// Normalize attributes
		$atts = shortcode_atts(array(
			'project_id' => 0,
			'per_page' => 10,
			'paged' => 1,
		), $atts);

		// Get current user
		$current_user_id = get_current_user_id();
		
		// If no user is logged in, show login message
		if (!$current_user_id) {
			return '<p>' . __('Please log in to view project subscriptions.', 'arsol-projects-for-woo') . '</p>';
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
		if (!AdminOrders::user_can_view_project($current_user_id, $project_id)) {
			return '<p>' . __('You do not have permission to view subscriptions for this project.', 'arsol-projects-for-woo') . '</p>';
		}

		// Get current page for pagination
		$current_page = max(1, (int) $atts['paged']);
		$per_page = max(1, (int) $atts['per_page']);

		// Get project subscriptions using the admin orders class
		$project_subscriptions = AdminOrders::get_project_subscriptions(
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
		// Start output buffering
		ob_start();

		// Normalize attributes
		$atts = shortcode_atts(array(
			'per_page' => 10,
			'paged' => 1,
		), $atts);

		// Get current user
		$current_user_id = get_current_user_id();
		
		// If no user is logged in, show login message
		if (!$current_user_id) {
			return '<p>' . __('Please log in to view your projects.', 'arsol-projects-for-woo') . '</p>';
		}

		// Get current page for pagination
		$current_page = max(1, (int) $atts['paged']);
		$per_page = max(1, (int) $atts['per_page']);
		
		// Get user's projects - using author parameter instead of meta query
		$args = array(
			'post_type' => 'project',
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
			'post_type' => 'project',
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
			'post_type' => 'project',
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
	 * Shortcode to display the standard WordPress comment form
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render_comment_form($atts) {
		// Start output buffering
		ob_start();

		// Normalize attributes
		$atts = shortcode_atts(array(
			'post_id' => get_the_ID(), // Default to current post
			'title_reply' => __('Leave a Comment', 'arsol-projects-for-woo'),
			'title_reply_to' => __('Leave a Reply to %s', 'arsol-projects-for-woo'),
			'cancel_reply_link' => __('Cancel Reply', 'arsol-projects-for-woo'),
			'label_submit' => __('Post Comment', 'arsol-projects-for-woo'),
		), $atts);

		// Get the post
		$post = get_post($atts['post_id']);
		
		// Check if post exists
		if (!$post) {
			return '<p>' . __('Error: Post not found.', 'arsol-projects-for-woo') . '</p>';
		}

		// Check if comments are open
		if (!comments_open($post->ID)) {
			return '<p>' . __('Comments are closed for this post.', 'arsol-projects-for-woo') . '</p>';
		}

		// Set up comment form arguments
		$comment_form_args = array(
			'title_reply' => $atts['title_reply'],
			'title_reply_to' => $atts['title_reply_to'],
			'cancel_reply_link' => $atts['cancel_reply_link'],
			'label_submit' => $atts['label_submit'],
			'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x('Comment', 'noun', 'arsol-projects-for-woo') . '</label><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required></textarea></p>',
			'comment_notes_before' => '<p class="comment-notes"><span id="email-notes">' . __('Your email address will not be published.', 'arsol-projects-for-woo') . '</span>' . __(' Required fields are marked ', 'arsol-projects-for-woo') . '<span class="required-field-marker">*</span></p>',
		);

		// Render the comment form
		comment_form($comment_form_args, $post->ID);

		// Return buffered content
		return ob_get_clean();
	}
}