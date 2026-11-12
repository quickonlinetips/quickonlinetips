// QOT tool-video recorder: drives a real Chromium page and captures it as video.
// Capture uses the Chrome DevTools screencast (sharp JPEG frames with timestamps),
// so 4K works: a 1280x720 page at deviceScaleFactor 3 = 3840x2160 pixels.
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const SIZES = {
  '720p': { w: 1280, h: 720, dsf: 1 },
  '1080p': { w: 1920, h: 1080, dsf: 1 },
  '4k': { w: 1280, h: 720, dsf: 3 }, // 3840x2160, same layout as 720p but 3x sharper
  vertical: { w: 540, h: 960, dsf: 2 }, // 1080x1920 for Shorts / Reels
};

const LOGO = 'https://www.quickonlinetips.com/archives/wp-content/uploads/qot-400-1.png';

// Visible cursor, click ripples and caption pill (headless browsers draw no cursor).
function overlay() {
  const install = () => {
    if (document.getElementById('pw-cur')) return;
    const st = document.createElement('style');
    st.textContent = `
#pw-cur{position:fixed;left:-50px;top:-50px;width:30px;height:30px;z-index:2147483647;pointer-events:none;transform:translate(-3px,-2px)}
.pw-rip{position:fixed;z-index:2147483646;pointer-events:none;width:16px;height:16px;margin:-8px 0 0 -8px;border-radius:50%;border:3px solid #E53E3E;animation:pwr .55s ease-out forwards}
@keyframes pwr{to{transform:scale(3.4);opacity:0}}
#pw-cap{position:fixed;left:50%;bottom:26px;transform:translateX(-50%);z-index:2147483645;background:rgba(17,24,39,.9);color:#fff;font:600 26px/1.3 -apple-system,Segoe UI,Roboto,sans-serif;padding:12px 26px;border-radius:999px;opacity:0;transition:opacity .3s;pointer-events:none;white-space:nowrap;max-width:92vw;overflow:hidden;text-overflow:ellipsis}
#pw-cap.on{opacity:1}`;
    document.head.appendChild(st);
    const c = document.createElement('div');
    c.id = 'pw-cur';
    c.innerHTML = '<svg width="30" height="30" viewBox="0 0 24 24"><path d="M4 2l15 11-7 1-4 7z" fill="#111" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/></svg>';
    document.body.appendChild(c);
    const cap = document.createElement('div'); cap.id = 'pw-cap'; document.body.appendChild(cap);
    document.addEventListener('mousemove', e => { c.style.left = e.clientX + 'px'; c.style.top = e.clientY + 'px'; }, true);
    document.addEventListener('mousedown', e => {
      const r = document.createElement('div'); r.className = 'pw-rip';
      r.style.left = e.clientX + 'px'; r.style.top = e.clientY + 'px';
      document.body.appendChild(r); setTimeout(() => r.remove(), 600);
    }, true);
    window.pwCaption = t => { cap.textContent = t; cap.classList.toggle('on', !!t); };
  };
  if (document.readyState === 'loading') window.addEventListener('DOMContentLoaded', install); else install();
}

function esc(s) { return String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])); }

