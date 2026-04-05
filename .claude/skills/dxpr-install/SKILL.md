---
name: dxpr-install
version: 1.1.0
description: |
  Install DXPR CMS sites end-to-end. This skill should be used when
  the user asks to "install DXPR CMS", "create a new site", "set up
  a new DXPR site", "install with all recipes", "install multilingual
  site", "run the installer", "set up DXPR", or any DXPR CMS
  installation task. Also use when the user mentions "site-install"
  or asks to "add another site" (multisite). Proactively invoke
  this skill instead of manually constructing drush site-install
  arguments.
---

## Preamble (run first)

Do NOT search or explore the codebase. Just run this single script:

```bash
if [ ! -f composer.json ]; then
  echo "STATUS: no-project"
elif [ -f vendor/bin/drush ]; then
  echo "STATUS: ready"
else
  echo "STATUS: needs-composer-install"
fi
if command -v ddev &>/dev/null; then
  echo "DDEV: installed"
else
  echo "DDEV: not-installed"
fi
if [ -n "$IS_DDEV_PROJECT" ]; then
  echo "DB: ddev"
elif mysql -u root -padmin -e "SELECT 1" &>/dev/null; then
  echo "DB: mysql://root:admin@127.0.0.1/<dbname>"
elif mysql -u root -e "SELECT 1" &>/dev/null; then
  echo "DB: mysql://root@127.0.0.1/<dbname>"
elif mysql -u root -proot -e "SELECT 1" &>/dev/null; then
  echo "DB: mysql://root:root@127.0.0.1/<dbname>"
else
  echo "DB: unknown"
fi
if [ -n "$DXPR_API_KEY" ]; then
  echo "KEY: env"
elif [ -f .env ] && grep -q 'DXPR_API_KEY' .env 2>/dev/null; then
  echo "KEY: dotenv"
elif [ -f CLAUDE.md ] && grep -q 'eyJ' CLAUDE.md 2>/dev/null; then
  echo "KEY: claude-md"
else
  echo "KEY: not-found"
fi
SITES=$(ls -d web/sites/*/settings.php 2>/dev/null | sed 's|web/sites/||;s|/settings.php||' | tr '\n' ',' | sed 's/,$//')
echo "SITES: ${SITES:-none}"
```

## Workflow: Gather First, Execute Later

**CRITICAL: Do NOT run any slow commands (git clone, composer install, drush site:install) until all questions are answered and the user confirms the plan.**

### Phase 1: Gather inputs

**Use the AskUserQuestion tool** for interactive prompts — never dump questions as plain text. Batch related questions into a single AskUserQuestion call. Skip questions the preamble or user request already answered.

