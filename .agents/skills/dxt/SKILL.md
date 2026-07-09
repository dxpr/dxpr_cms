# DXPR Theme CLI

Manage DXPR Theme settings via `dxt:*` Drush commands. All commands output YAML.

## Commands

| Command | Alias | Description |
|---|---|---|
| `dxt:config:get <key>` | `dxt-cg` | Get setting value with schema |
| `dxt:config:set <key> <value>` | `dxt-cs` | Set setting (validates, rebuilds CSS) |
| `dxt:config:list` | `dxt-cl` | List settings (`--section`, `--detail`) |
| `dxt:config:export` | `dxt-ce` | Export to YAML (`--file`) |
| `dxt:config:import <file>` | `dxt-ci` | Import from YAML (`--dry-run`, supports `color_palette_*` keys) |
| `dxt:config:reset` | `dxt-cr` | Reset to defaults (`--section`) |
| `dxt:palette:get` | `dxt-pg-colors` | Get palette with Bootstrap class mapping |
| `dxt:palette:set <key=#hex ...>` | | Set entire color palette in one command |
| `dxt:generate:palette "<prompt>"` | `dxt-gp` | AI color palette (`--apply`) |
| `dxt:generate:fonts "<prompt>"` | `dxt-gf` | AI font selection (`--apply`) |
| `dxt:page:get <nid>` | `dxt-pg` | Get node theme fields |
| `dxt:page:set <nid>` | `dxt-ps` | Set node layout (`--layout`, `--hide-regions`, `--content-width`) |
| `dxt:subtheme:create` | `dxt-sc` | Create subtheme from starterkit |
| `dxt:setup-ai` | `dxt-sa` | Install AI skill files |

## Color Palette

```bash
# Set full palette in one command
drush dxt:palette:set base=#6C3CE1 basetext=#fff link=#6C3CE1 accent1=#00D4FF ...

# Set individual color
drush dxt:config:set color_palette_base "#6C3CE1"
```

Keys: base, basetext, link, accent1, accent1text, accent2, accent2text, text, headings, card, cardtext, footer, footertext, secheader, secheadertext, header, headertext, headerside, headersidetext, pagetitle, pagetitletext, graylight, graylighter, silver, body.

### Palette → Bootstrap Mapping

| Palette | Bootstrap Variable | Classes |
|---|---|---|
| `base` | `--bs-primary` | `btn-primary`, `bg-primary`, `text-primary` |
| `accent1` | `--bs-secondary` | `btn-secondary`, `bg-secondary` |
| `silver` | `--bs-light` | `bg-light` |
| `headings` | `--bs-dark` | `bg-dark` |
| `text` | `--bs-body-color` | inherited by body text |
| `headings` | `--bs-heading-color` | inherited by h1-h6 |
| `link` | `--bs-link-color` | inherited by links |
| `body` | `--bs-body-bg` | page background |

`btn-primary` = `base` color, `btn-secondary` = `accent1` color. Use `btn-secondary` for CTA buttons when `accent1` is the action color. Never use `btn-warning` just to get yellow.

## Theme vs Content Responsibility

DXPR Theme (dxt) owns the visual baseline: heading weight, text colors, backgrounds, font sizes. Content markup (dxb) inherits these automatically. Configure the theme FIRST — don't compensate with utility classes in content.

## Workflow

1. Run `drush dxt:config:list --section=<section> --detail` to see valid values
2. Use `drush dxt:config:set <key> <value>` to change settings
3. For colors: use `drush dxt:palette:set` with all key=#hex pairs in one command
4. Use `drush dxt:config:get <key>` to verify

Sections: layout, header, page-title, colors, fonts, typography, block-design, custom-css.

All state-changing commands support `--dry-run` and `--theme=<name>`.
