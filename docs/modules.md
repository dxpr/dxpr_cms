# List of modules used in DXPR CMS

DXPR CMS comes pre-configured with a comprehensive collection of Drupal modules to provide a complete content management and website building experience. Here are the key modules included:

## AI & Content Intelligence

- **[ai](https://drupal.org/project/ai)** - Core AI framework providing infrastructure for AI integrations including content generation and analysis
- **[ai_agents](https://drupal.org/project/ai_agents)** - AI agents framework enabling autonomous AI assistants for content creation and management tasks
- **[ai_checklist](https://drupal.org/project/ai_checklist)** - Interactive AI-powered checklists for guiding content creation and ensuring quality standards
- **[ai_content_strategy](https://drupal.org/project/ai_content_strategy)** - AI-powered content strategy tools for planning, optimizing and analyzing content effectiveness and engagement
- **[ai_social_posts](https://drupal.org/project/ai_social_posts)** - AI-powered social media post generation for content promotion
- **ai_social_posts_linkedin** - LinkedIn-specific AI social post generation capabilities with specialized formatting and engagement optimization (submodule of [ai_social_posts](https://drupal.org/project/ai_social_posts))
- **ai_social_posts_linkedin_article** - Generate LinkedIn article posts using AI for long-form professional content (submodule of [ai_social_posts](https://drupal.org/project/ai_social_posts))
- **[rl_sorting](https://drupal.org/project/rl_sorting)** - Reinforcement learning content sorting and prioritization based on relevance and engagement potential
- **[ai_image_alt_text](https://drupal.org/project/ai_image_alt_text)** - Automatically generates accessible alt text for images using AI image recognition and analysis
- **[ai_provider_dxpr](https://drupal.org/project/ai_provider_dxpr)** - DXPR AI provider integration offering free access to multiple AI models including OpenAI, Claude, Gemini, and others through DXPR's unified API
- **[analyze_ai_brand_voice](https://drupal.org/project/analyze_ai_brand_voice)** - AI-powered brand voice analysis ensuring content consistency with organizational tone and messaging guidelines
- **[analyze_ai_content_marketing_audit](https://drupal.org/project/analyze_ai_content_marketing_audit)** - AI-powered content marketing audit analyzing effectiveness, engagement potential, and optimization opportunities
- **[analyze_ai_content_security_audit](https://drupal.org/project/analyze_ai_content_security_audit)** - AI-powered security audit scanning content for potential security vulnerabilities and compliance issues
- **[analyze_ai_sentiments](https://drupal.org/project/analyze_ai_sentiments)** - Sentiments analysis using AI to evaluate emotional tone and reader perception of content
- **[analyze](https://drupal.org/project/analyze)** - Core framework for content analysis providing extensible analysis capabilities
- **analyze_basic_content_info** - Basic content information analysis including readability, word count, and structure metrics (submodule of [analyze](https://drupal.org/project/analyze))
- **analyze_page_views** - Page view analytics integration for analyzing content performance and engagement (submodule of [analyze](https://drupal.org/project/analyze))
- **[ckeditor_ai_agent](https://drupal.org/project/ckeditor_ai_agent)** - AI assistant integrated into CKEditor for real-time writing suggestions and content improvements
- **[diff](https://drupal.org/project/diff)** - Shows differences between content revisions with visual comparison tools
- **[key](https://drupal.org/project/key)** - Key management framework for securely storing API keys and credentials
- **[markdownify](https://drupal.org/project/markdownify)** - Converts HTML content to Markdown format for easier information exchange with AI models
- **markdownify_path** - Provides path-based Markdown conversion capabilities for specific content routes (submodule of [markdownify](https://drupal.org/project/markdownify))
- **[rl](https://drupal.org/project/rl)** - Reinforcement Learning module providing machine learning capabilities for content optimization

## Administration & UI

- **[admin_toolbar](https://drupal.org/project/admin_toolbar)** - Enhanced admin toolbar with dropdown menus providing quick access to administrative functions and improved navigation
- **[block_classes](https://drupal.org/project/block_classes)** - Allows adding custom CSS classes to blocks for enhanced styling control
- **[checklistapi](https://drupal.org/project/checklistapi)** - API for creating interactive checklists and progress tracking interfaces
- **[dashboard](https://drupal.org/project/dashboard)** - Customizable dashboard system allowing personalized admin interfaces with widgets and quick access links
- **[gin_lb](https://drupal.org/project/gin_lb)** - Gin Layout Builder integration providing modern UI for layout building
- **[gin_toolbar](https://drupal.org/project/gin_toolbar)** - Enhanced toolbar integration for Gin admin theme with improved navigation and user experience
- **[sam](https://drupal.org/project/sam)** - Simple Add More - Simplifies multi-value form widgets by hiding empty fields and providing "Add another" buttons
- **[smart_trim](https://drupal.org/project/smart_trim)** - Intelligent text trimming with configurable options for teaser displays
- **[tagify](https://drupal.org/project/tagify)** - Modern tag input widget transforming text fields into user-friendly tag selection interfaces
- **tagify_user_list** - User selection widget using Tagify for improved user reference fields (submodule of [tagify](https://drupal.org/project/tagify))

## Content Management

- **[add_content_by_bundle](https://drupal.org/project/add_content_by_bundle)** - Enhanced content creation interface displaying content types as cards with icons and descriptions
- **[autosave_form](https://drupal.org/project/autosave_form)** - Automatically saves form progress preventing data loss during content creation and editing sessions
- **[scheduler](https://drupal.org/project/scheduler)** - Schedule content publishing and unpublishing at specific dates and times for workflow automation
- **[scheduler_content_moderation_integration](https://drupal.org/project/scheduler_content_moderation_integration)** - Integrates Scheduler with Content Moderation enabling scheduled state transitions in editorial workflows
- **[trash](https://drupal.org/project/trash)** - Soft delete functionality allowing content recovery before permanent deletion with trash/recycle bin

## Page Building & Layout

- **[dxpr_builder](https://drupal.org/project/dxpr_builder)** - Advanced drag-and-drop page builder for creating complex layouts without coding knowledge required
- **[dxpr_cms_layouts](https://drupal.org/project/dxpr_cms_layouts)** - Custom layouts and layout options for DXPR CMS content types
- **[dxpr_theme_helper](https://drupal.org/project/dxpr_theme_helper)** - Helper module providing additional theme functionality and integration features for DXPR themes
- **[field_group](https://drupal.org/project/field_group)** - Groups form fields into tabs, accordions, and fieldsets for better content organization
- **[layout_builder_component_attributes](https://drupal.org/project/layout_builder_component_attributes)** - Add custom attributes to Layout Builder components for enhanced styling and behavior
- **[layout_disable](https://drupal.org/project/layout_disable)** - Control which content types can use Layout Builder with granular permissions
- **[section_library](https://drupal.org/project/section_library)** - Reusable layout sections library for consistent design patterns across pages and content
- **[views_bootstrap](https://drupal.org/project/views_bootstrap)** - Bootstrap components for Views including grids, cards, and responsive layouts
- **[views_color_scales](https://drupal.org/project/views_color_scales)** - Color-coded visualization scales for Views displays based on data values

## Media & Images

- **[focal_point](https://drupal.org/project/focal_point)** - Set focal points on images ensuring important areas remain visible when cropped responsively
- **[media_library_form_element](https://drupal.org/project/media_library_form_element)** - Form element providing media library integration for easier media selection in forms
- **[svg_image](https://drupal.org/project/svg_image)** - SVG image support allowing scalable vector graphics upload and display with security filtering

## Search & Discovery

- **[better_exposed_filters](https://drupal.org/project/better_exposed_filters)** - Enhanced exposed filters interface with better UX including select all and dropdown options
- **[selective_better_exposed_filters](https://drupal.org/project/selective_better_exposed_filters)** - Apply Better Exposed Filters selectively to specific filters maintaining standard UI for others
- **[simple_search_form](https://drupal.org/project/simple_search_form)** - Lightweight search form block providing basic search functionality without complex configuration requirements

## SEO & Analytics

- **[easy_breadcrumb](https://drupal.org/project/easy_breadcrumb)** - Automatic breadcrumb generation based on URL paths improving navigation and SEO structure
- **[google_tag](https://drupal.org/project/google_tag)** - Google Tag Manager integration for managing analytics, marketing, and measurement tags centrally
- **[hreflang](https://drupal.org/project/hreflang)** - Automatic hreflang tag generation for multilingual SEO helping search engines understand language variations
- **[metatag](https://drupal.org/project/metatag)** - Comprehensive meta tag management for SEO including Open Graph and Twitter Card support
- **[pathauto](https://drupal.org/project/pathauto)** - Automatically generates SEO-friendly URL aliases based on configurable patterns for all content
- **[redirect](https://drupal.org/project/redirect)** - Manages URL redirects preventing 404 errors and maintaining SEO value during site changes
- **[robotstxt](https://drupal.org/project/robotstxt)** - Manage robots.txt file through Drupal admin interface controlling search engine crawler access
- **[seo_checklist](https://drupal.org/project/seo_checklist)** - SEO checklist tracking tool ensuring all important SEO tasks and configurations are completed
- **[simple_sitemap](https://drupal.org/project/simple_sitemap)** - XML sitemap generation following sitemap protocol for better search engine content discovery
- **[sitemap](https://drupal.org/project/sitemap)** - Provides a human-readable HTML sitemap page for visitors to navigate site structure
- **[statistics](https://www.drupal.org/project/statistics)** - Sovereign privacy-first anonymous web analytics tracking content views and user activity

## Security & Spam Prevention

- **[captcha](https://drupal.org/project/captcha)** - CAPTCHA framework providing various challenge types to prevent automated spam form submissions
- **[friendlycaptcha](https://drupal.org/project/friendlycaptcha)** - Privacy-friendly CAPTCHA alternative that doesn't track users while preventing bot submissions effectively
- **[friendly_captcha_challenge](https://drupal.org/project/friendly_captcha_challenge)** - Challenge provider for Friendly Captcha implementing cryptographic puzzles instead of image recognition
- **[honeypot](https://drupal.org/project/honeypot)** - Hidden form field spam prevention technique catching bots without impacting legitimate user experience
- **[login_emailusername](https://drupal.org/project/login_emailusername)** - Allow users to login using either their username or email address for convenience

## Forms & User Input

- **[contact_block](https://drupal.org/project/contact_block)** - Provides contact form blocks that can be placed anywhere using the block system
- **[webform](https://drupal.org/project/webform)** - Comprehensive form builder for creating complex forms, surveys, and applications with submissions

## Location & Maps

- **[address](https://drupal.org/project/address)** - International address field with country-specific formatting and validation for global compatibility
- **[addtocal_augment](https://drupal.org/project/addtocal_augment)** - Add to calendar functionality allowing users to save events to their personal calendars
- **[geocoder](https://drupal.org/project/geocoder)** - Geocoding framework converting addresses to coordinates using various geocoding provider services
- **[geofield](https://drupal.org/project/geofield)** - Store geographic data including points, lines, and polygons with spatial query support
- **[leaflet](https://drupal.org/project/leaflet)** - Interactive map display using Leaflet library for lightweight and mobile-friendly mapping solutions
- **[smart_date](https://drupal.org/project/smart_date)** - Intelligent date field handling recurring dates, all-day events, and timezone conversions elegantly

## Multilingual & Translation

- **[tmgmt](https://drupal.org/project/tmgmt)** - Translation Management Tool for managing translation workflows with translator assignment and review
- **[tmgmt_google](https://drupal.org/project/tmgmt_google)** - Translation provider integration for TMGMT with DXPR AI Platform providing automated translation with post-editing capabilities

## Performance

- **[minifyhtml](https://drupal.org/project/minifyhtml)** - HTML minification removing unnecessary whitespace and comments to reduce page size
- **[speculative_loading](https://drupal.org/project/speculative_loading)** - Preloads links and resources based on user interaction patterns to improve perceived performance

## Privacy & Compliance

- **[klaro](https://drupal.org/project/klaro)** - Privacy-focused consent manager for GDPR compliance with customizable cookie consent interface

## Utility & Helper Modules

- **[automatic_updates](https://drupal.org/project/automatic_updates)** - Automated security updates for Drupal core and contrib modules keeping sites secure
- **[bpmn_io](https://drupal.org/project/bpmn_io)** - BPMN diagram viewer and editor for ECA workflows enabling visual workflow design
- **[eca](https://drupal.org/project/eca)** - Event-Condition-Action framework for creating automated workflows without custom code requirements
- **eca_content** - ECA plugins for content-related events and actions (submodule of [eca](https://drupal.org/project/eca))
- **eca_misc** - Miscellaneous ECA plugins for various workflow automation tasks (submodule of [eca](https://drupal.org/project/eca))
- **eca_render** - ECA plugins for rendering and display-related workflows (submodule of [eca](https://drupal.org/project/eca))
- **[linkit](https://drupal.org/project/linkit)** - Enhanced linking interface with autocomplete for internal content and media references
- **[menu_link_attributes](https://drupal.org/project/menu_link_attributes)** - Add custom attributes to menu links including classes, target, and rel attributes
- **[token](https://drupal.org/project/token)** - Token system providing placeholders for dynamic content replacement throughout the site
- **[token_or](https://drupal.org/project/token_or)** - Token OR logic allowing fallback tokens when primary tokens have no value

---

*This comprehensive list includes all modules pre-configured in DXPR CMS, organized by functionality to help you understand the platform's capabilities.*

*Last updated: September 2025*
