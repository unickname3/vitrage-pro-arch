(function ($) {
    'use strict';

    // Шим jQuery.browser (удалён из jQuery 1.9+), нужен theme.js для YTPlayer.
    if (!$.browser) {
        $.browser = {
            mobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
        };
    }

    $(function () {
        // Снять обработчик mail.php из theme.js (устаревший), чтобы не конфликтовать.
        $('#contact-form').off('submit');

        // Отправка формы обратной связи через AJAX.
        $(document).on('submit', '#contact-form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $result = $form.find('.vp-form-result');
            var $button = $form.find('button[type="submit"]');

            // Базовая браузерная валидация обязательных полей.
            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }

            $button.prop('disabled', true);

            $.ajax({
                url: vpForm.ajaxUrl,
                type: 'POST',
                data: $form.serialize() + '&action=' + vpForm.action + '&nonce=' + vpForm.nonce,
                dataType: 'json'
            }).done(function (response) {
                if (response && response.success) {
                    $result
                        .removeClass('vp-form-error')
                        .addClass('vp-form-success')
                        .text(response.data.message)
                        .show();
                    $form.trigger('reset');
                } else {
                    $result
                        .removeClass('vp-form-success')
                        .addClass('vp-form-error')
                        .text(response.data.message)
                        .show();
                }
            }).fail(function () {
                $result
                    .removeClass('vp-form-success')
                    .addClass('vp-form-error')
                    .text('Ошибка соединения. Попробуйте ещё раз или позвоните нам.')
                    .show();
            }).always(function () {
                $button.prop('disabled', false);
            });
        });

        // Плавный скролл для якорей.
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
