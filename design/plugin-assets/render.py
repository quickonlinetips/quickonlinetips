"""Render WordPress.org banners and icons for the QOT plugins.

Run from the repo root:  python3 design/plugin-assets/render.py
Needs: pip install playwright pillow && playwright install chromium

Each plugin gets, in plugins/<slug>/assets/:
  banner-1544x500.png, banner-772x250.png, icon-256x256.png, icon-128x128.png
The style matches the existing Quick META Keywords and Quick WordPress Version
assets: dark navy background with a faint dot grid, QUICKONLINETIPS label,
white title with one green word, and a dark window showing the plugin at work.
"""
import io
import os
import sys

from PIL import Image
from playwright.sync_api import sync_playwright

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

BASE_CSS = """
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { width: %(w)dpx; height: %(h)dpx; overflow: hidden; }
body {
  font-family: 'DejaVu Sans', sans-serif;
  background:
    radial-gradient(circle at 1px 1px, rgba(255,255,255,0.07) 1.5px, transparent 0) 0 0 / 24px 24px,
    radial-gradient(ellipse at 85%% 10%%, #2a344d 0%%, transparent 60%%),
    linear-gradient(115deg, #171d29 0%%, #1e2434 55%%, #252f45 100%%);
  color: #e8edf5;
}
.mono { font-family: 'DejaVu Sans Mono', monospace; }
"""

BANNER_CSS = """
.left { position: absolute; left: 80px; top: 0; bottom: 0; width: 640px;
        display: flex; flex-direction: column; justify-content: center; }
.label { font-size: 24px; letter-spacing: 4px; color: #8b97ab; margin-bottom: 14px; }
.title { font-size: 78px; font-weight: bold; line-height: 1.04; letter-spacing: -0.5px; }
.title .g { color: #7ee787; }
.sub { font-size: 31px; line-height: 1.42; color: #b8c3d4; margin-top: 30px; }
.win { position: absolute; right: 72px; top: 50%%; transform: translateY(-50%%);
       width: %(winw)dpx; background: #0f141f; border: 2px solid #2e3648;
       border-radius: 18px; box-shadow: 0 24px 60px rgba(0,0,0,0.45); overflow: hidden; }
.head { height: 52px; border-bottom: 2px solid #2e3648; display: flex;
        align-items: center; padding: 0 20px; gap: 12px; }
.dot { width: 15px; height: 15px; border-radius: 50%%; background: #3a465f; }
.head .spacer { flex: 1; }
.body { padding: 30px 34px 34px; }
"""

ICON_CSS = """
body { background: transparent; }
.tile { position: absolute; inset: 0; border-radius: 56px;
        background:
          radial-gradient(circle at 1px 1px, rgba(255,255,255,0.06) 1.5px, transparent 0) 0 0 / 22px 22px,
          linear-gradient(160deg, #252e44 0%, #181d2a 100%); }
svg { position: absolute; inset: 0; }
"""

GREEN = "#7ee787"
BLUE = "#79c0ff"
ORANGE = "#ffa657"

