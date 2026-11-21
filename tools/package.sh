#!/bin/sh
# Release packages from a git ref (default HEAD), in dist/:
#   hostgrouptree-<version>.zip          panel module
#   hostgrouptree-widget-<version>.zip   widget module
#   hostgrouptree-all-<version>.zip      both folders, to extract into <Zabbix frontend>/modules/
set -eu

cd "$(dirname "$0")/.."
ref="${1:-HEAD}"
version=$(git show "$ref:hostgrouptree/manifest.json" | sed -n 's/.*"version": *"\([^"]*\)".*/\1/p')
widget_version=$(git show "$ref:hostgrouptree-widget/manifest.json" | sed -n 's/.*"version": *"\([^"]*\)".*/\1/p')

if [ "$version" != "$widget_version" ]; then
	echo "Version mismatch: panel $version, widget $widget_version" >&2
	exit 1
fi

mkdir -p dist
git archive --format=zip --prefix=hostgrouptree/ -o "dist/hostgrouptree-$version.zip" "$ref:hostgrouptree"
git archive --format=zip --prefix=hostgrouptree-widget/ -o "dist/hostgrouptree-widget-$version.zip" \
	"$ref:hostgrouptree-widget"
git archive --format=zip -o "dist/hostgrouptree-all-$version.zip" "$ref" hostgrouptree hostgrouptree-widget

ls -l dist/*-"$version".zip
