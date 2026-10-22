# Sending a plugin update to WordPress.org

Plugins in this folder go to WordPress.org automatically when you publish a release on GitHub. There's no need to download zips or use SVN.

## One-time setup: add your WordPress.org login

Do this once. GitHub keeps these private, and nobody can read them back, not even you.

1. **Get your SVN password from WordPress.org.** Log in to [wordpress.org](https://wordpress.org), open your profile, go to **Account & Security**, and click **Generate password** under "SVN credentials". Copy it somewhere safe for a minute. It's different from your normal WordPress.org password.
2. **Add it to GitHub.** Open this repo on GitHub and go to **Settings > Secrets and variables > Actions**. Click **New repository secret** and add two secrets:
   - Name `SVN_USERNAME`. Value: your WordPress.org username (`pchere`).
   - Name `SVN_PASSWORD`. Value: the SVN password from step 1.

That's all. The setup works for every plugin in this folder.

## Each time you release an update

1. **Check the version number** is the same in both places in the plugin folder:
   - the `Version:` line at the top of the main PHP file
   - the `Stable tag:` line in `readme.txt`

   Also add a line for the new version under `== Changelog ==` in the readme.
2. **Publish a release on GitHub.** On the repo's main page, click **Releases**, then **Draft a new release**.
   - **Choose a tag:** type the plugin's folder name, a dash and the version, then click "Create new tag". For example `quick-meta-keywords-2.0`.
   - **Release title:** anything you like, for example `Quick META Keywords 2.0`.
   - Click **Publish release**.
3. **Watch it go.** Open the **Actions** tab. A run called "Deploy plugin to WordPress.org" starts straight away and takes about a minute. A green tick means it's done. WordPress.org shows the update to users within a few minutes.

The plugin's zip is also attached to the GitHub release, in case you want a copy.

### Release tags for each plugin

| Plugin | Tag example |
| --- | --- |
| Quick META Keywords | `quick-meta-keywords-2.0` |
| Quick Feed Copyright | `simple-feed-copyright-2.0` |
| Quick WordPress Version | `display-wordpress-version-2.0` |
| Quick Code Styler | `quick-code-styler-1.0` (only after WordPress.org approves it) |

The tag uses the plugin's WordPress.org address, which never changes, even when the plugin's display name does.

## What gets sent where

- **The plugin's code** (PHP, CSS, JS, `readme.txt`, `uninstall.php`) goes to WordPress.org's `trunk` and to `tags/<version>`.
- **The images** for the plugin page (everything in the plugin's `assets` folder, plus the `screenshot-1.png` files) go to WordPress.org's separate `assets` folder. They are not inside the plugin users download, so the download stays small.

## If something goes wrong

- **Red cross saying "Version numbers don't match".** The version in the PHP file, the readme's Stable tag and the release tag must all be the same. Fix the files, then delete the release and its tag on GitHub and publish it again.
- **Red cross at "Send to WordPress.org".** Usually a wrong or missing SVN password. Check the two secrets from the setup.
- **A brand-new plugin.** It can't be released this way until WordPress.org has approved it and created its page. Upload the first version through the [Add your plugin](https://wordpress.org/plugins/developers/add/) form instead.

## Testing without sending anything

Open the **Actions** tab, click **Deploy plugin to WordPress.org**, then **Run workflow**. Pick the plugin, type the version and leave **Test only** ticked. The run checks everything and makes the zip, but sends nothing to WordPress.org. The zip appears at the bottom of the run's page.
