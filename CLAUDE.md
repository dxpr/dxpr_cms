# DXPR CMS Project Documentation

## Overview

DXPR CMS is a Drupal-based marketing CMS distribution featuring the DXPR Builder no-code page builder. This project uses a sophisticated development setup with DDEV, Drupal recipes, and dynamic composer.json generation.

## Project Structure

### Key Directories

- **`/recipes/`** - Contains Drupal recipes for modular CMS features
  - Each recipe has its own `composer.json` and `recipe.yml`
  - Examples: `dxpr_cms_blog`, `dxpr_cms_events`, `dxpr_cms_multilingual`
  
- **`/project_template/`** - The base Drupal project template
  - Contains the production `composer.json` for end-users
  - Includes web/profiles directory for the installer profile
  
- **`/web/`** - Drupal docroot (standard Drupal structure)
  - Core Drupal files and directories
  - Modules, themes, and profiles directories
  
- **`/.ddev/`** - DDEV configuration and custom commands
  - Custom commands in `.ddev/commands/`
  - Configuration in `.ddev/config.yaml`

### Development Files

- **`dev.composer.json`** - Development dependencies and local path repositories
- **`patches/`** - Custom patches directory
- **`docs/`** - Developer documentation

## Composer.json Build Process

### ⚠️ IMPORTANT: Dynamic composer.json Generation

**The root `composer.json` is dynamically generated and should NEVER be edited directly!**

- `composer.json` is rebuilt every time you run `ddev rebuild`
- It is NOT tracked in version control (gitignored)
- Changes should be made to either:
  - `project_template/composer.json` - for production dependencies
  - `dev.composer.json` - for development dependencies

### How It Works

1. **During `ddev start` or `ddev rebuild`:**
   - The `generate-composer-json` script runs automatically
   - It merges `project_template/composer.json` with `dev.composer.json`
   - The result is written to the root `composer.json`

2. **The merge process:**
   - Base: `project_template/composer.json` (production template)
   - Override: `dev.composer.json` (development additions)
   - Local path repositories are added for all recipes
   - Repository priorities are adjusted

3. **Location of the generator script:**
   - `.ddev/homeadditions/bin/generate-composer-json`

## DDEV Configuration

### Basic Settings (`.ddev/config.yaml`)

- **Name:** dxpr-cms-dev
- **Type:** Drupal
- **Docroot:** web
- **PHP Version:** 8.3
- **Database:** MariaDB 10.11
- **Webserver:** nginx-fpm

### Custom DDEV Commands

#### `ddev rebuild`
- **Location:** `.ddev/commands/host/rebuild`
- **Purpose:** Completely rebuilds the development environment
- **Actions:**
  1. Drops the database
  2. Removes vendor/, composer files, and patches.lock.json
  3. Restarts DDEV (triggering composer.json regeneration)

#### `ddev list-modules`
- **Location:** `.ddev/commands/web/list-modules`
- Lists Drupal modules

#### `ddev tag`
- **Location:** `.ddev/commands/web/tag`
- Tagging functionality

#### `ddev test`
- **Location:** `.ddev/commands/web/test`
- Test runner command

### Post-start Hooks

The DDEV configuration includes important post-start hooks:

1. **Composer.json generation and installation:**
   ```bash
   test -f composer.lock || (generate-composer-json > composer.json && composer install)
   ```

2. **Profile symlink creation:**
   - Creates a relative symlink from `project_template/web/profiles/dxpr_cms_installer` 
   - To `web/profiles/dxpr_cms_installer`
   - Required for the installer profile to work correctly

## Drupal Recipes

The project uses Drupal Recipes for modular functionality. Each recipe in `/recipes/` contains:

- `composer.json` - Recipe dependencies
- `recipe.yml` - Recipe definition
- `config/` - Configuration to be imported
- `content/` - Default content (if applicable)
- `tests/` - Recipe tests

### Available Recipes

- **Content Types:** blog, news, events, case_study, page
- **Features:** multilingual, search, forms, analytics
- **SEO:** seo_basic, seo_tools
- **Performance:** performance optimization
- **Theme:** dxpr_theme, dxpr_builder integration
- **Core:** starter, content_type_base, roles

## Development Workflow

1. **Initial Setup:**
   ```bash
   ddev start
   ```
   This automatically generates composer.json and installs dependencies

2. **Making Dependency Changes:**
   - For production: Edit `project_template/composer.json`
   - For development: Edit `dev.composer.json`
   - Run `ddev rebuild` to apply changes

3. **Working with Recipes:**
   - Recipes are symlinked via path repositories
   - Changes to recipe files are immediately available
   - No need to rebuild for recipe changes

4. **Database Reset:**
   ```bash
   ddev drush sql:drop -y
   ddev drush site:install dxpr_cms_installer -y
   ```

## Important Notes

- Always use `ddev` commands instead of direct composer/drush commands
- The project uses Composer 2 with source installation preference
- Cypress dependencies are included for E2E testing
- The project includes patches for various modules (see dev.composer.json)