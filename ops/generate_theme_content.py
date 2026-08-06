#!/usr/bin/env python3
"""
Генерация content/data.json и content/images/ для темы WordPress.

Читает статическую версию сайта (vitrage-pro.ru/) и собирает:
- настройки сайта (контакты, тексты);
- категории галереи + работы с фото;
- команду;
- отзывы;
- новости.

Запуск: python ops/generate_theme_content.py
"""

import html
import json
import re
import shutil
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
STATIC = ROOT / "vitrage-pro.ru" / "vitrage-pro.ru"
OUT_JSON = ROOT / "wordpress" / "wp-content" / "themes" / "vitrage-pro" / "content" / "data.json"
OUT_IMAGES = ROOT / "wordpress" / "wp-content" / "themes" / "vitrage-pro" / "content" / "images"


def strip_tags(raw: str) -> str:
    text = re.sub(r"<\s*br\s*/?\s*>", "\n", raw, flags=re.IGNORECASE)
    text = re.sub(r"<[^>]+>", " ", text)
    text = html.unescape(text)
    text = re.sub(r"[ \t]+", " ", text)
    text = re.sub(r"\n\s*\n+", "\n\n", text)
    return text.strip()


def first_paragraph(raw: str, limit: int = 400) -> str:
    m = re.search(r"<p[^>]*>(.*?)</p>", raw, flags=re.IGNORECASE | re.DOTALL)
    if not m:
        return ""
    text = strip_tags(m.group(1))
    if len(text) > limit:
        text = text[:limit].rsplit(" ", 1)[0] + "…"
    return text


def about_text_from_index(raw: str, limit: int = 1500) -> str:
    """Текст секции «О мастерской» с главной (блок split-box about-me)."""
    m = re.search(r'<section id="about-me-section">(.*?)</section>', raw, flags=re.IGNORECASE | re.DOTALL)
    if not m:
        return ""
    block = m.group(1)
    # Текст идёт в <p> внутри split-box-content.
    texts = []
    for p in re.findall(r"<p>(.*?)</p>", block, flags=re.IGNORECASE | re.DOTALL):
        text = strip_tags(p).strip()
        if text and len(text) > 40 and "Витраж-про" not in text:
            texts.append(text)
    merged = "\n\n".join(texts)
    if len(merged) > limit:
        merged = merged[:limit].rsplit(" ", 1)[0] + "…"
    return merged


def article_text(raw: str, limit: int = 3000) -> str:
    """Текст из блока <article class="col-sm-8"> (страницы новостей/отзывов)."""
    m = re.search(r'<article[^>]*>(.*?)</article>', raw, flags=re.IGNORECASE | re.DOTALL)
    if not m:
        return ""
    text = strip_tags(m.group(1))
    # Убираем заголовок h1, если он остался в тексте.
    text = re.sub(r"^[^\n]*\n", "", text, count=1)
    if len(text) > limit:
        text = text[:limit].rsplit(" ", 1)[0] + "…"
    return text


def copy_image(src_rel: Path, dest_name: str) -> str:
    """Копирует изображение в content/images/, возвращает имя файла.

    src_rel — путь относительно assets/cache_image/ (например resources/23/x.jpg).
    """
    src = STATIC / "assets" / "cache_image" / src_rel
    if not src.exists():
        return ""
    dest = OUT_IMAGES / dest_name
    dest.parent.mkdir(parents=True, exist_ok=True)
    if not dest.exists():
        shutil.copy2(src, dest)
    return dest_name


def sanitize_name(name: str) -> str:
    name = re.sub(r"[^\w\-\.]+", "-", name)
    name = re.sub(r"-+", "-", name).strip("-")
    return name[:80]


