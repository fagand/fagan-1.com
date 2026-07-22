/* =================================================================
   bg.js — Deep-space background engine  |  fagan-1.com

   Usage:
     1. Add <canvas id="bg-canvas"></canvas> anywhere in <body>
     2. Include this script
     3. Call BgEngine.init({ ...optional config overrides... })

   LIVE LOOK-SWITCHING:
     BgEngine.setLook({ ...overrides... })  swaps colours / effect flags
     WITHOUT starting a second animation loop. Exactly one rAF draw loop
     ever runs. theme.js drives this to change the backdrop per "look".

   ----------------------------------------------------------------
   CONFIG REFERENCE — override any of these in init()/setLook():

   BG_FILL            css col  : base canvas fill. Default '#000'.
                                 (theme.js feeds it the palette's --bg)
   MOUSE_STRENGTH     (0–1)    : parallax intensity. Default 0.9
   MOUSE_EASE         (0–1)    : tracking lag. Lower = snappier.
   CARD_ID            string   : element id to apply 3D tilt to.

   NEBULA_COUNT       int      : number of fog blobs. Default 4.
   NEBULA_OPACITY     (0–0.2)  : blob brightness. Default 0.055.
   NEBULA_DRIFT       float    : blob wander speed. Default 0.00012.
   NEBULA_BREATHE     bool     : slow opacity pulse. Default true.

   ORB_COUNT          int      : accent glow orbs. Default 2.
   ORB_OPACITY        (0–0.2)  : orb brightness. Default 0.10.
   ORB_RADIUS         [min,max]: orb size px. Default [180,280].

   STAR_LAYERS        array    : depth layers { count, parallax,
                                 sizeMin, sizeMax }.

   CURSOR_SPOT        bool     : cursor spotlight. Default true.
   SHOOT_STARS        bool     : occasional shooting stars. Default true.
   SHOOT_MIN/MAX      ms       : interval range. Default 4500–9000.

   ---- per-look effect flags ----
   PARTICLE_DRIFT     float    : multiplies star drift. Default 1. (Abyss)
   AURORA             bool     : tall, slow nebula curtains. (Aurora)
   HUE_CYCLE          float    : nebula hue rotation speed. 0 = off. (Prism)

   ACCENT / STAR_TINTS / NEBULA_COLS — rgb arrays for the palette.
   ================================================================= */
