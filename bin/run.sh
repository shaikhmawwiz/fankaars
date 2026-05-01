#!/usr/bin/env bash
set -euo pipefail
php -S "${HOST:-127.0.0.1}:${PORT:-8080}" -t public
