<?php
/**
 * Comments Handler Class
 *
 * Handles all comment-related functionality for Arsol Projects For Woo.
 * Provides role-based commenting, moderation, and notifications.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

class Comments_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        // Comment permission filters
        add_filter('comments_open', array($this, 'filter_comments_open'), 10, 2);
        
        // Comment moderation filters
        add_filter('pre_comment_approved', array($this, 'filter_comment_moderation'), 10, 2);
        
        // Comment form filters to add custom fields if needed
        add_filter('comment_form_defaults', array($this, 'filter_comment_form_defaults'));
        
        // Comment notification hooks
        add_action('comment_post', array($this, 'send_comment_notifications'), 10, 3);
        
        // Comment content filters for security
        add_filter('comment_text', array($this, 'filter_comment_content'), 10, 2);
        
        // Comment author capability checks
        add_filter('map_meta_cap', array($this, 'map_comment_capabilities'), 10, 4);
    }

    /**
     * Filter whether comments are open for our custom post types
     *
     * @param bool $open Whether comments are open
     * @param int $post_id Post ID
     * @return bool Modified comments open status
     */
    public function filter_comments_open($open, $post_id) {
        $post = get_post($post_id);
        
        if (!$post || !$this->is_project_post_type($post->post_type)) {
            return $open;
        }

        // Check if comments are enabled for this post type
        if (!\Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type($post->post_type)) {
            return false;
        }

        // Check user role permissions
        if (!$this->user_can_comment($post)) {
            return false;
        }

        return $open;
    }

    /**
     * Filter comment moderation based on plugin settings
     *
     * @param int|string $approved Comment approval status
     * @param array $commentdata Comment data
     * @return int|string Modified approval status
     */
    public function filter_comment_moderation($approved, $commentdata) {
        $post = get_post($commentdata['comment_post_ID']);
        
        if (!$post || !$this->is_project_post_type($post->post_type)) {
            return $approved;
        }

        $settings = get_option('arsol_projects_settings', array());
        
        // Force moderation if enabled in settings
        if (!empty($settings['require_comment_moderation'])) {
            return 0; // Hold for moderation
        }

        return $approved;
    }

    /**
     * Filter comment form defaults for our post types
     *
     * @param array $defaults Comment form defaults
     * @return array Modified defaults
     */
    public function filter_comment_form_defaults($defaults) {
        global $post;
        
        if (!$post || !$this->is_project_post_type($post->post_type)) {
            return $defaults;
        }

        // Customize comment form for project post types
        $defaults['title_reply'] = $this->get_comment_form_title($post->post_type);
        $defaults['comment_notes_before'] = $this->get_comment_notes($post->post_type);
        $defaults['submit_button'] = '<input name="%1$s" type="submit" id="%2$s" class="%3$s button" value="%4$s" />';
        
        return $defaults;
    }

    /**
     * Send comment notifications when enabled
     *
     * @param int $comment_id Comment ID
     * @param int|string $approved Comment approval status
     * @param array $commentdata Comment data
     */
    public function send_comment_notifications($comment_id, $approved, $commentdata) {
        $settings = get_option('arsol_projects_settings', array());
        
        if (empty($settings['enable_comment_notifications'])) {
            return;
        }

        $post = get_post($commentdata['comment_post_ID']);
        
        if (!$post || !$this->is_project_post_type($post->post_type)) {
            return;
        }

        // Only send notifications for approved comments
        if ($approved !== 1) {
            return;
        }

        $comment = get_comment($comment_id);
        $this->send_notification_email($post, $comment);
    }

    /**
     * Filter comment content for security
     *
     * @param string $comment_content Comment content
     * @param WP_Comment $comment Comment object
     * @return string Filtered content
     */
    public function filter_comment_content($comment_content, $comment) {
        $post = get_post($comment->comment_post_ID);
        
        if (!$post || !$this->is_project_post_type($post->post_type)) {
            return $comment_content;
        }

        // Additional security filtering for project comments
        $comment_content = wp_kses_post($comment_content);
        
        return $comment_content;
    }

    /**
     * Map comment capabilities for role-based access
     *
     * @param array $caps Capabilities required
     * @param string $cap Capability being checked
     * @param int $user_id User ID
     * @param array $args Additional arguments
     * @return array Modified capabilities
     */
    public function map_comment_capabilities($caps, $cap, $user_id, $args) {
        // Handle comment editing/moderation capabilities
        if (in_array($cap, array('edit_comment', 'moderate_comments')) && !empty($args[0])) {
            $comment = get_comment($args[0]);
            
            if ($comment) {
                $post = get_post($comment->comment_post_ID);
                
                if ($post && $this->is_project_post_type($post->post_type)) {
                    // Allow project managers to moderate comments
                    if (\Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_manage_projects($user_id)) {
                        return array('exist');
                    }
                    
                    // Allow project authors to moderate comments on their projects
                    if ($post->post_author == $user_id) {
                        return array('exist');
                    }
                }
            }
        }

        return $caps;
    }

    /**
     * Check if user can comment on a post
     *
     * @param WP_Post $post Post object
     * @return bool Whether user can comment
     */
    private function user_can_comment($post) {
        $user_id = get_current_user_id();
        
        if (!$user_id) {
            return false;
        }

        $settings = get_option('arsol_projects_settings', array());
        $allowed_roles = isset($settings['comment_roles']) ? $settings['comment_roles'] : array('administrator');

        $user = wp_get_current_user();
        $user_roles = $user->roles;

        // Check if user has an allowed role
        return !empty(array_intersect($user_roles, $allowed_roles));
    }

    /**
     * Check if post type is one of our project post types
     *
     * @param string $post_type Post type
     * @return bool Whether it's a project post type
     */
    private function is_project_post_type($post_type) {
        return in_array($post_type, array('arsol-project', 'arsol-pfw-proposal', 'arsol-pfw-request'));
    }

    /**
     * Get comment form title based on post type
     *
     * @param string $post_type Post type
     * @return string Comment form title
     */
    private function get_comment_form_title($post_type) {
        switch ($post_type) {
            case 'arsol-project':
                return __('Leave a Project Comment', 'arsol-pfw');
            case 'arsol-pfw-proposal':
                return __('Comment on this Proposal', 'arsol-pfw');
            case 'arsol-pfw-request':
                return __('Comment on this Request', 'arsol-pfw');
            default:
                return __('Leave a Comment', 'arsol-pfw');
        }
    }

    /**
     * Get comment notes based on post type
     *
     * @param string $post_type Post type
     * @return string Comment notes
     */
    private function get_comment_notes($post_type) {
        switch ($post_type) {
            case 'arsol-project':
                return '<p class="comment-notes">' . __('Share updates, questions, or feedback about this project.', 'arsol-pfw') . '</p>';
            case 'arsol-pfw-proposal':
                return '<p class="comment-notes">' . __('Discuss this proposal with the project team.', 'arsol-pfw') . '</p>';
            case 'arsol-pfw-request':
                return '<p class="comment-notes">' . __('Add additional details or questions about this request.', 'arsol-pfw') . '</p>';
            default:
                return '';
        }
    }

    /**
     * Send notification email for new comments
     *
     * @param WP_Post $post Post object
     * @param WP_Comment $comment Comment object
     */
    private function send_notification_email($post, $comment) {
        $post_author = get_userdata($post->post_author);
        $post_type_label = $this->get_post_type_label($post->post_type);
        
        $subject = sprintf(__('New comment on your %s: %s'), $post_type_label, $post->post_title);
        
        $message = sprintf(
            __('A new comment has been posted on your %1$s "%2$s"' . "\n\n" .
               'Author: %3$s' . "\n" .
               'Comment: %4$s' . "\n\n" .
               'View the %5$s: %6$s' . "\n\n" .
               'You can moderate comments from your WordPress dashboard.'),
            $post_type_label,
            $post->post_title,
            $comment->comment_author,
            wp_strip_all_tags($comment->comment_content),
            $post_type_label,
            $this->get_post_url($post)
        );

        // Send to post author
        if ($post_author && $post_author->user_email) {
            wp_mail($post_author->user_email, $subject, $message);
        }

        // Send to administrators if different from post author
        $admin_email = get_option('admin_email');
        if ($admin_email && $admin_email !== $post_author->user_email) {
            wp_mail($admin_email, $subject, $message);
        }
    }

    /**
     * Get post type label for notifications
     *
     * @param string $post_type Post type
     * @return string Post type label
     */
    private function get_post_type_label($post_type) {
        switch ($post_type) {
            case 'arsol-project':
                return __('project', 'arsol-pfw');
            case 'arsol-pfw-proposal':
                return __('proposal', 'arsol-pfw');
            case 'arsol-pfw-request':
                return __('request', 'arsol-pfw');
            default:
                return __('post', 'arsol-pfw');
        }
    }

    /**
     * Get appropriate URL for post based on type
     *
     * @param WP_Post $post Post object
     * @return string Post URL
     */
    private function get_post_url($post) {
        switch ($post->post_type) {
            case 'arsol-project':
                return wc_get_account_endpoint_url('project-overview/' . $post->ID);
            case 'arsol-pfw-proposal':
                return wc_get_account_endpoint_url('project-view-proposal/' . $post->ID);
            case 'arsol-pfw-request':
                return wc_get_account_endpoint_url('project-view-request/' . $post->ID);
            default:
                return get_permalink($post);
        }
    }

    /**
     * Get comment count for a specific post type
     *
     * @param string $post_type Post type
     * @param string $status Comment status (approved, pending, etc.)
     * @return int Comment count
     */
    public static function get_comment_count_by_post_type($post_type, $status = 'approved') {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->comments} c 
             LEFT JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID 
             WHERE p.post_type = %s AND c.comment_approved = %s",
            $post_type,
            $status
        );
        
        return (int) $wpdb->get_var($query);
    }
} 