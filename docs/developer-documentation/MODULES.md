# DXPR CMS Modules Reference

This document lists all Drupal modules required by DXPR CMS recipes as defined in their composer.json files.

## Modules by Category

### AI & Content Intelligence

**ai**
- **Description**: Core AI framework providing infrastructure for AI integrations including content generation and analysis
- **Used by**: dxpr_cms_ai

**ai_agents**
- **Description**: AI agents framework enabling autonomous AI assistants for content creation and management tasks
- **Used by**: dxpr_cms_ai

**ai_content_strategy**
- **Description**: AI-powered content strategy tools for planning, optimizing and analyzing content effectiveness and engagement
- **Used by**: dxpr_cms_ai

**ai_image_alt_text**
- **Description**: Automatically generates accessible alt text for images using AI image recognition and analysis
- **Used by**: dxpr_cms_ai

**ai_provider_anthropic**
- **Description**: Integration with Anthropic's Claude AI models for advanced conversational and content generation capabilities
- **Used by**: dxpr_cms_ai

**ai_provider_openai**
- **Description**: OpenAI provider integration enabling GPT models for content generation and analysis
- **Used by**: dxpr_cms_ai

**analyze_ai_brand_voice**
- **Description**: AI-powered brand voice analysis ensuring content consistency with organizational tone and messaging guidelines
- **Used by**: dxpr_cms_ai

**analyze_ai_sentiment**
- **Description**: Sentiment analysis using AI to evaluate emotional tone and reader perception of content
- **Used by**: dxpr_cms_ai

**ckeditor_ai_agent**
- **Description**: AI assistant integrated into CKEditor for real-time writing suggestions and content improvements
- **Used by**: dxpr_cms_ai

**markdownify**
- **Description**: Converts HTML content to Markdown format for easier information exchange with AI models
- **Used by**: dxpr_cms_ai

### Administration & UI

**coffee**
- **Description**: Quick admin navigation tool with keyboard shortcuts for accessing admin pages and functions
- **Used by**: dxpr_cms_admin_ui

**dashboard**
- **Description**: Customizable dashboard system allowing personalized admin interfaces with widgets and quick access links
- **Used by**: dxpr_cms_admin_ui, dxpr_cms_starter


**gin_toolbar**
- **Description**: Enhanced toolbar integration for Gin admin theme with improved navigation and user experience
- **Used by**: dxpr_cms_admin_ui

**sam**
- **Description**: Simple Add More - Simplifies multi-value form widgets by hiding empty fields and providing "Add another" buttons
- **Used by**: dxpr_cms_admin_ui

**tagify**
- **Description**: Modern tag input widget transforming text fields into user-friendly tag selection interfaces
- **Used by**: dxpr_cms_admin_ui, dxpr_cms_content_type_base

### Content Management

**add_content_by_bundle**
- **Description**: Enhanced content creation interface displaying content types as cards with icons and descriptions
- **Used by**: dxpr_cms_blog, dxpr_cms_case_study, dxpr_cms_events, dxpr_cms_news

**autosave_form**
- **Description**: Automatically saves form progress preventing data loss during content creation and editing sessions
- **Used by**: dxpr_cms_content_type_base

**scheduler**
- **Description**: Schedule content publishing and unpublishing at specific dates and times for workflow automation
- **Used by**: dxpr_cms_content_type_base

**scheduler_content_moderation_integration**
- **Description**: Integrates Scheduler with Content Moderation enabling scheduled state transitions in editorial workflows
- **Used by**: dxpr_cms_content_type_base

**trash**
- **Description**: Soft delete functionality allowing content recovery before permanent deletion with trash/recycle bin
- **Used by**: dxpr_cms_content_type_base

### Page Building & Layout

**dxpr_builder**
- **Description**: Advanced drag-and-drop page builder for creating complex layouts without coding knowledge required
- **Used by**: dxpr_cms_blog, dxpr_cms_dxpr_builder

