#!/usr/bin/env bash
set -euo pipefail

# Example backup script for WordPress on VPS/shared shell.
# Fill values before usage.

DATE="$(date +%Y-%m-%d_%H-%M-%S)"
BACKUP_ROOT="${BACKUP_ROOT:-$HOME/backups/vitrage-pro}"
DB_NAME="${DB_NAME:-wordpress_db}"
DB_USER="${DB_USER:-wordpress_user}"
DB_PASS="${DB_PASS:-change-me}"
WP_PATH="${WP_PATH:-$HOME/public_html}"
KEEP_DAYS="${KEEP_DAYS:-14}"

mkdir -p "$BACKUP_ROOT/db" "$BACKUP_ROOT/files"

MYSQL_PWD="$DB_PASS" mysqldump -u "$DB_USER" "$DB_NAME" | gzip > "$BACKUP_ROOT/db/db_$DATE.sql.gz"
tar -czf "$BACKUP_ROOT/files/wp-content_$DATE.tar.gz" -C "$WP_PATH" wp-content

find "$BACKUP_ROOT/db" -type f -mtime +"$KEEP_DAYS" -delete
find "$BACKUP_ROOT/files" -type f -mtime +"$KEEP_DAYS" -delete

echo "Backup completed: $DATE"
