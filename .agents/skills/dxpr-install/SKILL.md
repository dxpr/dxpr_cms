# DXPR CMS Installer

Install DXPR CMS via `drush site:install` with the correct form key parameters.

## Quick Start

```bash
# Basic install with API key
vendor/bin/drush site:install dxpr_cms_installer \
  dxpr_cms_installer_keys.dxpr_key='YOUR_DXPR_API_KEY' -y

# All recipes
vendor/bin/drush site:install dxpr_cms_installer \
  "installer_recipes_form.add_ons=*" \
  dxpr_cms_installer_keys.dxpr_key='YOUR_DXPR_API_KEY' -y

# Specific recipes + languages
vendor/bin/drush site:install dxpr_cms_installer \
  --locale='nl' \
  --site-name="My Site" \
  "installer_recipes_form.add_ons=News|Events|Multilingual" \
  "dxpr_cms_installer_multilingual_configuration.additional_languages.de=de" \
  "dxpr_cms_installer_multilingual_configuration.additional_languages.fr=fr" \
  dxpr_cms_installer_keys.dxpr_key='YOUR_DXPR_API_KEY' -y
```

## drush site:install Options

| Option | Description | Default |
|---|---|---|
| `--locale` | Default site language (ISO code) | en |
| `--site-name` | Site name | "DXPR CMS" |
| `--account-name` | Admin username | admin |
| `--account-mail` | Admin email | admin@example.com |
| `--account-pass` | Admin password | auto-generated |
| `--db-url` | Database URL | auto in DDEV |

## Form Key Parameters

| Form key | Description |
|---|---|
| `installer_recipes_form.add_ons` | Recipe selection (pipe-separated or `*` for all) |
| `dxpr_cms_installer_keys.dxpr_key` | DXPR API key (JWT) |
| `dxpr_cms_installer_multilingual_configuration.additional_languages.<code>` | Additional language (one per lang) |
| `install_configure_form.date_default_timezone` | Timezone (Olson format) |
| `install_configure_form.site_mail` | Site "From:" email |

Available recipes: Case Studies, Events, Forms, Google Analytics, News, SEO Tools, Multilingual.

DDEV auto-detects database. Valet/native requires `--db-url`.

Get API key: https://app.dxpr.com/getting-started
