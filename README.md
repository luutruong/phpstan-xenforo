# PHPStan with XenForo

Automatically analyse add-ons to find potential bugs.

- Analyse PHP codes
- Analyse phrases to prevent missing phrases

## Installation

Clone this repo to your local.

Install all dependencies:

```bash
composer install
```

## Usage

```bash
$ sh xf-addon--analyse.sh /path/to/xenforo addonId
```

Example:

```bash
$ sh xf-addon--analyse.sh /var/www/html XFMG
```
