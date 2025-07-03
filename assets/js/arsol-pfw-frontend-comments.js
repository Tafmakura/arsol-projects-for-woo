/*
 * Simple AJAX Comments for Arsol Projects for WooCommerce
 * Based on https://rudrastyh.com/wordpress/ajax-comments.html
 */

// Function to update comment count dynamically - Global function
function updateCommentCount(action, count) {
    var commentsTitle = jQuery('.comments-title');
    
    // Get current count from title
    var currentText = commentsTitle.text();
    var currentMatch = currentText.match(/(\d+)/);
    var currentCount = currentMatch ? parseInt(currentMatch[1]) : 0;
    
    var newCount;
    if (action === 'add') {
        newCount = currentCount + 1;
    } else if (action === 'delete') {
        newCount = Math.max(0, currentCount - 1);
    } else if (action === 'set') {
        newCount = count;
    }
    
    // Update the count text
    if (newCount === 0) {
        commentsTitle.text('No Comments');
        jQuery('.comments-list').hide();
        if (jQuery('.no-comments').length === 0) {
            jQuery('.comments-container').prepend('<div class="no-comments"><p>No comments yet. Be the first to comment!</p></div>');
        }
    } else if (newCount === 1) {
        commentsTitle.text('One Comment');
        jQuery('.comments-list').show();
        jQuery('.no-comments').remove();
    } else {
        commentsTitle.text(newCount + ' Comments');
        jQuery('.comments-list').show();
        jQuery('.no-comments').remove();
    }
    
    // If this is the first comment, show the title
    if (currentCount === 0 && newCount === 1) {
        if (commentsTitle.length === 0) {
            jQuery('.commentlist').before('<h4 class="comments-title">One Comment</h4>');
        }
    }
}

// Validation functions
jQuery.extend(jQuery.fn, {
    /*
     * check if field value length more than 3 symbols (for name and comment)
     */
    validate: function () {
        if (jQuery(this).val().length < 3) {
            jQuery(this).addClass('error');
            return false;
        } else {
            jQuery(this).removeClass('error');
            return true;
        }
    },
    /*
     * check if email is correct
     */
    validateEmail: function () {
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/,
            emailToValidate = jQuery(this).val();
        if (!emailReg.test(emailToValidate) || emailToValidate == "") {
            jQuery(this).addClass('error');
            return false;
        } else {
            jQuery(this).removeClass('error');
            return true;
        }
    },
});

