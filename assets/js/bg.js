/* =================================================================
   bg.js — Deep-space background engine  |  fagan-1.com

   Usage:
     1. Add <canvas id="bg-canvas"></canvas> anywhere in <body>
     2. Include this script
     3. Call BgEngine.init({ ...optional config overrides... })

   ----------------------------------------------------------------
   CONFIG REFERENCE — override any of these in your init() call:

   MOUSE_STRENGTH     (0–1)    : parallax intensity. Default 0.9
   MOUSE_EASE         (0–1)    : tracking lag. Lower = snappier.
   CARD_ID            string   : element id to apply 3D tilt to.
                                  null = no tilt.
   CARD_TILT_DEG      (0–12)   : max tilt degrees. Default 5.
   CARD_TILT_EASE     (0–1)    : tilt smoothing. Default 0.07.

   NEBULA_COUNT       int      : number of fog blobs. Default 4.
   NEBULA_OPACITY     (0–0.2)  : blob brightness. Default 0.055.
   NEBULA_DRIFT       float    : blob wander speed. Default 0.00012.
   NEBULA_BREATHE     bool     : slow opacity pulse. Default true.

   ORB_COUNT          int      : accent glow orbs. Default 2.
   ORB_OPACITY        (0–0.2)  : orb brightness. Default 0.10.
   ORB_RADIUS         [min,max]: orb size px. Default [180,280].

   STAR_LAYERS        array    : three depth layers, each has:
     count    — star density
     parallax — px shift on mouse (near = higher)
     sizeMin/Max — dot radius range

   CURSOR_SPOT        bool     : cursor spotlight. Default true.
   CURSOR_SPOT_R      px       : spotlight radius. Default 420.
   CURSOR_SPOT_OP     (0–0.2)  : spotlight centre opacity. Default 0.045.

   SHOOT_STARS        bool     : occasional shooting stars. Default true.
   SHOOT_MIN/MAX      ms       : interval range. Default 4500–9000.
   ================================================================= */
(function (global) {
    'use strict';

    /* ---- Defaults ---- */
    var DEFAULT = {
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

        ACCENT:      [204, 255, 0],
        STAR_TINTS:  [[255,255,255],[210,228,255],[204,255,0]],
        NEBULA_COLS: [[204,255,0],[0,220,160],[90,60,240],[204,255,0]]
    };

    /* ---- Public API ---- */
    global.BgEngine = {
        init: function (opts) {
            var CFG = {}, k;
            for (k in DEFAULT) CFG[k] = DEFAULT[k];
            if (opts) for (k in opts) CFG[k] = opts[k];
            _boot(CFG);
        }
    };

    /* ---- Engine ---- */
    function _boot(CFG) {
        var canvas = document.getElementById('bg-canvas');
        if (!canvas) return;
        var ctx  = canvas.getContext('2d');
        var card = CFG.CARD_ID ? document.getElementById(CFG.CARD_ID) : null;

        var W = 0, H = 0;
        var mouse      = { x: 0, y: 0, tx: 0, ty: 0 };
        var mouseAlive = false;
        var tiltX = 0, tiltY = 0;
        var layers = [], nebulae = [], orbs = [], shooters = [];
        var nextShoot = 0;
        var t0 = Date.now();

        function rand(a, b) { return a + Math.random() * (b - a); }
        function pick(arr)  { return arr[Math.floor(Math.random() * arr.length)]; }
        function rgba(c, a) { return 'rgba('+c[0]+','+c[1]+','+c[2]+','+a+')'; }

        /* ---- resize ---- */
        function resize() {
            W = canvas.width  = window.innerWidth;
            H = canvas.height = window.innerHeight;
            mouse.tx = W / 2;
            mouse.ty = H / 2;
        }

        /* ---- stars ---- */
        function initStars() {
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
                        dx:     rand(-4e-5, 4e-5),
                        dy:     rand(-3e-5, 3e-5),
                        colour: pick(CFG.STAR_TINTS)
                    });
                }
                return { stars: stars, parallax: cfg.parallax };
            });
        }

        /* ---- nebulae ---- */
        function initNebulae() {
            nebulae = CFG.NEBULA_COLS.slice(0, CFG.NEBULA_COUNT).map(function (c) {
                return {
                    x: Math.random(), y: Math.random(),
                    rx: rand(0.28, 0.52), ry: rand(0.22, 0.42),
                    dx: rand(-CFG.NEBULA_DRIFT, CFG.NEBULA_DRIFT),
                    dy: rand(-CFG.NEBULA_DRIFT * 0.7, CFG.NEBULA_DRIFT * 0.7),
                    c:  c,
                    op: rand(0.025, CFG.NEBULA_OPACITY),
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

        /* ---- main draw loop ---- */
        function draw() {
            ctx.clearRect(0, 0, W, H);
            ctx.fillStyle = '#000';
            ctx.fillRect(0, 0, W, H);

            /* Smooth mouse */
            mouse.x += (mouse.tx - mouse.x) * CFG.MOUSE_EASE;
            mouse.y += (mouse.ty - mouse.y) * CFG.MOUSE_EASE;
            var mx = (mouse.x / W - 0.5) * CFG.MOUSE_STRENGTH;
            var my = (mouse.y / H - 0.5) * CFG.MOUSE_STRENGTH;
            var elapsed = (Date.now() - t0) * 0.001;

            /* --- Nebulae --- */
            nebulae.forEach(function (n) {
                n.x = (n.x + n.dx + 1) % 1;
                n.y = (n.y + n.dy + 1) % 1;
                var breathe = CFG.NEBULA_BREATHE
                    ? (0.55 + 0.45 * Math.sin(elapsed * 0.35 + n.breatheOff))
                    : 1;
                var op = n.op * breathe;
                var nx = n.x * W, ny = n.y * H, rw = n.rx * W;
                ctx.save();
                ctx.translate(nx, ny);
                ctx.scale(1, n.ry / n.rx);
                var g = ctx.createRadialGradient(0, 0, 0, 0, 0, rw);
                g.addColorStop(0, rgba(n.c, op));
                g.addColorStop(1, rgba(n.c, 0));
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
                    var sg   = ctx.createLinearGradient(tx, ty, s.x, s.y);
                    sg.addColorStop(0, rgba([255,255,255], 0));
                    sg.addColorStop(1, rgba([255,255,255], s.life * 0.9));
                    ctx.save();
                    ctx.beginPath(); ctx.moveTo(tx, ty); ctx.lineTo(s.x, s.y);
                    ctx.strokeStyle = sg;
                    ctx.lineWidth = Math.max(0.5, s.life * 1.8);
                    ctx.stroke();
                    /* glow dot at head */
                    var dotG = ctx.createRadialGradient(s.x, s.y, 0, s.x, s.y, 4);
                    dotG.addColorStop(0, rgba([255,255,255], s.life * 0.8));
                    dotG.addColorStop(1, rgba([255,255,255], 0));
                    ctx.beginPath(); ctx.arc(s.x, s.y, 4, 0, Math.PI * 2);
                    ctx.fillStyle = dotG; ctx.fill();
                    ctx.restore();
                    return s.x < W + 200 && s.y < H + 200;
                });
            }

            requestAnimationFrame(draw);
        }

        /* ---- card tilt loop (separate rAF for smooth interpolation) ---- */
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
        }, { passive: true });

        /* ---- boot ---- */
        resize();
        initStars();
        initNebulae();
        initOrbs();
        nextShoot = Date.now() + rand(2500, 5000);  /* first shot arrives quickly */
        requestAnimationFrame(draw);
        requestAnimationFrame(tiltLoop);
    }

})(window);
