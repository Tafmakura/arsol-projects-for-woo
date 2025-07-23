<?php
/**
 * Access Validation and Template Display Handler
 *
 * Handles access validation for objects and actions, and displays
 * appropriate templates when access is denied. Uses Capabilities_Handler
 * for WordPress capability checking.
 *
 * @package Arsol_Projects_For_Woo
 * @since 2.1.0
 */

namespace Arsol_Projects_For_Woo\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Access Handler Class
 * 
 * Validates access to objects and actions, displays no-access templates.
 * Uses Capabilities_Handler for WordPress capability checking.
 */
class Access_Handler {

    // ========================================
    // Main Access Validation Method
    // ========================================

    /**
     * Check if a user can access a specific object or perform an action.
     * This is the primary entry point for all access validation.
     *
     * @param string   $object_type   Type of object or action ('project', 'proposal', 'request', 'create_project', 'request_project').
     * @param int|null $object_id     The ID of the object (if applicable).
     * @param string   $action        Action being performed ('view', 'edit', 'delete', 'create').
     * @param int|null $user_id       User ID to check (defaults to current user).
     * @return bool                   True if user has access, false otherwise.
     */
    public static function can_access($object_type, $object_id = null, $action = 'view', $user_id = null) {
        $user_id = $user_id ? $user_id : get_current_user_id();

        if (empty($user_id)) {
            return false; // Must be logged in for any access.
        }

        // Admins have universal access.
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        switch ($object_type) {
            case 'project':
                return self::can_access_cpt($object_id, 'project', $action, $user_id);

            case 'proposal':
                return self::can_access_cpt($object_id, 'proposal', $action, $user_id);

            case 'request':
                return self::can_access_cpt($object_id, 'request', $action, $user_id);

            case 'create_project':
                return Capabilities_Handler::can_create_projects($user_id);

            case 'request_project':
                return Capabilities_Handler::can_create_requests($user_id);

            default:
                return false;
        }
    }

    // ========================================
    // CPT Specific Access Validation
    // ========================================

    /**
     * Generic CPT access validator.
     *
     * @param int      $post_id       The Post ID.
     * @param string   $post_type     The CPT slug ('project', 'proposal', 'request').
     * @param string   $action        The action ('view', 'edit', 'delete').
     * @param int      $user_id       The User ID.
     * @return bool
     */
    private static function can_access_cpt($post_id, $post_type, $action, $user_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'arsol-pfw-' . $post_type) {
            return false;
        }

