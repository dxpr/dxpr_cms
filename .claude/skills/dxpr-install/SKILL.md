---
name: dxpr-install
version: 1.0.0
description: |
  Install DXPR CMS sites via the bin/dxpr-install TUI wizard or
  non-interactive CLI. This skill should be used when the user asks to
  "install DXPR CMS", "create a new site", "set up a new DXPR site",
  "install with all recipes", "install multilingual site",
  "run the installer", "set up DXPR", or any DXPR CMS installation
  task. Also use when the user mentions "dxpr-install", "site-install",
  or asks to "add another site" (multisite). Proactively invoke this
  skill instead of manually constructing drush site-install arguments.
---

## Preamble (run first)

```bash
echo "=== DXPR CMS INSTALL CONTEXT ==="

# --- PHP ---
echo ""
echo "=== PHP ==="
php -v 2>/dev/null | head -1 || echo "PHP: NOT FOUND"

# --- DDEV detection ---
echo ""
echo "=== ENVIRONMENT ==="
if [ -n "$IS_DDEV_PROJECT" ]; then
  echo "RUNTIME: ddev container"
  echo "DB_URL: mysql://db:db@db:3306/db (auto)"
elif command -v ddev &>/dev/null && [ -f .ddev/config.yaml ]; then
  DDEV_STATUS=$(ddev status 2>/dev/null | head -3)
  echo "DDEV: project found (.ddev/config.yaml)"
  echo "$DDEV_STATUS"
  echo "HINT: run 'ddev exec bin/dxpr-install' to install inside DDEV"
else
  echo "DDEV: not detected"
fi

# --- MySQL credential probing (outside DDEV only) ---
if [ -z "$IS_DDEV_PROJECT" ]; then
  echo ""
  echo "=== DATABASE ==="
  if mysql -u root -e "SELECT 1" &>/dev/null 2>&1; then
    echo "MYSQL: root@localhost (no password) — works"
    echo "DB_URL: mysql://root@127.0.0.1/<dbname>"
  elif mysql -u root -padmin -e "SELECT 1" &>/dev/null 2>&1; then
    echo "MYSQL: root:admin@localhost — works"
    echo "DB_URL: mysql://root:admin@127.0.0.1/<dbname>"
  elif mysql -u root -proot -e "SELECT 1" &>/dev/null 2>&1; then
    echo "MYSQL: root:root@localhost — works"
    echo "DB_URL: mysql://root:root@127.0.0.1/<dbname>"
  else
    echo "MYSQL: could not auto-detect credentials — user must provide --db-url"
  fi
fi

# --- Installer binary ---
echo ""
echo "=== INSTALLER ==="
if [ -x bin/dxpr-install ]; then
  echo "BINARY: bin/dxpr-install (available)"
elif [ -f vendor/bin/dxpr-install ]; then
  echo "BINARY: vendor/bin/dxpr-install (available)"
elif [ -f composer.json ]; then
  echo "BINARY: NOT FOUND — run 'composer install' first"
else
  echo "BINARY: NOT FOUND — not a dxpr_cms project directory"
fi

# --- DXPR API key detection ---
echo ""
echo "=== API KEY ==="
if [ -n "$DXPR_API_KEY" ]; then
  echo "ENV: \$DXPR_API_KEY is set"
elif [ -f .env ] && grep -q 'DXPR_API_KEY' .env 2>/dev/null; then
  echo "FILE: found in .env"
elif [ -f CLAUDE.md ] && grep -q 'eyJ' CLAUDE.md 2>/dev/null; then
  echo "FILE: JWT found in CLAUDE.md"
else
  echo "NOT FOUND: user must provide --api-key or get one at https://app.dxpr.com/getting-started"
fi

# --- Existing sites ---
echo ""
echo "=== EXISTING SITES ==="
SITE_COUNT=0
for settings in web/sites/*/settings.php; do
  [ -f "$settings" ] || continue
  SITE_COUNT=$((SITE_COUNT + 1))
  site_dir=$(dirname "$settings" | sed 's|web/sites/||')
  db=$(grep "'database'" "$settings" 2>/dev/null | grep -v "^\s*[*/]" | head -1 | sed "s/.*=> *'//;s/'.*//")
  echo "  $site_dir (db: ${db:-unknown})"
done
[ "$SITE_COUNT" -eq 0 ] && echo "  (none — fresh codebase)"

echo ""
echo "=== AVAILABLE RECIPES ==="
echo "Case Studies, Events, Forms, Google Analytics, News, SEO Tools, Multilingual"
```

## Decision Tree (after preamble)

Follow this logic to determine the right installation approach:

### 1. Is the installer binary available?

- **No** → Run `composer install` first (or `ddev composer install` if DDEV project)
- **Yes** → Continue

