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

API key (required for DXPR Builder to function): https://app.dxpr.com/getting-started

## Post-install: Configure API key and AI providers

After install, run this to ensure the DXPR key, AI provider, and default AI operations are fully wired up. This is idempotent and safe to re-run. Replace `YOUR_JWT` with the actual key.

```bash
vendor/bin/drush php:eval '
$jwt = "YOUR_JWT";
$key = \Drupal\key\Entity\Key::load("dxpr_builder_key");
if (!$key) {
  $key = \Drupal\key\Entity\Key::create(["id" => "dxpr_builder_key", "label" => "DXPR Builder API Key", "key_type" => "authentication", "key_provider" => "config"]);
}
$key->setKeyValue($jwt);
$key->save();
\Drupal::configFactory()->getEditable("dxpr_builder.settings")->set("api_key_storage", "key")->set("key_provider", "dxpr_builder_key")->set("json_web_token", NULL)->save();
\Drupal::configFactory()->getEditable("ckeditor_ai_agent.settings")->set("key_provider", "dxpr_builder_key")->set("model", "dxai:kavya-m1")->save();
\Drupal::configFactory()->getEditable("ai_provider_dxpr.settings")->set("api_key", "dxpr_builder_key")->save();
\Drupal::configFactory()->getEditable("ai.settings")
  ->set("default_providers.chat", ["provider_id" => "dxpr", "model_id" => "kavya-m1"])
  ->set("default_providers.chat_with_image_vision", ["provider_id" => "dxpr", "model_id" => "kavya-m1"])
  ->set("default_providers.chat_with_complex_json", ["provider_id" => "dxpr", "model_id" => "kavya-m1"])
  ->set("default_providers.chat_with_tools", ["provider_id" => "dxpr", "model_id" => "kavya-m1"])
  ->set("default_providers.chat_with_structured_response", ["provider_id" => "dxpr", "model_id" => "kavya-m1"])
  ->set("default_providers.translate_text", ["provider_id" => "dxpr", "model_id" => "kavya-m1-fast"])
  ->save();
echo "Done.\n";
'
vendor/bin/drush cr
```