// SINGLE DOCUMENT READY HANDLER - All functionality consolidated
jQuery(function($) {
    
    // ===== COMMENT FORM SUBMISSION =====
    $('#commentform').submit(function() {
        
        // define some vars
        var button = $('#submit'), // submit button
            respond = $('#respond'), // comment form container
            commentlist = $('#comments.commentlist'), // comment list container (existing structure)
            cancelreplylink = $('#cancel-comment-reply-link');
            
        // if user is logged in, do not validate author and email fields
        if ($('#author').length)
            $('#author').validate();
        
        if ($('#email').length)
            $('#email').validateEmail();
            
        // validate comment in any case
        $('#comment').validate();
        
        // if comment form isn't in process, submit it
        if (!button.hasClass('loadingform') && !$('#author').hasClass('error') && !$('#email').hasClass('error') && !$('#comment').hasClass('error')) {
            
            // ajax request
            $.ajax({
                type: 'POST',
                url: arsolComments.ajaxurl, // admin-ajax.php URL
                data: $(this).serialize() + '&action=arsol_ajax_comments', // send form data + action parameter
                beforeSend: function(xhr) {
                    // what to do just after the form has been submitted
                    button.addClass('loadingform').val('Loading...');
                },
                error: function (request, status, error) {
                    if (status == 500) {
                        alert('Error while adding comment');
                    } else if (status == 'timeout') {
                        alert('Error: Server doesn\'t respond.');
                    } else {
                        // process WordPress errors
                        var wpErrorHtml = request.responseText.split("<p>"),
                            wpErrorStr = wpErrorHtml[1].split("</p>");
                            
                        alert(wpErrorStr[0]);
                    }
                },
                success: function (addedCommentHTML) {
                
                    // if this post already has comments
                    if (commentlist.length > 0) {
                    
                        // if in reply to another comment
                        if (respond.parent().hasClass('comment')) {
                        
                            // if the other replies exist
                            if (respond.parent().children('.children').length) {    
                                respond.parent().children('.children').append(addedCommentHTML);
                            } else {
                                // if no replies, add <ol class="children"> list
                                addedCommentHTML = '<ol class="children">' + addedCommentHTML + '</ol>';
                                respond.parent().append(addedCommentHTML);
                            }
                            
                            // remove the reply form
                            cancelreplylink.trigger("click");
                            
                        } else {
                            // simple comment
                            commentlist.append(addedCommentHTML);
                        }
                        
                    } else {
                        // if no comments yet, create the comment list with correct structure
                        addedCommentHTML = '<ol id="comments" class="commentlist">' + addedCommentHTML + '</ol>';
                        respond.before($(addedCommentHTML));
                    }
                    
                    // clear form fields
                    $('#comment').val('');
                    
                    // Update comment count
                    updateCommentCount('add');
                    
                },
                complete: function() {
                    // what to do after a comment has been added
                    button.removeClass('loadingform').val('Post Comment');
                }
            });
        }
        return false;
    });
    
    // ===== COMMENT EDITING AND DELETION =====
    
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
        
        // Create edit form
        var editForm = $('<div class="arsol-edit-form">' +
            '<textarea class="arsol-edit-textarea">' + originalText + '</textarea>' +
            '<div class="arsol-edit-buttons">' +
                '<button type="button" class="arsol-save-edit">Save</button> ' +
                '<button type="button" class="arsol-cancel-edit">Cancel</button>' +
            '</div>' +
        '</div>');
        
        // Hide original content and show edit form
        commentContent.hide();
        commentContent.after(editForm);
        
        // Focus on textarea
        
        // Hide reply link and edit/delete actions while editing
        commentBody.find(".reply").hide();
        commentBody.find(".arsol-comment-actions").hide();
        editForm.find('.arsol-edit-textarea').focus();
    });
    
    // Handle save edit
    $(document).on('click', '.arsol-save-edit', function(e) {
        e.preventDefault();
        
        var editForm = $(this).closest('.arsol-edit-form');
        var commentBody = editForm.closest('.comment-body');
        var commentContent = commentBody.find('.comment-content');
        var commentId = commentBody.find('.arsol-edit-comment').data('comment-id');
        var newContent = editForm.find('.arsol-edit-textarea').val().trim();
        
        // Basic validation
        if (newContent === '') {
            alert('Comment cannot be empty');
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true).text('Saving...');
        
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
            success: function(response) {
                if (response === 'success') {
                    // Update comment content
                    commentContent.text(newContent);
                    
                    // Remove edit form and show original content
                    
                    // Show reply link and edit/delete actions again
                    commentBody.find(".reply").show();
                    commentBody.find(".arsol-comment-actions").show();
                    editForm.remove();
                    commentContent.show();
                } else {
                    alert('Error saving comment');
                    $(this).prop('disabled', false).text('Save');
                }
            },
            error: function(xhr, status, error) {
                alert('Error saving comment: ' + (xhr.responseText || 'Unknown error'));
                $(this).prop('disabled', false).text('Save');
            }
        });
    });
    
    // Handle cancel edit
    $(document).on('click', '.arsol-cancel-edit', function(e) {
        e.preventDefault();
        
        var editForm = $(this).closest('.arsol-edit-form');
        var commentBody = editForm.closest('.comment-body');
        var commentContent = commentBody.find('.comment-content');
        
        // Remove edit form and show original content
        
        // Show reply link and edit/delete actions again
        commentBody.find(".reply").show();
        commentBody.find(".arsol-comment-actions").show();
        editForm.remove();
        commentContent.show();
    });
    
    // Handle delete comment
    $(document).on('click', '.arsol-delete-comment', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }
        
        var deleteButton = $(this);
        var originalText = deleteButton.text();
        var commentId = $(this).data('comment-id');
        var commentLi = deleteButton.closest('li.comment');
        
        // Show loading state
        deleteButton.prop('disabled', true).text('Deleting...');
        
        // AJAX request to delete comment
        $.ajax({
            type: 'POST',
            url: arsolComments.ajaxurl,
            data: {
                action: 'arsol_delete_comment',
                comment_id: commentId,
                nonce: arsolComments.nonce
            },
            success: function(response) {
                if (response === 'success') {
                    // Remove the comment from DOM with fade effect
                    commentLi.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Update comment count after removal
                        updateCommentCount("delete");
                    });
                } else {
                    alert('Error deleting comment');
                    // Reset button state on error
                    deleteButton.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                alert('Error deleting comment: ' + (xhr.responseText || 'Unknown error'));
                // Reset button state on error
                deleteButton.prop('disabled', false).text(originalText);
            }
        });
    });

    
    // Handle reply link click
    $(document).on('click', '.comment-reply-link', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent event bubbling
        
        // Store reference to the clicked link
        var clickedLink = $(this);
        var belowElement = clickedLink.data('belowelement');
        
        // Check if we have the correct data attribute
        if (!belowElement) {
            console.log('No belowelement data found on reply link');
            return;
        }
        
        var commentId = belowElement.replace('comment-', '');
        var postId = $('#commentform input[name="comment_post_ID"]').val();
        var commentElement = $('#comment-' + commentId);
        
        // Additional check: make sure we found the comment element
        if (commentElement.length === 0) {
            console.log('Comment element not found for ID:', commentId);
            return;
        }
        
        // Get the direct comment body (first level only, not nested)
        var directCommentBody = commentElement.children('.comment-body').first();
        
        // Check if reply form is already showing for this specific comment (direct child only)
        if (directCommentBody.children('.arsol-reply-form-container').length > 0) {
            console.log('Reply form already open for comment:', commentId);
            return;
        }
        
        // Check if ANY reply form is currently open (additional safety)
        if ($('.arsol-reply-form-container').length > 0) {
            console.log('Another reply form is open, closing it first');
            $('.arsol-reply-form-container').remove();
            $('.comment-reply-link').text('Reply').show(); // Show all reply links when closing other forms
        }
        
        // Show loading state for this specific link
        clickedLink.text('Loading...');
        
        // AJAX request to get reply form
        $.ajax({
            type: 'POST',
            url: arsolComments.ajaxurl,
            data: {
                action: 'arsol_reply_comment',
                comment_id: commentId,
                post_id: postId,
                nonce: arsolComments.nonce
            },
            success: function(response) {
                // Double-check that no form exists before adding (direct child only)
                if (directCommentBody.children('.arsol-reply-form-container').length > 0) {
                    console.log('Form already exists, not adding another');
                    clickedLink.text('Reply');
                    return;
                }
                
                // Add reply form to the specific comment's body only (direct child)
                directCommentBody.append(response);
                
                // Focus on the reply textarea (be specific to avoid nested ones)
                directCommentBody.find('.arsol-reply-form textarea').first().focus();
                
                // Hide the reply link for this specific comment
                clickedLink.hide();
            },
            error: function(xhr, status, error) {
                console.log('Error loading reply form:', xhr.responseText || 'Unknown error');
                alert('Error loading reply form: ' + (xhr.responseText || 'Unknown error'));
                // Reset only this specific reply link text
                clickedLink.text('Reply');
            }
        });
    });
    
    // Handle reply form submission
    $(document).on('submit', '.arsol-reply-form', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var commentId = form.data('comment-id');
        var postId = form.data('post-id');
        var formData = form.serialize();
        
        // Basic validation
        var content = form.find('textarea[name="comment"]').val().trim();
        if (content === '') {
            alert('Please enter your reply');
            return;
        }
        
        // Show loading state
        form.find('.arsol-reply-submit').prop('disabled', true).text('Posting...');
        
        // AJAX request to submit reply
        $.ajax({
            type: 'POST',
            url: arsolComments.ajaxurl,
            data: formData + '&action=arsol_ajax_comments',
            success: function(response) {
                // Find the parent comment element
                var parentComment = $('#comment-' + commentId);
                
                // Check if there's already a children list
                var childrenList = parentComment.find('> .children');
                if (childrenList.length === 0) {
                    // Create new children list
                    childrenList = $('<ul class="children"></ul>');
                    parentComment.append(childrenList);
                }
                
                // Add the new reply
                childrenList.append(response);
                
                // Remove the reply form
                form.closest('.arsol-reply-form-container').remove();
                
                // Show the reply link again for this comment
                parentComment.find('.comment-reply-link').first().show();
                
                // Scroll to new comment
                var newComment = childrenList.find('li:last-child');
                $('html, body').animate({
                    scrollTop: newComment.offset().top - 100
                }, 500);
                
                // Update comment count
                updateCommentCount('add');
            },
            error: function(xhr, status, error) {
                alert('Error posting reply: ' + (xhr.responseText || 'Unknown error'));
                form.find('.arsol-reply-submit').prop('disabled', false).text('Post Reply');
            }
        });
    });
    
    // Handle reply form cancel
    $(document).on('click', '.arsol-reply-cancel', function(e) {
        e.preventDefault();
        
        var replyForm = $(this).closest('.arsol-reply-form-container');
        var commentElement = replyForm.closest('.comment');
        
        // Remove reply form
        replyForm.remove();
        
        // Show the reply link again for this comment
        commentElement.find('.comment-reply-link').first().show();
    });
    
}); 
