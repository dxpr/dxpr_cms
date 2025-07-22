# DXPR CMS Themes Reference

This document lists all Drupal themes used by DXPR CMS recipes as defined in their composer.json files.

## Themes

### DXPR Theme

**dxpr_theme**
- **Description**: Premium Drupal theme with extensive customization options and modern design patterns built-in
- **Used by**: dxpr_cms_dxpr_theme
- **Type**: Drupal Theme
- **Features**:
  - Extensive customization options through theme settings
  - Modern, responsive design patterns
  - Tight integration with DXPR Builder
  - Professional pre-built layouts
  - Global styling controls

### Base Themes

**bootstrap5**
- **Description**: Bootstrap 5 base theme providing modern responsive design components and utilities
- **Used by**: dxpr_cms_dxpr_theme
- **Type**: Drupal Base Theme
- **Purpose**: Provides the underlying theme foundation with Bootstrap 5 CSS framework and JavaScript components

### Admin Themes

**gin**
- **Description**: Modern, accessible admin theme providing a clean and efficient administrative user interface experience
- **Used by**: dxpr_cms_admin_ui
- **Type**: Drupal Admin Theme
- **Features**:
  - Modern, clean administrative interface
  - Accessibility focused design
  - Enhanced user experience for content editors
  - Responsive admin interface
  - Customizable admin theme settings

## Recipe Integration

The DXPR Theme is integrated through the `dxpr_cms_dxpr_theme` recipe, which:
- Installs and configures the DXPR Theme
- Sets up default blocks and regions
- Configures theme settings for optimal performance
- Integrates with DXPR Builder for seamless page building

## Summary

DXPR CMS uses a premium theme system built on modern web standards, providing users with a professional, customizable foundation for their websites. The theme system is designed to work seamlessly with the DXPR Builder page building tools.