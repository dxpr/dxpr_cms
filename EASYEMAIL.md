# EasyEmail Removal Implementation Plan

## Goal
Remove EasyEmail functionality from DXPR CMS recipes to reduce distribution size and complexity.

## Implementation Steps

### 1. Remove EasyEmail Recipe Directories
```bash
rm -rf recipes/easy_email_express/
rm -rf recipes/easy_email_standard/
rm -rf recipes/easy_email_text_format/
rm -rf recipes/easy_email_types_core/
rm -rf recipes/easy_email_types_default/
```

### 2. Update dxpr_cms_starter Dependencies
- Remove `easy_email_express` from `recipes/dxpr_cms_starter/recipe.yml`
- Remove `"drupal/easy_email_express": "*"` from `recipes/dxpr_cms_starter/composer.json`

### 3. Clean Up Remaining References
- Search for any remaining easy_email references: `grep -r "easy_email" recipes/`
- Update `docs/developer-documentation/dxpr_cms_project_desc.html` to remove EasyEmail references

### 4. Test Implementation
Run the complete test to verify everything works:
```bash
ddev rebuild && ddev drush site-install dxpr_cms_installer dxpr_cms_installer_api_keys_configuration.json_web_token='eyJhbGciOiJQUzI1NiIsImtpZCI6IjR6RGRXS1pGNGRfbXprcVVMc2tYb3ItcE96bGRITFN0WGI1Q1pUX3d4UnMifQ.eyJpc3MiOiJodHRwczpcL1wvZHhwci5jb20iLCJzdWIiOiIyMDAyNiIsImF1ZCI6Imh0dHBzOlwvXC9wYWNrYWdlcy5keHByLmNvbSIsInNjb3BlIjoiZHhwclwvZHhwcl9idWlsZGVyIGR4cHJcL2R4cHJfYnVpbGRlcl9lIiwiZHhwcl90aWVyIjoiZHhwcl9lbnRlcnByaXNlIiwianRpIjoiZTc3Zjk0NjJjMDdmOTdiZTUyYzE3MmJhMWRiYmVkMWI1ZWMwYWUxZjYwYTZlNGEwNjc0YzNhN2E1MGFmZDdmYiJ9.Ck2qLAYdHy1uADJyOEfmo4NtRbIzGoveInJOVnTNL3DFz-hI8pHZv5V8kJcxnGMzaZHQZrkIBtTpG1Kgf4fQWuwYWTOW_cOo6YTD1aeEe4Sfevm9ILmfTzIypH__UbRmffSgApt0F6YBJsdwc5wqPkClNmCg1h9YkL1_Cc8E26Qw1nFAAvjEmGBCSjoZWtUboI8a8NIIm0u5MUjauTZ_ADhOEEGeOeyDAGINivngQLp5erYF91fdVnVDS983VpTEsZr_94d7qj6AOnO48AqTv16OYkWFP2ON-6KEzf5SshyEBEovRZ9D5NDOTx6RMMSXtqE2j6Ms0Sy7uR3ZPv-BTiazy5weBTPCZ-ppzulaOVxTqH5TANjayDQQMqFEvf-MO1QcUkA-rrEtqichlZmbcDPHCSKJmhRkJNIfXn0tsDlKRQu9t2MT0dop_YFXZHXy9uPFZVLb5SxdbX_-d1rVQAuZNDc3QQ4IYmxKcbeq39U2GCfhd5BRvyQYpEObR-_Xl_8NjjXTWkquLMvmu5ANDn08GBdF8E90STqr8rvVUvKVUIAkuuqUJNvicsCM22vb2fJKlUzYwUkncRZuofHpDHjCpDum6TnOz-A7Hojul02GAQIXClk65QZxhvTj8vHEQl5Es8wM133wtPIJldsR9x9Z0DxpBoS1cvrvnwquYPE' -y
```

## Files Affected
- Remove: 5 EasyEmail recipe directories
- Update: `recipes/dxpr_cms_starter/recipe.yml`
- Update: `recipes/dxpr_cms_starter/composer.json`
- Update: `docs/developer-documentation/dxpr_cms_project_desc.html`

## Result
- Eliminates mailsystem and symfony_mailer_lite dependencies
- Reduces package size by removing complex email infrastructure
- Site uses core Drupal email functionality (plain text)