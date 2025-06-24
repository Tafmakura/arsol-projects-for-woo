/**
 * Arsol Projects Email Admin JavaScript
 */
function arsolSendTestEmail(emailType) {
    const resultDiv = document.getElementById('arsol-test-email-result');
    resultDiv.innerHTML = '<span style="color: #0073aa;">Sending test email...</span>';
    
    jQuery.ajax({
        url: arsol_email_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'arsol_send_test_email',
            email_type: emailType,
            nonce: arsol_email_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                resultDiv.innerHTML = '<span style="color: green; font-weight: bold;">✓ ' + response.data + '</span>';
            } else {
                resultDiv.innerHTML = '<span style="color: red; font-weight: bold;">✗ ' + response.data + '</span>';
            }
        },
        error: function() {
            resultDiv.innerHTML = '<span style="color: red; font-weight: bold;">✗ Error sending test email</span>';
        }
    });
}
