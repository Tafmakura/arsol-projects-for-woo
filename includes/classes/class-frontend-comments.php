<?php
/**
 * Frontend Comments Handler
 *
 * Handles comments functionality for project-related post types with rich text,
 * threaded replies, edit/delete capabilities, and email notifications.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

class Comments {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize comments functionality
     */
    public function init() {
        // Enable comments for our post types based on settings
        add_filter('comments_open', array($this, 'enable_comments_for_post_types'), 10, 2);
        
        // Add comment support to post types
        add_action('init', array($this, 'add_comment_support_to_post_types'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Handle comment notifications
        add_action('comment_post', array($this, 'send_comment_notifications'), 10, 3);
        
        // Add edit/delete capabilities
        add_action('wp_ajax_edit_project_comment', array($this, 'handle_edit_comment'));
        add_action('wp_ajax_delete_project_comment', array($this, 'handle_delete_comment'));
        
        // Filter comment form defaults
        add_filter('comment_form_defaults', array($this, 'customize_comment_form'));
        
        // Add custom comment meta for edit tracking
        add_action('comment_post', array($this, 'add_comment_meta'), 10, 3);
        
        // Add edit/delete links to comments
        add_filter('comment_text', array($this, 'add_comment_edit_delete_links'), 10, 2);
    }
    
    /**
     * Enable comments for project post types based on settings
     */
    public function enable_comments_for_post_types($open, $post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return $open;
        }
        
        // Check if comments are enabled for this post type in settings
        if (in_array($post->post_type, ['arsol-pfw-project', 'arsol-pfw-request', 'arsol-pfw-proposal'])) {
            return \Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type($post->post_type);
        }
        
        return $open;
    }
    
    /**
     * Add comment support to post types based on settings
     */
    public function add_comment_support_to_post_types() {
        $post_types = ['arsol-pfw-project', 'arsol-pfw-request', 'arsol-pfw-proposal'];
        
        foreach ($post_types as $post_type) {
            if (\Arsol_Projects_For_Woo\Admin\Settings_General::is_comments_enabled_for_post_type($post_type)) {
                add_post_type_support($post_type, 'comments');
            }
        }
    }
    
    /**
     * Enqueue scripts and styles for comments
     */
    public function enqueue_scripts() {
        if (!is_account_page()) {
            return;
        }
        
        global $wp_query;
        
        // Check if we're on a project-related page
        $is_project_page = (
            isset($wp_query->query_vars['project-overview']) ||
            isset($wp_query->query_vars['project-view-proposal']) ||
            isset($wp_query->query_vars['project-view-request'])
        );
        
        if ($is_project_page) {
            // Enqueue our custom comments script
            wp_enqueue_script(
                'arsol-pfw-frontend-comments',
                ARSOL_PROJECTS_PLUGIN_URL . 'assets/js/arsol-pfw-frontend-comments.js',
                array('jquery', 'wp-util'),
                ARSOL_PROJECTS_VERSION,
                true
            );
            
            // Localize script for AJAX
            wp_localize_script('arsol-pfw-frontend-comments', 'arsolComments', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arsol_comments_nonce'),
                'strings' => array(
                    'edit' => __('Edit', 'arsol-pfw'),
                    'delete' => __('Delete', 'arsol-pfw'),
                    'save' => __('Save', 'arsol-pfw'),
                    'cancel' => __('Cancel', 'arsol-pfw'),
                    'confirm_delete' => __('Are you sure you want to delete this comment?', 'arsol-pfw'),
                    'error' => __('An error occurred. Please try again.', 'arsol-pfw'),
                    'comment_updated' => __('Comment updated successfully.', 'arsol-pfw'),
                    'comment_deleted' => __('Comment deleted successfully.', 'arsol-pfw')
                )
            ));
            
            // Add custom CSS for comments
            wp_add_inline_style('woocommerce-general', '
                .arsol-comment-actions { margin-top: 10px; }
                .arsol-comment-actions a { margin-right: 10px; color: #0073aa; text-decoration: none; }
                .arsol-comment-actions a:hover { text-decoration: underline; }
                .arsol-comment-edit-form { margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 4px; }
                .arsol-comment-edit-form textarea { width: 100%; min-height: 80px; }
                .comment-meta .avatar { display: none !important; }
                .comment-author .avatar { display: none !important; }
            ');
        }
    }
    
    /**
     * Customize comment form defaults
     */
    public function customize_comment_form($defaults) {
        if (!is_account_page()) {
            return $defaults;
        }
        
        $defaults['comment_field'] = '<div class="arsol-comment-form"><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" required="required" placeholder="' . esc_attr__('Write your comment...', 'arsol-pfw') . '"></textarea></div>';
        $defaults['title_reply'] = __('Add a Comment', 'arsol-pfw');
        $defaults['title_reply_to'] = __('Reply to %s', 'arsol-pfw');
        $defaults['cancel_reply_link'] = __('Cancel Reply', 'arsol-pfw');
        $defaults['label_submit'] = __('Post Comment', 'arsol-pfw');
        $defaults['submit_button'] = '<input name="%1$s" type="submit" id="%2$s" class="%3$s button" value="%4$s" />';
        
        return $defaults;
    }
    
    /**
     * Add comment meta for edit tracking
     */
    public function add_comment_meta($comment_id, $comment_approved, $commentdata) {
        // Add original author ID for edit permissions
        add_comment_meta($comment_id, '_arsol_original_author', get_current_user_id());
        add_comment_meta($comment_id, '_arsol_created_date', current_time('mysql'));
    }
    
    /**
     * Add edit/delete links to comments
     */
    public function add_comment_edit_delete_links($comment_text, $comment) {
        if (!is_account_page()) {
            return $comment_text;
        }
        
        $current_user_id = get_current_user_id();
        $comment_author_id = get_comment_meta($comment->comment_ID, '_arsol_original_author', true);
        
        // Only show edit/delete links to comment author or project managers
        if ($current_user_id == $comment_author_id || current_user_can('manage_options')) {
            $edit_link = '<a href="#" class="arsol-edit-comment" data-comment-id="' . $comment->comment_ID . '">' . __('Edit', 'arsol-pfw') . '</a>';
            $delete_link = '<a href="#" class="arsol-delete-comment" data-comment-id="' . $comment->comment_ID . '">' . __('Delete', 'arsol-pfw') . '</a>';
            
            $comment_text .= '<div class="arsol-comment-actions">' . $edit_link . ' | ' . $delete_link . '</div>';
        }
        
        return $comment_text;
    }
    
    /**
     * Handle AJAX comment editing
     */
    public function handle_edit_comment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_comments_nonce')) {
            wp_die(__('Security check failed.', 'arsol-pfw'));
        }
        
        $comment_id = intval($_POST['comment_id']);
        $new_content = wp_kses_post($_POST['content']);
        
        if (!$comment_id || !$new_content) {
            wp_send_json_error(__('Invalid comment data.', 'arsol-pfw'));
        }
        
        $comment = get_comment($comment_id);
        if (!$comment) {
            wp_send_json_error(__('Comment not found.', 'arsol-pfw'));
        }
        
        // Check permissions
        $current_user_id = get_current_user_id();
        $comment_author_id = get_comment_meta($comment_id, '_arsol_original_author', true);
        
        if ($current_user_id != $comment_author_id && !current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to edit this comment.', 'arsol-pfw'));
        }
        
        // Update comment
        $updated = wp_update_comment(array(
            'comment_ID' => $comment_id,
            'comment_content' => $new_content
        ));
        
        if ($updated) {
            // Add edit meta
            update_comment_meta($comment_id, '_arsol_edited_date', current_time('mysql'));
            update_comment_meta($comment_id, '_arsol_edited_by', $current_user_id);
            
            wp_send_json_success(array(
                'message' => __('Comment updated successfully.', 'arsol-pfw'),
                'content' => apply_filters('comment_text', $new_content, $comment)
            ));
        } else {
            wp_send_json_error(__('Failed to update comment.', 'arsol-pfw'));
        }
    }
    
    /**
     * Handle AJAX comment deletion
     */
    public function handle_delete_comment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_comments_nonce')) {
            wp_die(__('Security check failed.', 'arsol-pfw'));
        }
        
        $comment_id = intval($_POST['comment_id']);
        
        if (!$comment_id) {
            wp_send_json_error(__('Invalid comment ID.', 'arsol-pfw'));
        }
        
        $comment = get_comment($comment_id);
        if (!$comment) {
            wp_send_json_error(__('Comment not found.', 'arsol-pfw'));
        }
        
        // Check permissions
        $current_user_id = get_current_user_id();
        $comment_author_id = get_comment_meta($comment_id, '_arsol_original_author', true);
        
        if ($current_user_id != $comment_author_id && !current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to delete this comment.', 'arsol-pfw'));
        }
        
        // Delete comment
        $deleted = wp_delete_comment($comment_id, true);
        
        if ($deleted) {
            wp_send_json_success(__('Comment deleted successfully.', 'arsol-pfw'));
        } else {
            wp_send_json_error(__('Failed to delete comment.', 'arsol-pfw'));
        }
    }
    
    /**
     * Send email notifications to project participants
     */
    public function send_comment_notifications($comment_id, $comment_approved, $commentdata) {
        if ($comment_approved !== 1) {
            return;
        }
        
        $comment = get_comment($comment_id);
        $post = get_post($comment->comment_post_ID);
        
        if (!in_array($post->post_type, ['arsol-pfw-project', 'arsol-pfw-request', 'arsol-pfw-proposal'])) {
            return;
        }
        
        // Get project participants
        $participants = array();
        
        // Add post author
        $post_author = get_userdata($post->post_author);
        if ($post_author) {
            $participants[$post_author->ID] = $post_author;
        }
        
        // Add project managers from WooCommerce customer role
        $customer_users = get_users(array('role' => 'customer'));
        foreach ($customer_users as $user) {
            if (wc_customer_bought_product($user->user_email, $user->ID, $post->ID)) {
                $participants[$user->ID] = $user;
            }
        }
        
        // Remove comment author from notifications
        unset($participants[$comment->user_id]);
        
        if (empty($participants)) {
            return;
        }
        
        // Prepare email content
        $comment_author = get_userdata($comment->user_id);
        $comment_author_name = $comment_author ? $comment_author->display_name : $comment->comment_author;
        
        $subject = sprintf(
            __('New comment on %s: %s', 'arsol-pfw'),
            ucfirst(str_replace(['arsol-pfw-', '-'], ['', ' '], $post->post_type)),
            $post->post_title
        );
        
        $message = sprintf(
            __('A new comment has been posted by %s:', 'arsol-pfw') . "\n\n" .
            '%s' . "\n\n" .
            __('You can view and reply to this comment here:', 'arsol-pfw') . "\n" .
            '%s',
            $comment_author_name,
            wp_strip_all_tags($comment->comment_content),
            $this->get_comment_url($post, $comment_id)
        );
        
        // Send emails to participants
        foreach ($participants as $participant) {
            wp_mail(
                $participant->user_email,
                $subject,
                $message,
                array('Content-Type: text/plain; charset=UTF-8')
            );
        }
    }
    
    /**
     * Get comment URL for notifications
     */
    private function get_comment_url($post, $comment_id) {
        switch ($post->post_type) {
            case 'arsol-pfw-project':
                $endpoint = 'project-overview';
                break;
            case 'arsol-pfw-proposal':
                $endpoint = 'project-view-proposal';
                break;
            case 'arsol-pfw-request':
                $endpoint = 'project-view-request';
                break;
            default:
                return get_permalink($post->ID);
        }
        
        return wc_get_account_endpoint_url($endpoint, $post->ID) . '#comment-' . $comment_id;
    }
}