(function (global) {
    'use strict';

    /* ---- Defaults ---- */
    var DEFAULT = {
        BG_FILL:         '#000000',
        MOUSE_STRENGTH:  0.9,
        MOUSE_EASE:      0.06,
        CARD_ID:         null,
        CARD_TILT_DEG:   5,
        CARD_TILT_EASE:  0.07,

        NEBULA_COUNT:    4,
        NEBULA_OPACITY:  0.055,
        NEBULA_DRIFT:    0.00012,
        NEBULA_BREATHE:  true,

        ORB_COUNT:       2,
        ORB_OPACITY:     0.10,
        ORB_RADIUS:      [180, 280],

        STAR_LAYERS: [
            { count: 220, parallax:  6, sizeMin: 0.25, sizeMax: 1.0 },
            { count:  90, parallax: 16, sizeMin: 0.50, sizeMax: 1.8 },
            { count:  26, parallax: 38, sizeMin: 1.20, sizeMax: 3.2 }
        ],

        CURSOR_SPOT:     true,
        CURSOR_SPOT_R:   420,
        CURSOR_SPOT_OP:  0.045,

        SHOOT_STARS:     true,
        SHOOT_MIN:       4500,
        SHOOT_MAX:       9000,

        PARTICLE_DRIFT:  1,
        AURORA:          false,
        HUE_CYCLE:       0,

        /* per-look signature effects (all optional / off by default) */
        PARTICLES:       null,   /* {count,color,mode:'rise'|'drift',speed,sizeMin,sizeMax,glow} */
        CURTAINS:        null,   /* {count,colors,speed,opacity}  — aurora light columns        */
        WASH:            null,   /* {speed,opacity}               — slow full-screen hue wash    */
        SHOOT_COLOR:     [255, 255, 255],

        ACCENT:      [204, 255, 0],
        STAR_TINTS:  [[255,255,255],[210,228,255],[204,255,0]],
        NEBULA_COLS: [[204,255,0],[0,220,160],[90,60,240],[204,255,0]]
    };

    var booted = false;   /* guards against a second rAF loop */

    /* ---- Public API ---- */
    global.BgEngine = {
        init: function (opts) {
            if (booted) { if (this._apply) this._apply(opts || {}); return; }
            var CFG = {}, k;
            for (k in DEFAULT) CFG[k] = DEFAULT[k];
            if (opts) for (k in opts) CFG[k] = opts[k];
            _boot(CFG);
        },
        /* Change the look live. Never starts a new loop. */
        setLook: function (opts) {
            if (!booted) { this.init(opts); return; }
            if (this._apply) this._apply(opts || {});
        },
        _apply: null,
        _getCfg: null   /* returns a snapshot of the live config (theme.js reads
                           the page-level density so look switches can restore it) */
    };

    /* ---- colour helpers ---- */
    function rgba(c, a) { return 'rgba(' + c[0] + ',' + c[1] + ',' + c[2] + ',' + a + ')'; }

    function rgbToHsl(c) {
        var r = c[0] / 255, g = c[1] / 255, b = c[2] / 255;
        var mx = Math.max(r, g, b), mn = Math.min(r, g, b);
        var h = 0, s = 0, l = (mx + mn) / 2;
        if (mx !== mn) {
            var d = mx - mn;
            s = l > 0.5 ? d / (2 - mx - mn) : d / (mx + mn);
            if (mx === r)      h = (g - b) / d + (g < b ? 6 : 0);
            else if (mx === g) h = (b - r) / d + 2;
            else               h = (r - g) / d + 4;
            h /= 6;
        }
        return [h, s, l];
    }
    function hslToRgb(h, s, l) {
        h = ((h % 1) + 1) % 1;
        var r, g, b;
        if (s === 0) { r = g = b = l; }
        else {
            var hue2rgb = function (p, q, t) {
                if (t < 0) t += 1; if (t > 1) t -= 1;
                if (t < 1 / 6) return p + (q - p) * 6 * t;
                if (t < 1 / 2) return q;
                if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
                return p;
            };
            var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            var p = 2 * l - q;
            r = hue2rgb(p, q, h + 1 / 3);
            g = hue2rgb(p, q, h);
            b = hue2rgb(p, q, h - 1 / 3);
        }
        return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
    }

    /* ---- Engine ---- */
    function _boot(CFG) {
        var canvas = document.getElementById('bg-canvas');
        if (!canvas) return;
        booted = true;
        var ctx  = canvas.getContext('2d');
        var card = CFG.CARD_ID ? document.getElementById(CFG.CARD_ID) : null;

        var W = 0, H = 0;
        var mouse      = { x: 0, y: 0, tx: 0, ty: 0 };
        var mouseAlive = false;
        var tiltX = 0, tiltY = 0;
        var layers = [], nebulae = [], orbs = [], shooters = [];
        var particles = [], curtains = [];
        var nextShoot = 0;
        var t0 = Date.now();

        var reduceMotion = window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function rand(a, b) { return a + Math.random() * (b - a); }
        function pick(arr)  { return arr[Math.floor(Math.random() * arr.length)]; }

        /* ---- resize ---- */
        function resize() {
            W = canvas.width  = window.innerWidth;
            H = canvas.height = window.innerHeight;
            mouse.tx = W / 2;
            mouse.ty = H / 2;
        }

        /* ---- stars ---- */
        function initStars() {
            var drift = CFG.PARTICLE_DRIFT || 1;
            layers = CFG.STAR_LAYERS.map(function (cfg) {
                var stars = [];
                for (var i = 0; i < cfg.count; i++) {
                    stars.push({
                        x:      Math.random(),
                        y:      Math.random(),
                        r:      rand(cfg.sizeMin, cfg.sizeMax),
                        alpha:  rand(0.35, 1.0),
                        twk:    rand(0.003, 0.009),
                        twkOff: rand(0, Math.PI * 2),
                        dx:     rand(-4e-5, 4e-5) * drift,
                        dy:     rand(-3e-5, 3e-5) * drift,
                        colour: pick(CFG.STAR_TINTS)
                    });
                }
                return { stars: stars, parallax: cfg.parallax };
            });
        }

        /* ---- nebulae ---- */
        function initNebulae() {
            var cols = CFG.NEBULA_COLS.slice(0, CFG.NEBULA_COUNT);
            nebulae = cols.map(function (c) {
                var rx = CFG.AURORA ? rand(0.34, 0.6)  : rand(0.28, 0.52);
                var ry = CFG.AURORA ? rand(0.55, 0.95) : rand(0.22, 0.42);
                var dscale = CFG.AURORA ? 0.5 : 1;
                return {
                    x: Math.random(), y: Math.random(),
                    rx: rx, ry: ry,
                    dx: rand(-CFG.NEBULA_DRIFT, CFG.NEBULA_DRIFT) * dscale,
                    dy: rand(-CFG.NEBULA_DRIFT * 0.7, CFG.NEBULA_DRIFT * 0.7) * dscale,
                    c:   c,
                    hsl: rgbToHsl(c),
                    op:  rand(0.025, CFG.NEBULA_OPACITY) * (CFG.AURORA ? 1.5 : 1),
                    breatheOff: rand(0, Math.PI * 2)
                };
            });
        }

        /* ---- orbs ---- */
        function initOrbs() {
            orbs = [];
            for (var i = 0; i < CFG.ORB_COUNT; i++) {
                orbs.push({
                    x:  rand(0.25, 0.75), y: rand(0.2, 0.8),
                    r:  rand(CFG.ORB_RADIUS[0], CFG.ORB_RADIUS[1]),
                    op: rand(0.05, CFG.ORB_OPACITY),
                    px: rand(12, 28)
                });
            }
        }

        /* ---- drifting / rising particles (Abyss bubbles, Ember sparks) ---- */
        function initParticles() {
            particles = [];
            var p = CFG.PARTICLES;
            if (!p) return;
            for (var i = 0; i < p.count; i++) {
                var rise = p.mode === 'rise';
                particles.push({
                    x:   Math.random(),
                    y:   Math.random(),
                    r:   rand(p.sizeMin || 0.8, p.sizeMax || 2.2),
                    a:   rand(0.30, 0.85),
                    tw:  rand(0.004, 0.018),
                    two: rand(0, Math.PI * 2),
                    vx:  rand(-4e-5, 4e-5) * (p.speed || 1),
                    vy:  (rise ? -rand(6e-5, 1.7e-4) : rand(-5e-5, 5e-5)) * (p.speed || 1)
                });
            }
        }

        /* ---- aurora curtains: soft, tall, swaying light columns ---- */
        function initCurtains() {
            curtains = [];
            var c = CFG.CURTAINS;
            if (!c) return;
            for (var i = 0; i < c.count; i++) {
                curtains.push({
                    baseX: (i + 0.5) / c.count + rand(-0.05, 0.05),
                    w:     rand(0.10, 0.18),
                    col:   c.colors[i % c.colors.length],
                    phase: rand(0, Math.PI * 2),
                    amp:   rand(0.03, 0.08),
                    speed: rand(0.05, 0.12) * (c.speed || 1),
                    op:    (c.opacity || 0.12) * rand(0.75, 1.15),
                    cy:    rand(0.35, 0.55)
                });
            }
        }

        /* ---- spawn shooting star ---- */
        function spawnShooter() {
            var angle = rand(25, 55) * Math.PI / 180;
            var spd   = rand(14, 24);
            shooters.push({
                x:     rand(W * 0.1, W * 0.9),
                y:     rand(-60, H * 0.35),
                vx:    Math.cos(angle) * spd,
                vy:    Math.sin(angle) * spd,
                len:   rand(100, 220),
                life:  1.0,
                decay: rand(0.012, 0.022)
            });
            nextShoot = Date.now() + rand(CFG.SHOOT_MIN, CFG.SHOOT_MAX);
        }

        /* current nebula colour (hue-cycled for Prism) */
        function nebColour(n, elapsed) {
            if (CFG.HUE_CYCLE) {
                return hslToRgb(n.hsl[0] + elapsed * CFG.HUE_CYCLE, n.hsl[1], n.hsl[2]);
            }
            return n.c;
        }

        /* slow full-screen hue wash (Prism) */
        function drawWash(elapsed) {
            if (!CFG.WASH) return;
            var h = elapsed * (CFG.WASH.speed || 0.02);
            var op = CFG.WASH.opacity != null ? CFG.WASH.opacity : 0.06;
            var g = ctx.createLinearGradient(0, 0, W, H);
            g.addColorStop(0.0, rgba(hslToRgb(h,        0.6, 0.55), op));
            g.addColorStop(0.5, rgba(hslToRgb(h + 0.16, 0.6, 0.55), op));
            g.addColorStop(1.0, rgba(hslToRgb(h + 0.33, 0.6, 0.55), op));
            ctx.fillStyle = g;
            ctx.fillRect(0, 0, W, H);
        }

        /* aurora curtains — soft tall columns that sway sideways */
        function drawCurtains(elapsed) {
            if (!CFG.CURTAINS) return;
            curtains.forEach(function (cu) {
                var x = (cu.baseX + Math.sin(elapsed * cu.speed + cu.phase) * cu.amp) * W;
                var breathe = 0.6 + 0.4 * Math.sin(elapsed * 0.3 + cu.phase);
                var r = cu.w * W;
                ctx.save();
                ctx.translate(x, cu.cy * H);
                ctx.scale(1, 2.6);
                var g = ctx.createRadialGradient(0, 0, 0, 0, 0, r);
                g.addColorStop(0, rgba(cu.col, cu.op * breathe));
                g.addColorStop(1, rgba(cu.col, 0));
                ctx.beginPath(); ctx.arc(0, 0, r, 0, Math.PI * 2);
                ctx.fillStyle = g; ctx.fill();
                ctx.restore();
            });
        }

        /* drifting / rising particles (Abyss bubbles, Ember sparks) */
        function drawParticles(animate) {
            if (!CFG.PARTICLES) return;
            var p = CFG.PARTICLES, col = p.color;
            particles.forEach(function (pt) {
                if (animate) {
                    pt.x = (pt.x + pt.vx + 1) % 1;
                    pt.y = (pt.y + pt.vy + 1) % 1;
                    pt.two += pt.tw;
                }
                var a  = pt.a * (animate ? (0.5 + 0.5 * Math.sin(pt.two)) : 0.8);
                var px = pt.x * W, py = pt.y * H;
                if (p.glow) {
                    var gr = pt.r * 4;
                    var g  = ctx.createRadialGradient(px, py, 0, px, py, gr);
                    g.addColorStop(0, rgba(col, a * 0.5));
                    g.addColorStop(1, rgba(col, 0));
                    ctx.beginPath(); ctx.arc(px, py, gr, 0, Math.PI * 2);
                    ctx.fillStyle = g; ctx.fill();
                }
                ctx.beginPath(); ctx.arc(px, py, pt.r, 0, Math.PI * 2);
                ctx.fillStyle = rgba(col, a); ctx.fill();
            });
        }

        /* ---- main draw loop (the ONLY rAF draw loop) ---- */
        function draw() {
            ctx.clearRect(0, 0, W, H);
            ctx.fillStyle = CFG.BG_FILL || '#000';
            ctx.fillRect(0, 0, W, H);

            mouse.x += (mouse.tx - mouse.x) * CFG.MOUSE_EASE;
            mouse.y += (mouse.ty - mouse.y) * CFG.MOUSE_EASE;
            var mx = (mouse.x / W - 0.5) * CFG.MOUSE_STRENGTH;
            var my = (mouse.y / H - 0.5) * CFG.MOUSE_STRENGTH;
            var elapsed = (Date.now() - t0) * 0.001;

            drawWash(elapsed);
            drawCurtains(elapsed);

            /* --- Nebulae --- */
            nebulae.forEach(function (n) {
                n.x = (n.x + n.dx + 1) % 1;
                n.y = (n.y + n.dy + 1) % 1;
                var breathe = CFG.NEBULA_BREATHE
                    ? (0.55 + 0.45 * Math.sin(elapsed * 0.35 + n.breatheOff))
                    : 1;
                var op = n.op * breathe;
                var col = nebColour(n, elapsed);
                var nx = n.x * W, ny = n.y * H, rw = n.rx * W;
                ctx.save();
                ctx.translate(nx, ny);
                ctx.scale(1, n.ry / n.rx);
                var g = ctx.createRadialGradient(0, 0, 0, 0, 0, rw);
                g.addColorStop(0, rgba(col, op));
                g.addColorStop(1, rgba(col, 0));
                ctx.beginPath(); ctx.arc(0, 0, rw, 0, Math.PI * 2);
                ctx.fillStyle = g; ctx.fill();
                ctx.restore();
            });

            /* --- Accent orbs --- */
            orbs.forEach(function (o) {
                var ox = o.x * W + mx * -o.px;
                var oy = o.y * H + my * -o.px;
                var g  = ctx.createRadialGradient(ox, oy, 0, ox, oy, o.r);
                g.addColorStop(0, rgba(CFG.ACCENT, o.op));
                g.addColorStop(1, rgba(CFG.ACCENT, 0));
                ctx.beginPath(); ctx.arc(ox, oy, o.r, 0, Math.PI * 2);
                ctx.fillStyle = g; ctx.fill();
            });

            /* --- Stars --- */
            layers.forEach(function (layer) {
                var offX = mx * layer.parallax * -1;
                var offY = my * layer.parallax * -1;
                layer.stars.forEach(function (s) {
                    s.x = (s.x + s.dx + 1) % 1;
                    s.y = (s.y + s.dy + 1) % 1;
                    s.twkOff += s.twk;
                    var a  = s.alpha * (0.45 + 0.55 * Math.sin(s.twkOff));
                    var sx = s.x * W + offX;
                    var sy = s.y * H + offY;
                    if (s.r > 1.4) {
                        var hr = s.r * 5;
                        var g  = ctx.createRadialGradient(sx, sy, 0, sx, sy, hr);
                        g.addColorStop(0, rgba(s.colour, a * 0.35));
                        g.addColorStop(1, rgba(s.colour, 0));
                        ctx.beginPath(); ctx.arc(sx, sy, hr, 0, Math.PI * 2);
                        ctx.fillStyle = g; ctx.fill();
                    }
                    ctx.beginPath(); ctx.arc(sx, sy, s.r, 0, Math.PI * 2);
                    ctx.fillStyle = rgba(s.colour, a); ctx.fill();
                });
            });

            /* --- Particles --- */
            drawParticles(true);

            /* --- Cursor spotlight --- */
            if (CFG.CURSOR_SPOT && mouseAlive) {
                var sg = ctx.createRadialGradient(
                    mouse.x, mouse.y, 0,
                    mouse.x, mouse.y, CFG.CURSOR_SPOT_R
                );
                sg.addColorStop(0,    rgba(CFG.ACCENT, CFG.CURSOR_SPOT_OP));
                sg.addColorStop(0.45, rgba(CFG.ACCENT, CFG.CURSOR_SPOT_OP * 0.25));
                sg.addColorStop(1,    rgba(CFG.ACCENT, 0));
                ctx.beginPath();
                ctx.arc(mouse.x, mouse.y, CFG.CURSOR_SPOT_R, 0, Math.PI * 2);
                ctx.fillStyle = sg;
                ctx.fill();
            }

            /* --- Shooting stars --- */
            if (CFG.SHOOT_STARS) {
                if (Date.now() > nextShoot) spawnShooter();
                shooters = shooters.filter(function (s) {
                    s.x += s.vx; s.y += s.vy;
                    s.life -= s.decay;
                    if (s.life <= 0) return false;
                    var spd  = Math.sqrt(s.vx * s.vx + s.vy * s.vy);
                    var tx   = s.x - (s.vx / spd) * s.len * s.life;
                    var ty   = s.y - (s.vy / spd) * s.len * s.life;
                    var sc   = CFG.SHOOT_COLOR;
                    var sg   = ctx.createLinearGradient(tx, ty, s.x, s.y);
                    sg.addColorStop(0, rgba(sc, 0));
                    sg.addColorStop(1, rgba(sc, s.life * 0.9));
                    ctx.save();
                    ctx.beginPath(); ctx.moveTo(tx, ty); ctx.lineTo(s.x, s.y);
                    ctx.strokeStyle = sg;
                    ctx.lineWidth = Math.max(0.5, s.life * 1.8);
                    ctx.stroke();
                    var dotG = ctx.createRadialGradient(s.x, s.y, 0, s.x, s.y, 4);
                    dotG.addColorStop(0, rgba(sc, s.life * 0.8));
                    dotG.addColorStop(1, rgba(sc, 0));
                    ctx.beginPath(); ctx.arc(s.x, s.y, 4, 0, Math.PI * 2);
                    ctx.fillStyle = dotG; ctx.fill();
                    ctx.restore();
                    return s.x < W + 200 && s.y < H + 200;
                });
            } else {
                shooters.length = 0;
            }

            requestAnimationFrame(draw);
        }

        /* ---- card tilt loop ---- */
        function tiltLoop() {
            if (card) {
                var tx = (mouse.x / W - 0.5) * CFG.CARD_TILT_DEG;
                var ty = (mouse.y / H - 0.5) * CFG.CARD_TILT_DEG;
                tiltX += (ty  - tiltX) * CFG.CARD_TILT_EASE;
                tiltY += (-tx - tiltY) * CFG.CARD_TILT_EASE;
                card.style.transform =
                    'perspective(1400px) ' +
                    'rotateX('  + tiltX.toFixed(2) + 'deg) ' +
                    'rotateY('  + tiltY.toFixed(2) + 'deg) ' +
                    'translateZ(4px)';
            }
            requestAnimationFrame(tiltLoop);
        }

        /* ---- single still frame for reduced-motion users ---- */
        function drawStatic() {
            var elapsed = 0;
            ctx.clearRect(0, 0, W, H);
            ctx.fillStyle = CFG.BG_FILL || '#000';
            ctx.fillRect(0, 0, W, H);
            drawWash(elapsed);
            drawCurtains(elapsed);
            nebulae.forEach(function (n) {
                var col = nebColour(n, elapsed);
                var nx = n.x * W, ny = n.y * H, rw = n.rx * W;
                ctx.save();
                ctx.translate(nx, ny);
                ctx.scale(1, n.ry / n.rx);
                var g = ctx.createRadialGradient(0, 0, 0, 0, 0, rw);
                g.addColorStop(0, rgba(col, n.op));
                g.addColorStop(1, rgba(col, 0));
                ctx.beginPath(); ctx.arc(0, 0, rw, 0, Math.PI * 2);
                ctx.fillStyle = g; ctx.fill();
                ctx.restore();
            });
            orbs.forEach(function (o) {
                var ox = o.x * W, oy = o.y * H;
                var g  = ctx.createRadialGradient(ox, oy, 0, ox, oy, o.r);
                g.addColorStop(0, rgba(CFG.ACCENT, o.op));
                g.addColorStop(1, rgba(CFG.ACCENT, 0));
                ctx.beginPath(); ctx.arc(ox, oy, o.r, 0, Math.PI * 2);
                ctx.fillStyle = g; ctx.fill();
            });
            layers.forEach(function (layer) {
                layer.stars.forEach(function (s) {
                    ctx.beginPath();
                    ctx.arc(s.x * W, s.y * H, s.r, 0, Math.PI * 2);
                    ctx.fillStyle = rgba(s.colour, s.alpha * 0.75);
                    ctx.fill();
                });
            });
            drawParticles(false);
        }

        /* ---- re-seed the world for a new look (no new loop) ---- */
        function applyLook(opts) {
            var k;
            for (k in opts) CFG[k] = opts[k];
            card = CFG.CARD_ID ? document.getElementById(CFG.CARD_ID) : card;
            initStars();
            initNebulae();
            initOrbs();
            initParticles();
            initCurtains();
            shooters.length = 0;
            nextShoot = Date.now() + rand(CFG.SHOOT_MIN, CFG.SHOOT_MAX);
            if (reduceMotion) drawStatic();
        }
        global.BgEngine._apply = applyLook;
        global.BgEngine._getCfg = function () {
            var o = {}, key;
            for (key in CFG) o[key] = CFG[key];
            return o;
        };

        /* ---- events ---- */
        window.addEventListener('mousemove', function (e) {
            mouse.tx = e.clientX;
            mouse.ty = e.clientY;
            mouseAlive = true;
        });
        document.addEventListener('mouseleave', function () {
            mouse.tx = W / 2;
            mouse.ty = H / 2;
        });
        window.addEventListener('resize', function () {
            resize(); initStars();
            if (reduceMotion) drawStatic();
        }, { passive: true });

        /* ---- boot ---- */
        resize();
        initStars();
        initNebulae();
        initOrbs();
        initParticles();
        initCurtains();

        if (reduceMotion) {
            CFG.SHOOT_STARS    = false;
            CFG.NEBULA_BREATHE = false;
            drawStatic();
            return;
        }

        nextShoot = Date.now() + rand(2500, 5000);
        requestAnimationFrame(draw);
        requestAnimationFrame(tiltLoop);
    }

})(window);
