#!/usr/bin/env bash
# Run from cPanel Terminal or cron after cloning into .mg-deploy/source.
set -euo pipefail
export PATH="/usr/local/cpanel/3rdparty/lib/path-bin:/usr/local/bin:/usr/bin:/bin:$PATH"
export GIT_TERMINAL_PROMPT=0
umask 077

site="$HOME/thitruongmmo.com"
state="$site/.mg-deploy"
source_dir="$state/source"
public="$site/public_html/malwareguard"
config="$site/malwareguard_app/config/local.php"
site_url="https://thitruongmmo.com"
[[ -f "$public/index.php" && -r "$config" ]]
mkdir -p "$state/releases"
exec 9>"$state/lock"
flock -n 9 || exit 0

git -C "$source_dir" fetch -q origin refs/heads/main
revision=$(git -C "$source_dir" rev-parse FETCH_HEAD)
release="$state/releases/$revision"
if [[ -f "$state/active" && $(cat "$state/active") == "$revision" ]]; then exit 0; fi
work=$(mktemp -d "$state/.stage.XXXXXXXX")
trap 'rm -rf -- "$work"' EXIT
mkdir "$work/release"
git -C "$source_dir" archive "$revision" | tar -x -C "$work/release"
cd "$work/release"
find app public bin config tests -type f -name '*.php' -print0 > "$work/php-files"
while IFS= read -r -d '' file; do php -l "$file" >/dev/null; done < "$work/php-files"
php tests/run.php
ln -s "$config" config/local.php
if [[ ! -d "$release" ]]; then mv "$work/release" "$release"; fi
cd "$release"

# Each HTML response points to its own immutable stylesheet and script URLs.
# Publish the complete asset directory before making its index live.
assets="$public/assets"
install -d -m 0755 "$assets"
if [[ ! -d "$assets/$revision" ]]; then
    cp -R public/assets "$work/assets"
    find "$work/assets" -type d -exec chmod 755 '{}' +
    find "$work/assets" -type f -exec chmod 644 '{}' +
    mv "$work/assets" "$assets/$revision"
fi
find public/assets -type f -print0 > "$work/asset-files"
[[ -s "$work/asset-files" ]]
while IFS= read -r -d '' file; do
    url="$site_url/assets/$revision/${file#public/assets/}"
    curl -fsS --max-time 20 "$url" -o "$work/check"
    cmp "$file" "$work/check"
done < "$work/asset-files"

cp "$public/index.php" "$work/previous.php"
if [[ ! -f "$state/original-index.php" ]]; then cp "$work/previous.php" "$state/original-index.php"; fi
php -r 'echo "<?php\n", "const ASSET_DIRECTORY = ", var_export($argv[1],true), ";\nrequire ", var_export($argv[2],true), ";\n";' "assets/$revision" "$release/public/index.php" > "$work/index.php"
php -l "$work/index.php" >/dev/null
chmod 644 "$work/index.php"
mv -f "$work/index.php" "$public/index.php"
if ! curl -fsS --max-time 20 "$site_url/?release=$revision" -o "$work/login" ||
   ! grep -Fq "assets/$revision/css/base.css" "$work/login" ||
   ! grep -Fq 'autocomplete="current-password"' "$work/login"; then
    chmod 644 "$work/previous.php"
    mv -f "$work/previous.php" "$public/index.php"
    echo "Login check failed. Previous index restored." >&2
    exit 1
fi
install -m 0700 deploy/cpanel.sh "$work/runner"
mv -f "$work/runner" "$state/deploy.sh"
echo "$revision" > "$work/active"
mv -f "$work/active" "$state/active"
printf '%s Homepage and assets verified for %s\n' "$(date -Is)" "$revision"
