# Проверка 301-редиректов через curl

## Быстрый запуск
```bash
bash ops/check-redirects-curl.sh
```

## Проверка для конкретного домена
```bash
bash ops/check-redirects-curl.sh "https://vitrage-pro.ru"
```

## Что считать успешным
- Для legacy URL вида `*.html` статус должен быть `301`.
- В `Location` должен быть новый URL без `.html`.
- Должна соблюдаться каноникализация на `https://vitrage-pro.ru` (без `www`).

## Точечная проверка одного URL
```bash
curl -I "https://vitrage-pro.ru/gallery/okna.html"
```
