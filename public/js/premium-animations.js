/**
 * ══════════════════════════════════════════════════════════════
 * TWINS — Premium Animation System
 * ══════════════════════════════════════════════════════════════
 * Stack  : GSAP 3.12 + ScrollTrigger + Lenis 1.0
 * Fitur  :
 *   A. Hero Beranda  — deck reveal, text slide, blur paragraph
 *   B. Lenis         — smooth scroll mewah
 *   C. Scroll Reveal — stagger kartu & elemen masuk layar
 *   D. Text Split    — heading pecah per-kata, fade & slide
 *   E. Magnetic Btn  — tombol mengikuti kursor
 *   F. Parallax      — gambar bergerak lebih lambat
 * ══════════════════════════════════════════════════════════════
 */

(function () {
    'use strict';

    /* ── Guard: GSAP wajib ada ──────────────────────────────── */
    if (typeof gsap === 'undefined') {
        console.warn('[TWINS] GSAP tidak ditemukan. Animasi dilewati.');
        return;
    }

    /* ── Accessibility: skip jika prefer-reduced-motion ─────── */
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const isMobile = () => window.innerWidth < 768;

    /* ══════════════════════════════════════════════════════════
       HELPER — parse scale & rotation dari string transform
    ══════════════════════════════════════════════════════════ */
    function parseTransform(str) {
        str = str || '';
        const sm = str.match(/scale\(([\d.]+)\)/);
        const rm = str.match(/rotate\(([-\d.]+)deg\)/);
        return {
            scale:    sm ? parseFloat(sm[1]) : 1,
            rotation: rm ? parseFloat(rm[1]) : 0,
        };
    }

    /* ══════════════════════════════════════════════════════════
       A. HERO BERANDA ANIMATION
         Badge jatuh dari atas → teks slide kiri/kanan
         → paragraf blur-masuk → kartu numpuk → fan-out arc
    ══════════════════════════════════════════════════════════ */
    function initHeroAnimation() {
        const badge     = document.getElementById('hero-badge');
        const wordLeft  = document.getElementById('hero-word-left');
        const wordRight = document.getElementById('hero-word-right');
        const paragraph = document.getElementById('hero-paragraph');
        const cards     = Array.from(document.querySelectorAll('#nftContainer .nft-card'));

        if (!cards.length) return;

        requestAnimationFrame(() => requestAnimationFrame(() => {

            /* Simpan posisi akhir dari updateLayout() */
            const finals = cards.map(card => {
                const t = parseTransform(card.style.transform);
                return {
                    left:      card.style.left      || '50%',
                    top:       card.style.top        || '50%',
                    transform: card.style.transform  || 'translate(-50%,-50%)',
                    scale:     t.scale,
                    rotation:  t.rotation,
                    opacity:   parseFloat(card.style.opacity) || 1,
                    zIndex:    card.style.zIndex     || '1',
                };
            });

            /* Stack semua kartu di tengah */
            cards.forEach(card => {
                card.style.transition = 'none';
                card.style.left = '50%'; card.style.top = '50%';
                card.style.zIndex = '5'; card.style.transform = '';
                gsap.set(card, { xPercent:-50, yPercent:-50, scale:0.68, rotation:0, opacity:0, willChange:'transform,opacity' });
            });

            if (badge)     gsap.set(badge,     { y:-40, opacity:0 });
            if (wordLeft)  gsap.set(wordLeft,  { x:-55, opacity:0 });
            if (wordRight) gsap.set(wordRight,  { x: 55, opacity:0 });
            if (paragraph) gsap.set(paragraph, { opacity:0, filter:'blur(12px)' });

            const tl = gsap.timeline({ defaults:{ ease:'expo.out' } });

            if (badge)     tl.to(badge,     { y:0, opacity:1, duration:0.9, ease:'back.out(1.4)' }, 0.2);
            if (wordLeft)  tl.to(wordLeft,  { x:0, opacity:1, duration:0.95 }, 0.45);
            if (wordRight) tl.to(wordRight,  { x:0, opacity:1, duration:0.95 }, 0.60);
            if (paragraph) tl.to(paragraph, { opacity:1, filter:'blur(0px)', duration:1.1, ease:'power2.out' }, 0.95);

            /* Phase 1 — dealing kartu */
            const dealStart = 1.0;
            [...cards].reverse().forEach((card, i) => {
                tl.to(card, {
                    opacity:1, scale:0.80, duration:0.25, ease:'power3.out',
                    onStart: () => { card.style.zIndex = String(5 + i); },
                }, dealStart + i * 0.11);
            });

            /* Phase 2 — fan-out ke arc */
            const fanStart = dealStart + cards.length * 0.11 - 0.05;
            cards.forEach((card, i) => {
                const f = finals[i];
                tl.to(card, {
                    left:f.left, top:f.top, scale:f.scale, rotation:f.rotation, opacity:f.opacity,
                    duration:1.4, ease:'expo.out',
                    onStart:    () => { card.style.zIndex = f.zIndex; },
                    onComplete: () => {
                        gsap.set(card, { clearProps:'transform,xPercent,yPercent,scale,rotation,willChange' });
                        card.style.left = f.left; card.style.top = f.top;
                        card.style.transform = f.transform;
                        card.style.opacity = String(f.opacity);
                        card.style.zIndex = f.zIndex;
                        card.style.transition = '';
                    },
                }, fanStart + i * 0.08);
            });
        }));
    }

    /* ══════════════════════════════════════════════════════════
       B. LENIS SMOOTH SCROLL
    ══════════════════════════════════════════════════════════ */
    function initLenis() {
        if (typeof Lenis === 'undefined') return;

        const lenis = new Lenis({
            duration: 1.35,
            easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            smoothWheel: true,
            wheelMultiplier: 0.9,
            touchMultiplier: 1.5,
        });

        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add(time => lenis.raf(time * 1000));
        gsap.ticker.lagSmoothing(0);
        window._lenis = lenis;
    }

    /* ══════════════════════════════════════════════════════════
       C. SCROLL REVEAL — Staggered
    ══════════════════════════════════════════════════════════ */
    function initScrollReveal() {

        /* ─ C1. Outlet cards stagger grid ─────────────────── */
        const grids = document.querySelectorAll('[data-stagger-grid]');
        grids.forEach(grid => {
            const items = grid.querySelectorAll('[data-stagger-item]');
            if (!items.length) return;
            gsap.set(items, { opacity:0, y:60, scale:0.94 });
            ScrollTrigger.create({
                trigger: grid, start:'top 82%',
                onEnter: () => gsap.to(items, {
                    opacity:1, y:0, scale:1,
                    duration:0.75, stagger:0.13, ease:'back.out(1.3)', clearProps:'all',
                }),
                once: true,
            });
        });

        /* ─ C2. Promo cards stagger ────────────────────────── */
        const promoCards = document.querySelectorAll('.promo-card');
        if (promoCards.length) {
            gsap.set(promoCards, { opacity:0, y:50 });
            ScrollTrigger.create({
                trigger:'.promo-slider-container', start:'top 85%',
                onEnter: () => gsap.to(promoCards, {
                    opacity:1, y:0, duration:0.7, stagger:0.1, ease:'power3.out', clearProps:'all',
                }),
                once: true,
            });
        }

        /* ─ C3. Tentang Toko: slide dari kiri & kanan ─────── */
        const boxLeft  = document.querySelector('[data-reveal-left]');
        const boxRight = document.querySelector('[data-reveal-right]');
        if (boxLeft && boxRight) {
            gsap.set(boxLeft,  { opacity:0, x:-60 });
            gsap.set(boxRight, { opacity:0, x: 60 });
            ScrollTrigger.create({
                trigger:'.highlight-container', start:'top 80%',
                onEnter: () => {
                    gsap.to(boxLeft,  { opacity:1, x:0, duration:1.1, ease:'expo.out', clearProps:'all' });
                    gsap.to(boxRight, { opacity:1, x:0, duration:1.1, delay:0.12, ease:'expo.out', clearProps:'all' });
                },
                once: true,
            });
        }

        /* ─ C4. Keunggulan: feature items kiri & kanan ─────── */
        const featLeft  = document.querySelectorAll('.feature-list.left-side .feature-item');
        const featRight = document.querySelectorAll('.feature-list.right-side .feature-item');
        const featImg   = document.querySelector('.product-image-container');

        if (featLeft.length) {
            gsap.set(featLeft, { opacity:0, x:-50 });
            ScrollTrigger.create({
                trigger:'.grid-container', start:'top 78%',
                onEnter: () => gsap.to(featLeft, {
                    opacity:1, x:0, duration:0.85, stagger:0.18, ease:'power3.out', clearProps:'all',
                }),
                once: true,
            });
        }
        if (featRight.length) {
            gsap.set(featRight, { opacity:0, x:50 });
            ScrollTrigger.create({
                trigger:'.grid-container', start:'top 78%',
                onEnter: () => gsap.to(featRight, {
                    opacity:1, x:0, duration:0.85, stagger:0.18, ease:'power3.out', clearProps:'all',
                }),
                once: true,
            });
        }
        if (featImg) {
            gsap.set(featImg, { opacity:0, scale:0.88 });
            ScrollTrigger.create({
                trigger:'.grid-container', start:'top 78%',
                onEnter: () => gsap.to(featImg, {
                    opacity:1, scale:1, duration:1.1, ease:'expo.out', clearProps:'all',
                }),
                once: true,
            });
        }

        /* ─ C5. Testimonial header ─────────────────────────── */
        const testiHeader = document.querySelectorAll('[data-reveal-up]');
        testiHeader.forEach(el => {
            gsap.set(el, { opacity:0, y:35 });
            ScrollTrigger.create({
                trigger:el, start:'top 88%',
                onEnter: () => gsap.to(el, {
                    opacity:1, y:0, duration:0.85, ease:'power3.out', clearProps:'all',
                }),
                once: true,
            });
        });
    }

    /* ══════════════════════════════════════════════════════════
       D. TEXT SPLIT ANIMATION
         Tiap kata dalam heading pecah → slide up dari clip
    ══════════════════════════════════════════════════════════ */
    function initTextSplit() {
        const targets = document.querySelectorAll('[data-split-text]');

        targets.forEach(el => {
            if (el.querySelector('.sw')) return; // sudah di-split

            /* Split hanya text node; preserve child elements (span gradient) */
            const nodes = Array.from(el.childNodes);
            el.innerHTML = '';

            nodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    node.textContent.trim().split(/\s+/).forEach((word, wi, arr) => {
                        const clip  = document.createElement('span');
                        const inner = document.createElement('span');
                        clip.className  = 'sw';   // split-word clip
                        inner.className = 'swi';  // split-word inner
                        inner.textContent = word;
                        clip.appendChild(inner);
                        el.appendChild(clip);
                        if (wi < arr.length - 1) el.appendChild(document.createTextNode(' '));
                    });
                } else {
                    /* Preserve gradient span, wrap its text too */
                    const spanEl = node.cloneNode ? node.cloneNode(true) : node;
                    el.appendChild(spanEl);
                }
            });

            const inners = el.querySelectorAll('.swi');
            gsap.set(inners, { y:'110%', opacity:0 });

            ScrollTrigger.create({
                trigger: el, start:'top 88%',
                onEnter: () => gsap.to(inners, {
                    y:'0%', opacity:1,
                    duration:0.8, stagger:0.07, ease:'power3.out', clearProps:'all',
                }),
                once: true,
            });
        });
    }

    /* ══════════════════════════════════════════════════════════
       E. MAGNETIC BUTTON
         Tombol mengikuti kursor saat didekati
    ══════════════════════════════════════════════════════════ */
    function initMagnetic() {
        if (isMobile()) return; // Tidak ada hover event di mobile

        const btns = document.querySelectorAll('.btn-fill, .btn-action, .btn-outline, .btn-highlights-sm, .btn-fill.main-cta');

        btns.forEach(btn => {
            const strength = parseFloat(btn.dataset.magnet) || 0.32;

            btn.addEventListener('mousemove', e => {
                const r = btn.getBoundingClientRect();
                const dx = e.clientX - (r.left + r.width  / 2);
                const dy = e.clientY - (r.top  + r.height / 2);
                gsap.to(btn, { x: dx * strength, y: dy * strength, duration:0.35, ease:'power2.out' });
            });

            btn.addEventListener('mouseleave', () => {
                gsap.to(btn, { x:0, y:0, duration:0.7, ease:'elastic.out(1, 0.45)' });
            });
        });
    }

    /* ══════════════════════════════════════════════════════════
       F. PARALLAX IMAGE
         Gambar bergerak lebih lambat saat scroll (depth effect)
    ══════════════════════════════════════════════════════════ */
    function initParallax() {
        if (isMobile()) return; // Hemat performa mobile

        const imgs = document.querySelectorAll('[data-parallax]');
        imgs.forEach(img => {
            const wrap  = img.closest('[data-parallax-wrap]');
            const speed = parseFloat(img.dataset.parallaxSpeed) || 0.22;

            if (wrap) wrap.style.overflow = 'hidden';
            gsap.set(img, { scale:1.15 }); // slightly zoomed in to avoid edge reveal

            gsap.to(img, {
                yPercent: -15 * speed * 5,
                ease:'none',
                scrollTrigger: {
                    trigger: wrap || img,
                    start:'top bottom',
                    end:'bottom top',
                    scrub: 1.2,
                },
            });
        });
    }

    /* ══════════════════════════════════════════════════════════
       INIT — DOMContentLoaded
    ══════════════════════════════════════════════════════════ */
    document.addEventListener('DOMContentLoaded', () => {

        /* Hero beranda (tidak butuh ScrollTrigger) */
        initHeroAnimation();

        /* Scroll-based animations — butuh ScrollTrigger */
        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);

            /* Inject CSS split-word styles */
            const style = document.createElement('style');
            style.textContent = `
                .sw  { display:inline-block; overflow:hidden; vertical-align:bottom; }
                .swi { display:inline-block; will-change:transform; }
                [data-parallax-wrap] { overflow:hidden; }
            `;
            document.head.appendChild(style);

            initLenis();
            initScrollReveal();
            initTextSplit();
            initMagnetic();
            initParallax();

            /* Refresh setelah semua load */
            window.addEventListener('load', () => ScrollTrigger.refresh());
        }
    });

})();
