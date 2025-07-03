jQuery(function($) {
    
    // Handle comment form submission
    $('.comment-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = form.serialize();
        
        // Add loading state
        form.addClass('loadingform');
        form.find('input[type="submit"]').prop('disabled', true);
        
        $.ajax({
            type: 'POST',
            url: arsolComments.ajaxurl,
            data: formData + '&action=arsol_ajax_comments',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Add the new comment to the list
                    var commentList = $('#comments .commentlist');
                    if (commentList.length === 0) {
                        $('#comments').append('<ul class="commentlist"></ul>');
                        commentList = $('#comments .commentlist');
                    }
                    
                    commentList.append(response.data.comment_html);
                    
                    // Clear the form
                    form[0].reset();
                    
                    // Show success message
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message || 'Error posting comment', 'error');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Error posting comment';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
                showNotice(errorMessage, 'error');
            },
            complete: function() {
                form.removeClass('loadingform');
                form.find('input[type="submit"]').prop('disabled', false);
            }
        });
    });
    
    // Handle edit comment click
    $(document).on('click', '.arsol-edit-comment', function(e) {
        e.preventDefault();
        
        var commentId = $(this).data('comment-id');
        var commentBody = $(this).closest('.comment-body');
        var commentContent = commentBody.find('.comment-content');
        var originalText = commentContent.text().trim();
        
        // Check if already editing
        if (commentBody.find('.arsol-edit-form').length > 0) {
            return;
        }
        
        // Create edit form with better structure
        var editForm = $('<div class="arsol-edit-form" role="form" aria-label="Edit comment">' +
            '<label for="arsol-edit-textarea-' + commentId + '" class="screen-reader-text">Edit comment content</label>' +
            '<textarea id="arsol-edit-textarea-' + commentId + '" class="arsol-edit-textarea" rows="4" required>' + originalText + '</textarea>' +
            '<div class="arsol-edit-buttons">' +
                '<button type="button" class="arsol-save-edit" aria-describedby="save-help-' + commentId + '">Save</button>' +
                '<button type="button" class="arsol-cancel-edit">Cancel</button>' +
                '<span id="save-help-' + commentId + '" class="screen-reader-text">Save changes to this comment</span>' +
            '</div>' +
        '</div>');
        
        // Hide original content and show edit form
        commentContent.hide();
        commentContent.after(editForm);
        
        // Focus on textarea for accessibility
        editForm.find('.arsol-edit-textarea').focus();
    });
    
    // Handle save edit
    $(document).on('click', '.arsol-save-edit', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var editForm = button.closest('.arsol-edit-form');
        var commentBody = editForm.closest('.comment-body');
        var commentContent = commentBody.find('.comment-content');
        var commentId = commentBody.find('.arsol-edit-comment').data('comment-id');
        var newContent = editForm.find('.arsol-edit-textarea').val().trim();
        
        // Validate content
        if (newContent === '') {
            showNotice('Comment content cannot be empty', 'error');
            editForm.find('.arsol-edit-textarea').focus();
            return;
        }
        
        // Show loading state
        button.prop('disabled', true).text('Saving...');
        editForm.addClass('loading');
        
        // AJAX request to save edit
        $.ajax({
            type: 'POST',
            url: arsolComments.ajaxurl,
            data: {
                action: 'arsol_edit_comment',
                comment_id: commentId,
                comment_content: newContent,
                nonce: arsolComments.nonce
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update the comment content
                    commentContent.html(response.data.comment_content).show();
                    editForm.remove();
                    
                    // Show success message
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message || 'Error updating comment', 'error');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Error updating comment';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
                showNotice(errorMessage, 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Save');
                editForm.removeClass('loading');
            }
        });
    });
    
    // Handle cancel edit
    $(document).on('click', '.arsol-cancel-edit', function(e) {
        e.preventDefault();
        
        var editForm = $(this).closest('.arsol-edit-form');
        var commentContent = editForm.siblings('.comment-content');
        
        // Show original content and remove edit form
        commentContent.show();
        editForm.remove();
    });
    
    // Handle delete comment
    $(document).on('click', '.arsol-delete-comment', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
            return;
        }
        
        var button = $(this);
        var commentId = button.data('comment-id');
        var commentLi = button.closest('li.comment');
        
        // Show loading state
        button.prop('disabled', true).text('Deleting...');
        
        // AJAX request to delete comment
        $.ajax({
            type: 'POST',
            url: arsolComments.ajaxurl,
            data: {
                action: 'arsol_delete_comment',
                comment_id: commentId,
                nonce: arsolComments.nonce
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Remove the comment from DOM with fade effect
                    commentLi.fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    // Show success message
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message || 'Error deleting comment', 'error');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = 'Error deleting comment';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                }
                showNotice(errorMessage, 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Delete');
            }
        });
    });
    
    // Handle keyboard navigation for edit form
    $(document).on('keydown', '.arsol-edit-textarea', function(e) {
        // Save with Ctrl+Enter
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            $(this).closest('.arsol-edit-form').find('.arsol-save-edit').click();
        }
        // Cancel with Escape
        else if (e.key === 'Escape') {
            e.preventDefault();
            $(this).closest('.arsol-edit-form').find('.arsol-cancel-edit').click();
        }
    });
    
    /**
     * Show notice message
     * 
     * @param {string} message
     * @param {string} type - 'success' or 'error'
     */
    function showNotice(message, type) {
        // Remove existing notices
        $('.arsol-comment-notice').remove();
        
        // Create new notice
        var notice = $('<div class="arsol-comment-notice arsol-notice-' + type + '" role="alert" aria-live="polite">' +
            '<p>' + message + '</p>' +
            '<button type="button" class="arsol-notice-dismiss" aria-label="Dismiss notice">&times;</button>' +
        '</div>');
        
        // Add to page
        $('#comments').prepend(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Handle notice dismissal
    $(document).on('click', '.arsol-notice-dismiss', function() {
        $(this).closest('.arsol-comment-notice').fadeOut(300, function() {
            $(this).remove();
        });
    });
    
});
