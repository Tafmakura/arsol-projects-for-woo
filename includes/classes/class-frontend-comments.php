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
        
        // Generate comment output
        ob_start();
        ?>
        <li <?php comment_class('depth-' . $comment_depth); ?> id="comment-<?php echo $comment->comment_ID; ?>">
            <article id="div-comment-<?php echo $comment->comment_ID; ?>" class="comment-body">
                <footer class="comment-meta">
                    <div class="comment-author vcard">
                        <b class="fn"><?php echo get_comment_author($comment->comment_ID); ?></b>
                        <span class="says">says:</span>
                    </div>
                    <div class="comment-metadata">
                        <a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>">
                            <time datetime="<?php comment_time('c'); ?>">
                                <?php printf(__('%1$s at %2$s'), get_comment_date('', $comment->comment_ID), get_comment_time()); ?>
                            </time>
                        </a>
                    </div>
                </footer>
                
                <div class="comment-content">
                    <?php comment_text($comment->comment_ID); ?>
                </div>
                
                <div class="reply">
                    <?php 
                    comment_reply_link(array_merge(array(
                        'add_below' => 'div-comment',
                        'depth' => $comment_depth,
                        'max_depth' => get_option('thread_comments_depth')
                    ), array('comment' => $comment))); 
                    ?>
                </div>
            </article>
        </li>
        <?php
        
        $comment_output = ob_get_clean();
        wp_die($comment_output);
    }
} 