def main() -> None:
    OUT_IMAGES.mkdir(parents=True, exist_ok=True)

    # ---------- Категории и работы ----------
    categories = []
    gallery = []

    # Описания категорий из docs/import-gallery-item-real.csv (реальные тексты).
    category_desc = {}
    csv_path = ROOT / "docs" / "import-gallery-item-real.csv"
    if csv_path.exists():
        import csv as csv_mod
        with csv_path.open(encoding="utf-8") as f:
            for row in csv_mod.DictReader(f):
                title = (row.get("post_title") or "").strip()
                content = (row.get("post_content") or "").strip()
                if title and content and "перенесен из статической версии" not in content:
                    category_desc[title] = content

    category_map = {
        "fyuzing": "Фьюзинг",
        "okna": "Окна",
        "podarki": "От эскиза к витражу",
        "dveri": "Двери",
        "mozaika": "Мозаика",
        "interery": "Интерьеры",
        "peregorodki": "Перегородки",
        "potolki": "Потолки",
        "svetilniki": "Светильники",
        "rospis": "Роспись",
    }

    for slug, name in category_map.items():
        page = STATIC / "gallery" / f"{slug}.html"
        if not page.exists():
            continue

        raw = page.read_text(encoding="utf-8", errors="ignore")

        # Описание категории: из CSV (реальные тексты), иначе — первый абзац.
        desc = category_desc.get(name, "")
        if not desc:
            m = re.search(r'<div class="post-content">(.*?)</div>', raw, flags=re.IGNORECASE | re.DOTALL)
            if m:
                desc = first_paragraph(m.group(1), 600)

        categories.append({
            "slug": slug,
            "name": name,
            "description": desc,
        })

        # Изображения: ../assets/cache_image/resources/N/xxx_1200x0_5fb.jpg
        images = re.findall(
            r'\.\./assets/cache_image/(resources/\d+/[^"\']+?\.(?:jpg|jpeg|png))',
            raw,
            flags=re.IGNORECASE,
        )
        seen = set()
        order = 0
        for img_rel in images:
            if img_rel in seen:
                continue
            seen.add(img_rel)

            base = Path(img_rel).name
            # Убираем суффикс размера MODX: name_1200x0_5fb.jpg -> name.jpg
            clean = re.sub(r"_\d+x\d+_[0-9a-f]+\.(jpg|jpeg|png)$", r".\1", base, flags=re.IGNORECASE)
            dest_name = f"{slug}-{order:02d}-{sanitize_name(clean)}"

            copied = copy_image(Path(img_rel), dest_name)
            if not copied:
                continue

            title = f"{name} — фото {order + 1}"
            gallery.append({
                "title": title,
                "slug": f"{slug}-{order + 1:02d}",
                "image": copied,
                "description": "",
                "category": name,
                "order": order,
            })
            order += 1

    # ---------- Команда ----------
    # Карта: spec-N.html -> (имя, фото) со страницы-списка komanda.html.
    team_grid_raw = (STATIC / "komanda.html").read_text(encoding="utf-8", errors="ignore")
    team_grid = {}  # slug -> photo filename
    for m in re.finditer(
        r'<a href="komanda/(spec-\d+\.html)" class="gl-item-image-inner">.*?background-image: url\(([^)]+)\);',
        team_grid_raw,
        flags=re.IGNORECASE | re.DOTALL,
    ):
        slug = m.group(1).replace(".html", "")
        url = m.group(2).strip()
        m2 = re.search(r"assets/cache_image/([^\"']+\.(?:jpg|jpeg|png))", url)
        if m2:
            team_grid[slug] = m2.group(1)

    team = []
    team_pages = sorted((STATIC / "komanda").glob("spec-*.html"))
    for page in team_pages:
        raw = page.read_text(encoding="utf-8", errors="ignore")

        name_m = re.search(r'<h1 class="page-header-title">([^<]+)</h1>', raw)
        if not name_m:
            name_m = re.search(r"<h1>([^<]+)</h1>", raw)
        name = name_m.group(1).strip() if name_m else page.stem

        # Био: непустой блок post-content (spec-1) или <article> (spec-2..5).
        bio = ""
        for m in re.finditer(r'<div class="post-content">(.*?)</div>', raw, flags=re.IGNORECASE | re.DOTALL):
            candidate = strip_tags(m.group(1)).strip()
            if candidate:
                bio = candidate
                break
        if not bio:
            bio = article_text(raw, 2500)
        bio = bio[:2500].strip()

        # Фото из списка komanda.html (500x0 вариант).
        photo = ""
        grid_img = team_grid.get(page.stem, "")
        if grid_img:
            clean = re.sub(r"_\d+x\d+_[0-9a-f]+\.(jpg|jpeg|png)$", r".\1", Path(grid_img).name, flags=re.IGNORECASE)
            photo = copy_image(Path(grid_img), f"team-{sanitize_name(clean)}")

        team.append({
            "name": name,
            "slug": page.stem,
            "position": "",
            "photo": photo,
            "bio": bio,
        })

    # ---------- Отзывы ----------
    reviews = []
    review_pages = sorted((STATIC / "reviews").glob("review-*.html"))
    for i, page in enumerate(review_pages, start=1):
        raw = page.read_text(encoding="utf-8", errors="ignore")
        text = article_text(raw, 2500)
        if not text:
            continue
        reviews.append({
            "author": f"Отзыв {i}",
            "city": "",
            "text": text,
            "slug": page.stem,
            "order": i,
        })

    # ---------- Новости ----------
    news = []
    news_pages = sorted((STATIC / "news").glob("news-*.html"))
    for page in news_pages:
        raw = page.read_text(encoding="utf-8", errors="ignore")

        title_m = re.search(r"<h1>([^<]+)</h1>", raw)
        title = title_m.group(1).strip() if title_m else page.stem

        # Первый параграф статьи.
        text = article_text(raw, 3000)
        if not text:
            text = title

        # Дата из подписи "07.09.2018 г."
        date = ""
        d_m = re.search(r"(\d{2}\.\d{2}\.\d{4})", raw)
        if d_m:
            day, month, year = d_m.group(1).split(".")
            date = f"{year}-{month}-{day} 10:00:00"

        news.append({
            "title": title,
            "slug": page.stem,
            "text": text,
            "date": date,
            "image": "",
        })

    # ---------- Настройки сайта ----------
    index_raw = (STATIC / "index.html").read_text(encoding="utf-8", errors="ignore")
    contacts_raw = (STATIC / "contacts.html").read_text(encoding="utf-8", errors="ignore")

    settings = {
        "vp_phone": "+7 (905) 771-08-25",
        "vp_phone_2": "8 (915) 064-31-01",
        "vp_email": "info@vitrage-pro.ru",
        "vp_address": "г. Москва, Малый Демидовский переулок, д.3",
        "vp_vk": "http://vk.com/vitragepro",
        "vp_hero_title": "Профессиональное проектирование и изготовление художественных витражей",
        "vp_hero_subtitle": "Авторских светильников и мозаичных панно на заказ",
        "vp_about_title": "Мы создаем уникальные витражи, предметы интерьера и мозаичные панно для вашего интерьера.",
        "vp_about_text": about_text_from_index(index_raw, 1500),
        "vp_gallery_title": "Наши работы",
        "vp_gallery_subtitle": "Свежие проекты и работы",
        "vp_show_testimonials": 1,
        "vp_cta_title": "Задать вопрос",
        "vp_cta_subtitle": "Интересны наши работы",
        "vp_cta_button1_text": "Подробнее о нас",
        "vp_cta_button1_url": "",
        "vp_cta_button2_text": "Оставить заявку",
        "vp_cta_button2_url": "",
        "vp_footer_text": "- Профессиональное проектирование и изготовление художественных витражей, авторских светильников и мозаичных панно на заказ.",
        "vp_copyright": "© Витраж Про 2018 / Все права защищены",
        "vp_form_recipient": "info@vitrage-pro.ru",
        "vp_form_subject": "Заявка с сайта vitrage-pro.ru",
        "vp_form_success": "Спасибо! Мы свяжемся с вами в ближайшее время.",
        "vp_show_subscribe": 0,
    }

    # Стартовый слайд hero (как на исходном сайте).
    hero_img = ""
    for rel in ["18-09-2018/cw2hsr-1a_1400x700_b2f.jpg"]:
        src = STATIC / "assets" / "cache_image" / rel
        if src.exists():
            hero_img = copy_image(Path(rel), "hero-slide-1.jpg")
    if hero_img:
        settings["vp_hero_slides"] = [{
            "image": hero_img,  # локальный файл; importer заменит на URL медиатеки
            "title": settings["vp_hero_title"],
            "subtitle": settings["vp_hero_subtitle"],
            "btn_text": "",
            "btn_url": "",
        }]

    data = {
        "settings": settings,
        "categories": categories,
        "gallery": gallery,
        "team": team,
        "reviews": reviews,
        "news": news,
        "pages": vitrage_pro_extra_pages(),
    }

    OUT_JSON.parent.mkdir(parents=True, exist_ok=True)
    OUT_JSON.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")

    img_count = len(list(OUT_IMAGES.rglob("*")))
    print(f"OK: {OUT_JSON}")
    print(f"categories: {len(categories)}")
    print(f"gallery items: {len(gallery)}")
    print(f"team: {len(team)}")
    print(f"reviews: {len(reviews)}")
    print(f"news: {len(news)}")
    print(f"images in content/images: {img_count}")


def vitrage_pro_extra_pages() -> list:
    """Дополнительные страницы с реальным контентом (кроме автосозданных setup-ом)."""
    pages = []

    # «Витражи тиффани» (бывш. about/ceny.html).
    ceny = STATIC / "about" / "ceny.html"
    if ceny.exists():
        raw = ceny.read_text(encoding="utf-8", errors="ignore")
        texts = []
        m = re.search(r'<div class="post-content">(.*?)</div>', raw, flags=re.IGNORECASE | re.DOTALL)
        if m:
            for p in re.findall(r"<p[^>]*>(.*?)</p>", m.group(1), flags=re.IGNORECASE | re.DOTALL):
                t = strip_tags(p).strip()
                if t and len(t) > 30:
                    texts.append(t)
        if texts:
            pages.append({
                "slug": "ceny",
                "title": "Витражи тиффани",
                "parent": "about",
                "content": "\n\n".join(texts),
            })

    return pages


if __name__ == "__main__":
    main()
