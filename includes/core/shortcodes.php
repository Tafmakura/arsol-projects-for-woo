<?php
/**
 * Shortcodes Class
 *
 * Handles all shortcodes functionality for the Arsol Projects plugin.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */


namespace Arsol_Projects_For_Woo\Core;

use Arsol_Projects_For_Woo\Woo\AdminOrders;
use Arsol_Projects_For_Woo\Woocommerce;

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
		// Core shortcodes with arsol_pfw_ prefix
		add_shortcode('arsol_pfw_projects', array($this, 'render_projects'));
		add_shortcode('arsol_pfw_project', array($this, 'render_single_project'));
		add_shortcode('arsol_pfw_project_categories', array($this, 'render_project_categories'));
		add_shortcode('arsol_pfw_project_orders', array($this, 'project_orders_shortcode'));
		
		// Only register subscription shortcode if WooCommerce Subscriptions is active
		if (class_exists('WC_Subscriptions')) {
			add_shortcode('arsol_pfw_project_subscriptions', array($this, 'project_subscriptions_shortcode'));
		}
		
		add_shortcode('arsol_pfw_user_projects', array($this, 'user_projects_shortcode'));
		add_shortcode('arsol_pfw_user_projects_count', array($this, 'user_projects_count_shortcode'));
		add_shortcode('arsol_pfw_projects_count', array($this, 'projects_count_shortcode'));
		
		// Template override example/demo shortcode
		add_shortcode('arsol_pfw_template_override_demo', array($this, 'template_override_demo_shortcode'));

		// Template override shortcodes - match setting names exactly
		add_shortcode('arsol_pfw_project_overview', array($this, 'project_content_active_shortcode'));
		add_shortcode('arsol_pfw_proposal_overview', array($this, 'project_content_proposal_shortcode'));
		add_shortcode('arsol_pfw_request_overview', array($this, 'project_content_request_shortcode'));
		add_shortcode('arsol_pfw_project_form', array($this, 'project_form_shortcode'));
		add_shortcode('arsol_pfw_edit_project_form', array($this, 'project_edit_form_shortcode'));
		add_shortcode('arsol_pfw_request_form', array($this, 'project_request_form_shortcode'));
		add_shortcode('arsol_pfw_edit_request_form', array($this, 'project_request_edit_form_shortcode'));
		add_shortcode('arsol_pfw_projects_list', array($this, 'projects_listing_active_shortcode'));
		add_shortcode('arsol_pfw_proposals_list', array($this, 'projects_listing_proposals_shortcode'));
		add_shortcode('arsol_pfw_requests_list', array($this, 'projects_listing_requests_shortcode'));
		add_shortcode('arsol_pfw_no_access', array($this, 'access_denied_shortcode'));

		// File-related shortcodes
		add_shortcode('arsol_pfw_proposal_files', array($this, 'proposal_files_shortcode'));
		add_shortcode('arsol_pfw_request_file_upload', array($this, 'request_file_upload_shortcode'));
		add_shortcode('arsol_pfw_project_files_list', array($this, 'project_files_list_shortcode'));
	}

	/**
	 * Detect the current context for shortcode execution
	 *
	 * @return string Context type: 'my_account', 'single_post', or 'public_page'
	 */
	private function detect_context() {
		// My Account context
		if (is_wc_endpoint_url() || is_account_page()) {
			return 'my_account';
		}
		
		// Single post context
		global $post;
		        if ($post && in_array($post->post_type, ['arsol-pfw-project', 'arsol-pfw-proposal', 'arsol-pfw-request'])) {
			return 'single_post';
		}
		
		// Public page context
		return 'public_page';
	}

	/**
	 * Resolve dynamic parameters from shortcode attributes and URL parameters
	 *
	 * @param array $atts Shortcode attributes
	 * @param array $dynamic_params List of parameters that can be dynamic
	 * @return array Resolved parameters
	 */
	private function resolve_dynamic_parameters($atts, $dynamic_params = array()) {
		$default_dynamic_params = array(
			'customer_id' => 'intval',
			'status' => 'sanitize_text_field', 
			'category' => 'sanitize_text_field',
			'orderby' => 'sanitize_text_field',
			'order' => 'sanitize_text_field',
			'paged' => 'intval',
			'search' => 'sanitize_text_field',
			'date_from' => 'sanitize_text_field',
			'date_to' => 'sanitize_text_field',
		);

		$dynamic_params = array_merge($default_dynamic_params, $dynamic_params);
		$params = $atts;
		
		foreach ($dynamic_params as $param => $sanitizer) {
			// Only override if not hardcoded in shortcode
			if (empty($atts[$param]) && isset($_GET[$param])) {
				$params[$param] = $sanitizer($_GET[$param]);
			}
		}
		
		// Special handling for pagination (always dynamic)
		$params['paged'] = max(1, intval($_GET['paged'] ?? $atts['paged']));
		
		return $this->validate_dynamic_parameters($params);
	}

	/**
	 * Validate dynamic parameters for security
	 *
	 * @param array $params Parameters to validate
	 * @return array Validated parameters
	 */
	private function validate_dynamic_parameters($params) {
		// Validate customer_id exists
		if (!empty($params['customer_id'])) {
			$user = get_user_by('id', intval($params['customer_id']));
			if (!$user) {
				$params['customer_id'] = 0;
			}
		}
		
		// Validate status against allowed values
		$allowed_statuses = array('active', 'completed', 'on-hold', 'cancelled', 'proposal', 'request');
		if (!empty($params['status']) && !in_array($params['status'], $allowed_statuses)) {
			$params['status'] = '';
		}
		
		// Validate orderby against allowed fields
		$allowed_orderby = array('date', 'title', 'menu_order', 'author', 'modified');
		if (!empty($params['orderby']) && !in_array($params['orderby'], $allowed_orderby)) {
			$params['orderby'] = 'date';
		}
		
		// Validate order
		if (!empty($params['order']) && !in_array(strtoupper($params['order']), array('ASC', 'DESC'))) {
			$params['order'] = 'DESC';
		}
		
		return $params;
	}

	/**
	 * Resolve project ID from various sources
	 *
	 * @param int $provided_id Explicitly provided ID
	 * @param string $expected_post_type Expected post type
	 * @return int Project ID or 0 if not found
	 */
	private function resolve_project_id($provided_id, $expected_post_type) {
		// Explicit ID always wins
		if ($provided_id) {
			return intval($provided_id);
		}
		
		$context = $this->detect_context();
		
		switch ($context) {
			case 'my_account':
				// Get from my-account URL: /my-account/project-overview/123
				return $this->get_id_from_account_url();
				
			case 'single_post':
				// Use current post if it's the right type
				global $post;
				return ($post && $post->post_type === $expected_post_type) ? $post->ID : 0;
				
			default:
				// Require explicit ID
				return 0;
		}
	}

	/**
	 * Get ID from my-account URL structure
	 *
	 * @return int ID from URL or 0 if not found
	 */
	private function get_id_from_account_url() {
		$endpoints = array('view-project', 'view-project-orders', 'view-project-subscriptions', 'view-proposal', 'view-request');
		
		foreach ($endpoints as $endpoint) {
			$value = get_query_var($endpoint);
			if ($value) {
				return intval($value);
			}
		}

		return 0;
	}

	/**
	 * Check if customer can view project content
	 *
	 * @param int $project_id Project ID
	 * @return bool Whether user can view the project
	 */
	private function can_customer_view_project($project_id) {
		$project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);
		
		if (!$project || $project->get_stage() !== 'publish') {
			return false;
		}
		
		// If user is logged in and owns the project
		if (is_user_logged_in() && $project->get_author_id() == get_current_user_id()) {
			return true;
		}
		
		return false;
	}

	/**
	 * Get context-appropriate error message
	 *
	 * @param string $content_type Type of content (project, proposal, request)
	 * @return string Error message
	 */
	private function get_context_error_message($content_type) {
		$context = $this->detect_context();
		
		switch ($context) {
			case 'my_account':
				return sprintf(
					__('No %s found in your account.', 'arsol-pfw'),
					$content_type
				);
				
			case 'single_post':
				return sprintf(
					__('This shortcode should be used on %s pages.', 'arsol-pfw'),
					$content_type
				);
				
			default:
				return sprintf(
					__('Please specify a %s ID: [shortcode_name project_id="123"]', 'arsol-pfw'),
					$content_type
				);
		}
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
			'arsol_pfw_projects'
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
			'arsol_pfw_project'
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
			'arsol_pfw_project_categories'
		);

		ob_start();
		// Code to display project categories
		
		return ob_get_clean();
	}

	/**
	 * Shortcode to display orders associated with a project.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function project_orders_shortcode($atts) {
		$atts = shortcode_atts(['id' => 0], $atts, 'arsol_pfw_project_orders');
		$project_id = absint($atts['id']);

		if (!$project_id || !Access_Handler::can_access('project', $project_id, 'view')) {
			return do_shortcode('[arsol_pfw_no_access]');
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
		include(plugin_dir_path(dirname(dirname(__FILE__))) . 'ui/components/frontend/section-project-orders-table.php');
		
		// Return buffered content
		return ob_get_clean();
	}

	/**
	 * Shortcode to display subscriptions associated with a project.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function project_subscriptions_shortcode($atts) {
		$atts = shortcode_atts(['id' => 0], $atts, 'arsol_pfw_project_subscriptions');
		$project_id = absint($atts['id']);

		if (!$project_id || !Access_Handler::can_access('project', $project_id, 'view')) {
			return do_shortcode('[arsol_pfw_no_access]');
		}

		ob_start();
		// Check if WooCommerce Subscriptions is active
		if (!class_exists('WC_Subscriptions')) {
			return '<p>' . __('WooCommerce Subscriptions plugin is required to display subscription information.', 'arsol-pfw') . '</p>';
		}

		// Get current user
		$current_user_id = get_current_user_id();
		
		// Get current page for pagination
		$current_page = max(1, (int) $atts['paged']);
		$per_page = max(1, (int) $atts['per_page']);

		// Get project subscriptions using the admin orders class
		$project_subscriptions = Woocommerce::get_project_subscriptions(
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
		include(plugin_dir_path(dirname(dirname(__FILE__))) . 'ui/components/frontend/section-project-subscriptions-table.php');
		
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
			'post_type' => 'arsol-pfw-project',
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
		include(plugin_dir_path(dirname(dirname(__FILE__))) . 'ui/partials/frontend/projects/projects-listing-project.php');
		
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
			'post_type' => 'arsol-pfw-project',
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
			'post_type' => 'arsol-pfw-project',
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
			'post_type'      => 'arsol-pfw-project',
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
			'meta_key'   => ARSOL_PFW_PROJECT_META_KEY,
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
		<div class="arsol-template-override-demo <?php echo esc_attr($style_class); ?>">
			<h3><?php echo esc_html($atts['title']); ?></h3>
			<p><?php echo esc_html($atts['message']); ?></p>
			<p class="demo-info">
				<strong><?php _e('Project Type:', 'arsol-pfw'); ?></strong> <?php echo esc_html(ucfirst($atts['type'])); ?><br>
				<strong><?php _e('Demo Shortcode:', 'arsol-pfw'); ?></strong> 
				[arsol_template_override_demo title="<?php echo esc_attr($atts['title']); ?>" message="<?php echo esc_attr($atts['message']); ?>" type="<?php echo esc_attr($atts['type']); ?>" style="<?php echo esc_attr($atts['style']); ?>"]
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Active project content shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function project_content_active_shortcode($atts) {
		$atts = shortcode_atts(array(
			'project_id' => 0,
		), $atts, 'arsol_pfw_project_overview');

		$project_id = $this->resolve_project_id($atts['project_id'], 'arsol-pfw-project');
		
		if (!$project_id) {
			return '<p>' . $this->get_context_error_message('project') . '</p>';
		}

		// Check permissions using Access_Handler
		if (!Access_Handler::can_access('project', $project_id, 'view')) {
			return do_shortcode('[arsol_pfw_no_access]');
		}

		ob_start();
		
		// Set up global post object (same pattern as proposal and request shortcodes)
		global $post;
		$original_post = $post;
		$post_obj = get_post($project_id);
		if ($post_obj) {
			$post = $post_obj;
			setup_postdata($post);
		}

		// Load the project content template
		include ARSOL_PFW_PLUGIN_DIR . 'ui/components/frontend/endpoint-view-project.php';
		
		// Restore original post
		$post = $original_post;
		if ($post_obj) {
			wp_reset_postdata();
		}

		return ob_get_clean();
	}

	/**
	 * Renders the content for a single proposal, checking permissions first.
	 *
	 * @param array $atts Shortcode attributes, expects 'id'.
	 * @return string HTML content for the proposal or no-access message.
	 */
	public function project_content_proposal_shortcode($atts) {
		$atts = shortcode_atts(['id' => 0], $atts, 'arsol_pfw_proposal_overview');
		$proposal_id = absint($atts['id']);

		if (!$proposal_id || !Access_Handler::can_access('proposal', $proposal_id, 'view')) {
			return do_shortcode('[arsol_pfw_no_access]');
		}

		ob_start();
		// The actual content is usually in a template file.
		// For example: wc_get_template('myaccount/view-proposal-content.php', ['proposal_id' => $proposal_id]);
		echo 'Proposal content for ID: ' . esc_html($proposal_id);
		return ob_get_clean();
	}

	/**
	 * Renders the content for a single request, checking permissions first.
	 *
	 * @param array $atts Shortcode attributes, expects 'id'.
	 * @return string HTML content for the request or no-access message.
	 */
	public function project_content_request_shortcode($atts) {
		$atts = shortcode_atts(['id' => 0], $atts, 'arsol_pfw_request_overview');
		$request_id = absint($atts['id']);

		if (!$request_id || !Access_Handler::can_access('request', $request_id, 'view')) {
			return do_shortcode('[arsol_pfw_no_access]');
		}

		ob_start();
		// The actual content is usually in a template file.
		echo 'Request content for ID: ' . esc_html($request_id);
		return ob_get_clean();
	}

	/**
	 * Project proposal processing content shortcode
	 * Displays processing message for proposals regardless of actual content
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function projects_listing_active_shortcode($atts) {
		$atts = shortcode_atts(array(
			'per_page' => 10,
			'paged' => 1,
			'customer_id' => 0,
			'status' => 'active',
			'category' => '',
			'orderby' => 'date',
			'order' => 'DESC',
			'search' => '',
		), $atts, 'arsol_pfw_projects_list');

		// Resolve dynamic parameters
		$params = $this->resolve_dynamic_parameters($atts);
		
		// Only show user's own projects - no public projects supported
			return $this->render_user_projects($params, 'active');
	}

	/**
	 * Render user's own projects (my-account context)
	 *
	 * @param array $params Resolved parameters
	 * @param string $project_type Type of projects to show
	 * @return string HTML output
	 */
	private function render_user_projects($params, $project_type = 'active') {
		if (!is_user_logged_in()) {
			return '<p>' . __('Please log in to view your projects.', 'arsol-pfw') . '</p>';
		}

		ob_start();
		
		// Set up query variables for template
		$current_user_id = get_current_user_id();
		$paged = max(1, intval($params['paged']));
		$per_page = max(1, intval($params['per_page']));

		// Build query args
		$query_args = array(
			'post_type' => 'arsol-pfw-project',
			'post_status' => 'publish',
			'author' => $current_user_id,
			'posts_per_page' => $per_page,
			'paged' => $paged,
			'orderby' => $params['orderby'],
			'order' => $params['order'],
		);

		// Add status filter
		if ($project_type === 'active') {
			$query_args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key' => '_arsol_pfw_project_stage',
					'value' => 'active',
					'compare' => '='
				),
				array(
					'key' => '_arsol_pfw_project_stage',
					'value' => 'completed',
					'compare' => '!='
				),
				array(
					'key' => '_arsol_pfw_project_stage',
					'compare' => 'NOT EXISTS'
				)
			);
		} elseif ($params['status']) {
			$query_args['meta_query'] = array(
				array(
					'key' => '_arsol_pfw_project_stage',
					'value' => $params['status'],
					'compare' => '='
				)
			);
		}

		// Add category filter
		if ($params['category']) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'arsol-pfw-project-category',
					'field' => 'slug',
					'terms' => $params['category']
				)
			);
		}

		// Add search filter
		if ($params['search']) {
			$query_args['s'] = $params['search'];
		}

		$query = new \WP_Query($query_args);

		$total_pages = $query->max_num_pages;
		$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
		$current_tab = $project_type;

		// Load the projects listing template
		include ARSOL_PFW_PLUGIN_DIR . 'ui/partials/frontend/projects/projects-listing-project.php';
		
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Project proposals listing shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function projects_listing_proposals_shortcode($atts) {
		$atts = shortcode_atts(array(
			'per_page' => 10,
			'paged' => 1,
			'customer_id' => 0,
			'status' => '',
			'orderby' => 'date',
			'order' => 'DESC',
			'search' => '',
		), $atts, 'arsol_pfw_proposals_list');

		// Resolve dynamic parameters
		$params = $this->resolve_dynamic_parameters($atts);
		
		// Only show user's own proposals - no public proposals supported
			return $this->render_user_proposals($params);
	}

	/**
	 * Render user's own proposals (my-account context)
	 *
	 * @param array $params Resolved parameters
	 * @return string HTML output
	 */
	private function render_user_proposals($params) {
		if (!is_user_logged_in()) {
			return '<p>' . __('Please log in to view your proposals.', 'arsol-pfw') . '</p>';
		}

		ob_start();
		
		// Set up query variables for template
		$current_user_id = get_current_user_id();
		$paged = max(1, intval($params['paged']));
		$per_page = max(1, intval($params['per_page']));

		// Build query args
		$query_args = array(
			'post_type' => 'arsol-pfw-proposal',
			'post_status' => 'publish',  // Only published proposals on frontend
			'author' => $current_user_id,
			'posts_per_page' => $per_page,
			'paged' => $paged,
			'orderby' => $params['orderby'],
			'order' => $params['order'],
		);

		// Add search filter
		if ($params['search']) {
			$query_args['s'] = $params['search'];
		}

		$query = new \WP_Query($query_args);

		$total_pages = $query->max_num_pages;
		$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
		$current_tab = 'proposals';

		// Load the proposals listing template
		include ARSOL_PFW_PLUGIN_DIR . 'ui/partials/frontend/projects/projects-listing-proposals.php';
		
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Project requests listing shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function projects_listing_requests_shortcode($atts) {
		$atts = shortcode_atts(array(
			'per_page' => 10,
			'paged' => 1,
			'customer_id' => 0,
			'status' => '',
			'orderby' => 'date',
			'order' => 'DESC',
			'search' => '',
		), $atts, 'arsol_pfw_requests_list');

		// Resolve dynamic parameters
		$params = $this->resolve_dynamic_parameters($atts);
		
		// Only show user's own requests - no public requests supported
			return $this->render_user_requests($params);
	}

	/**
	 * Render user's own requests (my-account context)
	 *
	 * @param array $params Resolved parameters
	 * @return string HTML output
	 */
	private function render_user_requests($params) {
		if (!is_user_logged_in()) {
			return '<p>' . __('Please log in to view your requests.', 'arsol-pfw') . '</p>';
		}

		ob_start();
		
		// Set up query variables for template
		$current_user_id = get_current_user_id();
		$paged = max(1, intval($params['paged']));
		$per_page = max(1, intval($params['per_page']));

		// Build query args
		$query_args = array(
			'post_type' => 'arsol-pfw-request',
			'post_status' => 'publish',  // Only published requests on frontend
			'author' => $current_user_id,
			'posts_per_page' => $per_page,
			'paged' => $paged,
			'orderby' => $params['orderby'],
			'order' => $params['order'],
		);

		// Add search filter
		if ($params['search']) {
			$query_args['s'] = $params['search'];
		}

		$query = new \WP_Query($query_args);

		$total_pages = $query->max_num_pages;
		$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
		$current_tab = 'requests';

		// Load the requests listing template
		include ARSOL_PFW_PLUGIN_DIR . 'ui/partials/frontend/projects/projects-listing-requests.php';
		
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Shortcode for project creation and editing form.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML form.
	 */
	public function project_form_shortcode($atts) {
		$user_id = get_current_user_id();
		if (empty($user_id)) {
			return do_shortcode('[arsol_pfw_no_access]');
		}

		$atts       = shortcode_atts(['id' => 0], $atts, 'arsol_pfw_project_form');
		$project_id = absint($atts['id']);
		$mode       = $project_id ? 'edit' : 'create';

		$can_access = ('edit' === $mode)
			? Access_Handler::can_access('project', $project_id, 'edit', $user_id)
			: Access_Handler::can_access('create_project', null, 'create', $user_id);

		if (!$can_access) {
			return do_shortcode('[arsol_pfw_no_access]');
		}

		ob_start();
		include ARSOL_PFW_PLUGIN_DIR . 'ui/components/frontend/endpoint-create-project.php';
		return ob_get_clean();
	}

	/**
	 * Shortcode for project request form.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML form.
	 */
	public function project_request_form_shortcode($atts) {
		if (!Access_Handler::can_access('request_project')) {
			return do_shortcode('[arsol_pfw_no_access]');
		}

		ob_start();
		include ARSOL_PFW_PLUGIN_DIR . 'ui/components/frontend/endpoint-create-request.php';
		return ob_get_clean();
	}

	/**
	 * Access denied shortcode.
	 *
	 * @param array $atts Shortcode attributes (ignored).
	 * @return string HTML output.
	 */
	public function access_denied_shortcode($atts) {
		ob_start();
		include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/no-access.php';
		return ob_get_clean();
	}

	/**
	 * Project edit form shortcode (for custom implementations)
	 * Automatically sets is_edit=true for simple usage
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function project_edit_form_shortcode($atts) {
		$atts = shortcode_atts(array(
			'form_id' => 'edit-project-form',
			'post_id' => 0,
		), $atts, 'arsol_pfw_edit_project_form');

		// Automatically set is_edit to true for simple implementation
		$atts['is_edit'] = true;
		
		// Use the main project form shortcode with edit mode
		return $this->project_form_shortcode($atts);
	}

	/**
	 * Request edit form shortcode (for custom implementations)
	 * Automatically sets is_edit=true for simple usage
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function project_request_edit_form_shortcode($atts) {
		$atts = shortcode_atts(array(
			'form_id' => 'edit-request-form',
			'post_id' => 0,
			'request_id' => 0,
		), $atts, 'arsol_pfw_edit_request_form');

		// Support both old and new attribute names
		$post_id = !empty($atts['request_id']) ? $atts['request_id'] : $atts['post_id'];
		$atts['post_id'] = $post_id;

		// Automatically set is_edit to true for simple implementation
		$atts['is_edit'] = true;
		
		// Use the main request form shortcode with edit mode
		return $this->project_request_form_shortcode($atts);
	}

	/**
	 * Shortcode for displaying files associated with a proposal.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function proposal_files_shortcode($atts) {
		$atts = shortcode_atts(['id' => 0], $atts, 'arsol_pfw_proposal_files');
		$proposal_id = absint($atts['id']);

		if (!$proposal_id || !Access_Handler::can_access('proposal', $proposal_id, 'view')) {
			return do_shortcode('[arsol_pfw_no_access]');
		}
		// ... existing file display logic
		return 'File display logic here.';
	}

	/**
	 * Shortcode for uploading files to a request.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function request_file_upload_shortcode($atts) {
		$atts = shortcode_atts(['id' => 0], $atts, 'arsol_pfw_request_file_upload');
		$request_id = absint($atts['id']);

		if (!$request_id || !Access_Handler::can_access('request', $request_id, 'edit')) {
			return do_shortcode('[arsol_pfw_no_access]');
		}
		// ... existing file upload form logic
		return 'File upload form logic here.';
	}

	/**
	 * Shortcode for listing files associated with a project.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function project_files_list_shortcode($atts) {
		$atts = shortcode_atts(['id' => 0], $atts, 'arsol_pfw_project_files_list');
		$project_id = absint($atts['id']);

		if (!$project_id || !Access_Handler::can_access('project', $project_id, 'view')) {
			return do_shortcode('[arsol_pfw_no_access]');
		}
		// ... existing file list logic
		return 'File list logic here.';
	}
}
