# Импорт контента и редиректы: быстрый запуск

## 1) Импорт CPT через WP All Import
- Типы контента:
  - `gallery_item` -> файл `docs/import-gallery-item.csv`
  - `team_member` -> файл `docs/import-team-member.csv`
  - `review_item` -> файл `docs/import-review-item.csv`
- Заполненные реальные шаблоны (из текущих HTML):
  - `docs/import-gallery-item-real.csv`
  - `docs/import-team-member-real.csv`
  - `docs/import-review-item-real.csv`
  - `docs/import-news-posts-real.csv`
- На шаге маппинга:
  - `post_title` -> Заголовок
  - `post_content` -> Контент
  - `post_status` -> Статус (publish)
  - `slug` -> ЧПУ
  - `featured_image_url` -> Изображение записи
  - `gallery_category` -> таксономия `gallery_category` (для `gallery_item`)

## 2) Проверка после импорта
- Открыть архивы:
  - `/gallery/`
  - `/komanda/`
  - `/reviews/`
- Проверить, что карточки открываются и содержат фото/текст.

## 3) Настройка редиректов
- Полная карта: `docs/redirects-map.csv`
- Для Apache: `ops/redirects-apache.conf`
- Для Nginx: `ops/redirects-nginx.conf`
- Финальные прод-конфиги под `vitrage-pro.ru`:
  - Apache (`.htaccess`): `ops/prod-vitrage-pro-ru.htaccess`
  - Nginx server block: `ops/prod-vitrage-pro-ru.nginx.conf`

## 4) Валидация SEO после релиза
- Проверить 10-15 старых URL и убедиться, что отдают `301`.
- Проверить `sitemap.xml` и индексацию в поисковых системах.
