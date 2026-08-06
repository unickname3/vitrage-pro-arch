<?php
/**
 * Форма поиска.
 *
 * @package VitragePro
 */

declare(strict_types=1);
?>
<form role="search" method="get" class="search-form vp-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="form-group form-btn-inside">
        <input type="search" class="form-control no-bg" placeholder="Поиск по сайту…"
               value="<?php echo esc_attr(get_search_query()); ?>" name="s" />
        <button type="submit" aria-label="Найти"><i class="fa fa-search"></i></button>
    </div>
</form>
