# AGENTS.md

## Cursor Cloud specific instructions

### Overview
This repository contains multiple PHP/HTML web applications on separate branches (thesis writing tool, drone UI, project management, login pages, etc.). The primary/most-developed project is the **Tez Yazım Arayüzü** (Thesis Writing Interface) on the `cursor/tez-yaz-m-aray-z-3911` branch.

### Tech Stack
- **PHP 8.3** with built-in dev server (no Apache/Nginx needed for development)
- **SQLite** for data storage (no external database required)
- Vanilla HTML/CSS/JavaScript frontend

### Running the Dev Server
```
php -S 0.0.0.0:8080 -t /workspace
```
The app will be available at `http://localhost:8080`.

### Key Notes
- No package manager dependencies are needed — `composer.json` exists but PHPWord was removed; the app uses pure PHP RTF export.
- The SQLite database (`data/tez.db`) is auto-created on first request.
- The `data/` directory must be writable for SQLite DB and file uploads.
- There are no automated tests, linters, or build steps in this codebase.
- Different branches contain independent projects — check `git branch -a` to see all available applications.