**dxpr_theme_helper**
- **Description**: Helper module providing additional theme functionality and integration features for DXPR themes
- **Used by**: dxpr_cms_blog, dxpr_cms_dxpr_builder, dxpr_cms_dxpr_theme

**field_group**
- **Description**: Groups form fields into tabs, accordions, and fieldsets for better content organization
- **Used by**: dxpr_cms_dxpr_builder, dxpr_cms_seo_tools


**section_library**
- **Description**: Reusable layout sections library for consistent design patterns across pages and content
- **Used by**: dxpr_cms_dxpr_builder

### Media & Images

**focal_point**
- **Description**: Set focal points on images ensuring important areas remain visible when cropped responsively
- **Used by**: dxpr_cms_image, dxpr_cms_seo_tools

**media_library_form_element**
- **Description**: Form element providing media library integration for easier media selection in forms
- **Used by**: dxpr_cms_dxpr_theme

**svg_image**
- **Description**: SVG image support allowing scalable vector graphics upload and display with security filtering
- **Used by**: dxpr_cms_image

### Search & Discovery

**better_exposed_filters**
- **Description**: Enhanced exposed filters interface with better UX including select all and dropdown options
- **Used by**: dxpr_cms_blog, dxpr_cms_news

**search_api**
- **Description**: Framework for creating custom search solutions with support for multiple backends and facets
- **Used by**: dxpr_cms_search

**search_api_autocomplete**
- **Description**: Adds autocomplete suggestions to search forms powered by Search API for better user experience
- **Used by**: dxpr_cms_search

**search_api_exclude**
- **Description**: Exclude specific entities from search indexing for privacy or relevance control purposes
- **Used by**: dxpr_cms_search

**selective_better_exposed_filters**
- **Description**: Apply Better Exposed Filters selectively to specific filters maintaining standard UI for others
- **Used by**: dxpr_cms_blog, dxpr_cms_news

**simple_search_form**
- **Description**: Lightweight search form block providing basic search functionality without complex configuration requirements
- **Used by**: dxpr_cms_search

### SEO & Analytics

**easy_breadcrumb**
- **Description**: Automatic breadcrumb generation based on URL paths improving navigation and SEO structure
- **Used by**: dxpr_cms_seo_basic

**google_tag**
- **Description**: Google Tag Manager integration for managing analytics, marketing, and measurement tags centrally
- **Used by**: dxpr_cms_google_analytics

**hreflang**
- **Description**: Automatic hreflang tag generation for multilingual SEO helping search engines understand language variations
- **Used by**: dxpr_cms_multilingual

**metatag**
- **Description**: Comprehensive meta tag management for SEO including Open Graph and Twitter Card support
- **Used by**: dxpr_cms_seo_tools

**pathauto**
- **Description**: Automatically generates SEO-friendly URL aliases based on configurable patterns for all content
- **Used by**: dxpr_cms_content_type_base, dxpr_cms_seo_basic

**redirect**
- **Description**: Manages URL redirects preventing 404 errors and maintaining SEO value during site changes
- **Used by**: dxpr_cms_seo_basic

**robotstxt**
- **Description**: Manage robots.txt file through Drupal admin interface controlling search engine crawler access
- **Used by**: dxpr_cms_seo_tools

**seo_checklist**
- **Description**: SEO checklist tracking tool ensuring all important SEO tasks and configurations are completed
- **Used by**: dxpr_cms_seo_tools

**simple_sitemap**
- **Description**: XML sitemap generation following sitemap protocol for better search engine content discovery
- **Used by**: dxpr_cms_seo_tools

**sitemap**
- **Description**: Provides a human-readable HTML sitemap page for visitors to navigate site structure
- **Used by**: dxpr_cms_seo_tools

**statistics**
- **Description**: Sovereign privacy-first anonymous web analytics tracking content views and user activity
- **Used by**: dxpr_cms_basic_analytics

