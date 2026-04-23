#!/usr/bin/env python3
import csv
import html
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

CSV_FILES = [
    ROOT / "docs" / "import-gallery-item-real.csv",
    ROOT / "docs" / "import-team-member-real.csv",
    ROOT / "docs" / "import-review-item-real.csv",
    ROOT / "docs" / "import-news-posts-real.csv",
]

NOISE_PATTERNS = [
    r"^\s*Подписаться\.{0,3}\s*$",
    r"^\s*Задать вопрос\s*$",
    r"^\s*Testimonials\s*$",
    r"^\s*Свежие работы\s*$",
    r"^\s*Beautiful People\s*$",
]


def strip_tags(raw_html: str) -> str:
    text = re.sub(r"<\s*br\s*/?\s*>", "\n", raw_html, flags=re.IGNORECASE)
    text = re.sub(r"<[^>]+>", " ", text)
    text = html.unescape(text)
    text = re.sub(r"\s+", " ", text).strip()
    return text


def is_noise(text: str) -> bool:
    if len(text) < 30:
        return True
    if "© Витраж Про 2018 / Все права защищены" in text:
        return True
    if "Vitrage pro Мы создаем уникальные витражи" in text:
        return True
    for pattern in NOISE_PATTERNS:
        if re.match(pattern, text, flags=re.IGNORECASE):
            return True
    if "tag! )" in text or "Витраж-про Витраж-про" in text:
        return True
    return False


def extract_meaningful_text(page_path: Path, fallback_title: str) -> str:
    source = page_path.read_text(encoding="utf-8", errors="ignore")

    paragraph_chunks = re.findall(r"<p[^>]*>.*?</p>", source, flags=re.IGNORECASE | re.DOTALL)
    heading_chunks = re.findall(r"<h[23][^>]*>.*?</h[23]>", source, flags=re.IGNORECASE | re.DOTALL)
    list_item_chunks = re.findall(r"<li[^>]*>.*?</li>", source, flags=re.IGNORECASE | re.DOTALL)

    paragraphs = []
    headings = []
    list_items = []
    seen = set()
    for chunk in paragraph_chunks:
        text = strip_tags(chunk)
        if not text or is_noise(text):
            continue
        if text in seen:
            continue
        seen.add(text)
        paragraphs.append(text)

    for chunk in heading_chunks:
        text = strip_tags(chunk)
        if not text or is_noise(text):
            continue
        if text in seen:
            continue
        seen.add(text)
        headings.append(text)

    for chunk in list_item_chunks:
        text = strip_tags(chunk)
        if not text or is_noise(text):
            continue
        if len(text) < 60:
            continue
        if text in seen:
            continue
        seen.add(text)
        list_items.append(text)

    if paragraphs:
        merged_parts = []
        if headings:
            merged_parts.append(headings[0])
        merged_parts.extend(paragraphs[:3])
        if list_items and len(" ".join(merged_parts)) < 500:
            merged_parts.append("Ключевые пункты: " + "; ".join(list_items[:4]))
        merged = " ".join(merged_parts).replace("© Витраж Про 2018 / Все права защищены", "").strip()
        if merged:
            return merged

    if headings:
        merged = " ".join(headings[:2]).strip()
        if list_items:
            merged = f"{merged}. Ключевые пункты: {'; '.join(list_items[:4])}"
        return merged

    # Fallback to first h1 if paragraph extraction is poor.
    h1_match = re.search(r"<h1[^>]*>(.*?)</h1>", source, flags=re.IGNORECASE | re.DOTALL)
    if h1_match:
        h1_text = strip_tags(h1_match.group(1))
        if h1_text and not is_noise(h1_text):
            return f"{h1_text}. Контент перенесен из статической версии сайта."

    return f"{fallback_title}. Контент перенесен из статической версии сайта и доступен для редактирования в админке."


def process_csv(csv_path: Path) -> None:
    rows = []
    with csv_path.open("r", encoding="utf-8", newline="") as f:
        reader = csv.DictReader(f)
        fieldnames = reader.fieldnames or []
        for row in reader:
            source_html = row.get("source_html", "").strip()
            title = row.get("post_title", "Материал").strip() or "Материал"
            if source_html:
                page_path = ROOT / source_html
                if page_path.exists():
                    row["post_content"] = extract_meaningful_text(page_path, title)
            rows.append(row)

    with csv_path.open("w", encoding="utf-8", newline="") as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)


def main() -> None:
    for csv_file in CSV_FILES:
        if csv_file.exists():
            process_csv(csv_file)
            print(f"Updated: {csv_file.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
