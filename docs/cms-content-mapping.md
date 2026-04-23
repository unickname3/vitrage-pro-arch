# CMS content mapping

## Типы контента

### gallery_item
- Источник: `gallery/*.html`
- Поля:
  - `post_title` — заголовок категории/работы.
  - `post_content` — текст описания.
  - `thumbnail` — главное изображение.
  - `gallery_category` — категория для фильтрации.

### team_member
- Источник: `komanda/spec-*.html`
- Поля:
  - `post_title` — ФИО.
  - `post_content` — био/описание.
  - `thumbnail` — фото сотрудника.

### review_item
- Источник: `reviews/review-*.html`
- Поля:
  - `post_title` — заголовок отзыва.
  - `post_content` — текст отзыва.
  - `thumbnail` (опц.) — фото автора.

### page (contacts)
- Источник: `contacts.html`
- Поля:
  - `vp_phone`, `vp_email`, `vp_address` (Settings -> General).
  - Контент страницы — вводная часть и карта.

## Рекомендация по импорту
1. Создать структуру страниц (`about`, `gallery`, `komanda`, `reviews`, `price`, `contacts`).
2. Заполнить CPT контентом вручную (до 50-100 записей) или через WP All Import.
3. Для изображений использовать только медиатеку WordPress (без внешних ссылок).
