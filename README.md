# DXPR CMS

DXPR CMS is a powerful and enhanced version of Drupal 10, incorporating some
of the best modules and themes available. It is designed to help you quickly
set up and start building your site. Unlike traditional distributions, DXPR
CMS utilizes the Drupal recipe system, ensuring flexibility and ease of
customization.

## Getting started with DXPR CMS

Follow these steps to install DXPR CMS:

1. Install [DDEV](https://ddev.com/) if you haven't already.
2. Clone the DXPR CMS repository:
   ```bash
   git clone https://github.com/dxpr/dxpr_cms.git
   ```
3. Navigate to the project directory:
   ```bash
   cd dxpr_cms
   ```
4. Configure and start the DDEV environment:
   ```bash
   ddev config --project-type=drupal11 --database=mariadb:11.4 --docroot=web
   ddev start
   ddev composer install
   ```
5. Complete the installation using one of these methods:

   **Option A: Web-based installation**
   Open your browser and navigate to `http://dxpr-cms.ddev.site` or
   `https://dxpr-cms.ddev.site` to access the Drupal installation wizard.
   During installation, you can select optional recipes (Case Studies,
   Events, Forms, Analytics, News, SEO Tools) to customize your site.

   **Option B: Interactive TUI wizard**
   Run the interactive installer which guides you through each step:
   ```bash
   ddev exec bin/dxpr-install
   ```
   The wizard prompts for recipe selection, site name, API key, languages,
   and admin credentials — the same steps as the web-based installer.

   **Option C: Non-interactive CLI installation**
   For automated or scripted installations with all options on one line:
   ```bash
   ddev exec bin/dxpr-install \
     --api-key='YOUR_DXPR_API_KEY' \
     --all-recipes \
     --site-name='My Site' \
     --admin-pass='admin' \
     --no-interaction
   ```

   With specific recipes and languages:
   ```bash
   ddev exec bin/dxpr-install \
     --api-key='YOUR_DXPR_API_KEY' \
     --recipes="News,Events,Multilingual" \
     --languages="nl,de,fr" \
     --site-name='My Site' \
     --no-interaction
   ```

   Run `bin/dxpr-install --help` for all available options.

   Replace `YOUR_DXPR_API_KEY` with your actual DXPR API key from
   [app.dxpr.com/getting-started](https://app.dxpr.com/getting-started).

   **Option D: Advanced — raw drush site-install**
   For direct control over the installation process, you can use drush
   with form key parameters:
   ```bash
   ddev drush site-install dxpr_cms_installer \
     "installer_recipes_form.add_ons=Case Studies|Events|Forms|Google Analytics|News|SEO Tools|Multilingual" \
     site_name='My Site' \
     dxpr_cms_installer_keys.dxpr_key='YOUR_DXPR_API_KEY' -y
   ```

   Available recipes: `Case Studies`, `Events`, `Forms`, `Google Analytics`,
   `News`, `SEO Tools`, `Multilingual`. Use `*` for all recipes.

   **Installing optional recipes after installation**
   If you want to add recipes later, install them manually:
   ```bash
   ddev drush recipe ../recipes/dxpr_cms_case_study
   ddev drush recipe ../recipes/dxpr_cms_events
   ddev drush recipe ../recipes/dxpr_cms_forms
   ddev drush recipe ../recipes/dxpr_cms_news
   ddev drush recipe ../recipes/dxpr_cms_multilingual
   ddev drush recipe ../recipes/dxpr_cms_google_analytics
   ddev drush recipe ../recipes/dxpr_cms_seo_tools
   ```

## Future-proof your digital strategy with scalable tools for efficient content management

Welcome to DXPR CMS, a dynamic Drupal distribution that simplifies complex content management challenges. Designed to enhance productivity, ensure data security, and leverage the potential of AI, this platform integrates tools like [DXPR Builder](https://www.drupal.org/project/dxpr_builder) and [DXPR Theme](https://www.drupal.org/project/dxpr_theme), alongside cutting-edge AI modules to empower users, streamline workflows, and reduce operational costs.

## Key features of DXPR CMS

### DXPR Builder: empowering content management

[DXPR Builder](https://www.drupal.org/project/dxpr_builder) is an intuitive drag-and-drop page builder that allows users of all technical levels to create engaging, mobile-responsive web pages effortlessly. While it does not yet integrate AI features, it remains a key part of our CMS, streamlining content creation and management with minimal backend involvement.

### DXPR Theme: simplifying customization

The [DXPR Theme](https://www.drupal.org/project/dxpr_theme) offers a visual interface that enables developers to swiftly align site design with organizational branding guidelines. Although it doesn't yet feature AI integrations, it minimizes time spent on styling and enhances site functionality, promoting consistent brand identity across digital assets.

### AI-first features: a new approach to content management

DXPR CMS distinguishes itself by offering AI-powered assistance directly
within the user interface. Unlike other platforms that rely on cramped
sidebars or popup tools, we make AI a central part of the content creation
experience. Our AI modules enhance productivity and usability without
distraction.

## AI-enhanced content management modules

### AI Image Alt Text

The [AI Image Alt Text](https://www.drupal.org/project/ai_image_alt_text)
module automatically generates alt texts for images using AI vision models.
Integrated with image widgets, it exposes a button for authorized users to
generate alt texts, supporting alt text creation in the language of the
entity.

### AI Views Sorting (coming soon)

The [AI Views Sorting](https://www.drupal.org/project/ai_sorting) module
introduces an AI-based sorting plugin for Drupal Views. Using the UCB2
algorithm, it ranks and displays content dynamically based on user engagement
metrics. This feature will be a powerful tool for improving content relevance
and discovery over time.

### Analyze Module (coming soon)

The [Analyze Module](https://www.drupal.org/project/analyze) provides an API
framework for adding content-related information to Drupal entities. Its
"Analyze" tab serves as a unified interface where other modules can display
their analysis results.

### AI Security Analyze (coming soon)

AI-powered security analysis helps detect in-content security threats such
as malicious links or PII disclosure. By scanning content in real-time, this
module mitigates risks during the content creation process and enhances
overall security.

### AI Sentiments Analysis (coming soon)

The AI Sentiments Analysis feature provides real-time content analysis to
identify emotional tones such as joy, anger, or neutrality. This will allow
content editors to gauge the emotional impact of their content and make
adjustments accordingly.

### AI CKEditor Assistant (coming soon)

The AI CKEditor Assistant will introduce generative AI capabilities into
CKEditor via a slash command interface. Users will be able to prompt the AI
to generate content, perform corrections, or provide enhancements—all within
the CKEditor environment. This tool will make content creation faster and
smarter.

## Technical advantages

### Developer and site builder tools

DXPR CMS is equipped with a comprehensive suite of tools that enhance site
functionality and allow for deep integration with existing systems. Extensive
API support and customizable modules facilitate adoption and provide
continuous technical support. With upcoming AI modules, developers and site
builders will find it even easier to configure and optimize AI-driven
functionalities.

### Enhanced security

Our platform incorporates robust security protocols from the start, using
built-in modules and best practices to safeguard against emerging threats.
The AI security analysis feature adds an extra layer of protection,
identifying risks in real-time and ensuring your content remains secure and
compliant.

### Multilingual and localization support

DXPR CMS excels in managing multilingual content, including complex scenarios
involving right-to-left (RTL) languages. Features include:

- **Full RTL language support:** Ensures that text, layout, and user
  interfaces adapt seamlessly for RTL languages.
- **Sophisticated language navigation tools:** Enables easy switching between
  languages while maintaining user context.
- **Localized user experiences:** Automatically adjusts date formats,
  currency, and form validations to match local preferences.
- **Integration with leading translation services:** Supports both automated
  and manual translation workflows to ensure content accuracy and relevance.

## Community and support

Join the DXPR CMS community to connect with developers, share insights, and
collaborate on projects. Our forums are an invaluable resource for support
and staying updated with the latest developments.

## Case studies and success stories

Explore how diverse organizations utilize DXPR CMS to enhance their online
presence. Our case studies demonstrate the practical applications and
significant impacts of our distribution on digital strategies.

## Innovative Contributions to Drupal

### Pushing Boundaries in Drupal

DXPR CMS integrates open-source flexibility with enhanced features through
our [open core](https://en.wikipedia.org/wiki/Open-core_model) 
[DXPR Builder](https://www.drupal.org/project/dxpr_builder). While DXPR
Builder and DXPR Theme do not yet have AI capabilities, DXPR is actively
contributing to Drupal's innovation with a suite of AI-first modules. These
include AI Image Alt Text, AI Views Sorting, AI Sentiments Analysis, AI
Security Analyze, and the upcoming AI CKEditor Assistant, among others.

### Community Collaboration

**Strengthening Drupal Together:**  
We invite the Drupal community to collaborate on DXPR CMS, enhancing both our
platform and the broader ecosystem. By contributing to the open-source core
and sharing advancements, we empower users and developers to join us in
shaping a forward-thinking digital landscape.