const CARD_CSS = `
*{box-sizing:border-box}html,body{margin:0;height:100%}
body{font-family:-apple-system,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:radial-gradient(1200px 700px at 20% 10%,#2E6AAB 0%,#215387 45%,#0F2C4D 100%);color:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden}
.card{width:min(1500px,90vw);text-align:center}
.logo{background:#fff;display:inline-block;padding:18px 34px;border-radius:18px;box-shadow:0 10px 40px rgba(0,0,0,.25);opacity:0;animation:pop .7s .1s cubic-bezier(.2,1.4,.4,1) forwards}
.logo img{height:clamp(40px,6vw,74px);display:block}
h1{font-size:clamp(40px,5.6vw,84px);line-height:1.1;margin:44px 0 10px;opacity:0;animation:up .7s .5s ease-out forwards}
.sub{font-size:clamp(22px,2.4vw,36px);opacity:0;color:#CFE0F5;animation:up .7s .8s ease-out forwards;margin:0}
ul{list-style:none;padding:0;margin:40px auto 0;display:flex;flex-wrap:wrap;gap:16px;justify-content:center}
li{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:14px 26px;font-size:clamp(20px,2vw,30px);font-weight:600;opacity:0;animation:up .6s ease-out forwards}
.url{display:inline-block;margin-top:40px;background:#F6C343;color:#1A202C;font-weight:800;font-size:clamp(26px,3vw,46px);padding:16px 36px;border-radius:999px;opacity:0;animation:pop .7s 1.1s cubic-bezier(.2,1.4,.4,1) forwards}
@keyframes up{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:none}}
@keyframes pop{from{opacity:0;transform:scale(.7)}to{opacity:1;transform:none}}
@media (orientation:portrait){h1{font-size:9vw}.sub{font-size:5vw}li{font-size:4.4vw}.url{font-size:6vw}}`;

function introHTML({ title, subtitle, options = [] }) {
  const lis = options.map((o, i) => `<li style="animation-delay:${1.2 + i * 0.35}s">${esc(o)}</li>`).join('');
  return `<!doctype html><html><head><meta charset="utf-8"><style>${CARD_CSS}</style></head><body><div class="card">
<div class="logo"><img src="${LOGO}" alt="QuickOnlineTips"></div>
<h1>${esc(title)}</h1><p class="sub">${esc(subtitle || 'Free online tool · Watch how it works')}</p>
${lis ? `<ul>${lis}</ul>` : ''}</div></body></html>`;
}

function outroHTML({ line = 'For more tips and tricks, visit', url = 'QuickOnlineTips.com' } = {}) {
  return `<!doctype html><html><head><meta charset="utf-8"><style>${CARD_CSS}</style></head><body><div class="card">
<div class="logo"><img src="${LOGO}" alt="QuickOnlineTips"></div>
<h1 style="font-size:clamp(36px,4.6vw,70px)">${esc(line)}</h1>
<div class="url">${esc(url)}</div>
<p class="sub" style="margin-top:34px;animation-delay:1.5s">Free tools, games and tech guides since 2004</p></div></body></html>`;
}

