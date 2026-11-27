#!/usr/bin/env bash
set -euo pipefail
umask 077
state="$HOME/thitruongmmo.com/.mg-deploy"
[[ -x "$state/deploy.sh" ]]
entry='*/5 * * * * /bin/bash "$HOME/thitruongmmo.com/.mg-deploy/deploy.sh" >> "$HOME/thitruongmmo.com/.mg-deploy/deploy.log" 2>&1'
tmp=$(mktemp -d "$state/.cron.XXXXXXXX")
trap 'rm -rf -- "$tmp"' EXIT
if ! LC_ALL=C crontab -l > "$tmp/old" 2>"$tmp/error"; then
    grep -qi 'no crontab for' "$tmp/error" || { cat "$tmp/error" >&2; exit 1; }
fi
if ! grep -Fxq "$entry" "$tmp/old"; then
    cp "$tmp/old" "$state/crontab-before-malwareguard"
    { cat "$tmp/old"; printf '\n%s\n' "$entry"; } > "$tmp/new"
    crontab "$tmp/new"
fi
crontab -l | grep -Fx "$entry"
