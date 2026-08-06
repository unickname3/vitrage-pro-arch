/**
 * Vitrage Pro admin scripts.
 * Медиа-библиотека WordPress в мета-боксах и на странице «Настройки сайта».
 */
(function ($) {
    'use strict';

    /**
     * Инициализация мульти-галереи: контейнер + скрытое поле.
     *
     * @param {jQuery} $container Контейнер списка (.vp-media-list).
     */
    function initGallery($container) {
        if (!$container.length || $container.data('vp-init')) {
            return;
        }
        $container.data('vp-init', true);

        var $input = $container.siblings('input.vp-gallery-input');
        var $button = $container.parent().find('.vp-add-images');
        var frame = null;

        function render() {
            var ids = ($input.val() || '').split(',').filter(Boolean);
            if (!ids.length) {
                $container.empty();
                return;
            }

            var posts = [];
            ids.forEach(function (id) {
                posts.push(new wp.media.post('get-attachment', { id: id }));
            });

            Promise.all(posts).then(function (items) {
                var html = '';
                items.forEach(function (item, i) {
                    var thumb = item.sizes && item.sizes.thumbnail
                        ? item.sizes.thumbnail.url
                        : item.url;
                    html += '<div class="vp-media-item" data-id="' + item.id + '">'
                        + '<img src="' + thumb + '" alt="">'
                        + '<button type="button" class="vp-media-remove" title="Удалить">&times;</button>'
                        + '</div>';
                });
                $container.html(html);
            });
        }

        $button.on('click', function (e) {
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Выберите фотографии',
                multiple: true,
                library: { type: 'image' },
                button: { text: 'Добавить' },
            });

            frame.on('select', function () {
                var selection = frame.state().get('selection').toJSON();
                var existing = ($input.val() || '').split(',').filter(Boolean);
                selection.forEach(function (att) {
                    if (existing.indexOf(String(att.id)) === -1) {
                        existing.push(String(att.id));
                    }
                });
                $input.val(existing.join(',')).trigger('change');
                render();
            });

            frame.open();
        });

        $container.on('click', '.vp-media-remove', function () {
            var id = $(this).closest('.vp-media-item').data('id');
            var existing = ($input.val() || '').split(',').filter(Boolean).filter(function (v) {
                return String(v) !== String(id);
            });
            $input.val(existing.join(',')).trigger('change');
            render();
        });

        render();
    }

    /**
     * Инициализация одиночного выбора изображения.
     *
     * @param {jQuery} $wrap Обёртка (.vp-single-media).
     */
    function initSingle($wrap) {
        if (!$wrap.length || $wrap.data('vp-init')) {
            return;
        }
        $wrap.data('vp-init', true);

        var $input = $wrap.find('input.vp-single-input');
        var $preview = $wrap.find('.vp-single-media-preview');
        var $button = $wrap.find('.vp-set-image');
        var $remove = $wrap.find('.vp-remove-image');
        var frame = null;

        function render() {
            var url = $input.val();
            if (!url) {
                $preview.hide();
                return;
            }
            $preview.find('img').attr('src', url);
            $preview.show();
        }

        $button.on('click', function (e) {
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Выберите изображение',
                multiple: false,
                library: { type: 'image' },
                button: { text: 'Выбрать' },
            });

            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                $input.val(att.url).trigger('change');
                render();
            });

            frame.open();
        });

        $remove.on('click', function (e) {
            e.preventDefault();
            $input.val('').trigger('change');
            render();
        });

        render();
    }

    $(function () {
        // Мета-боксы и страницы настроек.
        $(document).on('change', '.vp-gallery-input', function () {
            initGallery($(this).siblings('.vp-media-list'));
        });

        $('.vp-gallery-list').each(function () {
            initGallery($(this));
        });

        $('.vp-single-media').each(function () {
            initSingle($(this));
        });

        // Повторяющиеся поля (слайды на главной).
        $(document).on('click', '.vp-add-row', function (e) {
            e.preventDefault();
            var $wrap = $(this).closest('.vp-repeater-wrap');
            var $rows = $wrap.find('.vp-repeater-rows');
            var index = $rows.find('.vp-repeater-row').length;
            var template = wp.template('vp-repeater-row');

            var html = template({ index: index }).split('__INDEX__').join(String(index));
            var $row = $(html);
            $rows.append($row);
            // Переинициализировать медиа-выбор в новой строке.
            $row.find('.vp-single-media').each(function () {
                $(this).data('vp-init', false);
                initSingle($(this));
            });
        });

        $(document).on('click', '.vp-remove-row', function (e) {
            e.preventDefault();
            $(this).closest('.vp-repeater-row').remove();
        });
    });
})(jQuery);
