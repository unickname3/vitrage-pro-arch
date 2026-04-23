# Инструкция по разворачиванию сайта на хостинге

## 1. Что нужно заранее
- Домен: `vitrage-pro.ru` (и доступ к DNS).
- Хостинг с поддержкой:
  - PHP 8.1+,
  - MySQL 5.7+ / MariaDB 10.4+,
  - HTTPS (Let's Encrypt),
  - доступ к файловому менеджеру или SFTP/SSH.
- Доступы:
  - панель хостинга,
  - база данных,
  - почта для заявок с формы.

## 2. Подготовка хостинга
1. Создай сайт/виртуальный хост для `vitrage-pro.ru`.
2. Включи SSL-сертификат.
3. Создай базу данных и пользователя БД.
4. Запиши параметры:
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASSWORD`
   - `DB_HOST`

## 3. Установка WordPress
1. Скачай свежий WordPress и загрузи в корень сайта (`public_html` или аналогичный каталог).
2. Запусти установку через браузер.
3. Укажи параметры БД из шага 2.
4. Создай администратора WordPress.
5. После входа в админку:
   - `Settings -> Permalinks` -> выбери `Post name`.
   - `Settings -> General` -> проверь корректные URL сайта.

## 4. Установка темы проекта
1. Скопируй тему из репозитория:
   - локальный путь: `wordpress/wp-content/themes/vitrage-pro`
   - на сервер: `wp-content/themes/vitrage-pro`
2. Скопируй ассеты старого сайта в тему:
   - из корня репозитория: `assets/`
   - в тему: `wp-content/themes/vitrage-pro/assets/`
3. В админке активируй тему `Vitrage Pro`:
   - `Appearance -> Themes`.

## 5. Базовые плагины
Установи и активируй:
- Contact Form 7 (или Fluent Forms),
- SEO-плагин (Yoast/Rank Math),
- кеш-плагин (по рекомендации хостинга),
- WebP/оптимизация изображений.

## 6. Настройка контента в админке
1. Создай меню:
   - `Appearance -> Menus`,
   - назначь `Primary menu` и `Footer menu`.
2. Заполни контакты:
   - `Settings -> General` (поля, добавленные темой).
3. Импортируй контент (через WP All Import или вручную):
   - `docs/import-gallery-item-real.csv`
   - `docs/import-team-member-real.csv`
   - `docs/import-review-item-real.csv`
   - `docs/import-news-posts-real.csv`
4. Проверь страницы:
   - `/gallery/`
   - `/komanda/`
   - `/reviews/`
   - `/contacts/`

## 7. Настройка редиректов со старых URL

### Вариант Apache (.htaccess)
- Возьми готовый файл: `ops/prod-vitrage-pro-ru.htaccess`.
- Скопируй правила в корневой `.htaccess` сайта (до стандартного блока WordPress).

### Вариант Nginx
- Возьми готовый конфиг: `ops/prod-vitrage-pro-ru.nginx.conf`.
- Добавь server-блок в конфигурацию Nginx и перезагрузи сервис.

## 8. Проверка перед запуском
- Убедись, что формы отправляют письма.
- Проверь 301-редиректы:
  - `bash ops/check-redirects-curl.sh "https://vitrage-pro.ru"`
- Проверь robots/sitemap:
  - `https://vitrage-pro.ru/robots.txt`
  - `https://vitrage-pro.ru/sitemap_index.xml` (или путь плагина SEO).
- Проверь мобильную версию и скорость ключевых страниц.

## 9. Запуск (go-live)
1. Сделай backup файлов и БД.
2. Направь DNS домена на прод-хостинг (если еще не направлен).
3. Проверь сайт после обновления DNS.
4. Пройди smoke-check:
   - главная,
   - галерея,
   - команда,
   - контакты + форма,
   - 5-10 старых `.html` URL (должны отдать 301).

## 10. Поддержка после запуска
- Еженедельно: проверка форм и бэкапов.
- Ежемесячно: обновление WordPress, темы и плагинов.
- После каждого обновления: быстрый regression-check страниц и формы.
