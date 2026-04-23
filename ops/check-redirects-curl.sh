#!/usr/bin/env bash
set -euo pipefail

DOMAIN="${1:-https://vitrage-pro.ru}"

URLS=(
  "/index.html"
  "/about.html"
  "/about/ceny.html"
  "/gallery.html"
  "/gallery/fyuzing.html"
  "/gallery/okna.html"
  "/gallery/podarki.html"
  "/gallery/dveri.html"
  "/gallery/mozaika.html"
  "/gallery/interery.html"
  "/gallery/peregorodki.html"
  "/gallery/potolki.html"
  "/gallery/svetilniki.html"
  "/gallery/rospis.html"
  "/komanda.html"
  "/komanda/spec-1.html"
  "/komanda/spec-2.html"
  "/komanda/spec-3.html"
  "/komanda/spec-4.html"
  "/komanda/spec-5.html"
  "/reviews.html"
  "/reviews/review-1.html"
  "/reviews/review-2.html"
  "/reviews/review-3.html"
  "/news/news-1.html"
  "/news/news-2.html"
  "/news/news-3.html"
  "/price.html"
  "/contacts.html"
)

echo "Checking redirects on: $DOMAIN"
echo

for path in "${URLS[@]}"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" -I "$DOMAIN$path")
  location=$(curl -s -I "$DOMAIN$path" | awk -F': ' '/^[Ll]ocation:/ {print $2}' | tr -d '\r' || true)
  printf "%-30s -> %s %s\n" "$path" "$code" "${location:-}"
done
