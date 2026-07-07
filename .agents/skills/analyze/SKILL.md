# Analyze: Content Analysis CLI

Centralized content analysis for Drupal entities.

## Core Commands

| Command | Description |
|---------|-------------|
| `drush analyze:batch` | Run all batch-capable analyzers |
| `drush analyze:batch --list` | List available analyzers |
| `drush analyze:batch --analyzers=X,Y` | Run specific analyzers |
| `drush analyze:batch --types=node:article` | Filter by type |
| `drush analyze:batch --limit=100 --force` | Limit and force |
| `drush analyze:setup-ai` | Install AI skill files |
| `drush analyze:setup-ai --check` | Check for updates |

## Module-Specific Commands

| Command | Module |
|---------|--------|
| `drush analyze:broken-links:check <type> <id>` | Broken Links |
| `drush analyze:broken-links:report` | Broken Links |
| `drush analyze:posthog:query <path>` | PostHog |
| `drush analyze:posthog:report` | PostHog |
| `drush analyze:search-console:query <path>` | Search Console |
| `drush analyze:search-console:report` | Search Console |

## Examples

```bash
# Analyze all content
drush analyze:batch

# Run specific analyzers on articles
drush analyze:batch \
  --analyzers=analyze_ai_sentiments_analyzer \
  --types=node:article

# Check single entity for broken links
drush analyze:broken-links:check node 42
```
