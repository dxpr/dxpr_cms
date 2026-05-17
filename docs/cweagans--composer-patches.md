================================================
FILE: docs/_index.md
================================================
---
title: Composer Patches
description: A simple plugin for Composer that allows you to apply patches to your dependencies.
layout: projecthome
github: https://github.com/cweagans/composer-patches
buttons:

- title: "View on Github"
  href: https://github.com/cweagans/composer-patches
  style: blue
- title: "Project updates"
  href: https://www.cweagans.net/projects/composer-patches
  style: blue

quicklinks:

- title: "Installation"
  icon: installation
  description: "Get up-and-running with Composer Patches in your project"
  href: /composer-patches/getting-started/installation
- title: "Recommended workflows"
  icon: plugins
  description: "Learn how to use Composer Patches with a group of engineers."
  href: /composer-patches/usage/recommended-workflows
- title: "Troubleshooting"
  icon: architecture
  description: "Figure out how to solve any problems that you run into."
  href: /composer-patches/troubleshooting/guide
- title: "Contributing"
  icon: themes
  description: "Contribute to development and keep the project alive and healthy."
  href: /composer-patches/project/contributing

---

{{< quicklinks >}}

{{< warning title="Nothing here applies to 1.x" >}}
All documentation for the current, production-ready version of the plugin is in the [1.x README.md](https://github.com/cweagans/composer-patches/blob/1.x/README.md) in the project repository.
{{< /warning >}}



================================================
FILE: docs/stork.toml
================================================
[input]
base_directory = ""
url_prefix = "/composer-patches"
frontmatter_handling = "Omit"
files = [
    { path = "_index.md", url = "/", title = "Composer Patches" },
    { path = "api/capabilities.md", url = "/api/capabilities/", title = "Capabilities" },
    { path = "api/events.md", url = "/api/events/", title = "Events" },
    { path = "api/overview.md", url = "/api/overview/", title = "Overview" },
    { path = "api/patches-lock-json.md", url = "/api/patches-lock-json/", title = "patches.lock.json" },
    { path = "getting-started/installation.md", url = "/getting-started/installation/", title = "Installation" },
    { path = "getting-started/system-requirements.md", url = "/getting-started/system-requirements/", title = "System Requirements" },
    { path = "getting-started/terminology.md", url = "/getting-started/terminology/", title = "Terminology" },
    { path = "project/code-of-conduct.md", url = "/project/code-of-conduct/", title = "Code of Conduct" },
    { path = "project/contributing.md", url = "/project/contributing/", title = "Contributing" },
    { path = "project/security.md", url = "/project/security/", title = "Security" },
    { path = "usage/commands.md", url = "/usage/commands/", title = "Commands" },
    { path = "usage/configuration.md", url = "/usage/configuration/", title = "Configuration" },
    { path = "usage/defining-patches.md", url = "/usage/defining-patches/", title = "Defining Patches" },
    { path = "usage/freeform-patcher.md", url = "/usage/freeform-patcher/", title = "Freeform Patcher" },
    { path = "usage/recommended-workflows.md", url = "/usage/recommended-workflows/", title = "Recommended Workflows" },
]



================================================
FILE: docs/api/_index.md
================================================
---
title: API
weight: 40
---



================================================
FILE: docs/api/capabilities.md
================================================
---
title: Capabilities
weight: 20
---

Your plugin can be capable of providing a `Resolver`, a `Downloader`, and/or a `Patcher`.

## Enabling a plugin capability

This functionality comes directly from Composer. In your plugin class, implement `\Composer\Plugin\Capable`. Your `getCapabilities()` method should return a list of capabilities that your plugin offers.

{{< highlight php "hl_lines=7 15-17" >}}

use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use cweagans\Composer\Capability\Downloader\DownloaderProvider;
use cweagans\Composer\Capability\Patcher\PatcherProvider;
use cweagans\Composer\Capability\Resolver\ResolverProvider;

class YourPlugin implements PluginInterface, Capable
{
    /**
     * @inheritDoc
     */
    public function getCapabilities(): array
    {
        return [
            ResolverProvider::class => YourResolverProvider::class,
            DownloaderProvider::class => YourDownloaderProvider::class,
            PatcherProvider::class => YourPatcherProvider::class,
        ];
    }
    
    [...]
}
{{< /highlight >}}


## Providers

Next, you need to implement the provider for your capability. The provider is responsible for instantiating any capability classes that your plugin offers and returns a list of them. Rather than duplicating the code here, you should refer to `\cweagans\Composer\Capability\Downloader\CoreDownloaderProvider`, `\cweagans\Composer\Capability\Patcher\CorePatcherProvider`, and `\cweagans\Composer\Capability\Resolver\CoreResolverProvider` for examples of how to write these classes. Your provider should extend one of the base classes provided (`\cweagans\Composer\Capability\Downloader\BaseDownloaderProvider`, `\cweagans\Composer\Capability\Patcher\BasePatcherProvider`, or `\cweagans\Composer\Capability\Resolver\BaseResolverProvider` for a `Downloader` provider, a `Patcher` provider, or a `Resolver` provider respectively.

## Capability classes

Finally, you should implement the actual capability classes that actually provide the functionality that you want to provide. There are base classes provided that you can extend to make this process easier - `\cweagans\Composer\Downloader\DownloaderBase`, `\cweagans\Composer\Patcher\PatcherBase`, and `\cweagans\Composer\Resolver\ResolverBase` for a `Downloader`, `Patcher`, or `Resolver` respectively. Otherwise, you will need to implement the appropriate interface (each of which lives in the same namespace as the corresponding base class).



================================================
FILE: docs/api/events.md
================================================
---
title: Events
weight: 30
---

In the process of applying patches, Composer Patches emits several different events. The event dispatcher system is the one provided by Composer, so any documentation related to the Composer event dispatcher also applies to plugins extending Composer Patches.

## Subscribing to an event.

This functionality comes directly from Composer. In your plugin class, implement `\Composer\EventDispatcher\EventSubscriberInterface`. Your `getSubscribedEvents()` method should return a list of events that you want to subscribe to and their respective handlers.

{{< highlight php "hl_lines=5 13-14" >}}

use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Plugin\PluginInterface;
use cweagans\Composer\Event\PatchEvents;

class YourPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public function getSubscribedEvents(): array
    {
        return array(
            PatchEvents::PRE_PATCH_GUESS_DEPTH => ['yourHandlerHere'],
            PatchEvents::PRE_PATCH_APPLY => ['aDifferentHandler', 10],
        );
    }
    
    [...]
}
{{< /highlight >}}

A full list of events can be found in `\cweagans\Composer\Event\PatchEvents` and `\cweagans\Composer\Event\PluginEvents`.

## Writing the event handler

Once you've subscribed to an event, you need to write the handler. The handler function should be located in your main plugin class and be a `public` method. If the event is listed in `\cweagans\Composer\Event\PatchEvents`, your handler function will receive a `\cweagans\Composer\Event\PatchEvent` as the first (and only) argument. If the event is listed in `\cweagans\Composer\Event\PluginEvents`, your handler function will receive a `\cweagans\Composer\Event\PluginEvent` object as the first (and only) argument.



================================================
FILE: docs/api/overview.md
================================================
---
title: Overview
weight: 10
---

In previous versions of Composer Patches, the plugin only had a certain set of functionality that couldn't really be modified. Now, much of the behavior of the plugin is accessible through APIs exposed to developers.

## Capabilities

A _capability_ is the name that Composer has given to individual components that implement a particular interface. For example, a Composer plugin might be capable of providing additional commands to the Composer console application. This process is [documented by composer](https://getcomposer.org/doc/articles/plugins.md#plugin-capabilities).

Composer Patches has declared a few additional capabilities that allow third-party plugins to extend the ability to find, download, and apply patches. Additional information may be found in the [Capabilities]({{< relref "capabilities.md" >}}) documentation.

## Events

An _event_ is emitted for various operations within the plugin. This allows you to hook into the process of resolving, downloading, or applying patches and perform some other action unrelated to the core functionality of the plugin. In some cases, you can alter data before handing it back to Composer Patches.



================================================
FILE: docs/api/patches-lock-json.md
================================================
---
title: patches.lock.json
weight: 40
---

{{< callout title="Filenames may vary in some projects" >}}
If the [`COMPOSER`]({{< relref "../usage/configuration.md#composer" >}}) environment variable is set when running various Composer Patches commands, the file normally named `patches.lock.json` will be named differently.
{{< /callout >}}

`patches.lock.json` is the mechanism that Composer Patches now uses to maintain a known-good list of patches to apply to the project. For external projects, the structure of `patches.lock.json` may also be treated as an API. If you're considering `patches.lock.json` as a data source for your project, there are a few things that you should keep in mind:

* `patches.lock.json` should be considered **read-only** for external uses.
* The general structure of `patches.lock.json` will not change. You can rely on a JSON file structured like so:
```json
{
    "_hash": "[the hash]",
    "patches": [{patch definition}, {patch definition}, ...]
}
```
* Each patch definition will look like the [expanded format]({{< relref "../usage/defining-patches.md#expanded-format" >}}) that users can put into their `composer.json` or external patches file.
* No _removals_ or _changes_ will be made to the patch definition object. _Additional_ keys may be created, so any JSON parsing you're doing should be tolerant of new keys.
* The `extra` object in each patch definition may contain a number of attributes set by other projects or by the user and should be treated as free-form input. Currently, Composer Patches uses this attribute to store information about where a patch was defined (in the `provenance` key).



================================================
FILE: docs/getting-started/_index.md
================================================
---
title: Getting Started
weight: 10
---



================================================
FILE: docs/getting-started/installation.md
================================================
---
title: Installation
weight: 20
---

Installation is the same as any other Composer package:

```shell
composer require cweagans/composer-patches:~2.0
```

{{< warning title="2.0.0 is not released yet" >}}
This documentation is being written in advance of the release of 2.0.0. The above command will not work yet.
{{< /warning >}}



================================================
FILE: docs/getting-started/system-requirements.md
================================================
---
title: System Requirements
weight: 10
---

Supporting every version of the software that we depend on is not feasible, so we support a limited, "modern-ish" system
configuration.

## PHP

We follow the list of [currently supported PHP versions](https://www.php.net/supported-versions.php) provided by the
community. Unsupported/EOL versions of PHP will not be supported under any circumstances.

## Composer

Composer 2.2 (LTS) is the minimum version. However, few people run such an old version of Composer and we do not
regularly test functionality with anything other than the current stable release. If you run into problems, we _will_
ask you to try a recent version of Composer before proceeding with troubleshooting.

## Patchers

The core plugin used to use multiple tools to try to apply a patch. Unfortunately, that led to a lot of unpredictable
behavior across various environments. Now, the core plugin _only_ relies on Git. As long as you have a relatively recent
version of Git, things should work.

If your Git is older than what is distributed with the current Debian "stable" release, we probably won't be able to do
much with it.



================================================
FILE: docs/getting-started/terminology.md
================================================
---
title: Terminology
weight: 30
---

{{< lead text="There are a few terms specific to this plugin. Understanding them before proceeding may help with understanding." >}}

{{< callout title="This document is for end-users" >}}
If you're looking for developer-focused documentation about each of these components, see the [Capabilities]({{< relref "../api/capabilities.md" >}}) page in the API documentation.
{{< /callout >}}

Resolvers, downloaders, and patchers are all small units of functionality that can be disabled individually or extended by other Composer plugins.

## Resolver

A _resolver_ is a component that looks for patch definitions in a particular place. If any patch definitions are found, they are added to a list maintained internally by the plugin.

An example of a resolver is `\\cweagans\\Composer\\Resolver\\PatchesFile`. If a [`patches-file`]({{< relref "../usage/configuration.md#patches-file" >}}) is configured in `composer.json`, the `PatchesFile` resolver opens the specified patches file, finds any defined patches, and adds them to the list of patches.

## Downloader

A _downloader_ is (intuitively) a component that downloads a patch. The `ComposerDownloader` is the default downloader and uses the same mechanism to download patches that is used by Composer to download packages.

## Patcher

A _patcher_ is a mechanism by which a downloaded patch can be applied to a package. The plugin ships with a handful of patchers in an effort to ensure that a particular system is able to apply a patch _somehow_. Generally, for each system program that is capable of applying a patch (in the core plugin, this is `git` (via `git apply`)), a `Patcher` can be defined that uses it.



================================================
FILE: docs/project/_index.md
================================================
---
title: Project
weight: 50
---



================================================
FILE: docs/project/code-of-conduct.md
================================================
---
title: Code of conduct
weight: 30
---

{{< lead text="Composer Patches has adopted the Contributor Covenant Code of Conduct. By participating in this project, you agree to abide by the terms below in all interactions with the community." >}}

---

## Our Pledge

We as members, contributors, and leaders pledge to make participation in our community a harassment-free experience for everyone, regardless of age, body size, visible or invisible disability, ethnicity, sex characteristics, gender identity and expression, level of experience, education, socio-economic status, nationality, personal appearance, race, caste, color, religion, or sexual identity and orientation.

We pledge to act and interact in ways that contribute to an open, welcoming, diverse, inclusive, and healthy community.

## Our Standards

Examples of behavior that contributes to a positive environment for our community include:

* Demonstrating empathy and kindness toward other people
* Being respectful of differing opinions, viewpoints, and experiences
* Giving and gracefully accepting constructive feedback
* Accepting responsibility and apologizing to those affected by our mistakes, and learning from the experience
* Focusing on what is best not just for us as individuals, but for the overall community

Examples of unacceptable behavior include:

* The use of sexualized language or imagery, and sexual attention or advances of any kind
* Trolling, insulting or derogatory comments, and personal or political attacks
* Public or private harassment
* Publishing others' private information, such as a physical or email address, without their explicit permission
* Other conduct which could reasonably be considered inappropriate in a professional setting

## Enforcement Responsibilities

Community leaders are responsible for clarifying and enforcing our standards of acceptable behavior and will take appropriate and fair corrective action in response to any behavior that they deem inappropriate, threatening, offensive, or harmful.

Community leaders have the right and responsibility to remove, edit, or reject comments, commits, code, wiki edits, issues, and other contributions that are not aligned to this Code of Conduct, and will communicate reasons for moderation decisions when appropriate.

## Scope

This Code of Conduct applies within all community spaces, and also applies when an individual is officially representing the community in public spaces. Examples of representing our community include using an official e-mail address, posting via an official social media account, or acting as an appointed representative at an online or offline event.

## Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be reported to the community leaders responsible for enforcement at me@cweagans.net. All complaints will be reviewed and investigated promptly and fairly.

All community leaders are obligated to respect the privacy and security of the reporter of any incident.

## Enforcement Guidelines

Community leaders will follow these Community Impact Guidelines in determining the consequences for any action they deem in violation of this Code of Conduct:

### 1. Correction

**Community Impact**: Use of inappropriate language or other behavior deemed unprofessional or unwelcome in the community.

**Consequence**: A private, written warning from community leaders, providing clarity around the nature of the violation and an explanation of why the behavior was inappropriate. A public apology may be requested.

### 2. Warning

**Community Impact**: A violation through a single incident or series of actions.

**Consequence**: A warning with consequences for continued behavior. No interaction with the people involved, including unsolicited interaction with those enforcing the Code of Conduct, for a specified period of time. This includes avoiding interactions in community spaces as well as external channels like social media. Violating these terms may lead to a temporary or permanent ban.

### 3. Temporary Ban

**Community Impact**: A serious violation of community standards, including sustained inappropriate behavior.

**Consequence**: A temporary ban from any sort of interaction or public communication with the community for a specified period of time. No public or private interaction with the people involved, including unsolicited interaction with those enforcing the Code of Conduct, is allowed during this period. Violating these terms may lead to a permanent ban.

### 4. Permanent Ban

**Community Impact**: Demonstrating a pattern of violation of community standards, including sustained inappropriate behavior, harassment of an individual, or aggression toward or disparagement of classes of individuals.

**Consequence**: A permanent ban from any sort of public interaction within the community.

## Attribution

This Code of Conduct is adapted from the [Contributor Covenant][homepage], version 2.1, available at [https://www.contributor-covenant.org/version/2/1/code_of_conduct.html][v2.1].

Community Impact Guidelines were inspired by [Mozilla's code of conduct enforcement ladder][Mozilla CoC].

For answers to common questions about this code of conduct, see the FAQ at [https://www.contributor-covenant.org/faq][FAQ]. Translations are available at [https://www.contributor-covenant.org/translations][translations].

[homepage]: https://www.contributor-covenant.org
[v2.1]: https://www.contributor-covenant.org/version/2/1/code_of_conduct.html
[Mozilla CoC]: https://github.com/mozilla/diversity
[FAQ]: https://www.contributor-covenant.org/faq
[translations]: https://www.contributor-covenant.org/translations



================================================
FILE: docs/project/contributing.md
================================================
---
title: Contributing
weight: 10
---

{{< lead text="Want to contribute to the project? Here's how." >}}

## Reporting issues

There are templates for bug reports and feature requests. Please use them. Issues opened with minimal information or not using one of the templates may be closed without comment.

When reporting issues, please try to be as descriptive as possible and include as much relevant information as you can. When providing the output of a command, please run the command in "very very verbose" mode (for Composer commands, this is `-vvv`; e.g. `composer install -vvv`).

### Security vulnerabilities

Please see the [security policy]({{< relref "security.md" >}}).

### Support

Support is handled through GitHub Discussions. Before asking for support, please read the relevant documentation. If an answer is too hard to find, please open an issue. Support requests submitted as an issue may be converted to a discussion or closed.

## Pull requests

In general, it's better to discuss a potential change before implementing it. If you have a specific feature in mind, please open a feature request and mention that you're planning on working on it. If a feature request already exists and has the `help-wanted` label, it's likely that a pull request adding the feature would be accepted.

### Workflow

Fork the project, create a feature branch, and send a pull request. Please sign your commits if possible.

Any added code should have test coverage of some kind. All PHP files in the project should comply with [PSR-12](https://www.php-fig.org/psr/psr-12/).

### Coding style fixes

Pull requests that are solely code style fixes are generally not accepted. Style fixes should be done as part of other pull requests to avoid unnecessary churn.

## Documentation

There are _always_ improvements that can be made to the documentation. There is some information about how to write documentation for this project and others in the [meta documentation](https://docs.cweagans.net/meta). If you need any assistance with documentation improvements, feel free to open an issue with some details on what you need.

## Sponsorship

If you don't have the time/energy to directly work on the project (or you're simply feeling generous), sponsorships are gratefully accepted via [GitHub Sponsors](https://github.com/sponsors/cweagans).



================================================
FILE: docs/project/security.md
================================================
---
title: Security
weight: 40
---

If there are vulnerabilities in composer-patches, don't hesitate to report them
using [the "Report a vulnerability" form](https://github.com/cweagans/composer-patches/security/advisories/new).

Once we have either published a fix or declined to address the vulnerability for whatever reason, you are free to
publicly disclose it. **Please do not disclose the vulnerability publicly until a fix is released.**



================================================
FILE: docs/troubleshooting/_index.md
================================================
---
title: Troubleshooting
weight: 30
---



================================================
FILE: docs/troubleshooting/guide.md
================================================
---
title: Guide
weight: 10
---

{{< lead text="Common problems that people have run into and how to fix them." >}}

## System readiness

If you've encountered a problem, a good first step is to run [`composer patches-doctor`]({{< relref "../usage/commands.md#composer-patches-doctor" >}}). This will run a few checks against your system and look for common configuration errors.

## Enable verbose output

If a command is failing and `composer patches-doctor` doesn't give you any errors, you should re-run your command with extra verbose output. Example: `composer -v install`. Composer Patches will usually display extra information about _why_ something is failing in any of the verbose output modes. Composer Patches only emits extra output in the normal verbose mode (`composer -v`), but if you're having other problems with Composer, you can also add one or two additional `v`'s to your command (`composer -vv` or `composer -vvv`) to get extra information from Composer itself.

## Upgrade system software

See the [system requirements]({{< relref "../getting-started/system-requirements.md" >}}) page for the minimum supported versions of PHP, Composer, and other software. Upgrade your software using the method appropriate for your operating system.

## Install patching software

Composer Patches requires `git` to be installed in order to apply patches. Previous versions of the plugin relied on
various versions of `patch`, but that is no longer the case. Make sure you have `git` installed and you should be all set.

## Download patches securely

If you've been referred here, you're trying to download patches over HTTP without explicitly telling Composer that you want to do that. See the [`secure-http`]({{< relref "../usage/configuration.md#secure-http" >}}) documentation for more information.



================================================
FILE: docs/troubleshooting/non-patchable-targets.md
================================================
---
title: Non-patchable targets
weight: 20
---

{{< lead text="There are some things that this plugin cannot patch." >}}

## Changes to `composer.json`

Attempting to use a patch to change `composer.json` in a dependency will _never_ work like you want it to.  By the time you're running `composer install`, the metadata from your dependencies' composer.json has already been aggregated by packagist (or whatever metadata repo you're using). Therefore, changes to `composer.json` in a dependency will have _no effect_ on installed packages.

This means that you cannot e.g. patch a dependency to be compatible with an earlier version of PHP or change the framework version that a plugin depends on.

If you need to modify a dependency's `composer.json` or its underlying dependencies, you must do one of the following:

- Work to get the underlying issue resolved in the upstream package.
- Fork the package and [specify your fork as the package repository](https://getcomposer.org/doc/05-repositories.md#vcs) in your root `composer.json`
- Specify compatible package version requirements in your root `composer.json`

@anotherjames over at @computerminds wrote an article about how to work around
that particular problem for a Drupal 8 -> Drupal 9 upgrade:

[James Williams](https://github.com/anotherjames) wrote an article about how to work around this problem for a Drupal 8 -> Drupal 9 upgrade: [Apply Drupal 9 compatibility patches with Composer](https://www.computerminds.co.uk/articles/apply-drupal-9-compatibility-patches-composer) ([archive](https://web.archive.org/web/20210124171010/https://www.computerminds.co.uk/articles/apply-drupal-9-compatibility-patches-composer)). Although it is specific to Drupal, you may be able to use the information to do something more specific to your project/ecosystem.

## Metapackages

Composer has the concept of a "metapackage", which is an empty package that contains requirements and will trigger their installation, but contains no files and will not write anything to the filesystem. Because there is no filesystem path available at all, this plugin is not capable of applying a patch to metapackages.

## Specific dependencies

Some dependencies cannot be patched by this plugin.

### `composer/composer` (installed globally)

Because Composer is typically installed and running on a system long before this plugin is available in a particular project, it is not possible for this plugin to modify Composer itself.

If you have installed Composer locally in your project (by requiring it in the `composer.json` for your project), you _can_ patch the project-level Composer. I'm not entirely sure why you'd want to do this, but it would technically work.

### `cweagans/composer-patches`

Composer Patches applies patches to dependencies as they are installed. If Composer Patches isn't installed, it cannot apply patches to itself as it is installed by Composer. There is no supported workaround for this limitation, as most behavior can be changed via [configuration]({{< relref "../usage/configuration.md" >}}) or through the [API]({{< relref "../api/overview.md" >}}).

### `cweagans/composer-configurable-plugin`

Similarly, because Composer Configurable Plugin is a dependency of Composer Patches (and is therefore installed _before_ Composer Patches), Composer Configurable Plugin cannot be patched. There is no supported workaround for this limitation. If you run into problems with this dependency, open an issue upstream and it will be addressed promptly.



================================================
FILE: docs/usage/_index.md
================================================
---
title: Usage
weight: 20
---



================================================
FILE: docs/usage/commands.md
================================================
---
title: Commands
weight: 50
---

## `composer patches-relock`
**Alias**: `composer prl`

`patches-relock` causes the plugin to re-discover all available patches and then write them to the `patches.lock.json` file for your project. This command should be used when changing the list of patches in your project. See the [recommended workflows]({{< relref "recommended-workflows.md" >}}) page for details.

---

## `composer patches-repatch`
**Alias**: `composer prp`

`patches-repatch` causes all patched dependencies to be deleted and re-installed, which causes patches to be re-applied to those dependencies. This command should be used when changing the list of patches in your project. See the [recommended workflows]({{< relref "recommended-workflows.md" >}}) page for details.

---

## `composer patches-doctor`
**Alias**: `composer pd`

`patches-doctor` runs a few checks against your system in an attempt to find common issues that people run into when using Composer Patches. A report will be emitted along with a handful of suggestions that may help you. The output of this command is required when submitting a bug report.



================================================
FILE: docs/usage/configuration.md
================================================
---
title: Configuration
weight: 10
---

{{< lead text="The plugin ships with reasonable defaults that should work on most environments, but many behaviors are configurable." >}}

## Configuration provided by Composer Patches

### `patches-file`

```json
{
    [...],
    "extra": {
        "composer-patches": {
            "patches-file": "mypatches.json"
        }
    }
}
```

**Default value**: `patches.json`

Patch definitions can additionally be loaded from a JSON file. This value should usually be the name of a file located alongside your `composer.json`.

Technically, this value can be a path to a file that is nested in a deeper directory in your project. I don't recommend doing that, as it may cause some confusion if you're using local patches (all paths to local patches will still be relative to the project root where `composer.json` is located).

---

### `package-depths`

```json
{
    [...],
    "extra": {
        "composer-patches": {
            "package-depths": {
                "my/package": 3
            }
        }
    }
}
```

**Default value**: empty

`package-depths` allows you to specify overrides for the default patch depth used for a given package. The value for each package is the value that would normally be passed to e.g. a `patch` command: `patch -p3 [...]`.

---

### `ignore-dependency-patches`

```json
{
    [...],
    "extra": {
        "composer-patches": {
            "ignore-dependency-patches": [
                "some/package",
            ]
        }
    }
}
```

**Default value**: empty

`ignore-dependency-patches` allows you to ignore patches defined by the listed dependencies. For instance, if your project requires `drupal/core` and `some/package`, and `some/package` defines a patch for `drupal/core`, listing `some/package` in `ignore-dependency-patches` would cause that patch to be ignored. This does _not_ affect the _target_ of those patches. For instance, listing `drupal/core` here would not cause patches _to_ `drupal/core` to be ignored.

---

### `default-patch-depth`

```json
{
    [...],
    "extra": {
        "composer-patches": {
            "default-patch-depth": 3
        }
    }
}
```

**Default value**: `1`

`default-patch-depth` changes the default patch depth for **every** package. The default value is usually the right choice (especially if the majority of your patches were created with `git`).

{{< warning title="Change this value with care" >}}
You probably don't need to change this value. Instead, consider setting a package-specific depth in `package-depths` or setting a `depth` on an individual patch.
{{< /warning >}}

---

### `disable-resolvers`

```json
{
    [...],
    "extra": {
        "composer-patches": {
            "disable-resolvers": [
                "\\cweagans\\Composer\\Resolver\\RootComposer",
                "\\cweagans\\Composer\\Resolver\\PatchesFile",
                "\\cweagans\\Composer\\Resolver\\Dependencies"
            ]
        }
    }
}
```

**Default value**: empty

`disable-resolvers` allows you to disable individual patch resolvers (for instance, if you want to disallow specifying patches in your root `composer.json`, you might want to add the `\\cweagans\\Composer\\Resolver\\RootComposer` resolver to this list). If a resolver is available and _not_ specified here, it will be used for resolving patches.

For completeness, both of the resolvers that ship with the plugin are listed above, but you should _not_ list both of them unless you don't want **any** patches to be discovered.

After changing this value, you should re-lock and re-apply patches to your project.

---

### `disable-downloaders`

```json
{
    [...],
    "extra": {
        "composer-patches": {
            "disable-downloaders": [
                "\\cweagans\\Composer\\Downloader\\ComposerDownloader"
            ]
        }
    }
}
```

**Default value**: empty

`disable-downloaders` allows you to disable individual patch downloaders. If a downloader is available and _not_ specified here, it may be used for downloading patches. 

{{< warning title="Change this value with care" >}}
You probably don't need to change this value unless you're building a plugin that provides an alternative download mechanism for packages.
{{< /warning >}}

---

### `disable-patchers`

```json
{
    [...],
    "extra": {
        "composer-patches": {
            "disable-patchers": [
                "\\cweagans\\Composer\\Patcher\\FreeformPatcher",
                "\\cweagans\\Composer\\Patcher\\GitPatcher",
                "\\cweagans\\Composer\\Patcher\\GitInitPatcher"
            ]
        }
    }
}
```

**Default value**: empty

`disable-patchers` allows you to disable individual patchers. If a patcher is available and _not_ specified here, it may be used to apply a patch to your project.
 
For completeness, all of the patchers that ship with the plugin are listed above, but you should _not_ list all of them. If no patchers are available, the plugin will throw an exception during `composer install`.

`GitPatcher` and `GitInitPatcher` should be enabled and disabled together. Do not disable one without the other.

After changing this value, you should re-lock and re-apply patches to your project.


## Relevant configuration provided by Composer

### `secure-http`

```json
{
    [...],
    "config": {
        "secure-http": false
    }
}
```

**Default value**: `true`

The relevant Composer documentation for this parameter can be found [here](https://getcomposer.org/doc/06-config.md#secure-http).

By default, Composer will block you from downloading anything from plain HTTP URLs. Setting this option will allow you to download data over plain HTTP. Generally, securing the endpoint where you are downloading patches from is a **much better** option. You can also download patches, save them locally, and distribute them with your project as an alternative. Nevertheless, if you really must download patches over plain HTTP, this is the way to do it.

---

### `HTTP_PROXY`

```shell
HTTP_PROXY=http://myproxy:1234 composer install
```

The relevant Composer documentation for this parameter can be found [here](https://getcomposer.org/doc/03-cli.md#http-proxy-or-http-proxy).

If you are using Composer behind an HTTP proxy (common in corporate network environments), setting this value will cause Composer to properly use the specified proxy. If you're using the default `ComposerDownloader` for downloading patches, this setting will be respected and patches will be downloaded through the proxy as well.

---

### `COMPOSER`

```shell
COMPOSER=composer-123.json composer install
```

The relevant Composer documentation for this parameter can be found [here](https://getcomposer.org/doc/03-cli.md#composer).

Some projects require the use of multiple `composer.json` files (along with their respective `composer.lock` and `patches.lock.json`). Composer Patches will create a different `patches.lock.json` file in the event that this environment variable is set. In the example above, `composer-123-patches.lock.json` would be the lock file that is used for patches.



================================================
FILE: docs/usage/defining-patches.md
================================================
---
title: Defining Patches
weight: 20
---

## Format

You can describe patches to the plugin in one of two ways: the compact format or the expanded format.

{{< callout title="Local files are supported too!" >}}
In any of the following examples, you can specify a path relative to the root of your project instead of a web address.
{{< /callout >}}

{{< warning title="Avoid using patches autogenerated by PR/MR URLs" >}}
The contents of these patches can change by pushing more commits to a pull request or merge request. A malicious user
could abuse this behavior to cause you to deploy code that you didn't mean to deploy. If you must use a PR/MR as the
basis for a patch, download the patch, include it in your project, and apply the patch using the local path instead.
{{< /warning >}}

### Compact format

```json
{
    [...],
    "extra": {
        "patches": {
            "the/project": {
                "This is the description of the patch": "https://www.example.com/path/to/file.patch",
                "This is another patch": "https://www.example.com/different/path/to/file.patch"
            }
        }
    }
}
```

This is the format that you may be familiar with from previous versions of Composer Patches. In your `composer.json`, this is an object with descriptions as keys and patch URLs for values. While this format is more convenient, it has some shortcomings - namely that only a description and URL can be specified. If this works for you/your project, you can keep using this format.

### Expanded format

```json
{
    [...],
    "extra": {
        "patches": {
            "the/project": [
                {
                    "description": "This is the description of the patch",
                    "url": "https://www.example.com/path/to/file.patch"
                },
                {
                    "description": "This is another patch",
                    "url": "https://www.example.com/different/path/to/file.patch"
                }
            ]
        }
    }
}
```

Internally, the plugin uses the expanded format for _all_ patches. Similar to the compact format, the only required fields in the expanded format are `description` and `url`. However, in the expanded format, you can specify several other fields:

```json
{
    [...],
    "extra": {
        "patches": {
            "the/project": [
                {
                    "description": "This is the description of the patch",
                    "url": "https://www.example.com/path/to/file.patch",
                    "sha256": "6f024c51ca5d0b6568919e134353aaf1398ff090c92f6173f5ce0315fa266b93",
                    "depth": 2,
                    "extra": {},
                },
                {
                    "description": "This is another patch",
                    "url": "https://www.example.com/different/path/to/file.patch",
                    "sha256": "795a84197ee01b9d50b40889bc5689e930a8839db3d43010e887ddeee643ccdc",
                    "depth": 3,
                    "extra": {
                        "issue-tracker-url": "https://jira.ecorp.com/issues/SM-519"
                    }
                }
            ]
        }
    }
}
```

`sha256` can either be specified in your patch definition as above or the sha256 of a patch file will be calculated and written to your `patches.lock.json` file as part of installation.

`depth` can be specified on a per-patch basis. If specified, this value overrides any other defaults. If not specified, the first available depth out of the following will be used:

1. A package-specific depth set in [`package-depths`]({{< relref "configuration.md#package-depths" >}})
2. Any package-specific depth override set globally in the plugin (see `cweagans\Composer\Util::getDefaultPackagePatchDepth()` for details.)
3. The global [`default-patch-depth`]({{< relref "configuration.md#default-patch-depth" >}})

`extra` is primarily a place for developers to store extra data related to a patch, but if you just need a place to put some extra data about a patch, `extra` is a good place for it. No validation is performed on the contents of `extra`. The [Freeform patcher]({{< relref "freeform-patcher.md" >}}) stores data here.


## Locations

Patches can be defined in multiple places. With the default configuration, you can define plugins in either `composer.json` or a `patches.json` file.

### `composer.json`

As in previous versions of Composer Patches, you can store patch definitions in your root `composer.json` like so:

```json
{
    [...],
    "extra": {
        "patches": {
            // your patch definitions here
        }
    }
}
```
This approach works for many teams, but you should consider moving your patch definitions to a separate `patches.json`. Doing so will mean that you don't have to update `composer.lock` every time you change a patch. Because patch data is locked in `patches.lock.json`, moving the data out of `composer.json` has little downside and can improve your workflow substantially.

### Patches file

If you're defining patches in `patches.json` (or some other separate patches file), the same formats can be used. Rather than nesting patch definitions in the `extra` key in `composer.json`, the plugin expects patches to be defined in a root-level `patches` key like so:

```json
{
    "patches": {
        // your patch definitions here
    }
}
```

### Dependencies

Packages required by your project can define patches as well. They can do so by defining patches in their root `composer.json` file. Defining patches in a separate `patches.json` in a dependency is currently unsupported.

{{< callout title="Patch paths are always relative to the root of your project" >}}
Patches defined by a dependency should always use a publicly accessible URL, rather than a local file path. Composer Patches _will not_ attempt to modify file paths so that the patch file can be found within the installed location of a dependency.
{{< /callout >}}


## Duplicate patches

If the same patch is defined in multiple places, the first one added to the patch collection "wins". Subsequent definitions of the same patch will be ignored without emitting an error. The criteria used for determining whether two patches are the same are:

* They have the same URL
* They have the same sha256 hash

## `patches.lock.json`

If `patches.lock.json` does not exist the first time you run `composer install` with this plugin enabled, one will be created for you. Generally, you shouldn't need to do anything with this file: commit it to your project repository alongside your `composer.json` and `composer.lock`, and commit any changes when you change your patch definitions.

This file is similar to `composer.lock` in that it includes a `_hash` and the expanded definition for all patches in your project. When `patches.lock.json` exists, patches will be installed from the locked definitions in this file (_instead_ of using the definitions in `composer.json` or elsewhere).



================================================
FILE: docs/usage/freeform-patcher.md
================================================
---
title: Freeform Patcher
weight: 30
---

{{< lead text="The core patchers try to take care of as many cases as possible, but sometimes you need custom behavior." >}}

Composer Patches now includes a "freeform patcher", which essentially lets you define a patcher and its arguments in your patch definition.

## Usage

To use the freeform patcher, you must use the [expanded format]({{< relref "defining-patches.md#expanded-format" >}}) for your patch definition. You'll need to add a few extra values to the `extra` key in your patch definition like so:

```json
{
    [...],
    "extra": {
        "patches": {
            "the/project": [
                {
                    "description": "This is another patch",
                    "url": "https://www.example.com/different/path/to/file.patch"
                    "depth": 123
                    "extra": {
                        "freeform": {
                            "executable": "/path/to/your/executable",
                            "args": "--verbose %s %s %s",
                        }
                    },
                },
            ]
        }
    }
}
```

If the `executable` and `args` values are not populated, the freeform patcher will not perform any work.

The `%s` placeholders will be populated by escaped arguments that are always provided in this order:

1. Patch depth
2. Path to the location on disk where the dependency was installed
3. Local path to the patch file

It is not possible to change the order of these arguments, but you can always create a small wrapper script in your project and handle the arguments how you'd like.

In this example, the command that would be run by the patcher is
```shell
/path/to/your/executable --verbose '/full/path/to/vendor/the/project' '123' '/full/path/to/file.patch`
```

{{< callout title="$PATH will be searched for executables" >}}
If the executable you want to run is included in your `$PATH`, you do not have to specify the full path to the executable.
{{< /callout >}}

## Dry run

If your patcher is capable of testing whether or not a patch can be applied (for instance, `patch` can do this with the `--dry-run` argument), you can also supply a set of dry run arguments that will be run first:

```json
{
    [...],
    "extra": {
        "patches": {
            "the/project": [
                {
                    "description": "This is another patch",
                    "url": "https://www.example.com/different/path/to/file.patch"
                    "depth": 123
                    "extra": {
                        "freeform": {
                            "executable": "/path/to/your/executable",
                            "args": "--verbose %s %s %s",
                            "dry_run_args": "--verbose --dry-run %s %s %s"
                        }
                    },
                },
            ]
        }
    }
}
```

The arguments will be provided in the same order as the `args` value.

## Tips

### Exit codes matter

If your tool returns an exit code of `0`, Composer Patches will assume that the patch was applied correctly (or that the dry run was successful and the patch should be applied). If it returns anything other than `0`, Composer Patches will assume that the patch was unsuccessful (or that the dry run indicated that attempting to apply the patch would be unsuccessful).

### Always run in verbose mode

Any patcher you configure here should always include the `--verbose` flag (or whatever your patcher's equivalent is). The output will not be printed to the console during normal operation, but if you are running composer with the `--verbose` flag (for instance, with `composer install --verbose` or `composer install -v`), the output will be printed so that you can see what was happening.



================================================
FILE: docs/usage/recommended-workflows.md
================================================
---
title: Recommended Workflows
weight: 40
---

{{< lead text="Common workflows for working with Composer Patches on a team." >}}

## Initial setup

The plugin can safely be [installed]({{< relref "../getting-started/installation.md" >}}) as part of initial project setup, even if you don't have any patches to apply right away. A `patches.lock.json` will still be written, but it will be empty.

## Add a patch to your project

1. [Define a patch]({{< relref "defining-patches.md" >}}) in your `composer.json` or your external patches file (either will work by default, but choose the appropriate place based on how your project is configured).
2. Run [`composer patches-relock`]({{< relref "commands.md#composer-patches-relock" >}}) to regenerate `patches.lock.json` with your new patch.
3. Run [`composer patches-repatch`]({{< relref "commands.md#composer-patches-repatch" >}}) to delete patched dependencies and reinstall them with any defined patches {{< warning title="Running `composer patches-repatch` will delete data" >}}
Ensure that you don't have any unsaved changes in any patched dependencies in your project.
{{< /warning >}}
4. If your patch definition was added to `composer.json`, run `composer update --lock` to update the content hash in `composer.lock`.
5. Commit any related changes to your external patches file (if configured), `composer.json`, `composer.lock`, and `patches.lock.json`.

## Apply patches added to the project by someone else

If you have an existing copy of the project and you're updating it to include someone else's changes:

1. Pull changes from your project's version control system.
2. Run [`composer patches-repatch`]({{< relref "commands.md#composer-patches-repatch" >}}) {{< warning title="Running `composer patches-repatch` will delete data" >}}
Ensure that you don't have any unsaved changes in any patched dependencies in your project.
{{< /warning >}}

If you're installing the project from scratch:

1. Clone the project
2. Run `composer install`

## Remove a patch

1. Delete the patch definition from your `composer.json` or external patches file.
2. Run [`composer patches-relock`]({{< relref "commands.md#composer-patches-relock" >}}) to regenerate `patches.lock.json` with your new patch.
3. Manually delete the dependency that you removed a patch from (the location of the dependency will vary by project, but a good starting point is to look in the `vendor/` directory).
4. Run [`composer patches-repatch`]({{< relref "commands.md#composer-patches-repatch" >}}) to delete patched dependencies and reinstall them with any defined patches {{< warning title="Running `composer patches-repatch` will delete data" >}}
Ensure that you don't have any unsaved changes in any patched dependencies in your project.
{{< /warning >}}
5. If your patch definition was removed from  `composer.json`, run `composer update --lock` to update the content hash in `composer.lock`.
6. Commit any related changes to your external patches file (if configured), `composer.json`, `composer.lock`, and `patches.lock.json`.
