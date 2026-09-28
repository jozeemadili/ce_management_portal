#!/bin/bash
#
# Installs the root-owned deploy helper and a NARROW sudoers rule so that
# `php artisan deploy --force` runs without a password prompt.
#
# Run it yourself, once, on the server (it asks before changing anything):
#
#   sudo bash scripts/deploy/install-deploy-helper.sh
#
# Options (all optional - sensible values are detected and shown first):
#   --user=josephat           deploy user (default: the user who ran sudo)
#   --web-group=www-data      group PHP-FPM runs as (default: detected)
#   --php-fpm=php8.2-fpm      PHP-FPM service (default: detected)
#   --nginx=nginx             Nginx service (default: nginx)
#   --live=/var/www/html/event.christembassytanzania.org
#   --git=/var/www/html/ce_management_portal_git
#   --with-sync               also allow rsync through sudo (only if the deploy
#                             user cannot own the LIVE code - not recommended)
#   --own-live-code           one-time: give the deploy user ownership of the
#                             LIVE code so rsync needs NO sudo (recommended)
#   --yes                     do not ask for confirmation
#
# It never grants general sudo, never uses chmod 777, and validates the
# sudoers file with visudo before it becomes active.
#
set -euo pipefail

HELPER_SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/ce-deploy-helper"
HELPER_DST="/usr/local/sbin/ce-deploy-helper"
SUDOERS_DST="/etc/sudoers.d/ce-deployment"

LIVE="/var/www/html/event.christembassytanzania.org"
GIT="/var/www/html/ce_management_portal_git"
DEPLOY_USER="${SUDO_USER:-}"
WEB_GROUP=""
PHP_FPM=""
NGINX="nginx"
WITH_SYNC="no"
OWN_LIVE="no"
ASSUME_YES="no"

for arg in "$@"; do
  case "$arg" in
    --user=*) DEPLOY_USER="${arg#*=}" ;;
    --web-group=*) WEB_GROUP="${arg#*=}" ;;
    --php-fpm=*) PHP_FPM="${arg#*=}" ;;
    --nginx=*) NGINX="${arg#*=}" ;;
    --live=*) LIVE="${arg#*=}" ;;
    --git=*) GIT="${arg#*=}" ;;
    --with-sync) WITH_SYNC="yes" ;;
    --own-live-code) OWN_LIVE="yes" ;;
    --yes) ASSUME_YES="yes" ;;
    -h|--help) sed -n '2,30p' "$0"; exit 0 ;;
    *) echo "Unknown option: $arg" >&2; exit 64 ;;
  esac
done

die() { echo "ERROR: $*" >&2; exit 1; }

[ "$(id -u)" -eq 0 ] || die "run with sudo: sudo bash $0"
[ -f "$HELPER_SRC" ] || die "helper template not found: $HELPER_SRC"
command -v visudo >/dev/null || die "visudo not found"

# ---- detect -----------------------------------------------------------------
if [ -z "$PHP_FPM" ]; then
  mapfile -t running < <(systemctl list-units --type=service --state=running --no-legend --plain 'php*-fpm.service' 2>/dev/null | awk '{print $1}' | sed 's/\.service$//')
  if [ "${#running[@]}" -eq 1 ]; then
    PHP_FPM="${running[0]}"
  elif [ "${#running[@]}" -gt 1 ]; then
    die "several PHP-FPM services are running (${running[*]}) - choose one with --php-fpm=NAME"
  else
    die "no running PHP-FPM service found - pass --php-fpm=NAME"
  fi
