# QOT GitHub Sync

A small helper plugin for test sites (such as a saved WordPress Playground). It is not for WordPress.org.

**Tools > QOT GitHub Sync** lists every plugin and theme in this repo:

- **Pull latest / Install from GitHub** replaces the copy on the site with the latest version from GitHub and keeps its settings. Plugins are switched on after pulling; themes are left for you to activate.
- **Readme** opens **Tools > QOT Readme Preview**, which shows the plugin's `readme.txt` laid out the way its WordPress.org page will look: banner, icon, description, screenshots, FAQ and changelog, plus checks for the short description length, tags, screenshot captions, the Stable tag and the changelog.

The Plugins screen also has a **Readme preview** link under each QOT plugin.

The readme is read fresh from GitHub each time, so after a change is pushed, just reload the page.

## Updating itself

The tool keeps itself up to date. While you use the admin it checks GitHub at most every 10 minutes, and again each time you open its page. When the `Version` in `qot-github-sync.php` on GitHub is higher than the copy on the site, it pulls the new copy and says so in a green notice. **Pull latest** in its own row still works too.

So whenever `qot-github-sync.php` changes, raise its `Version`. It only updates itself when it is installed in a folder named `qot-github-sync`.

## Installing it

Download [`tools/qot-github-sync.zip`](../qot-github-sync.zip) and upload it under Plugins > Add New > Upload Plugin. An older zip is fine: the tool updates itself to the latest version the first time you use the admin.
