(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Test AJAX connection
        $('#css-test-ajax').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $result = $('#css-test-result');
            
            $button.prop('disabled', true).text('Testing...');
            $result.html('<p>Testing AJAX connection...</p>');
            
            $.ajax({
                url: clothingShoesAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'css_create_all',
                    nonce: clothingShoesAjax.nonce,
                    test: true
                },
                success: function(response) {
                    $result.html('<p style="color: green;">✓ AJAX is working! Server responded successfully.</p>');
                    console.log('AJAX Test Success:', response);
                },
                error: function(xhr, status, error) {
                    $result.html('<p style="color: red;">✗ AJAX Error: ' + error + '</p>');
                    console.error('AJAX Test Error:', error, xhr.responseText);
                },
                complete: function() {
                    $button.prop('disabled', false).text('Test AJAX Connection');
                }
            });
        });
        
        // Run complete setup
        $('#css-run-setup').on('click', function(e) {
            e.preventDefault();
            
            console.log('Setup button clicked');
            
            var $button = $(this);
            var $status = $('#css-status');
            var $progress = $('.css-progress');
            var $progressFill = $('.css-progress-fill');
            var $progressText = $('.css-progress-text');
            
            // Disable button and show processing
            $button.prop('disabled', true).text('Setting up...');
            $status.removeClass('success error').addClass('processing')
                .html('<span class="spinner is-active" style="float: left; margin-right: 10px;"></span>' + 
                      clothingShoesAjax.creating)
                .show();
            
            $progress.show();
            
            // Update progress
            function updateProgress(percent, message) {
                $progressFill.css('width', percent + '%');
                $progressText.text(percent + '%' + (message ? ' - ' + message : ''));
                
                var statusHtml = '<span class="spinner is-active" style="float: left; margin-right: 10px;"></span>';
                statusHtml += message || clothingShoesAjax.creating;
                statusHtml += ' (' + percent + '%)';
                $status.html(statusHtml);
            }
            
            // Start with 10%
            updateProgress(10);
            
            // Make AJAX request
            $.ajax({
                url: clothingShoesAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'css_create_all',
                    nonce: clothingShoesAjax.nonce
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Setup Response:', response);
                    
                    if (response.success) {
                        updateProgress(100, 'Setup complete!');
                        
                        setTimeout(function() {
                            $status.removeClass('processing').addClass('success')
                                .html('<div style="display: flex; align-items: center;">' +
                                     '<span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 32px; margin-right: 10px;"></span>' +
                                     '<div><strong>' + response.data.message + '</strong>' +
                                     '<p>Your shop is now ready with sample products.</p>' +
                                     '<p><a href="' + woocommerce_admin.urls.products + '" class="button">View Products</a></p>' +
                                     '</div></div>');
                            
                            $button.prop('disabled', false).text('Run Setup Again');
                        }, 1000);
                        
                    } else {
                        $status.removeClass('processing').addClass('error')
                            .html('<span class="dashicons dashicons-warning" style="color: #dc3232; font-size: 32px; float: left; margin-right: 10px;"></span>' +
                                 '<div><strong>Error:</strong> ' + (response.data && response.data.message ? response.data.message : clothingShoesAjax.error) + 
                                 '<br><small>Check browser console for details.</small></div>');
                        
                        $button.prop('disabled', false).text('Try Again');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Setup AJAX Error:', error, xhr.responseText);
                    
                    $status.removeClass('processing').addClass('error')
                        .html('<span class="dashicons dashicons-warning" style="color: #dc3232; font-size: 32px; float: left; margin-right: 10px;"></span>' +
                             '<div><strong>AJAX Error:</strong> ' + error + 
                             '<br><small>Status: ' + status + '</small>' +
                             '<br><small>Check browser console for details.</small></div>');
                    
                    $button.prop('disabled', false).text('Try Again');
                }
            });
        });
        
        // Log that script is loaded
        console.log('Clothing & Shoes Setup admin script loaded');
        console.log('AJAX URL:', clothingShoesAjax.ajax_url);
        console.log('Nonce:', clothingShoesAjax.nonce);
        
    });
    
})(jQuery);