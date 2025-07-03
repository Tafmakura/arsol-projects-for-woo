<?php
/**
 * Frontend Comments AJAX Handler
 *
 * Handles AJAX requests for comment operations like editing and deletion.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

namespace Arsol_Projects_For_Woo\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

class Comments_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize AJAX handlers
     */
    public function init() {
        // AJAX actions for logged-in users
        add_action('wp_ajax_arsol_edit_comment', array($this, 'handle_edit_comment'));
        add_action('wp_ajax_arsol_delete_comment', array($this, 'handle_delete_comment'));
        add_action('wp_ajax_arsol_submit_comment', array($this, 'handle_submit_comment'));
        
        // AJAX actions for non-logged-in users (if needed)
        add_action('wp_ajax_nopriv_arsol_submit_comment', array($this, 'handle_submit_comment'));
    }
    
    /**
     * Handle AJAX comment editing
     */
    public function handle_edit_comment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_comments_nonce')) {
            wp_send_json_error(__('Security check failed.', 'arsol-pfw'));
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
            wp_send_json_error(__('Security check failed.', 'arsol-pfw'));
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
     * Handle AJAX comment submission
     */
    public function handle_submit_comment() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_comments_nonce')) {
            wp_send_json_error(__('Security check failed.', 'arsol-pfw'));
        }
        
        $post_id = intval($_POST['post_id']);
        $comment_content = wp_kses_post($_POST['comment']);
        $comment_parent = intval($_POST['comment_parent']);
        
        if (!$post_id || !$comment_content) {
            wp_send_json_error(__('Invalid comment data.', 'arsol-pfw'));
        }
        
        $post = get_post($post_id);
        if (!$post || !in_array($post->post_type, ['arsol-pfw-project', 'arsol-pfw-request', 'arsol-pfw-proposal'])) {
            wp_send_json_error(__('Invalid post.', 'arsol-pfw'));
        }
        
        // Check if comments are open for this post
        if (!comments_open($post_id)) {
            wp_send_json_error(__('Comments are closed for this post.', 'arsol-pfw'));
        }
        
        $current_user = wp_get_current_user();
        if (!$current_user->ID) {
            wp_send_json_error(__('You must be logged in to comment.', 'arsol-pfw'));
        }
        
        // Prepare comment data
        $comment_data = array(
            'comment_post_ID' => $post_id,
            'comment_content' => $comment_content,
            'comment_parent' => $comment_parent,
            'user_id' => $current_user->ID,
            'comment_author' => $current_user->display_name,
            'comment_author_email' => $current_user->user_email,
            'comment_author_url' => $current_user->user_url,
            'comment_type' => 'comment',
            'comment_approved' => 1
        );
        
        // Insert comment
        $comment_id = wp_insert_comment($comment_data);
        
        if ($comment_id) {
            // Add comment meta
            add_comment_meta($comment_id, '_arsol_original_author', $current_user->ID);
            add_comment_meta($comment_id, '_arsol_created_date', current_time('mysql'));
            
            // Get the comment object
            $comment = get_comment($comment_id);
            
            wp_send_json_success(array(
                'message' => __('Comment posted successfully.', 'arsol-pfw'),
                'comment_id' => $comment_id,
                'comment_html' => $this->format_comment_html($comment)
            ));
        } else {
            wp_send_json_error(__('Failed to post comment.', 'arsol-pfw'));
        }
    }
    
    /**
     * Format comment HTML for AJAX response
     */
    private function format_comment_html($comment) {
        $current_user_id = get_current_user_id();
        $comment_author_id = get_comment_meta($comment->comment_ID, '_arsol_original_author', true);
        
        ob_start();
        echo '<li id="comment-' . $comment->comment_ID . '" class="comment">';
        echo '<div class="comment-body">';
        echo '<div class="comment-author">' . $comment->comment_author . '</div>';
        echo '<div class="comment-meta">' . mysql2date('M j, Y \a\t g:i A', $comment->comment_date) . '</div>';
        echo '<div class="comment-content">' . apply_filters('comment_text', $comment->comment_content, $comment) . '</div>';
        
        // Add edit/delete links if user has permission
        if ($current_user_id == $comment_author_id || current_user_can('manage_options')) {
            echo '<div class="arsol-comment-actions">';
            echo '<a href="#" class="arsol-edit-comment" data-comment-id="' . $comment->comment_ID . '">' . __('Edit', 'arsol-pfw') . '</a> | ';
            echo '<a href="#" class="arsol-delete-comment" data-comment-id="' . $comment->comment_ID . '">' . __('Delete', 'arsol-pfw') . '</a>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '</li>';
        
        return ob_get_clean();
    }
}