        switch ($action) {
            case 'view':
                return self::can_view_post($post, $user_id);
            case 'edit':
                return self::can_edit_post($post, $post_type, $user_id);
            case 'delete':
                return self::can_delete_post($post, $post_type, $user_id);
        }
            return false;
    }

    /**
     * Check if user can view a post
     *
     * @param \WP_Post $post      The post object.
     * @param int      $user_id   The User ID.
     * @return bool
     */
    private static function can_view_post(\WP_Post $post, $user_id) {
        // Only published posts can be viewed on frontend
        if ($post->post_status !== 'publish') {
            return false;
        }
        
        // Post author can always view (who created the entity)
        if ((int) $post->post_author === (int) $user_id) {
            return true;
        }

        // Check if user is the assigned customer for this entity
        $customer_id = get_post_meta($post->ID, '_arsol_pfw_customer_id', true);
        if ((int) $customer_id === (int) $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can edit a post
     *
     * @param \WP_Post $post       The post object.
     * @param string   $post_type  The post type.
     * @param int      $user_id    The User ID.
     * @return bool
     */
    private static function can_edit_post(\WP_Post $post, $post_type, $user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        // Check if user can edit own posts
        if ($post->post_author == $user_id) {
            return $user->has_cap('edit_arsol_pfw_' . $post_type);
        }

        // Check if user can edit others' posts
        return $user->has_cap('edit_others_arsol_pfw_' . $post_type);
    }

    /**
     * Check if user can delete a post
     *
     * @param \WP_Post $post       The post object.
     * @param string   $post_type  The post type.
     * @param int      $user_id    The User ID.
     * @return bool
     */
    private static function can_delete_post(\WP_Post $post, $post_type, $user_id) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        // Check if user can delete own posts
        if ($post->post_author == $user_id) {
            return $user->has_cap('delete_arsol_pfw_' . $post_type);
        }

        // Check if user can delete others' posts
        return $user->has_cap('delete_others_arsol_pfw_' . $post_type);
    }

    // ========================================
    // Template Display Methods
    // ========================================

    /**
     * Display no-access template
     *
     * @param string $object_type Type of object ('project', 'proposal', 'request')
     * @param array  $context     Additional context data
     */
    public static function display_no_access_template($object_type = 'project', $context = array()) {
        $title = self::get_no_access_title($object_type);
        $message = self::get_no_access_message($object_type, $context);
        
        // Include the no-access template
        include ARSOL_PFW_PLUGIN_DIR . 'ui/templates/frontend/woocommerce/myaccount/no-access.php';
    }

    /**
     * Get no-access title
     *
     * @param string $object_type Type of object
     * @return string Title for no-access page
     */
    public static function get_no_access_title($object_type = 'project') {
        switch ($object_type) {
            case 'proposal':
                return __('Proposal Access Denied', 'arsol-projects-for-woo');
            case 'request':
                return __('Request Access Denied', 'arsol-projects-for-woo');
            case 'project':
            default:
                return __('Project Access Denied', 'arsol-projects-for-woo');
        }
    }

    /**
     * Get no-access message
     *
     * @param string $object_type Type of object
     * @param array  $context     Additional context data
     * @return string Message for no-access page
     */
    public static function get_no_access_message($object_type = 'project', $context = array()) {
        switch ($object_type) {
            case 'proposal':
                return __('You do not have permission to view this proposal.', 'arsol-projects-for-woo');
            case 'request':
                return __('You do not have permission to view this request.', 'arsol-projects-for-woo');
            case 'project':
            default:
                return __('You do not have permission to view this project.', 'arsol-projects-for-woo');
        }
    }

    /**
     * Get current context for debugging
     *
     * @return array Current context information
     */
    public static function get_current_context() {
        return array(
            'user_id' => get_current_user_id(),
            'post_id' => get_the_ID(),
            'post_type' => get_post_type(),
            'action' => $_REQUEST['action'] ?? 'view',
        );
    }

    /**
     * Validate nonce and permissions
     *
     * @param string $nonce_field   Nonce field name
     * @param string $nonce_action  Nonce action
     * @return bool Whether nonce is valid
     */
    public static function validate_nonce_and_permissions($nonce_field, $nonce_action) {
        if (!wp_verify_nonce($_POST[$nonce_field] ?? '', $nonce_action)) {
            wp_die(__('Security check failed.', 'arsol-projects-for-woo'));
        }
        return true;
    }

    /**
     * Check if user can view a specific post
     *
     * @param int $user_id User ID
     * @param int $post_id Post ID
     * @return bool Whether user can view the post
     */
    public static function user_can_view_post($user_id, $post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }

        $post_type = str_replace('arsol-pfw-', '', $post->post_type);
        return self::can_access($post_type, $post_id, 'view', $user_id);
    }

    /**
     * Check if user can view a specific project
     *
     * @param int $user_id User ID
     * @param int $project_id Project ID
     * @return bool Whether user can view the project
     */
    public static function user_can_view_project($user_id, $project_id) {
        return self::can_access('project', $project_id, 'view', $user_id);
    }

    /**
     * Handle no-access scenario with template display
     *
     * @param string $object_type Type of object
     * @param array  $context     Additional context data
     */
    public static function handle_no_access($object_type = 'project', $context = array()) {
        self::display_no_access_template($object_type, $context);
        exit;
    }

    /**
     * Check access and handle no-access scenario
     *
     * @param string $object_type Type of object
     * @param int    $object_id   Object ID
     * @param string $action      Action
     * @param int    $user_id     User ID
     * @param array  $context     Additional context data
     * @return bool Whether access is granted
     */
    public static function check_access_and_handle($object_type, $object_id, $action = 'view', $user_id = null, $context = array()) {
        if (!self::can_access($object_type, $object_id, $action, $user_id)) {
            self::handle_no_access($object_type, $context);
            return false;
        }
        return true;
    }
} 