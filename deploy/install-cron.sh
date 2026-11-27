#!/usr/bin/env bash
set -euo pipefail
umask 077
state="$HOME/.malwareguard-deploy"
[[ -x "$state/deploy.sh" ]]
entry='*/5 * * * * /bin/bash "$HOME/.malwareguard-deploy/deploy.sh" >> "$HOME/.malwareguard-deploy/deploy.log" 2>&1'
tmp=$(mktemp -d "$state/.cron.XXXXXXXX")
trap 'rm -rf -- "$tmp"' EXIT
if ! LC_ALL=C crontab -l > "$tmp/old" 2>"$tmp/error"; then
    grep -qi 'no crontab for' "$tmp/error" || { cat "$tmp/error" >&2; exit 1; }
fi
old_entry='*/5 * * * * /bin/bash "$HOME/thitruongmmo.com/.mg-deploy/deploy.sh" >> "$HOME/thitruongmmo.com/.mg-deploy/deploy.log" 2>&1'
if grep -Fxq "$old_entry" "$tmp/old" || ! grep -Fxq "$entry" "$tmp/old"; then
    cp "$tmp/old" "$state/crontab-before-layout-change"
    awk -v old="$old_entry" -v new="$entry" '$0 != old && $0 != new' "$tmp/old" > "$tmp/new"
    printf '%s\n' "$entry" >> "$tmp/new"
    crontab "$tmp/new"
fi
crontab -l | grep -Fx "$entry"
