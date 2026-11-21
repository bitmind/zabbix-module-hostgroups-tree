#!/bin/sh
# Copy both modules from a git ref (default HEAD) into a Zabbix "modules" folder (local or mounted):
#   tools/deploy.sh <modules dir> [ref]
# An existing hostgrouptree/config.json is kept (pass FORCE_CONFIG=1 to overwrite it).
# Committed files only: commit (or pass a ref) before deploying.
set -eu

cd "$(dirname "$0")/.."
dest="${1:?usage: tools/deploy.sh <modules dir> [ref]}"
ref="${2:-HEAD}"

for module in hostgrouptree hostgrouptree-widget; do
	mkdir -p "$dest/$module"

	if [ "$module" = hostgrouptree ] && [ -f "$dest/$module/config.json" ] && [ "${FORCE_CONFIG:-0}" != 1 ]; then
		git archive "$ref:$module" | tar -x -C "$dest/$module" --exclude config.json
		echo "$module: kept existing config.json"
	else
		git archive "$ref:$module" | tar -x -C "$dest/$module"
	fi

	# macOS AppleDouble files written on network/sshfs mounts.
	find "$dest/$module" -name '._*' -delete

	echo "$module $(sed -n 's/.*"version": *"\([^"]*\)".*/\1/p' "$dest/$module/manifest.json") -> $dest/$module"
done
