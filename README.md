# DXPR CMS

DXPR CMS is a Drupal distribution built on the
[Drupal recipe system](https://www.drupal.org/docs/extending-drupal/drupal-recipes).
It bundles [DXPR Builder](https://www.drupal.org/project/dxpr_builder) (drag-and-drop
page builder), [DXPR Theme](https://www.drupal.org/project/dxpr_theme), and a curated
set of modules for content management, SEO, analytics, multilingual support,
AI-assisted workflows, and HTML email.

## Getting started

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) (Docker Desktop or Colima)
- [DDEV](https://ddev.com/) v1.24.0 or later

### Installation

```bash
git clone https://github.com/dxpr/dxpr_cms.git
cd dxpr_cms
ddev start
ddev composer install
```

DDEV configuration is included in the repository, so no manual `ddev config`
step is needed.

After `composer install` finishes, complete the installation using one of
the methods below.

#### Web-based installation

Open your browser and navigate to the URL shown in the terminal output
(typically `https://dxpr-cms.ddev.site`). The installation wizard lets you
choose a site name, optional recipes, and enter your
[DXPR API key](https://app.dxpr.com/getting-started).

#### Command-line installation

```bash
ddev drush site-install dxpr_cms_installer \
  dxpr_cms_installer_keys.dxpr_key='YOUR_DXPR_API_KEY' -y
```

Replace `YOUR_DXPR_API_KEY` with your key from
[app.dxpr.com/getting-started](https://app.dxpr.com/getting-started).

To include optional recipes, add them as a pipe-separated list:

```bash
ddev drush site-install dxpr_cms_installer \
  "installer_recipes_form.add_ons=Case Studies|Events|Forms|Google Analytics|News|SEO Tools|Multilingual" \
  dxpr_cms_installer_keys.dxpr_key='YOUR_DXPR_API_KEY' -y
```

Use `"installer_recipes_form.add_ons=*"` to install all optional recipes.

#### After installation

Once installation completes, drush prints admin credentials. To access
your site:

```bash
ddev drush user-login    # Opens a one-time admin login link
ddev launch              # Opens the site homepage in your browser
```

You can also log in manually at `https://<project-name>.ddev.site/user/login`.

### Installing recipes after initial setup

```bash
ddev drush recipe ../recipes/dxpr_cms_case_study
ddev drush recipe ../recipes/dxpr_cms_events
ddev drush recipe ../recipes/dxpr_cms_forms
ddev drush recipe ../recipes/dxpr_cms_news
ddev drush recipe ../recipes/dxpr_cms_multilingual
ddev drush recipe ../recipes/dxpr_cms_google_analytics
ddev drush recipe ../recipes/dxpr_cms_seo_tools
ddev drush recipe ../recipes/easy_email_express
```

### Advanced: multilingual installation

When including the Multilingual recipe via command line, specify additional
languages with their language codes. The multilingual recipe configures translation fallback
hierarchies (e.g. regional variants fall back to their parent language)
via [language_hierarchy](https://www.drupal.org/project/language_hierarchy):

```bash
ddev drush site-install dxpr_cms_installer \
  "installer_recipes_form.add_ons=Multilingual" \
  "dxpr_cms_installer_multilingual_configuration.additional_languages.nl=nl" \
  "dxpr_cms_installer_multilingual_configuration.additional_languages.de=de" \
  "dxpr_cms_installer_multilingual_configuration.additional_languages.fr=fr" \
  "dxpr_cms_installer_multilingual_configuration.additional_languages.ar=ar" \
  dxpr_cms_installer_keys.dxpr_key='YOUR_DXPR_API_KEY' -y
```

## AI coding assistant integration

DXPR CMS includes [Agent Skills](https://agentskills.io/specification)
files that teach AI coding assistants how to install, configure, and manage
your site through natural language.

| Capability | What you can ask |
|------------|-----------------|
| **Installation** | "Install DXPR CMS with News, Events, and multilingual support" |
| **Page Building** | "Create a landing page for our spring campaign" |
| **Content Management** | "Add a testimonial content type with company and quote fields" |
| **Theme Settings** | "Set the header layout to centered and generate a warm color palette" |
| **Views & Menus** | "Create a view that shows the 5 most recent news articles" |
| **A/B Testing** | "Analyze the homepage hero experiment and show conversion rates" |
| **Translations** | "Translate node 42 to French and German" |
| **Site Management** | "Add a phone number field to the event content type" |

### AI-assisted installation

The repo includes a built-in AI skill for interactive installation. Install
[Claude Code](https://docs.anthropic.com/en/docs/claude-code), then from the
project root:

```bash
claude
```

Type "install DXPR CMS" and follow the prompts. The skill handles language
selection, recipe choices, API key entry, and more.

To use the skill from anywhere (without cloning first), install it globally:

```bash
mkdir -p ~/.claude/skills/dxpr-install && curl -sL \
  https://raw.githubusercontent.com/dxpr/dxpr_cms/1.x/.claude/skills/dxpr-install/SKILL.md \
  -o ~/.claude/skills/dxpr-install/SKILL.md
```

### Quick setup for AI assistants

After installing DXPR CMS, enable AI assistant support:

```bash
drush dxb:setup-ai    # Page builder
drush dxt:setup-ai    # Theme settings
drush rl:setup-ai     # A/B testing
drush wm:setup-ai     # Site management
```

Compatible with Claude Code, Codex CLI, Gemini CLI, GitHub Copilot,
Cursor, and other tools supporting the
[Agent Skills standard](https://agentskills.io/specification).

## Troubleshooting

### `ddev start` fails with hostname/sudo error

DDEV needs to add a hostname entry to `/etc/hosts`, which requires elevated
privileges. If `ddev start` fails:

```bash
sudo ddev hostname <project-name>.ddev.site 127.0.0.1
ddev start
```

### Port conflicts

If another DDEV project or local server is using the same ports:

```bash
ddev poweroff           # Stop all DDEV projects
ddev start
```

Or change the router ports in `.ddev/config.yaml`:

```yaml
router_http_port: "8080"
router_https_port: "8443"
```

### Docker is not running

DDEV requires Docker. If you see "docker not running" errors, start Docker
Desktop (or your Docker daemon) and retry `ddev start`.

### Composer runs out of memory

If `ddev composer install` fails with a memory error:

```bash
COMPOSER_MEMORY_LIMIT=-1 ddev composer install
```

### Block plugin warnings during installation

During `drush site-install` you may see warnings like:

```
[warning] The "field_block:node:page:field_content" block plugin was not found
```

These are expected and harmless. They occur because recipe configuration
references layout builder blocks that are registered after the configuration
is imported. The site works correctly despite these warnings.

## Contributing

Visit the [DXPR CMS project page](https://www.drupal.org/project/dxpr_cms)
on Drupal.org and the [GitHub repository](https://github.com/dxpr/dxpr_cms)
to report issues, submit patches, or contribute recipes.
