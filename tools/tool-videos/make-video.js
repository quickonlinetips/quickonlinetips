#!/usr/bin/env node
// Usage: node make-video.js <scenario> [--size 4k|1080p|720p|vertical] [--music pop|electro|funk|chill|none] [--out DIR]
// Example: node make-video.js mortgage-payoff-calculator --size 4k --music pop
const path = require('path');
const fs = require('fs');
const { execFileSync } = require('child_process');
const { record } = require('./lib/recorder');

const args = process.argv.slice(2);
const opt = (k, d) => { const i = args.indexOf('--' + k); return i >= 0 ? args[i + 1] : d; };
const name = args[0];
if (!name) { console.error('Usage: node make-video.js <scenario> [--size 4k] [--music pop] [--out DIR]'); process.exit(1); }
const scenario = require(path.join(__dirname, 'scenarios', name + '.js'));
const size = opt('size', '4k');
const music = opt('music', 'pop');
const outDir = path.resolve(opt('out', path.join(process.cwd(), 'out', name)));
fs.mkdirSync(outDir, { recursive: true });

(async () => {
  const { video } = await record(scenario, { size, outDir });
  const dur = parseFloat(execFileSync('ffprobe', ['-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', video]).toString());
  const final = path.join(outDir, `${scenario.slug}-${size}${music === 'none' ? '' : '-' + music}.mp4`);
  if (music === 'none') { fs.renameSync(video, final); }
  else {
    const wav = path.join(outDir, `music-${music}.wav`);
    execFileSync('python3', ['-I', path.join(__dirname, 'music.py'), music, String(dur), wav], { stdio: 'inherit' });
    execFileSync('ffmpeg', ['-y', '-v', 'error', '-i', video, '-i', wav, '-filter_complex', '[1:a]loudnorm=I=-18:TP=-1.5:LRA=11[a]',
      '-map', '0:v', '-map', '[a]', '-c:v', 'copy', '-c:a', 'aac', '-b:a', '192k', '-ar', '44100', '-shortest', '-movflags', '+faststart', final]);
  }
  console.log('DONE', final, dur.toFixed(1) + 's');
})().catch(e => { console.error(e); process.exit(1); });
