#!/bin/bash

set -e

_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" > /dev/null && pwd)"
_config="$_dir/phpstan.neon"

_xfDir="$1"
_addOnId="$2"

if [[ ! -d "$_xfDir" ]]; then
  echo "'$_xfDir' is invalid path to XenForo"
  exit 1
else
  if [[ ! -f "$_xfDir/src/XF.php" ]]; then
    echo "$_xfDir is not XenForo directory."
    exit 1
  fi
fi

if [[ ! -d "$_xfDir/src/addons/$_addOnId" ]]; then
  echo "Invalid add-on ID"
  exit 1
fi

if [[ -f "$_xfDir/src/addons/$_addOnId/_files/dev/phpstan.neon" ]]; then
  _config="$_xfDir/src/addons/$_addOnId/_files/dev/phpstan.neon"
  echo "Using custom phpneon file: $_config"
fi

export PHPSTAN_XENFORO_ROOT_DIR="$1"

exec vendor/bin/phpstan analyse \
  --level 6 \
  --configuration "$_config" \
  --error-format table \
  --memory-limit 512M \
  "$_xfDir/src/addons/$_addOnId"