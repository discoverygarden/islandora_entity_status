# Islandora Entity Status

![](https://github.com/discoverygarden/islandora_entity_status/actions/workflows/lint.yml/badge.svg)
![](https://github.com/discoverygarden/islandora_entity_status/actions/workflows/semver.yml/badge.svg)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

## Introduction

Cascades entity status to all referenced entities in an Islandora repository item

## Table of Contents

* [Features](#features)
* [Requirements](#requirements)
* [Installation](#installation)
* [Usage](#usage)
* [Troubleshooting/Issues](#troubleshootingissues)
* [Maintainers and Sponsors](#maintainers-and-sponsors)
* [Development/Contribution](#developmentcontribution)
* [License](#license)

## Features

Includes the command `islandora_entity_status:find-update-related-nodes` (aliased as `furnd`). This command will update a node's status and its related children's statuses in bulk.

It has two parameters:
 - `nodes`: A comma-separated list of node IDs to be be processed.
 - `status`: The status to be assigned to the nodes (0 or 1).

The module will maintain these status updates to their children through `node_update` and `media_presave` hooks. As well, the user will be alerted that their updates will cascade to their children when updating a node's status.

## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/Islandora/islandora)

## Installation

Install as usual, see
[this]( https://www.drupal.org/docs/extending-drupal/installing-modules) for
further information.

## Usage

```bash
drush islandora_entity_status:furnd --nodes="1,2,3" --status=1
```

## Troubleshooting/Issues

Having problems or solved a problem? Contact [discoverygarden](http://support.discoverygarden.ca).

## Maintainers/Sponsors

This project has been sponsored by:

* Boston College
* [discoverygarden](http://wwww.discoverygarden.ca)

## Development/Contribution

If you would like to contribute to this module, please check out our helpful
[Documentation for Developers](https://github.com/Islandora/islandora/wiki#wiki-documentation-for-developers)
info, [Developers](http://islandora.ca/developers) section on Islandora.ca and
contact [discoverygarden](http://support.discoverygarden.ca).

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
