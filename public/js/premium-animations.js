(function () {
    'use strict';

    if (typeof gsap === 'undefined') {
        console.warn('[TWINS] GSAP tidak ditemukan. Animasi dilewati.');
        return;
    }

    const isMobile = () => window.innerWidth < 768;

    function parseTransform(str) {
        str = str || '';
        const sm = str.match(/scale\(([\d.]+)\)/);
        const rm = str.match(/rotate\(([-\d.]+)deg\)/);
        return {
            scale: sm ? parseFloat(sm[1]) : 1,
            rotation: rm ? parseFloat(rm[1]) : 0,
        };
    }

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

    function initScrollReveal() {

        const grids = document.querySelectorAll('[data-stagger-grid]');
        grids.forEach(grid => {
            const items = grid.querySelectorAll('[data-stagger-item]');
            if (!items.length) return;
            gsap.set(items, { opacity: 0, y: 60, scale: 0.94 });
            ScrollTrigger.create({
                trigger: grid, start: 'top 82%',
                onEnter: () => gsap.to(items, {
                    opacity: 1, y: 0, scale: 1,
                    duration: 0.75, stagger: 0.13, ease: 'back.out(1.3)', clearProps: 'all',
                }),
                once: true,
            });
        });

        const promoCards = document.querySelectorAll('.promo-card');
        if (promoCards.length) {
            gsap.set(promoCards, { opacity: 0, y: 50 });
            ScrollTrigger.create({
                trigger: '.promo-slider-container', start: 'top 85%',
                onEnter: () => gsap.to(promoCards, {
                    opacity: 1, y: 0, duration: 0.7, stagger: 0.1, ease: 'power3.out', clearProps: 'all',
                }),
                once: true,
            });
        }

        const boxLeft = document.querySelector('[data-reveal-left]');
        const boxRight = document.querySelector('[data-reveal-right]');
        if (boxLeft && boxRight) {
            gsap.set(boxLeft, { opacity: 0, x: -60 });
            gsap.set(boxRight, { opacity: 0, x: 60 });
            ScrollTrigger.create({
                trigger: '.highlight-container', start: 'top 80%',
                onEnter: () => {
                    gsap.to(boxLeft, { opacity: 1, x: 0, duration: 1.1, ease: 'expo.out', clearProps: 'all' });
                    gsap.to(boxRight, { opacity: 1, x: 0, duration: 1.1, delay: 0.12, ease: 'expo.out', clearProps: 'all' });
                },
                once: true,
            });
        }

        const featLeft = document.querySelectorAll('.feature-list.left-side .feature-item');
        const featRight = document.querySelectorAll('.feature-list.right-side .feature-item');
        const featImg = document.querySelector('.product-image-container');

        if (featLeft.length) {
            gsap.set(featLeft, { opacity: 0, x: -50 });
            ScrollTrigger.create({
                trigger: '.grid-container', start: 'top 78%',
                onEnter: () => gsap.to(featLeft, {
                    opacity: 1, x: 0, duration: 0.85, stagger: 0.18, ease: 'power3.out', clearProps: 'all',
                }),
                once: true,
            });
        }
        if (featRight.length) {
            gsap.set(featRight, { opacity: 0, x: 50 });
            ScrollTrigger.create({
                trigger: '.grid-container', start: 'top 78%',
                onEnter: () => gsap.to(featRight, {
                    opacity: 1, x: 0, duration: 0.85, stagger: 0.18, ease: 'power3.out', clearProps: 'all',
                }),
                once: true,
            });
        }
        if (featImg) {
            gsap.set(featImg, { opacity: 0, scale: 0.88 });
            ScrollTrigger.create({
                trigger: '.grid-container', start: 'top 78%',
                onEnter: () => gsap.to(featImg, {
                    opacity: 1, scale: 1, duration: 1.1, ease: 'expo.out', clearProps: 'all',
                }),
                once: true,
            });
        }

        const testiHeader = document.querySelectorAll('[data-reveal-up]');
        testiHeader.forEach(el => {
            gsap.set(el, { opacity: 0, y: 35 });
            ScrollTrigger.create({
                trigger: el, start: 'top 88%',
                onEnter: () => gsap.to(el, {
                    opacity: 1, y: 0, duration: 0.85, ease: 'power3.out', clearProps: 'all',
                }),
                once: true,
            });
        });
    }

    function initTextSplit() {
        const targets = document.querySelectorAll('[data-split-text]');

        targets.forEach(el => {
            if (el.querySelector('.sw')) return;

            const nodes = Array.from(el.childNodes);
            el.innerHTML = '';

            nodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    node.textContent.trim().split(/\s+/).forEach((word, wi, arr) => {
                        const clip = document.createElement('span');
                        const inner = document.createElement('span');
                        clip.className = 'sw';
                        inner.className = 'swi';
                        inner.textContent = word;
                        clip.appendChild(inner);
                        el.appendChild(clip);
                        if (wi < arr.length - 1) el.appendChild(document.createTextNode(' '));
                    });
                } else {
                    const spanEl = node.cloneNode ? node.cloneNode(true) : node;
                    el.appendChild(spanEl);
                }
            });

            const inners = el.querySelectorAll('.swi');
            gsap.set(inners, { y: '110%', opacity: 0 });

            ScrollTrigger.create({
                trigger: el, start: 'top 88%',
                onEnter: () => gsap.to(inners, {
                    y: '0%', opacity: 1,
                    duration: 0.8, stagger: 0.07, ease: 'power3.out', clearProps: 'all',
                }),
                once: true,
            });
        });
    }

    function initMagnetic() {
        if (isMobile()) return;

        const btns = document.querySelectorAll('.btn-fill, .btn-action, .btn-outline, .btn-highlights-sm, .btn-fill.main-cta');

        btns.forEach(btn => {
            const strength = parseFloat(btn.dataset.magnet) || 0.32;

            btn.addEventListener('mousemove', e => {
                const r = btn.getBoundingClientRect();
                const dx = e.clientX - (r.left + r.width / 2);
                const dy = e.clientY - (r.top + r.height / 2);
                gsap.to(btn, { x: dx * strength, y: dy * strength, duration: 0.35, ease: 'power2.out' });
            });

            btn.addEventListener('mouseleave', () => {
                gsap.to(btn, { x: 0, y: 0, duration: 0.7, ease: 'elastic.out(1, 0.45)' });
            });
        });
    }

    function initParallax() {
        if (isMobile()) return;

        const imgs = document.querySelectorAll('[data-parallax]');
        imgs.forEach(img => {
            const wrap = img.closest('[data-parallax-wrap]');
            const speed = parseFloat(img.dataset.parallaxSpeed) || 0.22;

            if (wrap) wrap.style.overflow = 'hidden';
            gsap.set(img, { scale: 1.15 });

            gsap.to(img, {
                yPercent: -15 * speed * 5,
                ease: 'none',
                scrollTrigger: {
                    trigger: wrap || img,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 1.2,
                },
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.startHeroAnimation = initHeroAnimation;

        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);

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

            window.addEventListener('load', () => ScrollTrigger.refresh());
        }
    });

})();