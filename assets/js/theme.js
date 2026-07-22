/* =================================================================
   theme.js — "Looks" + light/dark system  |  fagan-1.com
   Shared across index.html and every sub-page so the chosen look and
   light/dark treatment persist site-wide.

   TWO independent axes, both remembered in localStorage:
     • LOOK  ('f1-look')  = backdrop effect + colour scheme, changed as a
        unit. No saved choice → random each visit; "Auto" restores that.
     • MODE  ('f1-mode')  = 'system' | 'light' | 'dark'. Manual override of
        the OS; 'system' follows prefers-color-scheme live.

   Palette TOKENS live in looks.css (single source of truth). This file
   reads the computed --bg / --accent-rgb so the canvas base + accent are
   single-sourced from there, and supplies the per-look backdrop effects
   (stars, nebulae, particles, aurora curtains, hue wash) that have no CSS
   equivalent. It drives bg.js via BgEngine.setLook (one rAF loop, ever).

   A tiny inline <head> bootstrap sets data-look + data-theme BEFORE first
   paint to avoid a flash; it only needs the look keys + mode resolve.
   ================================================================= */
(function (global) {
    'use strict';

    var KEY_LOOK = 'f1-look';
    var KEY_MODE = 'f1-mode';
    var ORDER    = ['nightfall', 'ember', 'abyss', 'aurora', 'prism'];

    /* ---- star-layer presets ---- */
    var LAYERS_DEFAULT = [
        { count: 220, parallax:  6, sizeMin: 0.25, sizeMax: 1.0 },
        { count:  90, parallax: 16, sizeMin: 0.50, sizeMax: 1.8 },
        { count:  26, parallax: 38, sizeMin: 1.20, sizeMax: 3.2 }
    ];
    var LAYERS_DENSE = [
        { count: 360, parallax:  6, sizeMin: 0.20, sizeMax: 0.9 },
        { count: 130, parallax: 16, sizeMin: 0.40, sizeMax: 1.5 },
        { count:  22, parallax: 38, sizeMin: 1.00, sizeMax: 2.4 }
    ];
    var LAYERS_SPARSE = [
        { count: 90,  parallax:  6, sizeMin: 0.25, sizeMax: 0.9 },
        { count: 34,  parallax: 16, sizeMin: 0.45, sizeMax: 1.5 },
        { count: 10,  parallax: 38, sizeMin: 1.10, sizeMax: 2.6 }
    ];

    /* Effect-flag baseline — merged UNDER each look so a switch reliably
       clears every effect. Page-level density (NEBULA_COUNT/ORB_COUNT/
       opacities) is captured separately from the running config below. */
    var BASELINE = {
        STAR_LAYERS:    LAYERS_DEFAULT,
        PARTICLE_DRIFT: 1,
        AURORA:         false,
        HUE_CYCLE:      0,
        SHOOT_STARS:    true,
        NEBULA_BREATHE: true,
        NEBULA_DRIFT:   0.00012,
        SHOOT_MIN:      9000,
        SHOOT_MAX:      16000,
        PARTICLES:      null,
        CURTAINS:       null,
        WASH:           null,
        SHOOT_COLOR:    [255, 255, 255]
    };

    /* Per-look backdrop effects (colours/flags only; base fill + accent are
       read live from the CSS tokens). Each look has a dark + light variant. */
    var LOOKS = {
        nightfall: {
            label: 'Nightfall',
            dark: {
                STAR_TINTS:  [[255,255,255],[210,228,255],[204,255,0]],
                NEBULA_COLS: [[204,255,0],[0,220,160],[90,60,240],[204,255,0]]
            },
            light: {
                STAR_TINTS:  [[60,66,52],[70,86,120],[110,130,0]],
                NEBULA_COLS: [[150,180,40],[70,180,150],[150,120,210],[150,180,40]],
                NEBULA_OPACITY: 0.05, SHOOT_COLOR: [40, 44, 28]
            }
        },
        ember: {
            label: 'Ember',
            dark: {
                STAR_TINTS:  [[255,244,230],[255,206,150],[255,150,70]],
                NEBULA_COLS: [[255,120,40],[230,70,30],[150,50,20]],
                PARTICLES: { count: 48, color: [255,180,90], mode: 'rise',
                             speed: 1.1, sizeMin: 0.7, sizeMax: 2.2, glow: true },
                SHOOT_COLOR: [255, 210, 160], SHOOT_MIN: 15000, SHOOT_MAX: 26000
            },
            light: {
                STAR_TINTS:  [[120,80,44],[150,90,50],[150,82,22]],
                NEBULA_COLS: [[240,150,70],[220,110,60],[200,120,60]],
                NEBULA_OPACITY: 0.06,
                PARTICLES: { count: 42, color: [200,110,40], mode: 'rise',
                             speed: 1.0, sizeMin: 0.7, sizeMax: 2.0, glow: true },
                SHOOT_COLOR: [150, 90, 40], SHOOT_MIN: 15000, SHOOT_MAX: 26000
            }
        },
        abyss: {
            label: 'Abyss',
            dark: {
                STAR_TINTS:  [[200,255,245],[110,220,215],[45,220,205]],
                NEBULA_COLS: [[0,200,185],[0,140,165],[15,110,140]],
                STAR_LAYERS: LAYERS_DENSE,
                PARTICLES: { count: 60, color: [130,235,225], mode: 'drift',
                             speed: 1.0, sizeMin: 0.8, sizeMax: 2.4, glow: true },
                SHOOT_STARS: false, NEBULA_DRIFT: 0.00008
            },
            light: {
                STAR_TINTS:  [[30,90,84],[26,100,96],[20,120,110]],
                NEBULA_COLS: [[40,160,150],[30,130,140],[40,120,120]],
                NEBULA_OPACITY: 0.06, STAR_LAYERS: LAYERS_DENSE,
                PARTICLES: { count: 54, color: [30,150,140], mode: 'drift',
                             speed: 1.0, sizeMin: 0.8, sizeMax: 2.2, glow: true },
                SHOOT_STARS: false, NEBULA_DRIFT: 0.00008
            }
        },
        aurora: {
            label: 'Aurora',
            dark: {
                STAR_TINTS:  [[220,235,255],[190,255,220],[110,255,176]],
                NEBULA_COLS: [[30,200,130],[110,80,240]],
                STAR_LAYERS: LAYERS_SPARSE, NEBULA_OPACITY: 0.04,
                CURTAINS: { count: 5, speed: 1.0, opacity: 0.16,
                            colors: [[60,240,150],[130,90,245],[80,225,190],[150,100,240],[60,240,150]] },
                SHOOT_STARS: false
            },
            light: {
                STAR_TINTS:  [[40,80,66],[60,70,110],[30,122,74]],
                NEBULA_COLS: [[60,180,120],[120,90,180]],
                STAR_LAYERS: LAYERS_SPARSE, NEBULA_OPACITY: 0.04,
                CURTAINS: { count: 5, speed: 1.0, opacity: 0.12,
                            colors: [[40,175,110],[110,80,180],[50,165,135],[120,90,185],[40,175,110]] },
                SHOOT_STARS: false
            }
        },
        prism: {
            label: 'Prism',
            dark: {
                STAR_TINTS:  [[255,220,235],[210,225,255],[220,255,220]],
                NEBULA_COLS: [[255,120,180],[120,180,255],[180,255,160],[220,160,255]],
                HUE_CYCLE: 0.02, NEBULA_DRIFT: 0.00009,
                WASH: { speed: 0.02, opacity: 0.05 },
                SHOOT_STARS: false
            },
            light: {
                STAR_TINTS:  [[130,90,110],[90,100,130],[100,120,90]],
                NEBULA_COLS: [[220,120,170],[120,150,220],[150,200,140],[190,140,210]],
                HUE_CYCLE: 0.02, NEBULA_DRIFT: 0.00009, NEBULA_OPACITY: 0.06,
                WASH: { speed: 0.02, opacity: 0.045 },
                SHOOT_STARS: false
            }
        }
    };

    /* ---- storage ---- */
    function readLook() { try { return localStorage.getItem(KEY_LOOK); } catch (e) { return null; } }
    function writeLook(v) { try { localStorage.setItem(KEY_LOOK, v); } catch (e) {} }
    function readMode() { try { return localStorage.getItem(KEY_MODE); } catch (e) { return null; } }
    function writeMode(v) { try { localStorage.setItem(KEY_MODE, v); } catch (e) {} }
    function randomKey() { return ORDER[Math.floor(Math.random() * ORDER.length)]; }

    function parseRgb(str) {
        var parts = String(str).split(',').map(function (n) { return parseInt(n, 10); });
        if (parts.length === 3 && parts.every(function (n) { return !isNaN(n); })) return parts;
        return null;
    }

    var lightMQ = global.matchMedia ? global.matchMedia('(prefers-color-scheme: light)') : null;
    function osLight() { return !!(lightMQ && lightMQ.matches); }

    var root = document.documentElement;

    /* ---- current state (data-look/data-theme set pre-paint by bootstrap) ---- */
    var active = root.getAttribute('data-look');
    if (!active || ORDER.indexOf(active) < 0) active = randomKey();
    var isAuto = (function () { var s = readLook(); return !s || s === 'auto'; })();

    var MODES = ['system', 'light', 'dark'];
    var mode = (function () { var m = readMode(); return MODES.indexOf(m) >= 0 ? m : 'system'; })();
    function effectiveTheme() { return mode === 'light' ? 'light' : mode === 'dark' ? 'dark' : (osLight() ? 'light' : 'dark'); }

    /* ---- capture the page's density config once (so look overrides can be
       reverted). bg.js has already booted from the page's init() call. ---- */
    var pageBase = {};
    if (global.BgEngine && global.BgEngine._getCfg) {
        var c = global.BgEngine._getCfg();
        ['NEBULA_COUNT','ORB_COUNT','NEBULA_OPACITY','ORB_OPACITY','MOUSE_STRENGTH','CARD_ID']
            .forEach(function (k) { if (k in c) pageBase[k] = c[k]; });
    }

    /* ---- drive the backdrop from current look + resolved theme ---- */
    function driveBackdrop() {
        if (!global.BgEngine) return;
        var look = LOOKS[active] || LOOKS.nightfall;
        var variant = (effectiveTheme() === 'light') ? look.light : look.dark;

        var cs = getComputedStyle(root);
        var bg = cs.getPropertyValue('--bg').trim() || '#000';
        var accent = parseRgb(cs.getPropertyValue('--accent-rgb').trim()) || [204, 255, 0];

        var cfg = {}, k;
        for (k in BASELINE) cfg[k] = BASELINE[k];
        for (k in pageBase) cfg[k] = pageBase[k];
        for (k in variant)  cfg[k] = variant[k];
        cfg.BG_FILL = bg;
        cfg.ACCENT  = accent;

        global.BgEngine.setLook(cfg);
    }

    /* ================= switcher UI ================= */
    var lookBtn, lookName, modeBtn, modeGlyph;

    var MODE_GLYPH = { system: '◐', light: '☀', dark: '☾' };
    var MODE_LABEL = { system: 'Follow system', light: 'Light', dark: 'Dark' };

    function lookLabel() { return isAuto ? 'Auto' : (LOOKS[active] ? LOOKS[active].label : 'Nightfall'); }
    function refreshLook() {
        if (!lookBtn) return;
        lookName.textContent = lookLabel();
        lookBtn.setAttribute('aria-label', 'Change look — current: ' + lookLabel());
    }
    function refreshMode() {
        if (!modeBtn) return;
        modeGlyph.textContent = MODE_GLYPH[mode];
        modeBtn.setAttribute('aria-label',
            'Light/dark: ' + MODE_LABEL[mode] + '. Click to change.');
    }

    function applyTheme() { root.setAttribute('data-theme', effectiveTheme()); }

    function setActive(key) {
        active = key;
        root.setAttribute('data-look', key);
        driveBackdrop();
        refreshLook();
    }

    function cycleLook() {
        var idx = isAuto ? ORDER.length : ORDER.indexOf(active);
        var next = (idx + 1) % (ORDER.length + 1);
        if (next === ORDER.length) {           /* Auto slot */
            isAuto = true;
            writeLook('auto');
            setActive(randomKey());
        } else {
            isAuto = false;
            writeLook(ORDER[next]);
            setActive(ORDER[next]);
        }
    }

    function cycleMode() {
        mode = MODES[(MODES.indexOf(mode) + 1) % MODES.length];
        writeMode(mode);
        applyTheme();
        driveBackdrop();   /* palette flipped → re-drive the canvas */
        refreshMode();
    }

    function makePill(cls, aria) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'hud-pill ' + cls;
        b.setAttribute('aria-label', aria);
        return b;
    }

    function buildControls() {
        var wrap = document.createElement('div');
        wrap.className = 'hud-controls';

        modeBtn = makePill('mode-switch', '');
        modeGlyph = document.createElement('span');
        modeGlyph.className = 'mode-glyph';
        modeGlyph.setAttribute('aria-hidden', 'true');
        modeBtn.appendChild(modeGlyph);
        modeBtn.addEventListener('click', cycleMode);

        lookBtn = makePill('look-switch', '');
        lookName = document.createElement('span');
        lookName.className = 'look-name';
        lookBtn.appendChild(lookName);
        lookBtn.addEventListener('click', cycleLook);

        wrap.appendChild(modeBtn);
        wrap.appendChild(lookBtn);
        document.body.appendChild(wrap);

        refreshMode();
        refreshLook();
    }

    /* ---- OS light/dark: only matters while following the system ---- */
    function onScheme() {
        if (mode !== 'system') return;
        applyTheme();
        driveBackdrop();
    }
    if (lightMQ) {
        if (lightMQ.addEventListener) lightMQ.addEventListener('change', onScheme);
        else if (lightMQ.addListener) lightMQ.addListener(onScheme);
    }

    function boot() {
        root.setAttribute('data-look', active);
        applyTheme();
        buildControls();
        driveBackdrop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

})(window);