### 2. Is this running inside DDEV?

- **RUNTIME: ddev container** → Database auto-configured, use `bin/dxpr-install` directly
- **DDEV: project found** → Suggest `ddev exec bin/dxpr-install` (runs inside the container)
- **DDEV: not detected** → Need `--db-url`, use auto-detected MySQL credentials from preamble

### 3. Is an API key available?

- **ENV/FILE found** → Extract and pass as `--api-key`
- **JWT found in CLAUDE.md** → Extract the JWT token from CLAUDE.md and use it
- **NOT FOUND** → Ask user for their key, or suggest `--skip-api-key` for dev-only installs

### 4. Are there existing sites?

- **None** → Fresh install, proceed normally
- **Existing sites found** → Ask user: install as **multisite** (`--multisite --sites-subdir=<name>`) on the same codebase, or create a **separate codebase** in a new directory?

### 5. What does the user want to install?

- If unspecified, ask about recipes and languages
- If "everything" or "full install", use `--all-recipes`
- If specific features mentioned (e.g. "with events and news"), map to `--recipes="Events,News"`
- If multilingual mentioned, ask which languages or suggest common ones

## Pre-bootstrap Awareness

This installer runs **before** Drupal exists. Do NOT attempt drush commands, Drupal API calls, or module operations until after installation completes. The installer is a standalone Symfony Console application that orchestrates `drush site-install` internally.

## Command Reference

### Interactive wizard

```bash
bin/dxpr-install                           # Inside DDEV container or native
ddev exec bin/dxpr-install                 # From host with DDEV project
```

### Non-interactive

```bash
bin/dxpr-install \
  --api-key='eyJ...' \
  --all-recipes \
  --site-name="My Site" \
  --admin-pass="secret" \
  --no-interaction

# With specific recipes and languages
bin/dxpr-install \
  --api-key='eyJ...' \
  --recipes="News,Events,Multilingual" \
  --languages="nl,de,ar" \
  --no-interaction
```

### Multisite

```bash
bin/dxpr-install \
  --multisite \
  --sites-subdir=site2 \
  --db-url="mysql://root:admin@127.0.0.1/site2_db" \
  --api-key='eyJ...' \
  --no-interaction
```

### Dry run

```bash
bin/dxpr-install --api-key='eyJ...' --all-recipes --dry-run
```

## Options

| Option | Description | Default |
|---|---|---|
| `--recipes` | Comma-separated recipe names | none |
| `--all-recipes` | Install all 7 optional recipes | off |
| `--site-name` | Site name | "DXPR CMS" |
| `--api-key` | DXPR API key (JWT) | required |
| `--skip-api-key` | Skip API key (dev only) | off |
| `--languages` | Comma-separated ISO codes | none |
| `--admin-email` | Admin email | admin@example.com |
| `--admin-pass` | Admin password | auto-generated |
| `--db-url` | Database URL | auto in DDEV |
| `--multisite` | Drupal multisite mode | off |
| `--sites-subdir` | Sites subdirectory name | default |
| `--dry-run` | Preview drush command only | off |
| `--no-interaction` | Skip all prompts | off |

## Recipes

| Recipe | Adds content type | Key modules |
|---|---|---|
| Case Studies | case_study | — |
| Events | event | geofield |
| Forms | — | webform |
| Google Analytics | — | google_tag |
| News | news | — |
| SEO Tools | — | simple_sitemap, seo_checklist |
| Multilingual | — | tmgmt, locale, content_translation |

If `--languages` is provided without Multilingual in `--recipes`, the installer auto-adds it.

## Environment Detection

| Signal | Action |
|---|---|
| `$IS_DDEV_PROJECT` set | Use `mysql://db:db@db:3306/db`, `drush` on PATH |
| `.ddev/config.yaml` exists | Suggest `ddev exec bin/dxpr-install` |
| `mysql -u root` succeeds | Use discovered credentials for `--db-url` |
| `$DXPR_API_KEY` set | Use as `--api-key` value |
| JWT in CLAUDE.md | Extract and use as `--api-key` |
| Existing `web/sites/*/settings.php` | Offer multisite option |

## Post-install

After successful installation, install AI skill files for each DXPR module:

```bash
drush dxt:setup-ai    # Theme CLI skill
drush dxb:setup-ai    # Builder CLI skill
drush wm:setup-ai     # Webmaster CLI skill
```

Then the full CLI ecosystem is available: `dxt:*` (theme), `dxb:*` (builder), `wm:*` (content/menus).

## API Key

Free DXPR API key at https://app.dxpr.com/getting-started — unlocks AI features across all DXPR modules (OpenAI, Claude, Gemini, MistralAI, XAI, Perplexity). The installer validates JWT format and `dxpr_tier` claim.