1. **Codebase location** — If "no-project": confirm target directory (infer from request)
2. **Environment** — If preamble shows `DB: ddev` (already inside a DDEV project), use DDEV — skip this question. If preamble shows `DDEV: not-installed`, use Valet/native — skip this question. Otherwise (DDEV is installed but this isn't a DDEV project), ask: "Use DDEV or Valet/native for this site?" DDEV auto-configures the database and uses `ddev exec` / `ddev drush` prefixes. Valet/native uses direct commands and requires a `--db-url`.
3. **Languages** — First, fetch the list of Drupal-supported languages by running:
   ```bash
   php -r "
   require 'web/core/lib/Drupal/Core/DependencyInjection/DependencySerializationTrait.php';
   require 'web/core/lib/Drupal/Core/Language/LanguageInterface.php';
   require 'web/core/lib/Drupal/Core/Language/Language.php';
   require 'web/core/lib/Drupal/Core/Language/LanguageManagerInterface.php';
   require 'web/core/lib/Drupal/Core/Language/LanguageManager.php';
   \\\$list = \\Drupal\\Core\\Language\\LanguageManager::getStandardLanguageList();
   foreach(\\\$list as \\\$code => \\\$info) { echo \\\$code . '|' . \\\$info[0] . \"\\n\"; }
   "
   ```
   For from-scratch installs where the codebase doesn't exist yet, run this after `git clone` but before asking the language question.
   Then ask the user which languages they want. Present common ones (English, Dutch, German, French, Spanish, Arabic, Chinese Simplified, Portuguese, Japanese) but accept any from the full list. The user can type names or codes. **Validate every language the user provides against the fetched list** — if something doesn't match (typo, unsupported), show the closest matches and ask them to clarify. The **first** language becomes the `--locale` (default site UI language). Any additional languages become `additional_languages` arguments. **If the user picks more than one language, silently add the Multilingual recipe — never ask the user about it separately.**
4. **Recipes** — Which optional add-ons? **Do NOT list Multilingual here** — it is handled automatically by the language question above. Present as multi-select:
   - Case Studies — portfolio/client work showcase
   - Events — event listings with dates, locations, maps
   - Forms — contact forms and webforms
   - Google Analytics — GA4 tracking via Google Tag Manager
   - News — news articles and listings
   - SEO Tools — sitemap, meta tags, SEO checklist
5. **API key** — If preamble found one, confirm it. If not, ask the user to paste the key directly (do NOT use a two-step "Do you have one?" → "Paste it" flow — just ask them to paste it or type "skip"). The key is required for DXPR Builder to function.
6. **Site name** — Default to directory name. Confirm or ask.
7. **Admin account** — Username (default: admin), email (default: admin@example.com), password.
8. **Site email** — The "From:" address for site-generated emails. Default: same as admin email.
9. **Timezone** — Default: auto-detect from system. Common: Europe/Amsterdam, America/New_York, etc.
10. **Database** — If DDEV: auto (skip this question). If Valet/native: use preamble DB credentials, default dbname = directory name with hyphens→underscores.
11. **Multisite** — Only if preamble SITES is not "none". Ask: multisite or separate codebase?

### Phase 2: Present plan for confirmation

Always show languages as full name + code, e.g. "French (fr)", "Chinese Simplified (zh-hans)" — never bare codes.

```
I'll set up DXPR CMS at ~/www/dxpr-cms-test1/:

  1. Clone repo + composer install (~2-3 min)
  2. Create database: dxpr_cms_test1
  3. Install with:
     - Default language: Dutch (nl)
     - Recipes: News, Events, Multilingual
     - Additional languages: German (de), French (fr)
     - API key: from CLAUDE.md
     - Site name: "My Site"
     - Admin: admin / admin@example.com
     - Timezone: Europe/Amsterdam
     - URL: http://dxpr-cms-test1.test

Proceed?
```

### Phase 3: Execute (only after confirmation)

#### Step 1: Bootstrap codebase (if STATUS was no-project)

**DDEV path:**
```bash
mkdir <site-name> && cd <site-name>
git clone https://github.com/dxpr/dxpr_cms.git .
ddev config --project-type=drupal11 --database=mariadb:11.4 --docroot=web
ddev start
ddev composer install
```

**Valet/native path:**
```bash
mkdir <site-name> && cd <site-name>
git clone https://github.com/dxpr/dxpr_cms.git .
composer install
```

**Important:** `composer install` can take 5-15 minutes. Use the **maximum timeout (600000ms)** for the Bash tool call. If it fails with a memory error, retry with `php -d memory_limit=2G $(which composer) install`.

#### Step 2: Create database (Valet/native only, DDEV auto-creates)

```bash
mysql -u root -padmin -e "CREATE DATABASE <dbname>"
```

#### Step 3: Install

Construct the drush command with form keys. Use `ddev drush` for DDEV or `vendor/bin/drush` for Valet/native. DDEV does not need `--db-url`.

**DDEV:**
```bash
ddev drush site:install dxpr_cms_installer \
  --yes \
  --locale='<langcode>' \
  --site-name="<name>" \
  --account-name="<username>" \
  --account-mail="<email>" \
  --account-pass="<password>" \
  --site-mail="<site-email>" \
  "installer_recipes_form.add_ons=<pipe-separated-recipes>" \
  "dxpr_cms_installer_keys.dxpr_key=<jwt>" \
  "dxpr_cms_installer_multilingual_configuration.additional_languages.<code>=<code>" \
  "install_configure_form.date_default_timezone=<timezone>"
```

**Valet/native:**
```bash
vendor/bin/drush site:install dxpr_cms_installer \
  --yes \
  --locale='<langcode>' \
  --site-name="<name>" \
  --account-name="<username>" \
  --account-mail="<email>" \
  --account-pass="<password>" \
  --site-mail="<site-email>" \
  --db-url="<url>" \
  "installer_recipes_form.add_ons=<pipe-separated-recipes>" \
  "dxpr_cms_installer_keys.dxpr_key=<jwt>" \
  "dxpr_cms_installer_multilingual_configuration.additional_languages.<code>=<code>" \
  "install_configure_form.date_default_timezone=<timezone>"
```

**Important:** `drush site:install` can take 10-30 minutes (especially with multilingual + recipes). Use the **maximum timeout (600000ms)** for the Bash tool call. If drush hits a memory limit, prefix with `php -d memory_limit=2G`.

Notes on drush form keys:
- **Omit form key arguments that are empty.** If no recipes were selected, do NOT pass `installer_recipes_form.add_ons` at all. If no additional languages, do NOT pass `additional_languages`. An empty value causes errors.
- Recipes are pipe-separated: `"installer_recipes_form.add_ons=Case Studies|Events|News"` or `*` for all
- Each additional language needs its own arg: `"...additional_languages.nl=nl"` `"...additional_languages.de=de"`
- Timezone uses Olson format: `"install_configure_form.date_default_timezone=Europe/Amsterdam"`
- Site mail: `"install_configure_form.site_mail=info@example.com"`

#### Step 4: Post-install verification

```bash
drush status --format=json
drush pml --status=enabled --no-core --format=json
```

#### Step 5: Install AI skill files (if available)

These drush commands are provided by DXPR Builder, DXPR Theme, and the
Webmaster module. They may not exist yet — only run the ones that are available:

```bash
drush dxt:setup-ai 2>/dev/null    # Theme CLI skill (if dxpr_theme provides it)
drush dxb:setup-ai 2>/dev/null    # Builder CLI skill (if dxpr_builder provides it)
drush wm:setup-ai  2>/dev/null    # Webmaster CLI skill (if webmaster provides it)
```

If a command fails with "not found", skip it — it means the module does not yet provide that command.

## Complete Options Reference

### drush site:install form keys

| Form key | Maps to |
|---|---|
| `installer_recipes_form.add_ons` | Recipe selection (pipe-separated or `*`) |
| `dxpr_cms_installer_keys.dxpr_key` | DXPR API key |
| `dxpr_cms_installer_multilingual_configuration.additional_languages.<code>` | Additional language (one per lang) |
| `install_configure_form.date_default_timezone` | Timezone |
| `install_configure_form.site_mail` | Site email |
| `install_configure_form.enable_update_status_module` | Update checking (0/1) |
| `install_configure_form.enable_update_status_emails` | Update emails (0/1) |

Drush native options: `--locale`, `--site-name`, `--account-name`, `--account-mail`, `--account-pass`, `--db-url`.

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

Multilingual is never shown to the user as a recipe choice. It is auto-added whenever more than one language is selected.

## Environment

| Preamble signal | Action |
|---|---|
| `STATUS: no-project` | Clone repo + composer install |
| `STATUS: needs-composer-install` | Run composer install |
| `STATUS: ready` | Use drush site:install with form keys |
| `DDEV: installed` | DDEV is available — ask user whether to use it |
| `DDEV: not-installed` | Use Valet/native (don't offer DDEV) |
| `DB: ddev` | Already a DDEV project — use DDEV automatically |
| `DB: mysql://root:admin@...` | Use discovered credentials |
| `KEY: claude-md` | Extract JWT from CLAUDE.md |
| `SITES: none` | Fresh install |
| `SITES: default,site2` | Offer multisite |

## API Key

Free at https://app.dxpr.com/getting-started — required for DXPR Builder to function. Also enables AI features (OpenAI, Claude, Gemini, MistralAI, XAI, Perplexity).
