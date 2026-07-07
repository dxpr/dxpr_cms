# RL: Reinforcement Learning Experiments (Drush CLI)

Manage A/B testing experiments powered by Thompson Sampling via Drush.

## Commands

### Discovery & Analysis
- `drush rl:list`: List all experiments with stats
- `drush rl:status <id>`: Experiment phase, confidence, distribution
- `drush rl:performance <id>`: Arm-level conversion rates and labels
- `drush rl:trends <id>`: Historical trends (--period=daily|weekly|monthly)
- `drush rl:export <id>`: Export full data (--snapshots for history)
- `drush rl:analyze <id>`: Full analysis with recommendations

### Experiment CRUD
- `drush rl:experiment:create <id> --module=X --name="Y"`: Create experiment
- `drush rl:experiment:update <id> --name="Y"`: Update metadata
- `drush rl:experiment:delete <id>`: Delete experiment + all data

### Configuration
- `drush rl:config:list`: List all settings
- `drush rl:config:get <key>`: Get one setting
- `drush rl:config:set <key> <value>`: Set with validation
- `drush rl:config:reset`: Reset to defaults
- `drush rl:event-log:clear`: Clear all snapshots

### Settings Keys
| Key | Type | Default |
|---|---|---|
| debug_mode | boolean | false |
| enable_event_log | boolean | true |
| event_log_max_rows | integer | 100000 |
| chart_line_threshold | integer | 9 |

All state-changing commands support `--dry-run`.
Analysis commands support `--format=json|yaml|table`.
