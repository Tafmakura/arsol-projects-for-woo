<?php

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simple AJAX Comments Handler
 * Based on https://rudrastyh.com/wordpress/ajax-comments.html
 */
class Frontend_Comments {

    /**
     * Maximum comment reply depth
     * Change this single value to control reply nesting throughout the system
     */
    private $max_reply_depth;

    /**
     * Get maximum reply depth from settings
     * Static method to access the depth value from templates and other classes
     */
    public static function get_max_reply_depth() {
        $settings = get_option('arsol_pfw_general_settings', array());
        $depth = isset($settings['comment_max_depth']) ? $settings['comment_max_depth'] : 5;
        // Ensure depth is between 0 and 5
        return max(0, min(5, intval($depth)));
    }

    /**
     * Constructor
     */
    public function __construct() {
        // Set max depth from settings
        $this->max_reply_depth = self::get_max_reply_depth();
        
        add_action('wp_ajax_arsol_ajax_comments', array($this, 'handle_ajax_comments'));
        
        add_action('wp_ajax_nopriv_arsol_ajax_comments', array($this, 'handle_ajax_comments'));
        
        // AJAX handlers for edit and delete
        add_action('wp_ajax_arsol_edit_comment', array($this, 'handle_edit_comment'));
        add_action('wp_ajax_arsol_delete_comment', array($this, 'handle_delete_comment'));
        add_action('wp_ajax_arsol_reply_comment', array($this, 'handle_reply_comment'));
        add_action('wp_ajax_nopriv_arsol_reply_comment', array($this, 'handle_reply_comment'));
        
        
    }

    /**
     * Handle AJAX comment submission
     */
    public function handle_ajax_comments() {
        
        // Get the submitted comment
        $comment = wp_handle_comment_submission(wp_unslash($_POST));
        
        if (is_wp_error($comment)) {
            $data = intval($comment->get_error_data());
            if (!empty($data)) {
                wp_die('<p>' . $comment->get_error_message() . '</p>', __('Comment Submission Failure'), array('response' => $data, 'back_link' => true));
            } else {
                wp_die('Unknown error');
            }
        }
        
        $user = wp_get_current_user();
        do_action('set_comment_cookies', $comment, $user);
        
        // Get comment to display
        $comment_depth = 1;
        $comment_parent = $comment->comment_parent;
        while ($comment_parent) {
            $comment_depth++;
            $parent_comment = get_comment($comment_parent);
            $comment_parent = $parent_comment->comment_parent;
        }
        
        // Store original author for edit/delete permissions
        add_comment_meta($comment->comment_ID, '_arsol_original_author', get_current_user_id());
        
        // Generate comment output to match existing structure exactly
        ob_start();
        ?>
        <li id="comment-<?php echo $comment->comment_ID; ?>" class="comment">
            <div class="comment-body">
                <div class="comment-author"><?php echo get_comment_author($comment->comment_ID); ?></div>
                <div class="comment-meta">
                    <time class="comment-date" datetime="<?php echo get_comment_date('c', $comment->comment_ID); ?>">
                        <?php echo get_comment_date('M j, Y \a\t g:i A', $comment->comment_ID); ?>
                    </time>
                </div>
                <div class="comment-content"><?php echo get_comment_text($comment->comment_ID); ?></div>
                
                <?php if ($comment_depth < $this->max_reply_depth): // Max depth for replies ?>
                <div class="reply">
                    <?php 
                    comment_reply_link(array(
                        'add_below' => 'comment',
                        'depth' => $comment_depth,
                        'max_depth' => $this->max_reply_depth,
                        'reply_text' => __('Reply', 'arsol-pfw')
                    ), $comment); 
                    ?>
                </div>
                <?php endif; ?>
                
                <?php 
                // Add edit/delete links if user has permission
                $current_user_id = get_current_user_id();
                if ($current_user_id == get_current_user_id() || current_user_can('manage_options')): 
                ?>
                <div class="arsol-comment-actions">
                    <a href="#" class="arsol-edit-comment" data-comment-id="<?php echo $comment->comment_ID; ?>"><?php _e('Edit', 'arsol-pfw'); ?></a> | 
                    <a href="#" class="arsol-delete-comment" data-comment-id="<?php echo $comment->comment_ID; ?>"><?php _e('Delete', 'arsol-pfw'); ?></a>
                </div>
                <?php endif; ?>
            </div>
        </li>
        <?php
        
        $comment_output = ob_get_clean();
        wp_die($comment_output);
    }

    /**
     * Handle AJAX comment editing
     */
    public function handle_edit_comment() {
        // Check nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_comments_nonce')) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }
        
        $comment_id = intval($_POST['comment_id']);
        $new_content = sanitize_textarea_field($_POST['comment_content']);
        
        if (empty($new_content)) {
            wp_die(__('Comment content cannot be empty', 'arsol-pfw'));
        }
        
        // Get the comment
        $comment = get_comment($comment_id);
        if (!$comment) {
            wp_die(__('Comment not found', 'arsol-pfw'));
        }
        
        // Check permissions
        $current_user_id = get_current_user_id();
        $comment_author_id = get_comment_meta($comment_id, '_arsol_original_author', true);
        
        if ($current_user_id != $comment_author_id && !current_user_can('manage_options')) {
            wp_die(__('You do not have permission to edit this comment', 'arsol-pfw'));
        }
        
        // Update the comment
        $updated_comment = array(
            'comment_ID' => $comment_id,
            'comment_content' => $new_content
        );
        
        $result = wp_update_comment($updated_comment);
        
        if (is_wp_error($result)) {
            wp_die(__('Failed to update comment', 'arsol-pfw'));
        }
        
        // Return the updated comment content
        wp_die($new_content);
    }
    