### Security & Spam Prevention

**captcha**
- **Description**: CAPTCHA framework providing various challenge types to prevent automated spam form submissions
- **Used by**: dxpr_cms_anti_spam

**friendlycaptcha**
- **Description**: Privacy-friendly CAPTCHA alternative that doesn't track users while preventing bot submissions effectively
- **Used by**: dxpr_cms_anti_spam

**friendly_captcha_challenge**
- **Description**: Challenge provider for Friendly Captcha implementing cryptographic puzzles instead of image recognition
- **Used by**: dxpr_cms_anti_spam

**honeypot**
- **Description**: Hidden form field spam prevention technique catching bots without impacting legitimate user experience
- **Used by**: dxpr_cms_anti_spam

**login_emailusername**
- **Description**: Allow users to login using either their username or email address for convenience
- **Used by**: dxpr_cms_authentication

### Email & Messaging

**easy_email**
- **Description**: Flexible email template system for creating and managing HTML emails with token support
- **Used by**: easy_email_standard, easy_email_types_core, easy_email_types_default

**easy_email_express**
- **Description**: Quick setup email configuration providing pre-configured email templates for common use cases
- **Used by**: dxpr_cms_starter, easy_email_express

**easy_email_standard**
- **Description**: Standard email configuration with common email types and templates for typical site needs
- **Used by**: easy_email_express, easy_email_standard

**easy_email_text_format**
- **Description**: Text format configuration for Easy Email ensuring consistent email content formatting
- **Used by**: easy_email_text_format, easy_email_types_core, easy_email_types_default

**easy_email_theme**
- **Description**: Theming support for Easy Email templates enabling branded email designs and layouts
- **Used by**: easy_email_standard

**easy_email_types_core**
- **Description**: Core email types for user account operations including registration and password recovery
- **Used by**: easy_email_express, easy_email_types_core

**easy_email_types_default**
- **Description**: Default email type providing a general-purpose email template for custom notifications
- **Used by**: easy_email_express, easy_email_types_default

**mailsystem**
- **Description**: Mail system configuration allowing different mail backends and formats for different modules
- **Used by**: easy_email_standard

**symfony_mailer_lite**
- **Description**: Lightweight Symfony Mailer integration for reliable email delivery with modern mail protocols
- **Used by**: easy_email_standard

### Forms & User Input

**contact_block**
- **Description**: Provides contact form blocks that can be placed anywhere using the block system
- **Used by**: dxpr_cms_dxpr_builder

**webform**
- **Description**: Comprehensive form builder for creating complex forms, surveys, and applications with submissions
- **Used by**: dxpr_cms_forms

### Location & Maps

**address**
- **Description**: International address field with country-specific formatting and validation for global compatibility
- **Used by**: dxpr_cms_events

**addtocal_augment**
- **Description**: Add to calendar functionality allowing users to save events to their personal calendars
- **Used by**: dxpr_cms_events

**geocoder**
- **Description**: Geocoding framework converting addresses to coordinates using various geocoding provider services
- **Used by**: dxpr_cms_events

**geofield**
- **Description**: Store geographic data including points, lines, and polygons with spatial query support
- **Used by**: dxpr_cms_events

**leaflet**
- **Description**: Interactive map display using Leaflet library for lightweight and mobile-friendly mapping solutions
- **Used by**: dxpr_cms_events

**smart_date**
- **Description**: Intelligent date field handling recurring dates, all-day events, and timezone conversions elegantly
- **Used by**: dxpr_cms_events

### Multilingual & Translation

**hreflang**
- **Description**: Automatic hreflang tag generation for multilingual SEO helping search engines understand language variations
- **Used by**: dxpr_cms_multilingual

**tmgmt**
- **Description**: Translation Management Tool for managing translation workflows with translator assignment and review
- **Used by**: dxpr_cms_multilingual

