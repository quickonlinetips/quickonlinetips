"""Preview the plugins' readme.txt files as WordPress.org plugin pages.

Run from the repo root:  python3 design/readme-preview/build.py OUT.html
Needs: pip install markdown pillow

GitHub shows readme.txt as plain text. WordPress.org turns the same file into
the plugin page: == Section == becomes a heading, = Question = becomes an FAQ
item or a changelog version, and *, **, `code` and [links](url) are Markdown.
This script does the same conversion so the text can be read and checked in
its final layout, with the banner, icon and screenshots in place.
"""
import base64
import html
import io
import os
import re
import sys

import markdown
from PIL import Image

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
ORDER = ["quick-meta-keywords", "simple-feed-copyright", "display-wordpress-version", "quick-code-styler"]
LIMIT_SHORT = 150  # WordPress.org cuts the short description after 150 characters
LIMIT_TAGS = 5     # and shows only the first 5 tags


def data_uri(path, max_w=None, jpeg=False):
    im = Image.open(path)
    if max_w and im.size[0] > max_w:
        im = im.resize((max_w, round(im.size[1] * max_w / im.size[0])), Image.LANCZOS)
    buf = io.BytesIO()
    if jpeg:
        im.convert("RGB").save(buf, "JPEG", quality=82)
        kind = "jpeg"
    else:
        im.save(buf, "PNG", optimize=True)
        kind = "png"
    return f"data:image/{kind};base64," + base64.b64encode(buf.getvalue()).decode()


def md(text):
    """Markdown the way WordPress.org reads it: a list may start right after a line of text."""
    lines, out = text.strip("\n").split("\n"), []
    for i, line in enumerate(lines):
        is_item = re.match(r"^\s*(\*|-|\d+\.)\s", line)
        if is_item and out and out[-1].strip() and not re.match(r"^\s*(\*|-|\d+\.)\s", out[-1]):
            out.append("")
        out.append(line)
    return markdown.markdown("\n".join(out), extensions=["sane_lists"])


def parse(path):
    raw = open(path, encoding="utf-8").read().replace("\r\n", "\n")
    m = re.match(r"===\s*(.+?)\s*===\n(.*?)\n\n(.*?)\n\n(?===)", raw, re.S)
    name, head, short = m.group(1), m.group(2), m.group(3).strip()
    fields = dict(re.findall(r"^([A-Za-z ]+):\s*(.+)$", head, re.M))
    sections = {}
    for title, body in re.findall(r"^==\s*(.+?)\s*==\n(.*?)(?=^==\s|\Z)", raw, re.S | re.M):
        subs = re.split(r"^=\s*(.+?)\s*=\s*$", body, flags=re.M)
        sections[title] = {"intro": subs[0], "items": list(zip(subs[1::2], subs[2::2]))}
    return name, fields, short, sections


def checks(fields, short, sections, n_shots):
    out = []
    n = len(short)
    out.append(("ok" if n <= LIMIT_SHORT else "bad",
                f"Short description: {n} of {LIMIT_SHORT} characters"
                + ("" if n <= LIMIT_SHORT else ". WordPress.org cuts off the rest.")))
    tags = [t.strip() for t in fields.get("Tags", "").split(",") if t.strip()]
    out.append(("ok" if len(tags) <= LIMIT_TAGS else "bad",
                f"Tags: {len(tags)} of {LIMIT_TAGS}" + ("" if len(tags) <= LIMIT_TAGS else ". Only the first 5 show.")))
    caps = re.findall(r"^\d+\.\s", sections.get("Screenshots", {}).get("intro", ""), re.M)
    out.append(("ok" if len(caps) == n_shots else "bad",
                f"Screenshots: {n_shots} images, {len(caps)} captions"))
    return out