    /**
     * Recursively get all child comment IDs for a given comment
     * 
     * @param int $comment_id The parent comment ID
     * @return array Array of all child comment IDs (including nested children)
     */
    private function get_all_child_comment_ids($comment_id) {
        $child_ids = array();
        
        // Get direct children
        $children = get_comments(array(
            'parent' => $comment_id,
            'fields' => 'ids',
            'status' => 'all'
        ));
        
        foreach ($children as $child_id) {
            $child_ids[] = $child_id;
            // Recursively get children of children
            $grandchildren = $this->get_all_child_comment_ids($child_id);
            $child_ids = array_merge($child_ids, $grandchildren);
        }
        
        return $child_ids;
    }
    
    /**
     * Handle AJAX comment deletion
     * Properly deletes comment and all its child replies
     */
    public function handle_delete_comment() {
        // Check nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_comments_nonce')) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }
        
        $comment_id = intval($_POST['comment_id']);
        
        // Get the comment
        $comment = get_comment($comment_id);
        if (!$comment) {
            wp_die(__('Comment not found', 'arsol-pfw'));
        }
        
        // Check permissions
        $current_user_id = get_current_user_id();
        $comment_author_id = get_comment_meta($comment_id, '_arsol_original_author', true);
        
        if ($current_user_id != $comment_author_id && !current_user_can('manage_options')) {
            wp_die(__('You do not have permission to delete this comment', 'arsol-pfw'));
        }
        
        // Get all child comment IDs recursively
        $child_comment_ids = $this->get_all_child_comment_ids($comment_id);
        
        // Delete all child comments first (bottom-up)
        foreach (array_reverse($child_comment_ids) as $child_id) {
            wp_delete_comment($child_id, true);
        }
        
        // Delete the parent comment
        $result = wp_delete_comment($comment_id, true); // true = force delete
        
        if (!$result) {
            wp_die(__('Failed to delete comment', 'arsol-pfw'));
        }
        
        wp_die('success');
    }
    
    /**
     * Handle AJAX reply form display
     */
    public function handle_reply_comment() {
        // Check nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'arsol_comments_nonce')) {
            wp_die(__('Security check failed', 'arsol-pfw'));
        }
        
        $comment_id = intval($_POST['comment_id']);
        $post_id = intval($_POST['post_id']);
        
        // Get the comment to determine depth
        $comment = get_comment($comment_id);
        if (!$comment) {
            wp_die(__('Comment not found', 'arsol-pfw'));
        }
        
        // Calculate depth
        $comment_depth = 1;
        $comment_parent = $comment->comment_parent;
        while ($comment_parent) {
            $comment_depth++;
            $parent_comment = get_comment($comment_parent);
            $comment_parent = $parent_comment->comment_parent;
        }
        
        // Check if we're at max depth
        if ($comment_depth >= $this->max_reply_depth) {
            wp_die(__('Maximum reply depth reached', 'arsol-pfw'));
        }
        
        // Get current user info
        $current_user = wp_get_current_user();
        
        // Generate reply form
        ob_start();
        ?>
        <div class="arsol-reply-form-container">
            <form class="arsol-reply-form" data-comment-id="<?php echo $comment_id; ?>" data-post-id="<?php echo $post_id; ?>">
                <div class="arsol-reply-form-header">
                    <strong><?php printf(__('Reply to %s', 'arsol-pfw'), get_comment_author($comment_id)); ?></strong>
                </div>
                
                <div class="arsol-reply-form-fields">
                    <?php if (!is_user_logged_in()): ?>
                        <div class="arsol-reply-form-row">
                            <label for="arsol-reply-author"><?php _e('Name', 'arsol-pfw'); ?> <span class="required">*</span></label>
                            <input type="text" id="arsol-reply-author" name="author" required>
                        </div>
                        <div class="arsol-reply-form-row">
                            <label for="arsol-reply-email"><?php _e('Email', 'arsol-pfw'); ?> <span class="required">*</span></label>
                            <input type="email" id="arsol-reply-email" name="email" required>
                        </div>
                    <?php endif; ?>
                    
                    <div class="arsol-reply-form-row">
                        <label for="arsol-reply-content"><?php _e('Your Reply', 'arsol-pfw'); ?> <span class="required">*</span></label>
                        <textarea id="arsol-reply-content" name="comment" rows="4" required></textarea>
                    </div>
                </div>
                
                <div class="arsol-reply-form-actions">
                    <button type="submit" class="arsol-reply-submit"><?php _e('Post Reply', 'arsol-pfw'); ?></button>
                    <button type="button" class="arsol-reply-cancel"><?php _e('Cancel', 'arsol-pfw'); ?></button>
                </div>
                
                <input type="hidden" name="comment_post_ID" value="<?php echo $post_id; ?>">
                <input type="hidden" name="comment_parent" value="<?php echo $comment_id; ?>">
            </form>
        </div>
        <?php
        
        $form_output = ob_get_clean();
        wp_die($form_output);
    }
}
