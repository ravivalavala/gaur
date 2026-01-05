jQuery(document).ready(function ($) {
    'use strict';

    // Handle Swatch Selection
    $('.swatch-item').on('click', function (e) {
        e.preventDefault();
        const $this = $(this);
        const container = $this.closest('.swatch-container');
        const attribute = container.data('attribute');

        // Toggle Bootstrap active classes
        container.find('.swatch-item').removeClass('active btn-primary').addClass('btn-outline-secondary');
        $this.addClass('active btn-primary').removeClass('btn-outline-secondary');

        // Update hidden input for WooCommerce cart
        $('input[name="gaur_attr_' + attribute + '"]').val($this.attr('title'));
    });
});