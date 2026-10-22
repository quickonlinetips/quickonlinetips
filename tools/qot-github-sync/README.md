# QOT GitHub Sync

A small helper plugin for test sites (such as a saved WordPress Playground). It is not for WordPress.org.

**Tools > QOT GitHub Sync** lists every plugin and theme in this repo:

- **Pull latest / Install from GitHub** replaces the copy on the site with the latest version from GitHub and keeps its settings. Plugins are switched on after pulling; themes are left for you to activate.
- **Readme** opens **Tools > QOT Readme Preview**, which shows the plugin's `readme.txt` laid out the way its WordPress.org page will look: banner, icon, description, screenshots, FAQ and changelog, plus checks for the short description length, tags, screenshot captions, the Stable tag and the changelog.

The Plugins screen also has a **Readme preview** link under each QOT plugin.

The readme is read fresh from GitHub each time, so after a change is pushed, just reload the page.

This helper sits in `tools/`, so it appears in its own list and can update itself with **Pull latest**.
