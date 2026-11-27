#!/usr/bin/env bash
set -euo pipefail
export PATH="/usr/local/cpanel/3rdparty/lib/path-bin:/usr/local/bin:/usr/bin:/bin:$PATH"
export GIT_TERMINAL_PROMPT=0
umask 077
site="$HOME/thitruongmmo.com"
state="$HOME/.malwareguard-deploy"
source_dir="$state/source"
config="$site/config/local.php"
[[ -r "$config" && -d "$source_dir/.git" ]]
exec 9>"$state/lock"
flock -n 9 || exit 0
git -C "$source_dir" fetch -q origin refs/heads/main
revision=$(git -C "$source_dir" rev-parse FETCH_HEAD)
if [[ -f "$state/active" && $(cat "$state/active") == "$revision" && -f "$state/mirrored" ]]; then exit 0; fi
stage=$(mktemp -d "$state/.stage.XXXXXXXX")
trap 'rm -rf -- "$stage"' EXIT
git -C "$source_dir" archive "$revision" | tar -x -C "$stage"
cd "$stage"
find app public bin config tests -type f -name '*.php' -print0 > "$stage/php-files"
while IFS= read -r -d '' file; do php -l "$file" >/dev/null; done < "$stage/php-files"
rm "$stage/php-files"
php tests/run.php
ln -s "$config" config/local.php
php bin/migrate.php
rm config/local.php
python3 deploy/publish.py "$site" "$stage" "$revision"
install -m 0700 deploy/cpanel.sh "$state/runner.new"
mv -f "$state/runner.new" "$state/deploy.sh"
touch "$state/mirrored"
echo "$revision" > "$state/active.new"
mv -f "$state/active.new" "$state/active"
printf '%s Published %s\n' "$(date -Is)" "$revision"
