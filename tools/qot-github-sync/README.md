# QOT GitHub Sync

A small helper plugin for test sites (such as a saved WordPress Playground). It is not for WordPress.org.

**Tools > QOT GitHub Sync** lists every plugin and theme in this repo:

- **Update / Pull again / Install from GitHub** replaces the copy on the site with the latest version from GitHub and keeps its settings. Plugins are switched on after pulling; themes are left for you to activate.
- **Readme** opens **Tools > QOT Readme Preview**, which shows the plugin's `readme.txt` laid out the way its WordPress.org page will look: banner, icon, description, screenshots, FAQ and changelog, plus checks for the short description length, tags, screenshot captions, the Stable tag and the changelog.

The Plugins screen also has a **Readme preview** link under each QOT plugin.

The readme is read fresh from GitHub each time, so after a change is pushed, just reload the page.

## Update available

Each row compares the copy on the site with GitHub, file by file, and shows **Update available** (with the new version number when it changed) or **Up to date**. Any changed, added or removed file counts, even if the version number wasn't raised. Click **Update** to pull it. Nothing updates on its own.

The tool lists itself under **Tools**, so it shows its own updates the same way.

## Installing it

Download [`tools/qot-github-sync.zip`](../qot-github-sync.zip) and upload it under Plugins > Add New > Upload Plugin. If the zip is older than GitHub, the tool's own row shows Update available.
