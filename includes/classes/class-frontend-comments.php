<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend Comments Handler - Best Practice Implementation
 * 
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */
class Frontend_Comments {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_arsol_ajax_comments', array($this, 'handle_ajax_comments'));
        add_action('wp_ajax_nopriv_arsol_ajax_comments', array($this, 'handle_ajax_comments'));
        add_action('wp_ajax_arsol_edit_comment', array($this, 'handle_edit_comment'));
        add_action('wp_ajax_arsol_delete_comment', array($this, 'handle_delete_comment'));
    }

    /**
     * Handle AJAX comment submission
     */
    public function handle_ajax_comments() {
        try {
            // Verify nonce
            if (!$this->verify_nonce()) {
                $this->send_json_error(__('Security check failed', 'arsol-pfw'), 403);
            }

            // Get the submitted comment
            $comment = wp_handle_comment_submission(wp_unslash($_POST));
            
            if (is_wp_error($comment)) {
                $this->send_json_error($comment->get_error_message(), $comment->get_error_code());
            }

            // Set comment cookies
            $user = wp_get_current_user();
            do_action('set_comment_cookies', $comment, $user);
            
            // Store original author for edit/delete permissions
            add_comment_meta($comment->comment_ID, '_arsol_original_author', get_current_user_id());
            
            // Generate comment HTML
            $comment_html = $this->generate_comment_html($comment);
            
            $this->send_json_success(array(
                'comment_html' => $comment_html,
                'comment_id' => $comment->comment_ID,
                'message' => __('Comment posted successfully', 'arsol-pfw')
            ));

        } catch (Exception $e) {
            $this->send_json_error(__('An error occurred while posting your comment', 'arsol-pfw'), 500);
        }
    }

    /**
     * Handle AJAX comment editing
     */
    public function handle_edit_comment() {
        try {
            // Verify nonce
            if (!$this->verify_nonce()) {
                $this->send_json_error(__('Security check failed', 'arsol-pfw'), 403);
            }

            // Check if user is logged in
            if (!is_user_logged_in()) {
                $this->send_json_error(__('You must be logged in to edit comments', 'arsol-pfw'), 401);
            }

            // Validate and sanitize input
            $comment_id = $this->validate_comment_id();
            $new_content = $this->validate_comment_content();
            
            // Get the comment
            $comment = get_comment($comment_id);
            if (!$comment) {
                $this->send_json_error(__('Comment not found', 'arsol-pfw'), 404);
            }

            // Check permissions
            if (!$this->user_can_edit_comment($comment)) {
                $this->send_json_error(__('You do not have permission to edit this comment', 'arsol-pfw'), 403);
            }

            // Update the comment
            $result = wp_update_comment(array(
                'comment_ID' => $comment_id,
                'comment_content' => $new_content
            ));

            if (is_wp_error($result)) {
                $this->send_json_error(__('Failed to update comment', 'arsol-pfw'), 500);
            }

            $this->send_json_success(array(
                'comment_content' => $new_content,
                'message' => __('Comment updated successfully', 'arsol-pfw')
            ));

        } catch (Exception $e) {
            $this->send_json_error(__('An error occurred while updating your comment', 'arsol-pfw'), 500);
        }
    }
    
    /**
     * Handle AJAX comment deletion
     */
    public function handle_delete_comment() {
        try {
            // Verify nonce
            if (!$this->verify_nonce()) {
                $this->send_json_error(__('Security check failed', 'arsol-pfw'), 403);
            }

            // Check if user is logged in
            if (!is_user_logged_in()) {
                $this->send_json_error(__('You must be logged in to delete comments', 'arsol-pfw'), 401);
            }

            // Validate input
            $comment_id = $this->validate_comment_id();
            
            // Get the comment
            $comment = get_comment($comment_id);
            if (!$comment) {
                $this->send_json_error(__('Comment not found', 'arsol-pfw'), 404);
            }

            // Check permissions
            if (!$this->user_can_delete_comment($comment)) {
                $this->send_json_error(__('You do not have permission to delete this comment', 'arsol-pfw'), 403);
            }

            // Delete the comment
            $result = wp_delete_comment($comment_id, true);

            if (!$result) {
                $this->send_json_error(__('Failed to delete comment', 'arsol-pfw'), 500);
            }

            $this->send_json_success(array(
                'message' => __('Comment deleted successfully', 'arsol-pfw')
            ));

        } catch (Exception $e) {
            $this->send_json_error(__('An error occurred while deleting your comment', 'arsol-pfw'), 500);
        }
    }

    /**
     * Verify nonce for security
     * 
     * @return bool
     */
    private function verify_nonce() {
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        return wp_verify_nonce($nonce, 'arsol_comments_nonce');
    }

    /**
     * Validate comment ID
     * 
     * @return int
     * @throws Exception
     */
    private function validate_comment_id() {
        $comment_id = intval($_POST['comment_id'] ?? 0);
        if ($comment_id <= 0) {
            throw new Exception(__('Invalid comment ID', 'arsol-pfw'));
        }
        return $comment_id;
    }

    /**
     * Validate comment content
     * 
     * @return string
     * @throws Exception
     */
    private function validate_comment_content() {
        $content = wp_kses_post($_POST['comment_content'] ?? '');
        $content = trim($content);
        
        if (empty($content)) {
            throw new Exception(__('Comment content cannot be empty', 'arsol-pfw'));
        }
        
        if (strlen($content) > 65535) { // MySQL TEXT limit
            throw new Exception(__('Comment content is too long', 'arsol-pfw'));
        }
        
        return $content;
    }

    /**
     * Check if user can edit comment
     * 
     * @param WP_Comment $comment
     * @return bool
     */
    private function user_can_edit_comment($comment) {
        $current_user_id = get_current_user_id();
        
        // Admin can edit any comment
        if (current_user_can('moderate_comments')) {
            return true;
        }
        
        // Check if user is the original author
        $original_author = get_comment_meta($comment->comment_ID, '_arsol_original_author', true);
        if ($original_author && $current_user_id == $original_author) {
            return true;
        }
        
        // Fallback to comment author check
        return $current_user_id == $comment->user_id;
    }

    /**
     * Check if user can delete comment
     * 
     * @param WP_Comment $comment
     * @return bool
     */
    private function user_can_delete_comment($comment) {
        // Use same logic as edit for now
        return $this->user_can_edit_comment($comment);
    }

    /**
     * Generate comment HTML
     * 
     * @param WP_Comment $comment
     * @return string
     */
    private function generate_comment_html($comment) {
        $comment_depth = $this->get_comment_depth($comment);
        $current_user_id = get_current_user_id();
        $can_edit = $this->user_can_edit_comment($comment);
        
        ob_start();
        ?>
        <li id="comment-<?php echo esc_attr($comment->comment_ID); ?>" class="comment" role="article">
            <div class="comment-body">
                <div class="comment-author" role="heading" aria-level="4">
                    <?php echo esc_html(get_comment_author($comment->comment_ID)); ?>
                </div>
                <div class="comment-meta">
                    <time class="comment-date" datetime="<?php echo esc_attr(get_comment_date('c', $comment->comment_ID)); ?>">
                        <?php echo esc_html(get_comment_date('M j, Y \a\t g:i A', $comment->comment_ID)); ?>
                    </time>
                </div>
                <div class="comment-content" role="complementary">
                    <?php echo wp_kses_post(get_comment_text($comment->comment_ID)); ?>
                </div>
                
                <?php if ($comment_depth < 5): ?>
                <div class="reply">
                    <?php 
                    comment_reply_link(array(
                        'add_below' => 'comment',
                        'depth' => $comment_depth,
                        'max_depth' => 5,
                        'reply_text' => __('Reply', 'arsol-pfw')
                    ), $comment); 
                    ?>
                </div>
                <?php endif; ?>
                
                <?php if ($can_edit): ?>
                <div class="arsol-comment-actions" role="group" aria-label="<?php esc_attr_e('Comment actions', 'arsol-pfw'); ?>">
                    <button type="button" class="arsol-edit-comment" data-comment-id="<?php echo esc_attr($comment->comment_ID); ?>" aria-label="<?php esc_attr_e('Edit this comment', 'arsol-pfw'); ?>">
                        <?php esc_html_e('Edit', 'arsol-pfw'); ?>
                    </button>
                    <span aria-hidden="true"> | </span>
                    <button type="button" class="arsol-delete-comment" data-comment-id="<?php echo esc_attr($comment->comment_ID); ?>" aria-label="<?php esc_attr_e('Delete this comment', 'arsol-pfw'); ?>">
                        <?php esc_html_e('Delete', 'arsol-pfw'); ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </li>
        <?php
        
        return ob_get_clean();
    }

    /**
     * Get comment depth
     * 
     * @param WP_Comment $comment
     * @return int
     */
    private function get_comment_depth($comment) {
        $depth = 1;
        $parent_id = $comment->comment_parent;
        
        while ($parent_id) {
            $depth++;
            $parent = get_comment($parent_id);
            $parent_id = $parent ? $parent->comment_parent : 0;
        }
        
        return $depth;
    }

    /**
     * Send JSON success response
     * 
     * @param array $data
     */
    private function send_json_success($data = array()) {
        wp_send_json_success($data);
    }

    /**
     * Send JSON error response
     * 
     * @param string $message
     * @param int $code
     */
    private function send_json_error($message, $code = 400) {
        wp_send_json_error(array(
            'message' => $message,
            'code' => $code
        ), $code);
    }
}
