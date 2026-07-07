# Design

Visual system captured from the live code (assets/css/site.css + index.html inline styles).
index.html mirrors the tokens inline; site.css is canonical for sub-pages.

## Theme

Deep-space HUD on true black. Fixed canvas starfield (assets/js/bg.js: nebulae, orbs,
3 parallax star layers, shooting stars, cursor spotlight) behind a faint #ccff00 grid
overlay that fades toward the bottom. Content sits in frosted-glass cards with viewfinder
corner ticks.

## Color

| Token | Value | Role |
|---|---|---|
| `--bg` | `#000000` | Body background (canvas paints it too) |
| `--accent` | `#ccff00` | Neon lime — links, glows, ticks, status dots |
| `--white` | `#ffffff` | Headings |
| `--text` | `rgba(255,255,255,0.82)` | Body copy |
| `--text-dim` | `rgba(255,255,255,0.40)` | Meta/kicker copy |
| `--card-bg` | `rgba(6,6,6,0.65)` | Glass card fill (index inline uses 0.58 — drift) |
| `--card-border` | `rgba(204,255,0,0.13)` | Card hairline |

Accent is used at low alphas (0.03–0.28) for fills/borders, full strength only for text
and 1px glow lines. Strategy: **restrained** — black surface, one accent ≤10%.

## Typography

- UI/body: `-apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', system-ui, sans-serif`
- Mono voice (kickers, nav, badges, footer): `ui-monospace, 'SF Mono', … monospace`,
  10–13px, uppercase, letter-spacing 0.05–0.24em
- **Heritage display: `Atak`** (`assets/fonts/atak.woff2`, 27KB + .ttf fallback) — the
  site's original 2006 dripping-graffiti font. Used for the homepage hero wordmark, era
  years, rail volume numbers and the footer wordmark. `--font-atak`, `font-display: swap`,
  preloaded. Glyphs drip below the baseline — reserve `padding-bottom` under it.
- Page h1: `clamp(28px, 5vw, 48px)`, weight 700, -0.025em, `em` = accent with glow
- Body: 14.5px / 1.78

## Homepage (index.html) — scroll story

Single long-scroll "keynote", not the old centred card. Four scenes driven by one rAF
loop (`transform`/`opacity` only; disabled under `prefers-reduced-motion`):
1. **Pinned hero** — ATAK wordmark advances toward the viewer and dissolves.
2. **The story** — a vertical era timeline of the site's real screenshots
   (`assets/img/eras/era1–5.jpg`, 2006 → final), each in a browser-chrome frame that
   drifts sideways + scales as it passes centre; a giant outlined ATAK year parallaxes behind.
3. **Pinned mixtape rail** — 11 volume cards sweep right→left; each carries its SoundCloud
   artwork (`assets/img/mixtapes/1–10.jpg`) under a scrim with a dripping ATAK number;
   volume 11 (no art) uses an accent-gradient fallback.
4. **Closing CTA** — "Access the music" → /mixtapes/, quick links, footer.

## Components

- **.site-header** — sticky 56px glass bar: pulsing live dot + mono brand + mono nav links
  (active = accent + underline glow)
- **.glass-card** — blur(24px) card, accent top-edge gradient line, corner viewfinder ticks
  that brighten on hover
- **.pill-btn** — mono uppercase pill, accent tint fill, sheen sweep on hover, -2px lift
- **.page-hero** — kicker line (`— // LABEL` style with glowing dash) + h1 + mono sub
- **.site-footer** — mono microtype, centred
- Homepage card adds: 3D tilt (BgEngine CARD_ID), cursor spotlight overlay, est. badge

## Motion

- Entrances: `fade-up` 0.9s `cubic-bezier(0.16,1,0.3,1)` with staggered delays (0.15–1.35s)
- Hovers: translateY lifts with back-out curve `cubic-bezier(0.34,1.56,0.64,1)`
- Ambient: pulse-dot 2.6s, equalizer bars (mixtapes), canvas starfield
- `prefers-reduced-motion` handled for eq bars only — **gap: canvas + entrances don't yet**

## Layout

- Sub-pages: `.page-wrap` max-width 980px, 52px/24px padding
- Homepage: single centred card, max-width 560px
- Grids: `repeat(auto-fill, minmax(420px,1fr))` for mixtape cards
- Breakpoint: 640px (sub-pages), 600px (homepage card)