PLUGINS = {
    "simple-feed-copyright": {
        "title": 'Quick <span class="g">Feed</span><br>Copyright',
        "sub": "Every post in your feed ends with your name and a link back.",
        "winw": 690,
        "window": f"""
          <div class="head">
            <div class="dot"></div><div class="dot"></div><div class="dot"></div>
            <div class="spacer"></div>
            <svg width="30" height="30" viewBox="0 0 24 24" style="position:static">
              <rect width="24" height="24" rx="5" fill="{ORANGE}"/>
              <circle cx="6.5" cy="17.5" r="2.2" fill="#0f141f"/>
              <path d="M5 11a8 8 0 0 1 8 8M5 5a14 14 0 0 1 14 14" stroke="#0f141f"
                    stroke-width="2.6" fill="none" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="body">
            <div style="font-size:27px;font-weight:bold;margin-bottom:20px">10 Quick WordPress Tips</div>
            <div style="height:12px;width:96%;background:#2b3448;border-radius:6px;margin-bottom:13px"></div>
            <div style="height:12px;width:82%;background:#2b3448;border-radius:6px;margin-bottom:26px"></div>
            <div style="border-top:2px dashed #34405a;padding-top:22px;font-size:23px;line-height:1.75;color:#b8c3d4">
              <div><i>Original article:</i> <span style="color:{BLUE};text-decoration:underline">10 Quick WordPress Tips</span></div>
              <div><i><span style="color:{GREEN}">&copy;</span>
                <span style="color:{BLUE};text-decoration:underline">Your Blog Name</span>.
                All Rights Reserved.</i></div>
            </div>
          </div>""",
        "icon": f"""
          <svg viewBox="0 0 256 256" width="256" height="256">
            <circle cx="72" cy="184" r="18" fill="{GREEN}"/>
            <path d="M54 118a84 84 0 0 1 84 84" stroke="{GREEN}" stroke-width="22"
                  fill="none" stroke-linecap="round"/>
            <path d="M54 62a140 140 0 0 1 140 140" stroke="{GREEN}" stroke-width="22"
                  fill="none" stroke-linecap="round" opacity="0.55"/>
            <circle cx="178" cy="82" r="50" fill="#1e2536"/>
            <circle cx="178" cy="82" r="42" fill="{GREEN}"/>
            <text x="178" y="102" text-anchor="middle" font-family="DejaVu Sans"
                  font-weight="bold" font-size="56" fill="#181d2a">&copy;</text>
          </svg>""",
    },
    "quick-code-styler": {
        "title": 'Quick <span class="g">Code</span><br>Styler',
        "sub": "Code blocks with syntax colours and a Copy button.",
        "winw": 690,
        "window": f"""
          <div class="head">
            <div class="dot"></div><div class="dot"></div><div class="dot"></div>
            <span class="mono" style="margin-left:10px;font-size:18px;color:#8b97ab;
                  border:2px solid #2e3648;border-radius:8px;padding:2px 10px">PHP</span>
            <div class="spacer"></div>
            <span style="font-size:19px;font-weight:bold;color:{GREEN};border:2px solid {GREEN};
                  border-radius:9px;padding:4px 14px">Copy</span>
          </div>
          <div class="body mono" style="font-size:23px;line-height:1.75;white-space:pre;color:#e8edf5">"""
        f"""<span style="color:#8b949e;font-style:italic">// Greet each visitor by name</span>
<span style="color:#ff7b72">function</span> <span style="color:#d2a8ff">qot_hello</span>( <span style="color:{ORANGE}">$name</span> ) {{
    <span style="color:#ff7b72">return</span> <span style="color:#a5d6ff">"Hello, "</span> . <span style="color:{ORANGE}">$name</span> . <span style="color:#a5d6ff">"!"</span>;
}}</div>""",
        "icon": f"""
          <svg viewBox="0 0 256 256" width="256" height="256">
            <rect x="44" y="56" width="168" height="144" rx="18" fill="none"
                  stroke="{GREEN}" stroke-width="14"/>
            <line x1="44" y1="92" x2="212" y2="92" stroke="{GREEN}" stroke-width="10"/>
            <rect x="72" y="114" width="52" height="14" rx="7" fill="#ff7b72"/>
            <rect x="132" y="114" width="52" height="14" rx="7" fill="{BLUE}"/>
            <rect x="72" y="142" width="88" height="14" rx="7" fill="{ORANGE}"/>
            <rect x="72" y="170" width="64" height="14" rx="7" fill="{GREEN}" opacity="0.6"/>
            <circle cx="186" cy="182" r="34" fill="{GREEN}"/>
            <rect x="171" y="166" width="22" height="26" rx="4" fill="none" stroke="#181d2a" stroke-width="5"/>
            <rect x="180" y="174" width="22" height="26" rx="4" fill="{GREEN}" stroke="#181d2a" stroke-width="5"/>
          </svg>""",
    },
}


def page(css, body):
    return f"<!doctype html><html><head><meta charset='utf-8'><style>{css}</style></head><body>{body}</body></html>"


def shot(browser, html, w, h, transparent=False):
    p = browser.new_page(viewport={"width": w, "height": h})
    p.set_content(html)
    p.wait_for_timeout(150)
    png = p.screenshot(omit_background=transparent)
    p.close()
    return Image.open(io.BytesIO(png))


def save(img, path):
    img.save(path, optimize=True)
    print(f"  {os.path.relpath(path, ROOT)}  {img.size[0]}x{img.size[1]}  {os.path.getsize(path)//1024}KB")


def main(slugs):
    with sync_playwright() as pw:
        browser = pw.chromium.launch()
        for slug in slugs:
            d = PLUGINS[slug]
            out = os.path.join(ROOT, "plugins", slug, "assets")
            os.makedirs(out, exist_ok=True)
            print(slug)

            css = BASE_CSS % {"w": 1544, "h": 500} + BANNER_CSS % {"winw": d["winw"]}
            body = (f'<div class="left"><div class="label">QUICKONLINETIPS</div>'
                    f'<div class="title">{d["title"]}</div><div class="sub">{d["sub"]}</div></div>'
                    f'<div class="win">{d["window"]}</div>')
            big = shot(browser, page(css, body), 1544, 500).convert("RGB")
            save(big, os.path.join(out, "banner-1544x500.png"))
            save(big.resize((772, 250), Image.LANCZOS), os.path.join(out, "banner-772x250.png"))

            css = BASE_CSS % {"w": 256, "h": 256} + ICON_CSS
            icon = shot(browser, page(css, f'<div class="tile"></div>{d["icon"]}'), 256, 256, transparent=True)
            save(icon, os.path.join(out, "icon-256x256.png"))
            save(icon.resize((128, 128), Image.LANCZOS), os.path.join(out, "icon-128x128.png"))
        browser.close()


if __name__ == "__main__":
    main(sys.argv[1:] or list(PLUGINS))
