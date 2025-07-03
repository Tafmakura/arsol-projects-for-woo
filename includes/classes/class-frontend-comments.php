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
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_arsol_ajax_comments', array($this, 'handle_ajax_comments'));
        add_action('wp_ajax_nopriv_arsol_ajax_comments', array($this, 'handle_ajax_comments'));
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
                
                <?php if ($comment_depth < 5): // Max depth for replies ?>
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
} 