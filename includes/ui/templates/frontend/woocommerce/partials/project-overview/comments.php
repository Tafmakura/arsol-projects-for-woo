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
                        
                        <ul class="comment-list">
                            <?php
                            wp_list_comments(array(
                                'style' => 'ul',
                                'short_ping' => true,
                                'avatar_size' => 32,
                            ), $comments);
                            ?>
                        </ul>
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
                            'title_reply' => sprintf(__('Leave a Comment on this %s', 'arsol-pfw'), ucfirst($phase_type)),
                            'comment_field' => '<p class="comment-form-comment"><label for="comment">' . __('Your Comment', 'arsol-pfw') . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="4" maxlength="65525" required="required"></textarea></p>',
                            'logged_in_as' => '',
                            'comment_notes_before' => '',
                            'comment_notes_after' => '',
                            'id_form' => 'commentform-' . $project_id,
                            'id_submit' => 'submit-' . $project_id,
                            'class_submit' => 'button submit',
                            'submit_button' => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
                            'submit_field' => '<p class="form-submit">%1$s %2$s</p>',
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
