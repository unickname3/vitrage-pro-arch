(function ($) {
    'use strict';

    $(function () {
        // Smooth scroll for internal anchors improves long-page navigation.
        $('a[href*="#"]').on('click', function (event) {
            var target = $(this.hash);
            if (target.length === 0) {
                return;
            }

            event.preventDefault();
            $('html, body').animate({ scrollTop: target.offset().top - 20 }, 350);
        });
    });
})(jQuery);
