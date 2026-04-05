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

## Workflow: Gather First, Execute Later

**CRITICAL: Do NOT run any slow commands (git clone, composer install, bin/dxpr-install) until all questions are answered and the user confirms the plan.** Collect all inputs first, present a summary, then execute only after approval.

### Phase 1: Gather all inputs (ask questions, no commands)

Walk through these questions using the preamble output. Skip questions that the preamble already answered. Ask remaining questions **in a single message** to avoid back-and-forth:

1. **Codebase location** — If preamble shows "not a dxpr_cms project directory": confirm the target directory with the user (default: infer from their request, e.g. "dxpr-cms-test1" → `~/www/dxpr-cms-test1/`)
2. **Recipes** — Which optional add-ons? (Case Studies, Events, Forms, Google Analytics, News, SEO Tools, Multilingual). If user said "all" or "full install", note `--all-recipes`. If unspecified, ask.
3. **API key** — If preamble found one (ENV/FILE/CLAUDE.md), confirm using it. If not found, ask for it or offer `--skip-api-key`.
4. **Languages** — Only relevant if Multilingual is selected. If multilingual mentioned, ask which languages.
5. **Site name** — Default to directory name or "DXPR CMS". Confirm or ask.
6. **Database** — If DDEV, auto-configured. If Valet/native, use auto-detected MySQL credentials from preamble. Database name defaults to directory name with hyphens replaced by underscores.
7. **Multisite** — Only if existing sites were detected in preamble. Ask: multisite or separate codebase?

### Phase 2: Present the plan for confirmation

Summarize everything before executing. Example:

```
I'll set up DXPR CMS at ~/www/dxpr-cms-test1/:

  1. Clone repo + composer install (~2-3 min)
  2. Create database: dxpr_cms_test1
  3. Run installer with:
     - Recipes: all
     - Languages: nl, de
     - API key: from CLAUDE.md
     - Site name: "DXPR Test 1"
     - URL: http://dxpr-cms-test1.test

Proceed?
```

### Phase 3: Execute (only after user confirms)

Run commands in order:

1. **Bootstrap codebase** (if needed):
   ```bash
   mkdir <site-name> && cd <site-name>
   git clone https://github.com/dxpr/dxpr_cms.git .
   composer install
   ```
2. **Create database** (Valet/native only):
   ```bash
   mysql -u root -padmin -e "CREATE DATABASE <dbname>"
   ```
3. **Run installer**:
   ```bash
   bin/dxpr-install \
     --api-key='...' \
     --recipes="..." \
     --languages="..." \
     --site-name="..." \
     --db-url="mysql://root:admin@127.0.0.1/<dbname>" \
     --admin-pass="admin" \
     --no-interaction
   ```

For **Valet/native**: the directory name under `~/www/` becomes the `.test` hostname automatically (e.g. `~/www/dxpr-cms-test1/` → `http://dxpr-cms-test1.test`).

For **DDEV**: use `ddev exec bin/dxpr-install` instead. Database auto-configured.

### Resolving preamble signals

| Preamble output | Meaning |
|---|---|
| `BINARY: NOT FOUND — not a dxpr_cms project directory` | Need git clone + composer install |
| `BINARY: NOT FOUND — run 'composer install' first` | Need composer install only |
| `BINARY: bin/dxpr-install (available)` | Ready to install |
| `RUNTIME: ddev container` | Inside DDEV, DB auto |
| `DDEV: project found` | Use `ddev exec` |
| `MYSQL: root:admin@localhost — works` | Use `mysql://root:admin@127.0.0.1/<dbname>` |
| `ENV: $DXPR_API_KEY is set` | Use that value |
| `FILE: JWT found in CLAUDE.md` | Extract JWT from CLAUDE.md |
| Existing sites listed | Offer multisite option |

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
