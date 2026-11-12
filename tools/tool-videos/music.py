"""Original background music for QOT tool videos, synthesized from scratch (no samples,
no stock library), so it is free of copyright claims.

Usage: python3 music.py <style> <seconds> <out.wav>
Styles: chill (100 BPM, calm), pop (124 BPM, bright and peppy),
        electro (128 BPM, high energy dance), funk (112 BPM, groovy)
"""
import sys, wave
import numpy as np

SR = 44100
STYLES = {
    'chill': dict(bpm=100, prog=[(50, [62, 66, 69]), (45, [61, 64, 69]), (47, [62, 66, 71]), (43, [62, 67, 71])],
                  kick=[0, 8], clap=[4, 12], hat=list(range(0, 16, 2)), hatv=0.05, bass=[(0, 0), (10, 0), (12, 0)],
                  arp=list(range(0, 16, 2)), arpv=0.11, stabs=[], pump=0.0, bright=3, padv=0.16, lead=0),
    'pop': dict(bpm=124, prog=[(40, [64, 68, 71]), (47, [63, 66, 71]), (37, [61, 64, 68]), (45, [61, 64, 69])],
                kick=[0, 8, 10], clap=[4, 12], hat=list(range(16)), hatv=0.045, bass=[(0, 0), (3, 12), (6, 0), (8, 0), (11, 12), (14, 0)],
                arp=list(range(16)), arpv=0.09, stabs=[], pump=0.35, bright=5, padv=0.12, lead=1),
    'electro': dict(bpm=128, prog=[(45, [69, 72, 76]), (41, [65, 69, 72]), (48, [67, 72, 76]), (43, [67, 71, 74])],
                    kick=[0, 4, 8, 12], clap=[4, 12], hat=[2, 6, 10, 14], hatv=0.09, bass=[(2, 0), (6, 0), (10, 0), (14, 12)],
                    arp=list(range(16)), arpv=0.10, stabs=[], pump=0.65, bright=9, padv=0.14, lead=1),
    'funk': dict(bpm=112, prog=[(38, [62, 65, 69, 72]), (43, [59, 62, 65, 67]), (38, [62, 65, 69, 72]), (45, [60, 64, 67, 69])],
                 kick=[0, 3, 7, 10], clap=[4, 12], hat=list(range(16)), hatv=0.05, bass=[(0, 0), (3, 12), (6, 0), (7, 7), (10, 0), (13, 12), (14, 10)],
                 arp=[], arpv=0.0, stabs=[2, 7, 10, 15], pump=0.2, bright=6, padv=0.06, lead=0),
}

style = sys.argv[1]; DUR = float(sys.argv[2]); OUT = sys.argv[3]
P = STYLES[style]
STEP = 60 / P['bpm'] / 4
BAR = 16 * STEP
N = int(SR * (DUR + 2))
rng = np.random.default_rng(7)
DR = np.zeros((N, 2)); MU = np.zeros((N, 2))  # drum bus, music bus (music gets pumped)

def hz(m): return 440 * 2 ** ((m - 69) / 12)

def put(bus, sig, t, pan=0.0, gain=1.0):
    i = int(t * SR)
    if i >= N: return
    sig = sig[: N - i] * gain
    bus[i:i + len(sig), 0] += sig * np.sqrt((1 - pan) / 2)
    bus[i:i + len(sig), 1] += sig * np.sqrt((1 + pan) / 2)

def osc(f, t, k):  # band-limited saw-ish tone with k harmonics
    return sum(np.sin(2 * np.pi * f * j * t) / j for j in range(1, k + 1))

def env(n, att, tau):
    t = np.arange(n) / SR
    return np.minimum(1, t / att) * np.exp(-t / tau)

def lowpass(x, a):
    y = np.empty_like(x); acc = 0.0
    for k in range(len(x)):
        acc += a * (x[k] - acc); y[k] = acc
    return y

def pluck(m, dur, k, tau):
    n = int(dur * SR); t = np.arange(n) / SR
    return osc(hz(m), t, k) * env(n, 0.003, tau)

