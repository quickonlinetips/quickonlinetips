#!/usr/bin/env bash
#
# Refresh the Bootstrap and Font Awesome files bundled with QuickStrap.
#
# Why this script: WordPress.org themes must ship their CSS and JavaScript
# inside the theme (no CDN links), so every Bootstrap or Font Awesome update
# used to mean downloading zips by hand and copying files around. Both projects
# publish their ready-built files in their GitHub release tags, so this script
# fetches just those files with git and copies the few the theme uses.
#
# Usage (from the repo root):
#   tools/update-quickstrap-vendor.sh                # newest Bootstrap 5.x and Font Awesome 7.x
#   tools/update-quickstrap-vendor.sh 5.3.8 7.3.1    # exact versions
#
# It then updates the version numbers in themes/quickstrap/functions.php.
# Check the result with `git status` and `git diff --stat`, test the theme,
# and commit as "QuickStrap: Bootstrap X and Font Awesome Y".
#
# Needs only git and bash (no npm, no downloads from other sites).

set -euo pipefail

BOOTSTRAP_MAJOR=5   # stay on Bootstrap 5; a new major version needs testing by hand
FONTAWESOME_MAJOR=7

repo_root="$(cd "$(dirname "$0")/.." && pwd)"
theme="$repo_root/themes/quickstrap"
vendor="$theme/vendor"
work="$(mktemp -d)"
trap 'rm -rf "$work"' EXIT

newest_tag() { # $1 repo URL, $2 regex for stable tags
	git ls-remote --tags --refs "$1" | awk -F/ '{print $3}' | grep -E "$2" | sort -V | tail -1
}

bs_tag="v${1:-}"
[ "$bs_tag" = "v" ] && bs_tag="$(newest_tag https://github.com/twbs/bootstrap "^v${BOOTSTRAP_MAJOR}\.[0-9]+\.[0-9]+$")"
fa_tag="${2:-}"
[ -z "$fa_tag" ] && fa_tag="$(newest_tag https://github.com/FortAwesome/Font-Awesome "^${FONTAWESOME_MAJOR}\.[0-9]+\.[0-9]+$")"

echo "Bootstrap:    $bs_tag"
echo "Font Awesome: $fa_tag"

fetch() { # $1 repo URL, $2 tag, $3 folder, rest: paths to check out
	local url="$1" tag="$2" dir="$3"
	shift 3
	git -c advice.detachedHead=false clone -q --depth 1 --branch "$tag" --filter=blob:none --sparse "$url" "$dir"
	git -C "$dir" sparse-checkout set --no-cone "$@"
}

fetch https://github.com/twbs/bootstrap "$bs_tag" "$work/bs" \
	/dist/css/bootstrap.min.css /dist/js/bootstrap.bundle.min.js /LICENSE
fetch https://github.com/FortAwesome/Font-Awesome "$fa_tag" "$work/fa" \
	/css/all.min.css '/webfonts/*.woff2' /LICENSE.txt

rm -rf "$vendor/bootstrap" "$vendor/font-awesome"
mkdir -p "$vendor/bootstrap" "$vendor/font-awesome/css" "$vendor/font-awesome/webfonts"

# Drop the source map comments: the .map files aren't shipped, and browsers'
# developer tools would report them as missing.
sed '/sourceMappingURL/d' "$work/bs/dist/css/bootstrap.min.css" > "$vendor/bootstrap/bootstrap.min.css"
sed '/sourceMappingURL/d' "$work/bs/dist/js/bootstrap.bundle.min.js" > "$vendor/bootstrap/bootstrap.bundle.min.js"
cp "$work/bs/LICENSE" "$vendor/bootstrap/LICENSE"

cp "$work/fa/css/all.min.css" "$vendor/font-awesome/css/all.min.css"
cp "$work/fa"/webfonts/*.woff2 "$vendor/font-awesome/webfonts/"
cp "$work/fa/LICENSE.txt" "$vendor/font-awesome/LICENSE.txt"

# Dark mode: Bootstrap keeps its dark colours under [data-bs-theme=dark],
# which only JavaScript can switch on. Copy those rules into style.css
# (between the quickstrap-bootstrap-dark markers) under the reader's own
# dark setting, so dark mode needs no script and follows each new Bootstrap.
dark_css="$work/bootstrap-dark.css"
perl -0777 -ne '
	print "\@media (prefers-color-scheme: dark) {\n";
	while ( /(?<=\})([^{}]+)\{([^{}]*)\}/g ) {
		my ( $sel, $body ) = ( $1, $2 );
		my @parts = split /,/, $sel;
		next if grep { $_ !~ /^\s*\[data-bs-theme=dark\]/ } @parts;
		$sel =~ s/\[data-bs-theme=dark\]/body:not(.qs-light-only)/g;
		print "\t$sel {$body}\n";
	}
	print "}\n";
' "$vendor/bootstrap/bootstrap.min.css" > "$dark_css"
perl -0777 -i -pe '
	BEGIN { local $/; open my $f, "<", shift @ARGV or die; $css = <$f>; }
	s{(/\* quickstrap-bootstrap-dark:start.*?\*/\n).*?(/\* quickstrap-bootstrap-dark:end \*/)}{$1$css$2}s
		or die "Dark mode markers not found in style.css\n";
' "$dark_css" "$theme/style.css"

# Version numbers used to load the files (they also bust browser caches).
sed -i \
	-e "s/define( 'QUICKSTRAP_BOOTSTRAP_VERSION', '[^']*' );/define( 'QUICKSTRAP_BOOTSTRAP_VERSION', '${bs_tag#v}' );/" \
	-e "s/define( 'QUICKSTRAP_FONTAWESOME_VERSION', '[^']*' );/define( 'QUICKSTRAP_FONTAWESOME_VERSION', '${fa_tag}' );/" \
	"$theme/functions.php"

echo
echo "Done. Files now in themes/quickstrap/vendor:"
( cd "$vendor" && find . -type f | sort | sed 's/^\.\//  /' )
echo
echo "Remember to update the versions in themes/quickstrap/readme.txt (Resources section)."
