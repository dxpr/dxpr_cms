# DXPR CMS Installer CLI

Install DXPR CMS via `bin/dxpr-install`. Standalone CLI that works before Drupal is installed.

## Quick Start

```bash
# Interactive wizard
bin/dxpr-install

# Non-interactive, all recipes
bin/dxpr-install --api-key='eyJ...' --all-recipes --no-interaction

# Specific recipes + languages
bin/dxpr-install --api-key='eyJ...' --recipes="News,Multilingual" --languages="nl,de" --no-interaction

# Dry run (show drush command)
bin/dxpr-install --api-key='eyJ...' --all-recipes --dry-run
```

## Options

| Option | Description | Default |
|---|---|---|
| `--recipes` | Comma-separated names | none |
| `--all-recipes` | All 7 optional recipes | off |
| `--site-name` | Site name | "DXPR CMS" |
| `--api-key` | DXPR API key (JWT) | required |
| `--skip-api-key` | Skip key (dev) | off |
| `--languages` | ISO codes | none |
| `--admin-email` | Admin email | admin@example.com |
| `--admin-pass` | Password | auto-generated |
| `--db-url` | Database URL | auto in DDEV |
| `--multisite` | Multisite mode | off |
| `--sites-subdir` | Sites subdir | default |
| `--dry-run` | Preview only | off |

Available recipes: Case Studies, Events, Forms, Google Analytics, News, SEO Tools, Multilingual.

DDEV auto-detects database. Valet/native requires `--db-url`.

Get API key: https://app.dxpr.com/getting-started
