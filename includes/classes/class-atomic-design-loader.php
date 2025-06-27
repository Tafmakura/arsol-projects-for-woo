<?php
/**
 * Atomic Design Loader
 *
 * Handles loading of atomic design components (elements, components, sections)
 * and WooCommerce-compatible templates with override support.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.1.0
 */

namespace Arsol_Projects_For_Woo;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Atomic Design Loader class
 */
class Atomic_Design_Loader {

    /**
     * Load atomic design component
     * 
     * @param string $type Component type: 'elements', 'components', 'sections'
     * @param string $file Component file name (with .php)
     * @param array $vars Variables to pass to component
     * @return bool True if component loaded, false if not found
     */
    public static function load_component($type, $file, $vars = []) {
        $path = ARSOL_PROJECTS_PLUGIN_DIR . "includes/ui/{$type}/frontend/{$file}";
        
        if (file_exists($path)) {
            // Extract variables for use in component
            extract($vars);
            
            // Include the component
            include $path;
            return true;
        }
        
        // Log missing component in debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("ARSOL: Component not found - {$type}/frontend/{$file}");
        }
        
        return false;
    }

    /**
     * Load WooCommerce-compatible template with override support
     * 
     * @param string $template_name Template file name
     * @param array $args Variables to pass to template
     * @param string $template_path Path for theme overrides (default: 'arsol-pfw/')
     * @return void
     */
    public static function wc_get_template($template_name, $args = [], $template_path = 'arsol-pfw/') {
        wc_get_template(
            $template_name,
            $args,
            $template_path,
            ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/templates/frontend/woocommerce/'
        );
    }

    /**
     * Load element (atom)
     * 
     * @param string $file Element file name (with .php)
     * @param array $vars Variables to pass to element
     * @return bool
     */
    public static function load_element($file, $vars = []) {
        return self::load_component('elements', $file, $vars);
    }

    /**
     * Load component (molecule)
     * 
     * @param string $file Component file name (with .php)
     * @param array $vars Variables to pass to component
     * @return bool
     */
    public static function load_molecule($file, $vars = []) {
        return self::load_component('components', $file, $vars);
    }

    /**
     * Load section (organism)
     * 
     * @param string $file Section file name (with .php)
     * @param array $vars Variables to pass to section
     * @return bool
     */
    public static function load_organism($file, $vars = []) {
        return self::load_component('sections', $file, $vars);
    }
}

// Global convenience functions
if (!function_exists('arsol_load_component')) {
    /**
     * Global function to load atomic design component
     * 
     * @param string $type Component type: 'elements', 'components', 'sections'
     * @param string $file Component file name (with .php)
     * @param array $vars Variables to pass to component
     * @return bool
     */
    function arsol_load_component($type, $file, $vars = []) {
        return \Arsol_Projects_For_Woo\Atomic_Design_Loader::load_component($type, $file, $vars);
    }
}

if (!function_exists('arsol_load_element')) {
    /**
     * Global function to load element (atom)
     */
    function arsol_load_element($file, $vars = []) {
        return \Arsol_Projects_For_Woo\Atomic_Design_Loader::load_element($file, $vars);
    }
}

if (!function_exists('arsol_load_molecule')) {
    /**
     * Global function to load component (molecule)
     */
    function arsol_load_molecule($file, $vars = []) {
        return \Arsol_Projects_For_Woo\Atomic_Design_Loader::load_molecule($file, $vars);
    }
}

if (!function_exists('arsol_load_organism')) {
    /**
     * Global function to load section (organism)
     */
    function arsol_load_organism($file, $vars = []) {
        return \Arsol_Projects_For_Woo\Atomic_Design_Loader::load_organism($file, $vars);
    }
}

if (!function_exists('arsol_wc_get_template')) {
    /**
     * Global function to load WooCommerce-compatible template
     */
    function arsol_wc_get_template($template_name, $args = [], $template_path = 'arsol-pfw/') {
        return \Arsol_Projects_For_Woo\Atomic_Design_Loader::wc_get_template($template_name, $args, $template_path);
    }
} 