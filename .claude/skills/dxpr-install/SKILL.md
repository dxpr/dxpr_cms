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
2. **Default site language** — What language should the site UI be in? Default: English (en). Common: nl, de, fr, es, ja, ar. This sets the `--locale` and the primary admin/UI language.
3. **Recipes** — Which optional add-ons? Present as multi-select:
   - Case Studies — portfolio/client work showcase
   - Events — event listings with dates, locations, maps
   - Forms — contact forms and webforms
   - Google Analytics — GA4 tracking via Google Tag Manager
   - News — news articles and listings
   - SEO Tools — sitemap, meta tags, SEO checklist
   - Multilingual — translation management (enables additional language selection)
4. **Additional languages** — Only if Multilingual selected. Which extra languages beyond the default? Common: nl, de, fr, es, ar, ja, zh-hans, pt-br.
5. **API key** — If preamble found one, confirm. If not, ask or offer skip for dev.
6. **Site name** — Default to directory name. Confirm or ask.
7. **Admin account** — Username (default: admin), email (default: admin@example.com), password.
8. **Site email** — The "From:" address for site-generated emails. Default: same as admin email.
9. **Timezone** — Default: auto-detect from system. Common: Europe/Amsterdam, America/New_York, etc.
10. **Database** — If DDEV: auto. If Valet/native: use preamble DB credentials, default dbname = directory name with hyphens→underscores.
11. **Multisite** — Only if preamble SITES is not "none". Ask: multisite or separate codebase?

### Phase 2: Present plan for confirmation

```
I'll set up DXPR CMS at ~/www/dxpr-cms-test1/:

  1. Clone repo + composer install (~2-3 min)
  2. Create database: dxpr_cms_test1
  3. Install with:
     - Default language: Dutch (nl)
     - Recipes: News, Events, Multilingual
     - Additional languages: de, fr
     - API key: from CLAUDE.md
     - Site name: "My Site"
     - Admin: admin / admin@example.com
     - Timezone: Europe/Amsterdam
     - URL: http://dxpr-cms-test1.test

Proceed?
```

### Phase 3: Execute (only after confirmation)

#### Step 1: Bootstrap codebase (if STATUS was no-project)

```bash
mkdir <site-name> && cd <site-name>
git clone https://github.com/dxpr/dxpr_cms.git .
composer install
```

#### Step 2: Create database (Valet/native only)

```bash
mysql -u root -padmin -e "CREATE DATABASE <dbname>"
```

#### Step 3: Install

Construct the full drush command with form keys:

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

Notes on drush form keys:
- Recipes are pipe-separated: `"installer_recipes_form.add_ons=Case Studies|Events|News"` or `*` for all
- Each additional language needs its own arg: `"...additional_languages.nl=nl"` `"...additional_languages.de=de"`
- Timezone uses Olson format: `"install_configure_form.date_default_timezone=Europe/Amsterdam"`
- Site mail: `"install_configure_form.site_mail=info@example.com"`

#### Step 4: Post-install verification

```bash
drush status --format=json
drush pml --status=enabled --no-core --format=json
drush dxt:palette:get 2>/dev/null
```

#### Step 5: Install AI skill files

```bash
drush dxt:setup-ai    # Theme CLI skill
drush dxb:setup-ai    # Builder CLI skill
drush wm:setup-ai     # Webmaster CLI skill
```

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

If additional languages are specified without Multilingual, auto-add it.

## Environment

| Preamble signal | Action |
|---|---|
| `STATUS: no-project` | Clone repo + composer install |
| `STATUS: needs-composer-install` | Run composer install |
| `STATUS: ready` | Use drush site:install with form keys |
| `DB: ddev` | Auto-configured, use `ddev exec` |
| `DB: mysql://root:admin@...` | Use discovered credentials |
| `KEY: claude-md` | Extract JWT from CLAUDE.md |
| `SITES: none` | Fresh install |
| `SITES: default,site2` | Offer multisite |

## API Key

Free at https://app.dxpr.com/getting-started — unlocks AI features (OpenAI, Claude, Gemini, MistralAI, XAI, Perplexity).
