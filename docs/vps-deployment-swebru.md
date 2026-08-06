# Разворачивание сайта на VPS SpaceWeb (sweb.ru) — подробная инструкция

Документ описывает запуск сайта «Витраж Про» (WordPress + тема `vitrage-pro`)
на виртуальном сервере (VPS/VDS) хостинг-провайдера **SpaceWeb (sweb.ru)**.

Инструкция рассчитана на технического специалиста: все команды можно
копировать и вставлять в терминал. После выполнения инструкции сайт будет
полностью работать, а контент — наполнен (202 работы, команда, отзывы, новости).

> **Коротко о VPS**: в отличие от обычного хостинга, VPS — это полноценный
> сервер с root-доступом. Всё ПО (веб-сервер, PHP, база данных) нужно установить
> и настроить. В SpaceWeb это можно упростить: при заказе выбрать готовый шаблон
> **LEMP** (Nginx + MySQL/MariaDB + PHP) — тогда стек уже установлен, останется
> только создать сайт и загрузить файлы.

---

## 0. Что нужно заранее

| Что | Где взять | Примечание |
|---|---|---|
| Домен `vitrage-pro.ru` | уже есть | можно перенести в SpaceWeb или оставить у текущего регистратора |
| Аккаунт в SpaceWeb | регистрация на sweb.ru | 3 дня тестового периода VPS бесплатно |
| Готовый архив темы | `downloads/vitrage-pro-theme.zip` (38 МБ) | лежит в репозитории проекта |
| Доступ к серверу по SSH | выдаётся в панели VPS после заказа | IP, логин root, пароль |
| Почта для заявок с формы | ящик владельца | `info@vitrage-pro.ru` и т.п. |

**Рекомендуемая конфигурация VPS** для сайта-визитки:

| Тариф SpaceWeb | CPU | RAM | Диск | Ориентировочная цена |
|---|---|---|---|---|
| «Облако Лайт» | 2 × 3,2 ГГц | 1 ГБ | 15 ГБ NVMe | ~0,77 ₽/час (~560 ₽/мес) |
| «Облако Базовый» (рекомендуется) | 2 × 3,2 ГГц | 2 ГБ | 30 ГБ NVMe | ~1,06 ₽/час (~770 ₽/мес) |

Для сайта-визитки с ~200 фотографиями достаточно «Облако Лайт», но с запасом
лучше взять «Облако Базовый» (2 ГБ RAM комфортно для WordPress).

---

## 1. Заказ VPS в SpaceWeb

