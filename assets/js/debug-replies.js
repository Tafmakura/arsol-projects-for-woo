// Debug script for reply issues
jQuery(document).ready(function($) {
    console.log('Debug script loaded');
    
    // Check how many reply links exist
    console.log('Reply links found:', $('.comment-reply-link').length);
    
    // Check each reply link's data
    $('.comment-reply-link').each(function(index) {
        console.log('Reply link ' + index + ':', {
            'belowelement': $(this).data('belowelement'),
            'text': $(this).text(),
            'href': $(this).attr('href')
        });
    });
    
    // Monitor clicks on reply links
    $(document).on('click', '.comment-reply-link', function(e) {
        console.log('Reply link clicked:', {
            'belowelement': $(this).data('belowelement'),
            'existing_forms': $('.arsol-reply-form-container').length,
            'this_comment_forms': $('#comment-' + $(this).data('belowelement').replace('comment-', '')).find('.arsol-reply-form-container').length
        });
    });
    
    // Monitor when forms are added to DOM
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                $(mutation.addedNodes).each(function() {
                    if ($(this).hasClass && $(this).hasClass('arsol-reply-form-container')) {
                        console.log('Reply form added to DOM');
                    }
                });
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}); 