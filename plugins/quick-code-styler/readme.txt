=== Quick Code Styler ===
Contributors: pchere
Tags: code, syntax highlighting, code block, copy button, dark mode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Beautiful code blocks with syntax colours and a Copy button. Dark, light or automatic. Tiny, fast, and works with the code you already have.

== Description ==

Quick Code Styler makes the code in your posts easy to read and easy to copy. Activate it and every code block on your site gets a clean code window, syntax colours and a Copy button, in new posts and old ones.

It works with the standard WordPress Code block and classic `<pre>` code, so there is no special block to learn and nothing to change in your posts.

**What you get**

* **A clean code window** with a header bar, rounded corners and comfortable spacing.
* **Syntax colours** for comments, quoted text, HTML tags, attributes, keywords and numbers. Works well for HTML, CSS, JavaScript, PHP, Python and shell commands.
* **A Copy button** on every code block. One click copies the code, ready to paste.
* **Three looks:** Dark, Light, or Automatic, which follows each visitor's light or dark mode.
* **Your own colours.** Pick any of the 8 colours with the WordPress colour picker and watch a live preview as you go.
* **Mobile friendly.** Long lines wrap on phones, or turn wrapping off to scroll sideways like a code editor.
* **Inline code and keyboard keys.** A `file_name.php` inside a sentence gets a neat highlight, and `<kbd>Ctrl</kbd> + <kbd>C</kbd>` looks like real keys. Both blend in with any theme, light or dark.
* **Clean post summaries.** On home pages and archives that show short post summaries, code is left out, so it never spills into the summary as messy text.
* **Readable by everyone.** The built-in colours meet the WCAG AA contrast level.

**Why it is light and fast**

* One small stylesheet and one tiny script, with no outside library.
* Loads only on pages that show code. Other pages get nothing extra.
* Turn off the colours and the Copy button and it loads no JavaScript at all.
* No special block or shortcode, so nothing breaks if you ever switch it off. Your code simply goes back to your theme's style.

Made by Quick Online Tips, tech help that works since 2004. See the [Quick Code Styler plugin page](https://www.quickonlinetips.com/archives/quick-code-styler-wordpress-plugin/) for more.

== Installation ==

1. In your WordPress dashboard, go to Plugins > Add New and search for "Quick Code Styler".
2. Click Install Now, then Activate.
3. That's it. Open any post that has code to see the new look.
4. To change the look or colours, go to Settings > Quick Code Styler.

To install by hand, upload the `quick-code-styler` folder to `/wp-content/plugins/` and activate it on the Plugins screen.

== Frequently Asked Questions ==

= Which languages are coloured? =

The colouring is simple and general, so it suits most short code you would show in a blog post: HTML, CSS, JavaScript, PHP, Python, shell commands and similar. It colours comments, quoted text, HTML tags, attributes, common keywords and numbers. It does not need you to pick a language.

= How is this different from a full syntax highlighter? =

Full highlighters understand hundreds of languages in detail, but load much larger files. Quick Code Styler is made for blogs, tutorials and how-to guides, where code is usually short. It gives you most of the look for a fraction of the weight.

= How do I show a keyboard key? =

Wrap the key in kbd tags, for example `<kbd>Ctrl</kbd>`. In the block editor, use Edit as HTML on the paragraph.

= Can I change the colours? =

Yes. Go to Settings > Quick Code Styler, choose Dark or Light, and pick your own colours. A preview shows the result before you save. Click Default under any colour to undo a change.

= What does Automatic do? =

It shows the light code window to visitors who use light mode on their phone or computer, and the dark one to visitors who use dark mode.

= Can I turn off the JavaScript? =

Yes. Turn off both Syntax colours and Copy button in the settings, and the plugin loads only its stylesheet.

= My page builder's code is not styled. =

The plugin looks for code in the post content. If your builder stores content elsewhere, add this to your theme's functions.php to load the styles on every page:

`add_filter( 'quick_code_styler_load', '__return_true' );`

= Does it slow my site down? =

No. It adds one small stylesheet and one tiny script, only on pages that show code.

= Where can I send feedback? =

Please use the support forum on WordPress.org, or the plugin page above.

== Screenshots ==

1. A code block with syntax colours and the Copy button, in the Dark look.
2. The same post in the Light look.
3. The settings page, with a live preview.
4. On a phone, long lines wrap so there is no sideways scrolling.

== Changelog ==

= 1.0 =
* First release.
