#!/usr/bin/env python3
"""
Лёгкая проверка PHP-файлов без интерпретатора:
- баланс скобок (), {}, [] вне строк и комментариев;
- баланс <?php ... ?> и endif/endforeach/while/if-блоков не проверяем (сложно);
- наличие открывающих/закрывающих тегов.

Не заменяет php -l, но ловит грубые ошибки.
"""

import re
import sys
from pathlib import Path


def strip_strings_and_comments(code: str) -> str:
    """Заменяет строки и комментарии пробелами, оставляя структуру."""
    out = []
    i = 0
    n = len(code)
    state = "code"  # code | single | double | line_comment | block_comment
    while i < n:
        c = code[i]
        nxt = code[i + 1] if i + 1 < n else ""

        if state == "code":
            if c == "/" and nxt == "/":
                state = "line_comment"
                out.append("  ")
                i += 2
                continue
            if c == "/" and nxt == "*":
                state = "block_comment"
                out.append("  ")
                i += 2
                continue
            if c == "#":
                state = "line_comment"
                out.append(" ")
                i += 1
                continue
            if c == "'":
                state = "single"
                out.append(" ")
                i += 1
                continue
            if c == '"':
                state = "double"
                out.append(" ")
                i += 1
                continue
            out.append(c)
            i += 1
            continue

        if state == "single":
            if c == "\\":
                out.append("  ")
                i += 2
                continue
            if c == "'":
                state = "code"
            out.append(" ")
            i += 1
            continue

        if state == "double":
            if c == "\\":
                out.append("  ")
                i += 2
                continue
            if c == '"':
                state = "code"
            out.append(" ")
            i += 1
            continue

        if state == "line_comment":
            if c == "\n":
                state = "code"
                out.append(c)
            else:
                out.append(" ")
            i += 1
            continue

        if state == "block_comment":
            if c == "*" and nxt == "/":
                state = "code"
                out.append("  ")
                i += 2
                continue
            out.append(" ")
            i += 1
            continue

    return "".join(out)


def check_file(path: Path) -> list:
    errors = []
    code = path.read_text(encoding="utf-8", errors="ignore")
    if "<?php" not in code and path.suffix == ".php":
        errors.append("no <?php tag")

    stripped = strip_strings_and_comments(code)

    pairs = {")": "(", "]": "[", "}": "{"}
    stack = []
    for i, c in enumerate(stripped):
        if c in "([{":
            stack.append((c, i))
        elif c in ")]}":
            if not stack:
                errors.append(f"unbalanced '{c}' at offset {i}")
                continue
            opener, oi = stack.pop()
            if opener != pairs[c]:
                errors.append(f"mismatched '{c}' at offset {i}, expected '{pairs[c]}' opened at {oi}")

    for opener, i in stack:
        errors.append(f"unclosed '{opener}' at offset {i}")

    return errors


def main() -> None:
    root = Path(sys.argv[1]) if len(sys.argv) > 1 else Path(".")
    php_files = sorted(root.rglob("*.php"))
    if not php_files:
        print("no php files")
        return

    total_errors = 0
    for f in php_files:
        errs = check_file(f)
        if errs:
            total_errors += len(errs)
            print(f"\n{f}:")
            for e in errs:
                print(f"  - {e}")
    print(f"\nChecked {len(php_files)} files, {total_errors} issues.")


if __name__ == "__main__":
    main()
