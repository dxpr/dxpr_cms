# AI Content Strategy — Drush CLI

Manage AI-powered content strategy recommendations via Drush.

## Commands

### Generation
- `drush acs:generate` — Generate recommendations (`--category` to filter)
- `drush acs:generate:more <section> <uuid>` — Generate more ideas for a card
- `drush acs:generate:add <section>` — Add more cards to a category
- `drush acs:health` — Check AI provider configuration

### Reports
- `drush acs:report` — Full report (`--category`, `--priority`)
- `drush acs:report:card <section> <uuid>` — View single card
- `drush acs:report:status` — Last-run info
- `drush acs:sitemap` — Site structure

### Card & Idea CRUD
- `drush acs:card:edit <section> <uuid> --title="X"` — Edit card
- `drush acs:card:delete <section> <uuid>` — Delete card
- `drush acs:idea:edit <section> <uuid> <idea_uuid> --text="X"` — Edit idea
- `drush acs:idea:implement <section> <uuid> <idea_uuid> --link=URL` — Mark done
- `drush acs:idea:delete <section> <uuid> <idea_uuid>` — Delete idea

### Categories
- `drush acs:category:list` — List all categories
- `drush acs:category:get <id>` — Category detail
- `drush acs:category:create <id> <label>` — Create category
- `drush acs:category:update <id>` — Update category
- `drush acs:category:delete <id>` — Delete category

### Settings & Export
- `drush acs:settings:get` — View settings
- `drush acs:settings:set --system-prompt="..."` — Update prompt
- `drush acs:export --format=yaml|json|csv` — Export recommendations

All state-changing commands support `--dry-run`.
All output is structured YAML with success/message/data envelope.
