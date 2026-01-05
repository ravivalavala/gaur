<?php
/**
 * Debug script
 */

add_action('admin_footer', function() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        console.log('jQuery loaded:', typeof $ !== 'undefined');
        console.log('clothingShoesAjax:', window.clothingShoesAjax);
        
        // Test click manually
        $('#css-run-setup').on('click', function() {
            console.log('Button clicked!');
            
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'css_create_all',
                    nonce: '<?php echo wp_create_nonce("clothing_shoes_nonce"); ?>'
                },
                success: function(response) {
                    console.log('Success:', response);
                },
                error: function(xhr, status, error) {
                    console.log('Error:', error);
                    console.log('Response:', xhr.responseText);
                }
            });
        });
    });
    </script>
    <?php
});