def pad(ms, dur, k):
    n = int(dur * SR); t = np.arange(n) / SR; s = np.zeros(n)
    for m in ms:
        for det in (-0.08, 0.08):
            s += osc(hz(m + det), t, k)
    a = np.minimum(1, t / 0.35) * np.minimum(1, (dur - t) / 0.3)
    return s * np.clip(a, 0, 1) / len(ms)

def bass(m, dur):
    n = int(dur * SR); t = np.arange(n) / SR; f = hz(m)
    return (np.sin(2 * np.pi * f * t) + 0.35 * np.sin(4 * np.pi * f * t) + 0.12 * np.sin(6 * np.pi * f * t)) * env(n, 0.005, 0.22)

def kick():
    n = int(0.35 * SR); t = np.arange(n) / SR
    f = 48 + 110 * np.exp(-t / 0.035)
    return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t / 0.13) + 0.3 * rng.standard_normal(n) * np.exp(-t / 0.003)

def noise_hit(dur, hp, tau):
    n = int(dur * SR); x = rng.standard_normal(n); x = x - lowpass(x, hp)
    return x * np.exp(-np.arange(n) / SR / tau)

K = kick(); HAT = noise_hit(0.06, 0.4, 0.014); OH = noise_hit(0.25, 0.4, 0.07); CL = noise_hit(0.22, 0.12, 0.055)
bars = int(np.ceil(DUR / BAR)) + 1
kick_times = []
for b in range(bars):
    t0 = b * BAR
    root, ch = P['prog'][b % 4]
    intro = b == 0
    outro = b >= bars - 2
    put(MU, pad(ch, BAR + 0.25, P['bright']), t0, 0, P['padv'])
    for idx, s in enumerate(P['arp']):
        note = ch[idx % len(ch)] + (12 if (idx // len(ch)) % 2 else 0)
        put(MU, pluck(note + 12 * P['lead'] * (idx % 8 == 7), STEP * 2, P['bright'], 0.09), t0 + s * STEP, 0.45 if idx % 2 else -0.45, P['arpv'])
    for s in P['stabs']:
        n = int(STEP * 1.6 * SR); tt = np.arange(n) / SR
        st = sum(osc(hz(m), tt, P['bright']) for m in ch) / len(ch) * env(n, 0.003, 0.06)
        put(MU, st, t0 + s * STEP, 0.2, 0.22)
    if not intro:
        for s, off in P['bass']:
            put(MU, bass(root + off, STEP * 2.2), t0 + s * STEP, 0, 0.34)
    if not intro and not outro:
        for s in P['kick']:
            put(DR, K, t0 + s * STEP, 0, 0.6); kick_times.append(t0 + s * STEP)
        for s in P['clap']:
            put(DR, CL, t0 + s * STEP, 0, 0.16)
        for s in P['hat']:
            sw = 0.18 * STEP if (style == 'funk' and s % 2) else 0
            if style == 'electro':
                put(DR, OH, t0 + s * STEP, 0.25, P['hatv'])
            else:
                put(DR, HAT, t0 + s * STEP + sw, 0.25, P['hatv'] * (1.0 if s % 4 == 2 else 0.6))

# sidechain pump: duck the music bus after every kick
if P['pump'] and kick_times:
    g = np.ones(N)
    dn = int(0.22 * SR); curve = 1 - P['pump'] * np.exp(-np.arange(dn) / SR / 0.07)
    for kt in kick_times:
        i = int(kt * SR); j = min(N, i + dn)
        g[i:j] = np.minimum(g[i:j], curve[: j - i])
    MU *= g[:, None]

# stereo delay on the music bus for space
d = int(STEP * 3 * SR)
wet = np.zeros_like(MU); wet[d:, 0] = MU[:-d, 1]; wet[d:, 1] = MU[:-d, 0]
mix = (MU + 0.18 * wet + DR)[: int(DUR * SR)]
t = np.arange(len(mix)) / SR
fade = np.minimum(1, t / 0.6) * np.minimum(1, (DUR - t) / 2.5)
mix *= np.clip(fade, 0, 1)[:, None]
mix = np.tanh(mix / np.max(np.abs(mix)) * 1.3) * 0.9
with wave.open(OUT, 'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR)
    w.writeframes((mix * 32767).astype('<i2').tobytes())
print('wrote', OUT, style, round(DUR, 2), 's')