fi
if [ -z "$WEB_GROUP" ]; then
  pool_group=$(grep -hE '^\s*group\s*=' /etc/php/*/fpm/pool.d/*.conf 2>/dev/null | head -1 | awk -F= '{gsub(/ /,"",$2); print $2}')
  WEB_GROUP="${pool_group:-www-data}"
fi

# ---- validate (these values are written into a root-run script) --------------
safe_path='^/[A-Za-z0-9._/-]+$'
safe_name='^[a-z_][a-z0-9_.-]*[$]?$'
safe_service='^[A-Za-z0-9@._-]+$'
[[ "$LIVE" =~ $safe_path ]] || die "unsafe LIVE path: $LIVE"
[[ "$GIT" =~ $safe_path ]] || die "unsafe Git path: $GIT"
[ -n "$DEPLOY_USER" ] || die "deploy user unknown - pass --user=NAME"
[[ "$DEPLOY_USER" =~ $safe_name ]] || die "unsafe user name: $DEPLOY_USER"
[ "$DEPLOY_USER" != "root" ] || die "the deploy user must not be root"
[[ "$WEB_GROUP" =~ $safe_name ]] || die "unsafe group name: $WEB_GROUP"
[[ "$PHP_FPM" =~ $safe_service ]] || die "unsafe PHP-FPM service name: $PHP_FPM"
[[ "$NGINX" =~ $safe_service ]] || die "unsafe Nginx service name: $NGINX"
id "$DEPLOY_USER" >/dev/null 2>&1 || die "user does not exist: $DEPLOY_USER"
getent group "$WEB_GROUP" >/dev/null || die "group does not exist: $WEB_GROUP"
[ -f "$LIVE/artisan" ] || die "LIVE is not a Laravel app: $LIVE"
[ -d "$GIT/.git" ] || die "not a Git repository: $GIT"
[ "$(readlink -f "$LIVE")" != "$(readlink -f "$GIT")" ] || die "LIVE and Git paths are the same"
systemctl cat "$PHP_FPM.service" >/dev/null 2>&1 || die "service not found: $PHP_FPM"
systemctl cat "$NGINX.service" >/dev/null 2>&1 || die "service not found: $NGINX"

# ---- build the sudoers rule ------------------------------------------------
H="$HELPER_DST"
RULES="$H check, $H fix-permissions, $H restart-php, $H restart-nginx"
if [ "$WITH_SYNC" = "yes" ]; then
  RULES="$RULES, $H sync, $H sync --dry-run"
fi
SUDOERS_CONTENT="# Managed by ce_management_portal scripts/deploy/install-deploy-helper.sh
# Lets $DEPLOY_USER run ONLY the root-owned deploy helper, with these exact
# arguments, without a password (for php artisan deploy --force).
# Remove with: sudo bash scripts/deploy/uninstall-deploy-helper.sh
Defaults!$H !requiretty
$DEPLOY_USER ALL=(root) NOPASSWD: $RULES
"

cat <<EOF

Christ Embassy Portal - deploy helper installer
------------------------------------------------
Deploy user:      $DEPLOY_USER
Web server group: $WEB_GROUP
PHP-FPM service:  $PHP_FPM
Nginx service:    $NGINX
LIVE:             $LIVE
Git working copy: $GIT
rsync via sudo:   $WITH_SYNC
Own LIVE code:    $OWN_LIVE

Will install:
  $HELPER_DST   (root:root 0755)
  $SUDOERS_DST  (root:root 0440):

$SUDOERS_CONTENT
EOF
if [ "$OWN_LIVE" = "yes" ]; then
  cat <<EOF
One-time ownership change (so rsync runs WITHOUT sudo):
  chown -hRP $DEPLOY_USER:$WEB_GROUP on $LIVE, except storage/, bootstrap/cache/ and .env
  .env: $DEPLOY_USER:$WEB_GROUP 0640 (readable by PHP, not by other users)

EOF
fi

if [ "$ASSUME_YES" != "yes" ]; then
  read -r -p "Proceed? (yes/no) " answer
  [ "$answer" = "yes" ] || { echo "Nothing changed."; exit 0; }
fi

# ---- install the helper ------------------------------------------------------
tmp_helper=$(mktemp)
tmp_sudoers=$(mktemp)
trap 'rm -f "$tmp_helper" "$tmp_sudoers"' EXIT

sed -e "s#@@LIVE_PATH@@#$LIVE#" \
    -e "s#@@GIT_PATH@@#$GIT#" \
    -e "s#@@DEPLOY_USER@@#$DEPLOY_USER#" \
    -e "s#@@WEB_GROUP@@#$WEB_GROUP#" \
    -e "s#@@PHP_FPM_SERVICE@@#$PHP_FPM#" \
    -e "s#@@NGINX_SERVICE@@#$NGINX#" \
    -e "s#@@ALLOW_SYNC@@#$WITH_SYNC#" \
    "$HELPER_SRC" > "$tmp_helper"
grep -qE '@@[A-Z_]+@@' "$tmp_helper" && die "helper still has unfilled placeholders"
bash -n "$tmp_helper" || die "helper has a syntax error"
install -o root -g root -m 0755 "$tmp_helper" "$HELPER_DST"
echo "Installed $HELPER_DST"

# ---- install sudoers - validated BEFORE it becomes active -----------------------
printf '%s' "$SUDOERS_CONTENT" > "$tmp_sudoers"
visudo -cf "$tmp_sudoers" >/dev/null || die "sudoers rule failed validation - nothing installed"
install -o root -g root -m 0440 "$tmp_sudoers" "$SUDOERS_DST"
visudo -c >/dev/null || { rm -f "$SUDOERS_DST"; die "full sudoers check failed - removed $SUDOERS_DST again"; }
echo "Installed $SUDOERS_DST (validated with visudo)"

# ---- optional one-time ownership of LIVE code ------------------------------------
if [ "$OWN_LIVE" = "yes" ]; then
  find "$LIVE" -mindepth 1 \( -path "$LIVE/storage" -o -path "$LIVE/bootstrap/cache" -o -path "$LIVE/.env" \) -prune \
    -o -exec chown -h "$DEPLOY_USER:$WEB_GROUP" {} +
  chown -h "$DEPLOY_USER:$WEB_GROUP" "$LIVE"
  if [ -f "$LIVE/.env" ]; then
    chown "$DEPLOY_USER:$WEB_GROUP" "$LIVE/.env"
    chmod 0640 "$LIVE/.env"
  fi
  echo "LIVE code now owned by $DEPLOY_USER:$WEB_GROUP (storage/, bootstrap/cache handled by fix-permissions)"
  "$HELPER_DST" fix-permissions
fi

cat <<EOF

Done. Test as $DEPLOY_USER (none of these should ask for a password):
  sudo -n $HELPER_DST check
  sudo -n -l $HELPER_DST fix-permissions
  cd $LIVE && php artisan deploy --dry-run
EOF
