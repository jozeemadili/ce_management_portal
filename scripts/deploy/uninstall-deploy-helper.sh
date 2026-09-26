#!/bin/bash
#
# Removes the deploy helper and its sudoers rule (undoes
# install-deploy-helper.sh). File ownership changes are NOT reverted.
#
#   sudo bash scripts/deploy/uninstall-deploy-helper.sh
#
set -euo pipefail

[ "$(id -u)" -eq 0 ] || { echo "run with sudo" >&2; exit 1; }

rm -f /etc/sudoers.d/ce-deployment && echo "Removed /etc/sudoers.d/ce-deployment"
rm -f /usr/local/sbin/ce-deploy-helper && echo "Removed /usr/local/sbin/ce-deploy-helper"
visudo -c >/dev/null && echo "sudoers configuration valid"

echo "php artisan deploy now needs --no-permissions --no-restart (or the helper reinstalled)."
