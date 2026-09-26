#!/bin/bash
#
# READ-ONLY server inspection for the deployment setup. Changes nothing,
# prints no secrets (.env is only checked for existence/permissions, never
# read). Run it as the deploy user, from anywhere:
#
#   bash scripts/deploy/inspect-server.sh
#
# Share the output when setting up or troubleshooting php artisan deploy.
#
set -uo pipefail

LIVE="${1:-/var/www/html/event.christembassytanzania.org}"
GIT="${2:-/var/www/html/ce_management_portal_git}"

h() { printf '\n== %s ==\n' "$*"; }

h "Who"
echo "user: $(id -un)   groups: $(id -Gn)"
echo "host: $(hostname)   os: $(. /etc/os-release 2>/dev/null && echo "$PRETTY_NAME")"

h "Tools"
for t in git rsync php nginx systemctl visudo; do
  printf '%-10s %s\n' "$t" "$(command -v "$t" || echo 'NOT FOUND')"
done
php -v 2>/dev/null | head -1
git --version 2>/dev/null
rsync --version 2>/dev/null | head -1

h "Services"
systemctl list-units --type=service --all --no-legend --plain 'php*-fpm.service' 2>/dev/null | awk '{print "php-fpm: " $1 "  " $3 "/" $4}'
systemctl list-units --type=service --all --no-legend --plain 'nginx.service' 2>/dev/null | awk '{print "nginx:   " $1 "  " $3 "/" $4}'
echo "php-fpm pool user/group:"; grep -hE '^\s*(user|group)\s*=' /etc/php/*/fpm/pool.d/*.conf 2>/dev/null | sort -u | sed 's/^/  /'
echo "nginx worker user:"; grep -hE '^\s*user\s' /etc/nginx/nginx.conf 2>/dev/null | sed 's/^/  /'

for dir in "$LIVE" "$GIT"; do
  h "Ownership: $dir"
  [ -d "$dir" ] || { echo "MISSING"; continue; }
  ls -ld "$dir"
  echo "owners (user:group count), excluding vendor/ node_modules/ .git/:"
  find "$dir" \( -path "$dir/vendor" -o -path "$dir/node_modules" -o -path "$dir/.git" \) -prune -o -printf '%u:%g\n' 2>/dev/null | sort | uniq -c | sort -rn | head -8
  echo "vendor/ owners:"; find "$dir/vendor" -printf '%u:%g\n' 2>/dev/null | sort | uniq -c | sort -rn | head -4
  first=$(find "$dir" \( -path "$dir/storage" -o -path "$dir/.git" -o -path "$dir/bootstrap/cache" -o -path "$dir/.env" -o -path "$dir/public/storage" \) -prune -o ! -writable -print -quit 2>/dev/null)
  echo "first path NOT writable by $(id -un) (outside protected paths): ${first:-none - all writable}"
done

h "LIVE runtime paths"
for p in storage storage/logs storage/framework bootstrap/cache public/storage .env; do
  [ -e "$LIVE/$p" ] || [ -L "$LIVE/$p" ] && ls -ld "$LIVE/$p" || echo "missing: $p"
done
[ -L "$LIVE/public/storage" ] && echo "public/storage -> $(readlink "$LIVE/public/storage")  (target exists: $([ -e "$LIVE/public/storage" ] && echo yes || echo NO))"
echo "storage owners:"; find "$LIVE/storage" "$LIVE/bootstrap/cache" -printf '%u:%g %m\n' 2>/dev/null | sort | uniq -c | sort -rn | head -6

h "Git working copy"
if [ -d "$GIT/.git" ]; then
  git -C "$GIT" remote -v | head -2
  echo "branch: $(git -C "$GIT" rev-parse --abbrev-ref HEAD)   head: $(git -C "$GIT" log -1 --format='%h %s')"
  echo "uncommitted tracked changes: $(git -C "$GIT" status --porcelain --untracked-files=no | wc -l)"
  echo "untracked files: $(git -C "$GIT" status --porcelain | grep -c '^??')"
  echo "vendor/ committed files: $(git -C "$GIT" ls-files vendor | wc -l)"
else
  echo "not a Git repository"
fi

h "Laravel (LIVE)"
(cd "$LIVE" && php artisan --version 2>/dev/null)
(cd "$LIVE" && php artisan list 2>/dev/null | grep -E '^\s+deploy\b' || echo "deploy command: not present yet")
ls "$LIVE/bootstrap/cache" 2>/dev/null | sed 's/^/  cache: /'

h "sudo (what $(id -un) may run WITHOUT a password)"
sudo -n -l 2>&1 | sed 's/^/  /' | head -20
ls -l /etc/sudoers.d/ 2>/dev/null | sed 's/^/  /'
[ -x /usr/local/sbin/ce-deploy-helper ] && { ls -l /usr/local/sbin/ce-deploy-helper; sudo -n /usr/local/sbin/ce-deploy-helper check 2>&1 | sed 's/^/  helper: /'; } || echo "  ce-deploy-helper: not installed"

echo
echo "Done - nothing was changed."