async function record(scenario, { size = '4k', outDir, fps = 30 } = {}) {
  const S = SIZES[size];
  if (!S) throw new Error('size must be one of ' + Object.keys(SIZES).join(', '));
  const framesDir = path.join(outDir, 'frames');
  fs.rmSync(framesDir, { recursive: true, force: true });
  fs.mkdirSync(framesDir, { recursive: true });

  const browser = await chromium.launch();
  const ctx = await browser.newContext({
    viewport: { width: S.w, height: S.h }, deviceScaleFactor: S.dsf,
    permissions: ['clipboard-read', 'clipboard-write'],
  });
  await ctx.addInitScript(overlay);
  const page = await ctx.newPage();
  const cdp = await ctx.newCDPSession(page);

  // ---- capture ----
  const frames = [];
  let n = 0;
  cdp.on('Page.screencastFrame', async f => {
    const file = path.join(framesDir, String(n++).padStart(6, '0') + '.jpg');
    fs.writeFileSync(file, Buffer.from(f.data, 'base64'));
    frames.push({ file, t: f.metadata.timestamp });
    try { await cdp.send('Page.screencastFrameAck', { sessionId: f.sessionId }); } catch (e) {}
  });
  const startCap = () => cdp.send('Page.startScreencast', { format: 'jpeg', quality: 92, maxWidth: S.w * S.dsf, maxHeight: S.h * S.dsf, everyNthFrame: 1 });

  // ---- helpers handed to the scenario ----
  const wait = ms => page.waitForTimeout(ms);
  // keep frames flowing while nothing changes on screen (screencast only sends on change)
  const ticker = setInterval(() => page.evaluate(() => { let d = document.getElementById('pw-tick'); if (!d) { d = document.createElement('div'); d.id = 'pw-tick'; d.style.cssText = 'position:fixed;left:0;top:0;width:1px;height:1px;z-index:2147483647;pointer-events:none'; document.documentElement.appendChild(d); } d.style.opacity = d.style.opacity === '0.01' ? '0.02' : '0.01'; }).catch(() => {}), 1000 / fps);
  const h = {
    page, wait, size, W: S.w, H: S.h,
    caption: t => page.evaluate(x => window.pwCaption && window.pwCaption(x), t),
    async card(html, ms) { await page.setContent(html, { waitUntil: 'load' }); await wait(ms); },
    intro: (o, ms = 5500) => h.card(introHTML(o), ms),
    outro: (o, ms = 4500) => h.card(outroHTML(o), ms),
    async goto(url) { await page.goto(url, { waitUntil: 'load', timeout: 60000 }); await page.mouse.move(S.w / 2, S.h / 2); await wait(800); },
    async scrollBy(dy) { await page.evaluate(d => window.scrollBy({ top: d, behavior: 'smooth' }), dy); await wait(750); },
    async moveTo(sel) {
      const off = await page.evaluate(q => { const r = document.querySelector(q).getBoundingClientRect(); return (r.top < 90 || r.bottom > innerHeight - 110) ? r.top - innerHeight * 0.4 : 0; }, sel);
      if (off) { await h.scrollBy(off); }
      const b = await page.locator(sel).first().boundingBox();
      await page.mouse.move(b.x + Math.min(b.width / 2, 70), b.y + b.height / 2, { steps: 30 });
    },
    async click(sel) { await h.moveTo(sel); await wait(150); await page.mouse.down(); await page.mouse.up(); await wait(250); },
    async type(sel, text) { await h.click(sel); await page.keyboard.type(text, { delay: 105 }); await wait(500); },
    async wheel(sel, dy, times = 3) { const b = await page.locator(sel).boundingBox(); await page.mouse.move(b.x + b.width / 2, b.y + Math.min(150, b.height / 2), { steps: 20 }); for (let i = 0; i < times; i++) { await page.mouse.wheel(0, dy); await wait(350); } },
  };

  await page.setContent('<body style="background:#0F2C4D"></body>');
  await startCap();
  await wait(300);
  await scenario.run(h);
  await wait(300);
  clearInterval(ticker);
  const endT = Date.now() / 1000; // screencast timestamps are seconds since epoch
  await cdp.send('Page.stopScreencast');
  await browser.close();

  // ---- frames -> constant-frame-rate video ----
  const list = path.join(outDir, 'frames.txt');
  let txt = '';
  for (let i = 0; i < frames.length; i++) {
    // Chrome only sends a frame when the screen changes, so hold the last frame until the real end
    const d = i + 1 < frames.length ? frames[i + 1].t - frames[i].t : Math.min(10, Math.max(0.5, endT - frames[i].t));
    txt += `file '${frames[i].file}'\nduration ${Math.max(d, 0.001).toFixed(4)}\n`;
  }
  txt += `file '${frames[frames.length - 1].file}'\n`;
  fs.writeFileSync(list, txt);
  const out = path.join(outDir, 'video-silent.mp4');
  execFileSync('ffmpeg', ['-y', '-v', 'error', '-f', 'concat', '-safe', '0', '-i', list,
    '-vf', `fps=${fps},scale=${S.w * S.dsf}:${S.h * S.dsf}:flags=lanczos,format=yuv420p`,
    '-c:v', 'libx264', '-preset', 'medium', '-crf', size === '4k' ? '18' : '20', '-movflags', '+faststart', out]);
  const dur = endT - frames[0].t;
  console.log(`captured ${frames.length} frames over ${dur.toFixed(1)}s (avg ${(frames.length / dur).toFixed(1)} fps) -> ${out}`);
  fs.rmSync(framesDir, { recursive: true, force: true });
  return { video: out, duration: dur };
}

module.exports = { record, introHTML, outroHTML, SIZES };
