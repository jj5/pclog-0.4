#!/bin/bash

main() {

  set -euo pipefail;

  [ -x bin/install-deps.sh ] || { echo "bin/install-deps.sh must be run from project root."; exit 1; }

  npm ci;

  composer install;

  vendor/bin/browscap-php browscap:update

}

main "$@";