1. Зайдите на [sweb.ru/vds/](https://sweb.ru/vds/).
2. Выберите тариф (рекомендуется «Облако Базовый» или «Облако Лайт»).
3. В блоке **«Операционная система»** выберите **Ubuntu 24.04 LTS**
   (или 22.04 LTS — они в списке шаблонов).
4. В блоке **«Программное обеспечение»** выберите **LEMP**
   (ставится: Nginx + MySQL/MariaDB + PHP-FPM). Это самый простой путь —
   веб-стек уже установлен.
   - Альтернатива: «WordPress» (сайт с WordPress «из коробки»), «LAMP»
     (Apache вместо Nginx) или «Без ПО» (полностью ручная установка — см. Приложение А).
   - Панель управления ispmanager/FASTPANEL/Hestia не обязательна: для этой
     инструкции достаточно SSH и консоли. Если хочется графическую панель —
     первый месяц ispmanager бесплатно.
5. Выберите **дата-центр**: для российской аудитории — СПб или Москва.
6. IP-адрес: обычный (бесплатная защита от DDoS включена по умолчанию).
7. Нажмите **«Заказать»** и оплатите. После оплаты в панели появится сервер.

> У SpaceWeb действует **3 дня бесплатного теста** VPS — можно проверить
> весь процесс до оплаты.

---

## 2. Доступ к серверу

### 2.1. Панель управления VPS

Панель SpaceWeb: **https://mcp.sweb.ru** (основная) и **https://vps.sweb.ru**
(раздел VPS). В панели доступны:
- **VNC-консоль** — «окно» сервера прямо в браузере (если SSH не работает);
- переустановка ОС/шаблона;
- бэкапы/снапшоты;
- DNS-редактор для доменов, зарегистрированных у SpaceWeb;
- информация для SSH: IP-адрес, логин (`root`), пароль (или SSH-ключ).

### 2.2. Подключение по SSH из Windows

**Способ 1 — PowerShell/CMD (встроенный OpenSSH, Windows 10+):**

```powershell
ssh root@<IP-адрес-сервера>
```

При первом подключении спросит подтверждение ключа (`yes`) и пароль root.

**Способ 2 — PuTTY** (если OpenSSH не установлен):
1. Скачайте PuTTY с https://www.putty.org.
2. В поле Host Name укажите `root@<IP-адрес-сервера>`, порт 22, нажмите Open.
3. Введите пароль root (при вводе символы не отображаются — это нормально).

**Способ 3 — WSL (Ubuntu в Windows):**
```bash
ssh root@<IP-адрес-сервера>
```

> **Подсказка**: для копирования файлов на сервер используйте **WinSCP**
> (https://winscp.net) — графический SFTP-клиент, работает как проводник.
> Логин `root`, пароль от SSH. Или команда `scp`:
> ```powershell
> scp vitrage-pro-theme.zip root@<IP-адрес>:/tmp/
> ```

---

## 3. Базовая настройка сервера

Подключитесь по SSH и выполните по очереди:

```bash
# 1. Обновить систему
sudo apt update && sudo apt upgrade -y

# 2. Установить полезные утилиты (если не установлены)
sudo apt install -y curl wget unzip zip htop

# 3. Проверить версии стека (если выбран шаблон LEMP)
nginx -v
php -v
mysql --version
```

Если вывод команд отсутствует (стек не установлен) — перейдите к
**Приложению А** (ручная установка LEMP), затем вернитесь сюда.

---

## 4. Создание базы данных

### 4.1. Подключение к MySQL/MariaDB

На Ubuntu в MariaDB root обычно подключается через системного пользователя
(без пароля). Попробуйте:

```bash
sudo mysql
```

Если команда завершилась ошибкой
`ERROR 1045 (28000): Access denied for user 'root'@'localhost'` —
значит, у root **уже задан пароль** (так бывает, если пароль задавали при
`mysql_secure_installation` или его настроил шаблон LEMP SpaceWeb).
В этом случае:

```bash
# 1. Подключиться с паролем (введите пароль root, если знаете его)
sudo mysql -u root -p

# 2. Не помните пароль? Проверьте, не записан ли он в файлах сервера:
sudo cat /root/.my.cnf 2>/dev/null          # часто тут лежит готовый пароль
sudo cat /etc/mysql/debian.cnf 2>/dev/null  # служебный аккаунт debian-sys-maint

# 3. Если пароль есть в debian.cnf — подключиться служебным аккаунтом:
sudo mysql -u debian-sys-maint -p           # пароль из файла /etc/mysql/debian.cnf
```

Если ни один вариант не сработал — выполните процедуру восстановления root
из раздела 4.3 ниже.

### 4.2. Создание базы данных и пользователя

В консоли MySQL выполните (замените `vp_password` на надёжный пароль):

```sql
CREATE DATABASE vitrage_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vp_user'@'localhost' IDENTIFIED BY 'vp_password';
GRANT ALL PRIVILEGES ON vitrage_pro.* TO 'vp_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> Если вместо MySQL стоит MariaDB и команда `mysql` не найдена, используйте
> эквивалент: `sudo mariadb`.

Запишите параметры (они понадобятся при установке WordPress):

```
DB_NAME = vitrage_pro
DB_USER = vp_user
DB_PASSWORD = vp_password
DB_HOST = localhost
```

### 4.3. Восстановление доступа root к MySQL (если пароль утерян)

Выполняйте по шагам, внимательно.

**Шаг 1. Остановите сервер БД.**

Для MariaDB:
```bash
sudo systemctl stop mariadb
```
Для MySQL:
```bash
sudo systemctl stop mysql
```

**Шаг 2. Запустите БД без проверки пароля.**

```bash
sudo mkdir -p /var/run/mysqld && sudo chown mysql:mysql /var/run/mysqld
sudo mysqld_safe --skip-grant-tables --skip-networking &
sleep 5
```

**Шаг 3. Подключитесь без пароля и сбросьте root на вход без пароля.**

```bash
sudo mysql -u root
```

В консоли MySQL выполните одну из команд — в зависимости от СУБД:

Для MariaDB:
```sql
FLUSH PRIVILEGES;
ALTER USER 'root'@'localhost' IDENTIFIED VIA unix_socket;
EXIT;
```

Для MySQL 8:
```sql
FLUSH PRIVILEGES;
ALTER USER 'root'@'localhost' IDENTIFIED WITH auth_socket;
EXIT;
```

> Альтернатива: вместо `unix_socket`/`auth_socket` можно сразу задать новый
> пароль: `ALTER USER 'root'@'localhost' IDENTIFIED BY 'новый-пароль';`
> (тогда в п. 4.1 используйте `sudo mysql -u root -p`).

**Шаг 4. Остановите временный процесс и запустите БД как обычно.**

```bash
sudo pkill mysqld_safe
sudo systemctl start mariadb    # или: sudo systemctl start mysql
```

**Шаг 5. Проверьте подключение.**

```bash
sudo mysql
```

Теперь должно работать без ошибок — вернитесь к разделу 4.2.

---

## 5. Установка WordPress

```bash
# 1. Папка сайта (как в конфиге Nginx — /var/www/vitrage-pro.ru/public)
sudo mkdir -p /var/www/vitrage-pro.ru/public
cd /var/www/vitrage-pro.ru/public

# 2. Скачать WordPress (последняя русская версия)
sudo wget https://ru.wordpress.org/latest-ru_RU.tar.gz
sudo tar -xzf latest-ru_RU.tar.gz --strip-components=1
sudo rm latest-ru_RU.tar.gz

# 3. Права доступа
sudo chown -R www-data:www-data /var/www/vitrage-pro.ru
sudo find /var/www/vitrage-pro.ru -type d -exec chmod 755 {} \;
sudo find /var/www/vitrage-pro.ru -type f -exec chmod 644 {} \;
```

---

## 6. Настройка Nginx

Настройка идёт в два этапа:

- **6.1 — предпросмотр по IP** (до перевода DNS и выпуска SSL): простой конфиг,
  чтобы сайт открывался по адресу `http://<IP-сервера>/`. На этом этапе
  устанавливается WordPress, активируется тема и запускается импорт контента.
- **6.2 — боевой конфиг** (после перевода DNS и выпуска SSL): домены,
  HTTPS и 301-редиректы со старых `.html`-адресов.

### 6.1. Предпросмотр по IP (временный конфиг)

Создайте конфиг сайта:

```bash
sudo nano /etc/nginx/sites-available/vitrage-pro.ru
```

Вставьте содержимое:

```nginx
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _ vitrage-pro.ru www.vitrage-pro.ru;

    root /var/www/vitrage-pro.ru/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;   # версия может отличаться — см. ниже
    }
}
```

> **Важно**: версия PHP в `fastcgi_pass` должна совпадать с установленной.
> Проверьте: `ls /run/php/` — если там `php8.3-fpm.sock`, замените `php8.2-fpm.sock`
> на `php8.3-fpm.sock`.

**Отключите дефолтную заглушку SpaceWeb** (иначе Nginx выдаст ошибку
«duplicate default server»). Найдите конфиг, который уже объявлен
default-сервером:

```bash
grep -rl "default_server" /etc/nginx/sites-enabled/
```

Скорее всего, выведет `/etc/nginx/sites-enabled/default` (или похожее имя —
это и есть заглушка «Hello World! …swtest.ru»). Отключите её (ссылка
удаляется, файл в `sites-available` остаётся):

```bash
sudo rm /etc/nginx/sites-enabled/default     # имя — из вывода grep
```

> Альтернатива: можно не отключать заглушку, а убрать `default_server` из
> конфига сайта (тогда по IP останется заглушка, а сайт будет открываться
> по домену через hosts-файл — см. шаг 10.3). Для предпросмотра по IP
> проще отключить заглушку.

Включите сайт и перезагрузите Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/vitrage-pro.ru /etc/nginx/sites-enabled/
sudo nginx -t          # проверка синтаксиса
sudo systemctl reload nginx
```

Теперь откройте `http://<IP-сервера>/` — вместо страницы-заглушки SpaceWeb
(«Hello World! …swtest.ru») откроется установка WordPress.

> **Почему по IP была заглушка**: страница «Hello World! This is the landing
> page of …swtest.ru» — это дефолтный сайт Nginx в шаблоне SpaceWeb. Он
> отвечает на любой запрос, для которого нет отдельного конфига. Пока конфиг
> сайта не включён или привязан только к домену, по IP видна заглушка — это
> нормально. Она также мешает включить наш конфиг с `default_server`
> (ошибка «duplicate default server»), поэтому выше мы её отключили.

Далее выполняйте шаги 7–9 (установка темы, активация, импорт контента) —
всё это работает по IP.

> **Про адреса WordPress**: если WordPress устанавливается по IP, адрес сайта
> в настройках будет `http://<IP>/`. Перед боевым запуском (шаг 10) поменяйте
> в админке «Настройки → Общие» оба поля — «Адрес WordPress (URL)» и
> «Адрес сайта (URL)» — на `https://vitrage-pro.ru`.

### 6.2. Боевой конфиг (после DNS и SSL)

Когда DNS домена переведён на сервер и выпущен SSL-сертификат (шаг 10),
замените содержимое конфига на боевое. Это готовый конфиг из проекта —
`ops/prod-vitrage-pro-ru.nginx.conf`, он включает 301-редиректы со старых
`.html`-адресов:

```bash
sudo nano /etc/nginx/sites-available/vitrage-pro.ru
```

```nginx
server {
    listen 80;
    server_name vitrage-pro.ru www.vitrage-pro.ru;
    return 301 https://vitrage-pro.ru$request_uri;
}

server {
    listen 443 ssl http2;
    server_name www.vitrage-pro.ru;
    return 301 https://vitrage-pro.ru$request_uri;
}

server {
    listen 443 ssl http2;
    server_name vitrage-pro.ru;

    root /var/www/vitrage-pro.ru/public;
    index index.php;

    # --- Редиректы со старых URL (301) ---
    rewrite ^/index\.html$ / permanent;
    rewrite ^/about\.html$ /about/ permanent;
    rewrite ^/about/ceny\.html$ /about/ceny/ permanent;
    rewrite ^/gallery\.html$ /gallery/ permanent;
    rewrite ^/gallery/fyuzing\.html$ /gallery/fyuzing/ permanent;
    rewrite ^/gallery/okna\.html$ /gallery/okna/ permanent;
    rewrite ^/gallery/podarki\.html$ /gallery/podarki/ permanent;
    rewrite ^/gallery/dveri\.html$ /gallery/dveri/ permanent;
    rewrite ^/gallery/mozaika\.html$ /gallery/mozaika/ permanent;
    rewrite ^/gallery/interery\.html$ /gallery/interery/ permanent;
    rewrite ^/gallery/peregorodki\.html$ /gallery/peregorodki/ permanent;
    rewrite ^/gallery/potolki\.html$ /gallery/potolki/ permanent;
    rewrite ^/gallery/svetilniki\.html$ /gallery/svetilniki/ permanent;
    rewrite ^/gallery/rospis\.html$ /gallery/rospis/ permanent;
    rewrite ^/komanda\.html$ /komanda/ permanent;
    rewrite ^/komanda/spec-1\.html$ /komanda/spec-1/ permanent;
    rewrite ^/komanda/spec-2\.html$ /komanda/spec-2/ permanent;
    rewrite ^/komanda/spec-3\.html$ /komanda/spec-3/ permanent;
    rewrite ^/komanda/spec-4\.html$ /komanda/spec-4/ permanent;
    rewrite ^/komanda/spec-5\.html$ /komanda/spec-5/ permanent;
    rewrite ^/reviews\.html$ /reviews/ permanent;
    rewrite ^/reviews/review-1\.html$ /reviews/review-1/ permanent;
    rewrite ^/reviews/review-2\.html$ /reviews/review-2/ permanent;
    rewrite ^/reviews/review-3\.html$ /reviews/review-3/ permanent;
    rewrite ^/news/news-1\.html$ /news/news-1/ permanent;
    rewrite ^/news/news-2\.html$ /news/news-2/ permanent;
    rewrite ^/news/news-3\.html$ /news/news-3/ permanent;
    rewrite ^/price\.html$ /price/ permanent;
    rewrite ^/contacts\.html$ /contacts/ permanent;
    # --- конец редиректов ---

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;   # версия может отличаться
    }
}
```

> **Важно**: версия PHP в `fastcgi_pass` должна совпадать с установленной
> (`ls /run/php/`). Боевой конфиг требует выпущенного SSL — до этого Nginx
> не запустится (нет сертификатов). Поэтому сначала 6.1, потом шаг 10 (SSL),
> и только затем 6.2.

Проверьте и перезагрузите:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## 7. Установка темы vitrage-pro

### 7.1. Скопировать архив темы на сервер

Через WinSCP или scp (см. п. 2.2) загрузите `vitrage-pro-theme.zip` в `/tmp`.

### 7.2. Распаковать в папку тем WordPress

```bash
sudo mkdir -p /var/www/vitrage-pro.ru/public/wp-content/themes/
cd /var/www/vitrage-pro.ru/public/wp-content/themes/
sudo unzip -o /tmp/vitrage-pro-theme.zip
sudo chown -R www-data:www-data /var/www/vitrage-pro.ru/public/wp-content/themes/vitrage-pro
```

Проверьте, что появилась папка:

```bash
ls /var/www/vitrage-pro.ru/public/wp-content/themes/vitrage-pro/
```

Должны быть папки `assets`, `content`, `inc` и файлы `style.css`, `functions.php`.

> **Если не работает unzip на сервере**: `sudo apt install -y unzip`

---

## 8. Установка WordPress через браузер

1. Откройте в браузере: `http://<IP-адрес-сервера>/` (пока без домена —
   предварительно можно временно прописать IP в hosts-файле, см. п. 10.3).
2. Выберите язык — **Русский**.
3. Укажите параметры базы данных из шага 4:
   - Имя базы: `vitrage_pro`
   - Пользователь: `vp_user`
   - Пароль: `vp_password`
   - Сервер БД: `localhost`
   - Префикс таблиц: `wp_`
4. Нажмите «Отправить» → «Выполнить установку».
5. Укажите название сайта («Витраж Про»), логин и пароль администратора,
   email. Установка завершена.
6. Войдите в админку: `http://<IP>/wp-admin/`.

> **Про адреса WordPress**: при установке по IP адрес сайта станет
> `http://<IP>/`. Это нормально для предпросмотра. Перед боевым запуском
> (шаг 10.5) адреса меняются на `https://vitrage-pro.ru`.

---

## 9. Активация темы и импорт контента

### 9.1. Активировать тему

В админке: **«Внешний вид → Темы»** → найдите тему **Vitrage Pro** →
**«Активировать»**.

При активации тема автоматически:
- создаст страницы: Мастерская, Витражи тиффани, Новости, Цены, Контакты,
  Политика конфиденциальности;
- создаст меню с категориями галереи;
- создаст 10 категорий галереи (Окна, Двери, Мозаика и т.д.);
- настроит постоянные ссылки и главную страницу.

### 9.2. Импортировать контент (один клик)

В админке: **«Настройки сайта → Импорт контента»** → **«Запустить импорт»**.

Импорт создаст:
- 202 работы с фотографиями (фото копируются в медиатеку WordPress);
- 5 сотрудников команды с фото и биографиями;
- 3 отзыва;
- 3 новости;
- страницу «Витражи тиффани» с текстом;
- заполнит настройки сайта (контакты, тексты главной, форму).

> Импорт длится 1–3 минуты. Если страница «зависла» — подождите: обработка
> 208 изображений идёт без видимого прогресса.

### 9.3. Проверить

Откройте сайт и пройдитесь по страницам: главная, `/gallery/`, `/gallery/okna/`,
`/komanda/`, `/reviews/`, `/news/`, `/price/`, `/contacts/`.

---

## 10. Домен и SSL

### 10.1. Если домен зарегистрирован в SpaceWeb

В панели управления (mcp.sweb.ru): **«Домены» → выберите vitrage-pro.ru →
DNS-записи** — добавьте:

| Тип | Имя | Значение |
|---|---|---|
| A | `@` | `<IP-адрес-сервера>` |
| A | `www` | `<IP-адрес-сервера>` |

### 10.2. Если домен у другого регистратора

На стороне текущего регистратора (там, где куплен домен) в DNS-зоне добавьте
те же A-записи: `@` и `www` → IP сервера. NS-серверы менять не нужно —
достаточно A-записей.

### 10.3. До изменения DNS — проверка по hosts-файлу

Чтобы сайт открывался по домену до того, как DNS обновится, добавьте строку в
файл `C:\Windows\System32\drivers\etc\hosts` (от имени администратора):

```
<IP-адрес-сервера> vitrage-pro.ru www.vitrage-pro.ru
```

После перехода на боевой DNS строку нужно удалить.

> **Важно**: проверка по домену через hosts-файл работает с конфигом 6.1
> (порт 80, без редиректа). Боевой конфиг 6.2 сразу уводит на HTTPS,
> поэтому до выпуска SSL он не подойдёт.

### 10.4. SSL-сертификат (Let's Encrypt, бесплатно)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d vitrage-pro.ru -d www.vitrage-pro.ru
```

Certbot сам пропишет сертификат в конфиг Nginx и настроит автообновление.
Проверка автообновления:

```bash
sudo certbot renew --dry-run
```

> На SpaceWeb можно также заказать платный SSL (от ~1900 ₽/год) — для этого
> сайта он не нужен, бесплатного Let's Encrypt достаточно.

### 10.5. Переключение адресов WordPress с IP на домен

Если WordPress устанавливался по IP, после выпуска SSL сделайте следующее:

1. В админке: **«Настройки → Общие»**.
2. Замените оба поля — «Адрес WordPress (URL)» и «Адрес сайта (URL)» —
   на `https://vitrage-pro.ru`.
3. Сохраните. Откроется `https://vitrage-pro.ru/wp-admin/` — войдите заново.
4. Замените конфиг Nginx на боевой (раздел 6.2).
5. Проверьте редиректы: `bash ops/check-redirects-curl.sh "https://vitrage-pro.ru"`.

---

## 11. Почта для формы обратной связи

Форма на странице «Контакты» отправляет письма через WordPress (`wp_mail`).
Чтобы письма гарантированно доходили (не попадали в спам), настройте SMTP:

### Вариант А — почта хостинга / домена

Если у домена есть почта (у SpaceWeb можно создать ящик вида
`info@vitrage-pro.ru`), уточните SMTP-параметры в поддержке SpaceWeb
(обычно `smtp.<домен>` / `smtp.yandex.ru` и т.п.) и заполните их в админке:

**«Настройки сайта → Форма и почта»** → поля SMTP:
- SMTP-сервер, порт (465 или 587), шифрование (`ssl`/`tls`),
- логин (email ящика), пароль, имя отправителя.

### Вариант Б — бесплатная Яндекс.Почта для домена

1. Подключите домен к Яндекс 360 (бесплатный тариф для организаций до 50
   сотрудников или Почта для домена на reg.ru-подобных сервисах) — либо
   используйте обычный ящик на Яндексе.
2. Включите «Пароли приложений» и создайте пароль для почты.
3. Заполните SMTP в админке:
   - сервер `smtp.yandex.ru`, порт `465`, шифрование `ssl`,
   - логин `info@vitrage-pro.ru` (или ваш ящик), пароль приложения.

### Проверка

Отправьте тестовое письмо с формы на `/contacts/` и убедитесь, что оно пришло
на `vp_form_recipient` («Настройки сайта → Форма и почта»).

---

## 12. Бэкапы

### 12.1. Снапшоты в панели SpaceWeb

В панели VPS (vps.sweb.ru) включите автоматические бэкапы (от ~1,5 ₽/день)
— это полный снимок сервера.

### 12.2. Резервное копирование сайта (cron)

Создайте скрипт бэкапа (файл `ops/backup-example.sh` в репозитории):

```bash
sudo nano /usr/local/bin/backup-vitrage.sh
```

Вставьте:

```bash
#!/bin/bash
# Резервное копирование сайта vitrage-pro.ru
BACKUP_DIR=/backups/vitrage-pro.ru
DATE=$(date +%Y-%m-%d)
mkdir -p "$BACKUP_DIR"

# Файлы сайта (без кеша)
tar -czf "$BACKUP_DIR/files-$DATE.tar.gz" \
  --exclude='wp-content/cache' \
  -C /var/www/vitrage-pro.ru public

# База данных (пароль укажите из шага 4)
mysqldump -u vp_user -p'vp_password' vitrage_pro \
  > "$BACKUP_DIR/db-$DATE.sql"

# Хранить 14 последних копий
find "$BACKUP_DIR" -name '*.tar.gz' -mtime +14 -delete
find "$BACKUP_DIR" -name '*.sql' -mtime +14 -delete
```

Назначьте права и ежедневный запуск:

```bash
sudo chmod +x /usr/local/bin/backup-vitrage.sh
sudo crontab -e
```

Добавьте строку (запуск каждый день в 3:00):

```
0 3 * * * /usr/local/bin/backup-vitrage.sh
```

---

## 13. Базовая безопасность

```bash
# 1. Включить файрвол (разрешить SSH, HTTP, HTTPS)
sudo apt install -y ufw
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# 2. Обновления безопасности автоматически
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure --priority=low unattended-upgrades
# выбрать "Yes" в диалоге

# 3. Запретить вход root по паролю (рекомендуется, после настройки SSH-ключа)
#    ВНИМАНИЕ: делайте только когда уверены, что SSH-ключ работает!
# sudo nano /etc/ssh/sshd_config  -> PermitRootLogin prohibit-password
# sudo systemctl restart ssh
```

---

## 14. Чек-лист перед запуском (go-live)

- [ ] Сайт открывается по `https://vitrage-pro.ru` с валидным SSL.
- [ ] Главная, `/gallery/`, `/gallery/okna/`, `/komanda/`, `/reviews/`,
      `/news/`, `/price/`, `/contacts/` — без ошибок.
- [ ] Фотографии в категориях открываются, лайтбокс работает.
- [ ] Форма на `/contacts/` отправляет письмо владельцу.
- [ ] Старые URL отдают 301-редирект (проверка скриптом):
      ```bash
      bash ops/check-redirects-curl.sh "https://vitrage-pro.ru"
      ```
- [ ] DNS-записи обновлены, hosts-файл очищен.
- [ ] Включены бэкапы (снапшот + cron).
- [ ] Мобильная версия и скорость страниц приемлемы (Google PageSpeed ≥ 60).

---

## 15. Поддержка после запуска

| Задача | Кто | Как часто |
|---|---|---|
| Изменение контента (тексты, фото, новости) | владелец | самостоятельно, в админке `wp-admin` |
| Обновление WordPress | владелец или техспециалист | 1 раз в месяц |
| Проверка формы и почты | владелец | еженедельно |
| Проверка бэкапов | техспециалист | ежемесячно |
| Обновление ОС сервера | техспециалист | ежемесячно (`apt update && apt upgrade`) |

Владельцу достаточно инструкции `docs/client-handover-guide.md`.

---

## Приложение А. Ручная установка LEMP (если VPS заказан «Без ПО»)

Если при заказе VPS вы не выбрали шаблон LEMP, установите стек вручную:

```bash
# 1. Nginx
sudo apt update
sudo apt install -y nginx

# 2. PHP 8.x + расширения (для WordPress)
sudo apt install -y php-fpm php-mysql php-curl php-gd php-mbstring php-xml php-zip php-intl

# 3. MariaDB (или MySQL)
sudo apt install -y mariadb-server
sudo mysql_secure_installation   # задать пароль root и ответить на вопросы
```

> **Важно**: если при `mysql_secure_installation` вы задали пароль root
> (или ответили «нет» на предложение переключиться на вход без пароля),
> дальше `sudo mysql` без пароля работать НЕ будет. Подключайтесь так:
> `sudo mysql -u root -p` (и вводите свой пароль). Все детали — в шаге 4.

Проверьте статусы:

```bash
sudo systemctl status nginx
sudo systemctl status php*-fpm
sudo systemctl status mariadb
```

Узнайте точную версию PHP (для `fastcgi_pass` в конфиге Nginx):

```bash
php -v
ls /run/php/
```

Далее продолжайте с **шага 4** (создание БД).

---

## Приложение Б. Частые проблемы

### `nginx -t` — duplicate default server for 0.0.0.0:80
Конфликт: заглушка SpaceWeb уже объявлена default-сервером на порту 80, и наш
конфиг тоже. Решение — отключить заглушку (см. раздел 6.1):
```bash
grep -rl "default_server" /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default     # имя — из вывода grep
sudo nginx -t
sudo systemctl reload nginx
```

### По IP открывается «Hello World! …swtest.ru»
Это дефолтная заглушка Nginx шаблона SpaceWeb. Она видна, пока конфиг сайта
не включён или не обслуживает этот адрес. Решение — временный конфиг из
раздела **6.1** (слушает порт 80 и отвечает по IP) и загруженные файлы
WordPress. Заглушка исчезнет после `sudo systemctl reload nginx`.

### `sudo mysql` — Access denied for user 'root'
У root уже задан пароль. Варианты:
1. Подключиться с паролем: `sudo mysql -u root -p` (пароль, если знаете).
2. Найти пароль в файлах: `sudo cat /root/.my.cnf` или
   `sudo cat /etc/mysql/debian.cnf` (аккаунт `debian-sys-maint`).
3. Сбросить root на вход без пароля — процедура в разделе **4.3**.

### Сайт не открывается, а Nginx работает
Проверьте файрвол и DNS:
```bash
sudo ufw status
curl -I http://127.0.0.1/          # ответит ли сам сервер
```

### Ошибка 502 Bad Gateway
Не совпадает версия PHP в `fastcgi_pass` конфига Nginx. Проверьте:
```bash
ls /run/php/
```
и исправьте строку `fastcgi_pass unix:/run/php/php8.X-fpm.sock;`.

### Белый экран WordPress
Включите отладку — добавьте в `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
Лог ошибок: `wp-content/debug.log`.

### Импорт контента не запускается
Проверьте права на папку темы:
```bash
sudo chown -R www-data:www-data /var/www/vitrage-pro.ru/public/wp-content/themes/vitrage-pro
```
и наличие файла `content/data.json` внутри темы.

### Письма с формы не приходят
Настройте SMTP (шаг 11). Без SMTP многие хостеры блокируют исходящую почту
или письма попадают в спам.

### SSL не выпускается
Убедитесь, что DNS уже указывает на сервер и порт 80 открыт:
```bash
dig +short vitrage-pro.ru
```
(должен вернуть IP вашего VPS).

---

## Полезные ссылки

- Заказ VPS: https://sweb.ru/vds/
- База знаний SpaceWeb: https://help.sweb.ru
- Панель управления: https://mcp.sweb.ru
- VNC-консоль и управление VPS: https://vps.sweb.ru
- Поддержка SpaceWeb: 8 (800) 777-86-49 (бесплатно по РФ), чат на сайте
- WordPress: https://ru.wordpress.org
- Готовый конфиг Nginx с редиректами: `ops/prod-vitrage-pro-ru.nginx.conf`
- Проверка редиректов: `ops/check-redirects-curl.sh`
- Скрипт бэкапа: `ops/backup-example.sh`
- Инструкция для владельца: `docs/client-handover-guide.md`