def plugin_page(slug):
    d = os.path.join(ROOT, "plugins", slug)
    name, fields, short, sec = parse(os.path.join(d, "readme.txt"))
    shots = sorted((f for f in os.listdir(d) if re.match(r"screenshot-\d+\.(png|jpg)$", f)),
                   key=lambda f: int(re.findall(r"\d+", f)[0]))
    caps = re.findall(r"^\d+\.\s+(.*)$", sec.get("Screenshots", {}).get("intro", ""), re.M)

    parts, nav = [], []

    def add(sid, title, body):
        nav.append(f'<a href="#{slug}-{sid}">{title}</a>')
        parts.append(f'<section id="{slug}-{sid}" class="sec"><h2>{title}</h2>{body}</section>')

    if "Description" in sec:
        s = sec["Description"]
        body = md(s["intro"]) + "".join(f"<h3>{html.escape(t)}</h3>{md(b)}" for t, b in s["items"])
        add("description", "Description", body)
    if shots:
        figs = "".join(
            f'<figure><img src="{data_uri(os.path.join(d, f), 900, True)}" alt="Screenshot {i + 1}" loading="lazy">'
            f'<figcaption>{md(caps[i]) if i < len(caps) else "<p><em>No caption in readme.txt</em></p>"}</figcaption></figure>'
            for i, f in enumerate(shots))
        add("screenshots", "Screenshots", f'<div class="shots">{figs}</div>')
    if "Installation" in sec:
        add("installation", "Installation", md(sec["Installation"]["intro"]))
    if "Frequently Asked Questions" in sec:
        s = sec["Frequently Asked Questions"]
        body = md(s["intro"]) + "".join(
            f"<details><summary>{html.escape(q)}</summary><div>{md(a)}</div></details>" for q, a in s["items"])
        add("faq", "FAQ", body)
    if "Changelog" in sec:
        s = sec["Changelog"]
        add("changelog", "Changelog", md(s["intro"]) + "".join(f"<h4>{html.escape(v)}</h4>{md(b)}" for v, b in s["items"]))
    if "Upgrade Notice" in sec:
        s = sec["Upgrade Notice"]
        notes = "".join(f"<h4>{html.escape(v)}</h4>{md(b)}" for v, b in s["items"])
        parts.append(f'<aside class="upgrade"><p class="eyebrow">Upgrade notice</p>'
                     f'<p class="hint">Not on the plugin page. WordPress shows this in people\'s dashboards next to the update.</p>{notes}</aside>')

    meta_rows = [("Version", fields.get("Stable tag", "")), ("Requires WordPress", fields.get("Requires at least", "")),
                 ("Tested up to", fields.get("Tested up to", "")), ("Requires PHP", fields.get("Requires PHP", "")),
                 ("License", fields.get("License", ""))]
    meta = "".join(f"<div><dt>{k}</dt><dd>{html.escape(v)}</dd></div>" for k, v in meta_rows if v)
    tags = "".join(f"<span>{html.escape(t.strip())}</span>" for t in fields.get("Tags", "").split(",") if t.strip())
    chk = "".join(f'<li class="{c}">{html.escape(t)}</li>' for c, t in checks(fields, short, sec, len(shots)))
    live = (f'<a href="https://wordpress.org/plugins/{slug}/">Current live page</a>'
            if slug != "quick-code-styler" else "<span>Not on WordPress.org yet</span>")

    banner = os.path.join(d, "assets", "banner-1544x500.png")
    icon = os.path.join(d, "assets", "icon-256x256.png")
    return f'''
<article class="plugin" id="{slug}" data-slug="{slug}">
  <img class="banner" src="{data_uri(banner, 1100)}" alt="">
  <header class="title">
    <img class="icon" src="{data_uri(icon, 128)}" width="96" height="96" alt="">
    <div><h1>{html.escape(name)}</h1><p class="by">By <span>QuickOnlineTips</span></p></div>
  </header>
  <p class="short">{html.escape(short)}</p>
  <div class="cols">
    <div class="main">
      <nav class="secnav">{"".join(nav)}</nav>
      {"".join(parts)}
    </div>
    <aside class="side">
      <dl class="meta">{meta}</dl>
      <div class="tags"><p class="eyebrow">Tags</p><div>{tags}</div></div>
      <div class="checks"><p class="eyebrow">Readme checks</p><ul>{chk}</ul></div>
      <p class="live">{live}</p>
      <p class="src"><code>plugins/{slug}/readme.txt</code></p>
    </aside>
  </div>
</article>'''


def build(out):
    names = {s: parse(os.path.join(ROOT, "plugins", s, "readme.txt"))[0] for s in ORDER}
    tabs = "".join(
        f'<button type="button" role="tab" id="tab-{s}" aria-controls="{s}" data-slug="{s}">{html.escape(n)}</button>'
        for s, n in names.items())
    pages = "".join(plugin_page(s) for s in ORDER)
    shell = open(os.path.join(os.path.dirname(os.path.abspath(__file__)), "shell.html"), encoding="utf-8").read()
    open(out, "w", encoding="utf-8").write(shell.replace("{{TABS}}", tabs).replace("{{PAGES}}", pages))
    print(out, os.path.getsize(out) // 1024, "KB")


if __name__ == "__main__":
    build(sys.argv[1] if len(sys.argv) > 1 else "readme-preview.html")
