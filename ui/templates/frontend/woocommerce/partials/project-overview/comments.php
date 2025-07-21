<?php
/**
 * Comments Partial Template
 *
 * Shared template for displaying comments across all project CPTs
 * Used by project-overview.php, project-view-proposal.php, and project-view-request.php
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure we have a project ID available
if (!isset($project_id) || !$project_id) {
    return;
}

// Get the post object
$post = get_post($project_id);
if (!$post) {
    return;
}

// Get post type for context
$post_type = get_post_type($project_id);
$phase_type = '';

// Map post types to phase types for context
switch ($post_type) {
    case 'arsol-pfw-request':
        $phase_type = 'request';
        break;
    case 'arsol-pfw-proposal':
        $phase_type = 'proposal';
        break;
    case 'arsol-pfw-project':
        $phase_type = 'project';
        break;
    default:
        $phase_type = 'unknown';
}

?>
<div class="project-comments-section">
    <?php
    /**
     * Hook: arsol_pfw_comments_before
     * 
     * @param int $project_id The project ID
     * @param string $phase_type The phase type (request, proposal, project)
     * @param WP_Post $post The post object
     */
    do_action('arsol_pfw_comments_before', $project_id, $phase_type, $post);
    ?>
    
    <div class="comments-container">
        <?php
        // Check if comments are open for this post
        if (comments_open($project_id) || get_comments_number($project_id)) {
            ?>
            <div class="comments-wrapper">
                <?php
                /**
                 * Hook: arsol_pfw_comments_wrapper_start
                 * 
                 * @param int $project_id The project ID
                 * @param string $phase_type The phase type
                 */
                do_action('arsol_pfw_comments_wrapper_start', $project_id, $phase_type);
                ?>
                
                <div class="comments-list">
                    <?php
                    // Get comments for this specific post
                    $comments = get_comments(array(
                        'post_id' => $project_id,
                        'status' => 'approve',
                        'order' => 'ASC'
                    ));
                    
                    if ($comments) {
                        ?>
                        <h4 class="comments-title">
                            <?php 
                            printf(
                                _n('One Comment', '%s Comments', count($comments), 'arsol-pfw'),
                                number_format_i18n(count($comments))
                            );
                            ?>
                        </h4>
                        
                        <ol id="comments" class="commentlist">
                            <?php
                            wp_list_comments(array(
                                'style' => 'ol',
                                'short_ping' => true,
                                'avatar_size' => 0, // No avatars
                                'max_depth' => \Arsol_Projects_For_Woo\Frontend\Comments::get_max_reply_depth(),   // Centralized depth control
                                'thread_comments' => true,
                                'reply_text' => __('Reply', 'arsol-pfw'),
                                'callback' => function($comment, $args, $depth) {
                                    $current_user_id = get_current_user_id();
                                    $comment_author_id = get_comment_meta($comment->comment_ID, '_arsol_original_author', true);
                                    
                                    echo '<li id="comment-' . $comment->comment_ID . '" class="comment">';
                                    echo '<div class="comment-body">';
                                    echo '<div class="comment-author">' . get_comment_author($comment) . '</div>';
                                    echo '<div class="comment-meta">';
                                    echo '<time class="comment-date" datetime="' . get_comment_date('c', $comment) . '">';
                                    echo get_comment_date('M j, Y \a\t g:i A', $comment);
                                    echo '</time>';
                                    echo '</div>';
                                    echo '<div class="comment-content">' . get_comment_text($comment) . '</div>';
                                    
                                    // Add reply link
                                    if ($depth < $args['max_depth']) {
                                        echo '<div class="reply">';
                                        comment_reply_link(array_merge($args, array(
                                            'add_below' => 'comment',
                                            'depth' => $depth,
                                            'max_depth' => $args['max_depth'],
                                            'reply_text' => __('Reply', 'arsol-pfw')
                                        )), $comment);
                                        echo '</div>';
                                    }
                                    
                                    // Add edit/delete links if user has permission
                                    if ($current_user_id == $comment_author_id || arsol_pfw_user_can('arsol_pfw_manage_all')) : ?>
                                        <div class="arsol-comment-actions">
                                            <button class="arsol-pfw-edit-comment-btn" data-comment-id="<?php echo $comment->comment_ID; ?>"><?php _e('Edit', 'arsol-pfw'); ?></button>
                                            <button class="arsol-pfw-delete-comment-btn" data-comment-id="<?php echo $comment->comment_ID; ?>"><?php _e('Delete', 'arsol-pfw'); ?></button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    echo '</div>';
                                }
                            ), $comments);
                            ?>
                        </ol>
                        <?php
                    }
                    ?>
                </div>
                
                <?php
                // Show comment form if comments are open
                if (comments_open($project_id)) {
                    ?>
                    <div class="comment-form-container">
                        <?php
                        $comment_form_args = array(
                            'title_reply' => __('Add a Comment', 'arsol-pfw'),
                            'title_reply_before' => '<h4 id="reply-title" class="comment-reply-title">',
                            'title_reply_after' => '</h4>',
                            'title_reply_to' => __('Reply to %s', 'arsol-pfw'),
                            'comment_field' => '<div class="arsol-comment-form"><textarea id="comment" name="comment" cols="45" rows="6" maxlength="65525" required="required" placeholder="' . esc_attr__('Write your comment...', 'arsol-pfw') . '"></textarea></div>',
                            'logged_in_as' => '',
                            'comment_notes_before' => '<p class="comment-notes">' . __('Logged in users can edit and delete their own comments.', 'arsol-pfw') . '</p>',
                            'comment_notes_after' => '',
                            'id_form' => 'commentform',
                            'id_submit' => 'submit',
                            'class_form' => 'comment-form',
                            'class_submit' => 'button submit',
                            'label_submit' => __('Post Comment', 'arsol-pfw'),
                            'submit_button' => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
                            'submit_field' => '<p class="form-submit">%1$s %2$s</p>',
                            'cancel_reply_link' => __('Cancel Reply', 'arsol-pfw'),
                            'format' => 'html5'
                        );
                        
                        /**
                         * Hook: arsol_pfw_comment_form_args
                         * 
                         * @param array $comment_form_args Comment form arguments
                         * @param int $project_id The project ID
                         * @param string $phase_type The phase type
                         */
                        $comment_form_args = apply_filters('arsol_pfw_comment_form_args', $comment_form_args, $project_id, $phase_type);
                        
                        comment_form($comment_form_args, $project_id);
                        ?>
                    </div>
                    <?php
                }
                ?>
                
                <?php
                /**
                 * Hook: arsol_pfw_comments_wrapper_end
                 * 
                 * @param int $project_id The project ID
                 * @param string $phase_type The phase type
                 */
                do_action('arsol_pfw_comments_wrapper_end', $project_id, $phase_type);
                ?>
            </div>
            <?php
        } else {
            ?>
            <div class="no-comments">
                <p class="no-comments-message">
                    <?php printf(__('Comments are not available for this %s.', 'arsol-pfw'), $phase_type); ?>
                </p>
            </div>
            <?php
        }
        ?>
    </div>
    
    <?php
    /**
     * Hook: arsol_pfw_comments_after
     * 
     * @param int $project_id The project ID
     * @param string $phase_type The phase type
     * @param WP_Post $post The post object
     */
    do_action('arsol_pfw_comments_after', $project_id, $phase_type, $post);
    ?>
</div>
