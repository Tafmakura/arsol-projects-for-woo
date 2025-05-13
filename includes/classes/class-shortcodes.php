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
 * Shortcodes class.
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
}

// Initialize the shortcodes.
new Arsol_Projects_Shortcodes();

