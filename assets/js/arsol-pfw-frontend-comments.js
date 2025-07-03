/**
 * Frontend Comments JavaScript
 *
 * Handles AJAX comment operations including editing, deletion, and submission.
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Comment functionality object
    const ArsolComments = {
        
        /**
         * Initialize comment functionality
         */
        init: function() {
            this.bindEvents();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Edit comment
            $(document).on('click', '.arsol-edit-comment', this.showEditForm);
            $(document).on('click', '.arsol-save-comment', this.saveComment);
            $(document).on('click', '.arsol-cancel-edit', this.cancelEdit);
            
            // Delete comment
            $(document).on('click', '.arsol-delete-comment', this.deleteComment);
            
            // AJAX form submission
            $(document).on('submit', '#commentform', this.submitComment);
            
            // Reply to comment
            $(document).on('click', '.comment-reply-link', this.handleReply);
        },
        
        /**
         * Show edit form for comment
         */
        showEditForm: function(e) {
            e.preventDefault();
            
            const commentId = $(this).data('comment-id');
            const commentBody = $('#comment-' + commentId + ' .comment-content');
            const originalContent = commentBody.html();
            
            // Store original content for cancel functionality
            commentBody.data('original-content', originalContent);
            
            // Create edit form
            const editForm = `
                <div class="arsol-comment-edit-form">
                    <textarea class="arsol-edit-textarea" rows="4">${originalContent.replace(/<[^>]*>/g, '')}</textarea>
                    <div class="arsol-edit-buttons">
                        <button class="button arsol-save-comment" data-comment-id="${commentId}">${arsolComments.strings.save}</button>
                        <button class="button arsol-cancel-edit" data-comment-id="${commentId}">${arsolComments.strings.cancel}</button>
                    </div>
                </div>
            `;
            
            commentBody.html(editForm);
            
            // Hide edit/delete actions
            $('#comment-' + commentId + ' .arsol-comment-actions').hide();
        },
        
        /**
         * Save edited comment
         */
        saveComment: function(e) {
            e.preventDefault();
            
            const commentId = $(this).data('comment-id');
            const newContent = $(this).closest('.arsol-comment-edit-form').find('.arsol-edit-textarea').val();
            
            if (!newContent.trim()) {
                alert(arsolComments.strings.error);
                return;
            }
            
            // Show loading state
            $(this).prop('disabled', true).text('Saving...');
            
            // AJAX request
            $.ajax({
                url: arsolComments.ajax_url,
                type: 'POST',
                data: {
                    action: 'arsol_edit_comment',
                    comment_id: commentId,
                    content: newContent,
                    nonce: arsolComments.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update comment content
                        const commentBody = $('#comment-' + commentId + ' .comment-content');
                        commentBody.html(response.data.content);
                        
                        // Show edit/delete actions
                        $('#comment-' + commentId + ' .arsol-comment-actions').show();
                        
                        // Show success message
                        ArsolComments.showMessage(response.data.message, 'success');
                    } else {
                        ArsolComments.showMessage(response.data || arsolComments.strings.error, 'error');
                        ArsolComments.restoreOriginalContent(commentId);
                    }
                },
                error: function() {
                    ArsolComments.showMessage(arsolComments.strings.error, 'error');
                    ArsolComments.restoreOriginalContent(commentId);
                }
            });
        },
        
        /**
         * Cancel edit operation
         */
        cancelEdit: function(e) {
            e.preventDefault();
            
            const commentId = $(this).data('comment-id');
            ArsolComments.restoreOriginalContent(commentId);
        },
        
        /**
         * Restore original comment content
         */
        restoreOriginalContent: function(commentId) {
            const commentBody = $('#comment-' + commentId + ' .comment-content');
            const originalContent = commentBody.data('original-content');
            
            commentBody.html(originalContent);
            $('#comment-' + commentId + ' .arsol-comment-actions').show();
        },
        
        /**
         * Delete comment
         */
        deleteComment: function(e) {
            e.preventDefault();
            
            if (!confirm(arsolComments.strings.confirm_delete)) {
                return;
            }
            
            const commentId = $(this).data('comment-id');
            
            // AJAX request
            $.ajax({
                url: arsolComments.ajax_url,
                type: 'POST',
                data: {
                    action: 'arsol_delete_comment',
                    comment_id: commentId,
                    nonce: arsolComments.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Remove comment from DOM
                        $('#comment-' + commentId).fadeOut(300, function() {
                            $(this).remove();
                        });
                        
                        // Show success message
                        ArsolComments.showMessage(response.data, 'success');
                    } else {
                        ArsolComments.showMessage(response.data || arsolComments.strings.error, 'error');
                    }
                },
                error: function() {
                    ArsolComments.showMessage(arsolComments.strings.error, 'error');
                }
            });
        },
        
        /**
         * Submit comment via AJAX
         */
        submitComment: function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitButton = form.find('input[type="submit"]');
            const comment = form.find('#comment').val();
            const postId = form.find('input[name="comment_post_ID"]').val();
            const commentParent = form.find('input[name="comment_parent"]').val() || 0;
            
            if (!comment.trim()) {
                ArsolComments.showMessage('Please enter a comment.', 'error');
                return;
            }
            
            // Show loading state
            submitButton.prop('disabled', true).val('Posting...');
            
            // AJAX request
            $.ajax({
                url: arsolComments.ajax_url,
                type: 'POST',
                data: {
                    action: 'arsol_submit_comment',
                    post_id: postId,
                    comment: comment,
                    comment_parent: commentParent,
                    nonce: arsolComments.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Add new comment to list
                        if (commentParent > 0) {
                            // Reply to existing comment
                            $('#comment-' + commentParent + ' .children').append(response.data.comment_html);
                        } else {
                            // New top-level comment
                            $('#comments .commentlist').append(response.data.comment_html);
                        }
                        
                        // Reset form
                        form[0].reset();
                        
                        // Show success message
                        ArsolComments.showMessage(response.data.message, 'success');
                        
                        // Scroll to new comment
                        $('html, body').animate({
                            scrollTop: $('#comment-' + response.data.comment_id).offset().top - 100
                        }, 300);
                    } else {
                        ArsolComments.showMessage(response.data || arsolComments.strings.error, 'error');
                    }
                    
                    // Reset submit button
                    submitButton.prop('disabled', false).val(arsolComments.strings.submit || 'Post Comment');
                },
                error: function() {
                    ArsolComments.showMessage(arsolComments.strings.error, 'error');
                    submitButton.prop('disabled', false).val(arsolComments.strings.submit || 'Post Comment');
                }
            });
        },
        
        /**
         * Handle reply to comment
         */
        handleReply: function(e) {
            e.preventDefault();
            
            const commentId = $(this).data('comment-id');
            const commentForm = $('#commentform');
            const parentField = commentForm.find('input[name="comment_parent"]');
            
            // Set parent comment ID
            parentField.val(commentId);
            
            // Move form below the comment
            $('#comment-' + commentId).after(commentForm);
            
            // Update form title
            const replyTitle = commentForm.find('.comment-reply-title');
            if (replyTitle.length) {
                replyTitle.text(arsolComments.strings.title_reply_to.replace('%s', $(this).closest('.comment').find('.comment-author').text()));
            }
            
            // Focus on comment textarea
            commentForm.find('#comment').focus();
        },
        
        /**
         * Show message to user
         */
        showMessage: function(message, type) {
            const messageClass = type === 'success' ? 'woocommerce-message' : 'woocommerce-error';
            const messageHtml = `<div class="${messageClass}">${message}</div>`;
            
            // Remove existing messages
            $('.woocommerce-message, .woocommerce-error').remove();
            
            // Add new message
            $('#comments').prepend(messageHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.' + messageClass).fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        ArsolComments.init();
    });
    
})(jQuery);