**tmgmt_google**
- **Description**: Google Translate integration for TMGMT providing automated translation with post-editing capabilities
- **Used by**: dxpr_cms_multilingual

### Performance

**minifyhtml**
- **Description**: HTML minification removing unnecessary whitespace and comments to reduce page size
- **Used by**: dxpr_cms_performance

### Privacy & Compliance

**klaro**
- **Description**: Privacy-focused consent manager for GDPR compliance with customizable cookie consent interface
- **Used by**: dxpr_cms_privacy_basic

### Utility & Helper Modules

**automatic_updates**
- **Description**: Automated security updates for Drupal core and contrib modules keeping sites secure
- **Used by**: dxpr_cms_starter

**bpmn_io**
- **Description**: BPMN diagram viewer and editor for ECA workflows enabling visual workflow design
- **Used by**: dxpr_cms_authentication, dxpr_cms_content_type_base, dxpr_cms_privacy_basic, dxpr_cms_seo_tools, dxpr_cms_starter

**eca**
- **Description**: Event-Condition-Action framework for creating automated workflows without custom code requirements
- **Used by**: dxpr_cms_authentication, dxpr_cms_content_type_base, dxpr_cms_privacy_basic, dxpr_cms_seo_tools, dxpr_cms_starter


**linkit**
- **Description**: Enhanced linking interface with autocomplete for internal content and media references
- **Used by**: dxpr_cms_content_type_base

**menu_link_attributes**
- **Description**: Add custom attributes to menu links including classes, target, and rel attributes
- **Used by**: dxpr_cms_dxpr_builder, dxpr_cms_privacy_basic

**project_browser**
- **Description**: In-site module and theme browser for discovering and installing Drupal extensions easily
- **Used by**: dxpr_cms_ai, dxpr_cms_google_analytics, dxpr_cms_starter

**token**
- **Description**: Token system providing placeholders for dynamic content replacement throughout the site
- **Used by**: dxpr_cms_authentication, dxpr_cms_content_type_base, dxpr_cms_seo_basic, dxpr_cms_starter

**token_or**
- **Description**: Token OR logic allowing fallback tokens when primary tokens have no value
- **Used by**: dxpr_cms_seo_tools

## Recipe Modules

The following are DXPR CMS recipe modules that bundle functionality:

- **dxpr_cms_admin_ui** - Administrative interface improvements
- **dxpr_cms_ai** - AI and machine learning features
- **dxpr_cms_analytics** - Analytics integration framework
- **dxpr_cms_basic_analytics** - Basic privacy-first analytics using Statistics module
- **dxpr_cms_anti_spam** - Spam prevention tools
- **dxpr_cms_authentication** - Authentication enhancements
- **dxpr_cms_blog** - Blog content type and features
- **dxpr_cms_case_study** - Case study content type
- **dxpr_cms_content_type_base** - Base content type configuration
- **dxpr_cms_dxpr_builder** - DXPR Builder integration
- **dxpr_cms_events** - Event management features
- **dxpr_cms_forms** - Form building tools
- **dxpr_cms_google_analytics** - Google Analytics integration
- **dxpr_cms_image** - Image handling enhancements
- **dxpr_cms_multilingual** - Multilingual capabilities
- **dxpr_cms_news** - News content type
- **dxpr_cms_page** - Basic page content type
- **dxpr_cms_performance** - Performance optimizations
- **dxpr_cms_privacy_basic** - Privacy compliance tools
- **dxpr_cms_remote_video** - Remote video embedding
- **dxpr_cms_roles** - User role configuration
- **dxpr_cms_search** - Search functionality
- **dxpr_cms_seo_basic** - Basic SEO tools
- **dxpr_cms_seo_tools** - Advanced SEO tools
- **dxpr_cms_starter** - Main starter recipe

## Summary

This modular architecture allows DXPR CMS to provide a flexible, feature-rich content management system where functionality can be enabled based on specific project needs.