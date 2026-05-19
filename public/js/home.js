/**
 * TWINS - Premium Bakery & Grocery Landing Page Script
 * Migrated from welcome.blade.php for maximum performance and caching.
 */

(function() {
    function initFinalReliability() {
        if (window._twinsStarted) return;
        window._twinsStarted = true;
        
        if (typeof gsap === 'undefined') {
            console.error("[TWINS] GSAP missing.");
            return;
        }

        console.log("[TWINS] Cinematic Engine Initialized");

        // --- PER-PAGE VISIBILITY (Prevent Flicker) ---
        gsap.set("header", { y: -100, opacity: 0 });
        gsap.set("section#beranda", { opacity: 0 });

        const parseTransform = (str) => {
            str = str || '';
            const sm = str.match(/scale\(([\d.]+)\)/);
            const rm = str.match(/rotate\(([-\d.]+)deg\)/);
            return { scale: sm ? parseFloat(sm[1]) : 1, rotation: rm ? parseFloat(rm[1]) : 0 };
        };

        const runHeroReveal = () => {
            try {
                console.log("[TWINS] Triggering High-Impact Reveal Sequence...");
                const badge = document.getElementById('hero-badge');
                const wordLeft = document.getElementById('hero-word-left');
                const wordRight = document.getElementById('hero-word-right');
                const paragraph = document.getElementById('hero-paragraph');
                const cards = Array.from(document.querySelectorAll('#nftContainer .nft-card'));
                const clips = document.querySelectorAll('.hero-text-clip');

                if (!cards.length) return;

                // I. Initial State (Clean & Fast)
                gsap.set(["#hero-badge", "#hero-word-left", "#hero-word-right", "#hero-paragraph"], { autoAlpha: 0 });
                gsap.set(clips, { overflow: 'visible' }); // No clipping during entry
                
                const finals = cards.map(card => {
                    const t = parseTransform(card.style.transform);
                    return {
                        left: card.style.left || '50%', top: card.style.top || '50%',
                        transform: card.style.transform || 'translate(-50%,-50%)',
                        scale: t.scale, rotation: t.rotation,
                        opacity: parseFloat(card.style.opacity) || 1,
                        zIndex: card.style.zIndex || '1'
                    };
                });

                // II. Prepare Elements
                cards.forEach(card => {
                    card.style.transition = 'none';
                    card.style.left = '50%'; card.style.top = '50%';
                    card.style.zIndex = '5'; card.style.transform = '';
                    gsap.set(card, { xPercent: -50, yPercent: -50, scale: 0.1, rotation: 0, autoAlpha: 0 });
                });

                // AMPLIFIED OFFSETS
                if (badge) gsap.set(badge, { y: -120 });
                if (wordLeft) gsap.set(wordLeft, { x: -150 });
                if (wordRight) gsap.set(wordRight, { x: 150 });
                if (paragraph) gsap.set(paragraph, { y: 40 });

                const htl = gsap.timeline({ defaults: { ease: 'power4.out' } });

                // III. THE SHOW (Parallel Action)
                // Start Text & Cards together for energy
                htl.to(cards, { scale: 0.75, autoAlpha: 1, duration: 0.8, stagger: 0.05, ease: "back.out(1.7)" }, 0);
                
                if (badge) htl.to(badge, { y: 0, autoAlpha: 1, duration: 1.2, ease: 'elastic.out(1, 0.6)' }, 0.1);
                if (wordLeft) htl.to(wordLeft, { x: 0, autoAlpha: 1, duration: 1.4 }, 0.2);
                if (wordRight) htl.to(wordRight, { x: 0, autoAlpha: 1, duration: 1.4 }, 0.3);
                if (paragraph) htl.to(paragraph, { autoAlpha: 1, y: 0, duration: 1.6 }, 0.5);

                // Opening Arc (Dramatic Sweep)
                cards.forEach((card, i) => {
                    const f = finals[i];
                    htl.to(card, {
                        left: f.left, top: f.top, scale: f.scale, rotation: f.rotation, autoAlpha: f.opacity,
                        duration: 1.8, ease: 'expo.out',
                        onStart: () => { card.style.zIndex = f.zIndex; },
                        onComplete: () => {
                            gsap.set(card, { clearProps: 'all' });
                            card.style.cssText = `left:${f.left}; top:${f.top}; transform:${f.transform}; opacity:${f.opacity}; z-index:${f.zIndex};`;
                        }
                    }, 0.8 + (i * 0.1));
                });

                // Cleanup states
                htl.set(clips, { overflow: 'hidden' }, "+=0.2");

            } catch (e) {
                console.error("[TWINS] Hero Animation Error:", e);
                gsap.set(["section#beranda", "#hero-badge", "h1", "p", ".nft-card"], { autoAlpha: 1 });
            }
        };

        window.twinsHeroManual = runHeroReveal;

        // Check if splash screen should be bypassed (e.g. returning from user.blade to outlet)
        const skipSplash = window.location.href.indexOf('skip_splash') !== -1 || window.location.hash === '#outlet';

        if (skipSplash) {
            // Instantly remove splash & show content
            const splashEl = document.getElementById('welcome-splash');
            if (splashEl) splashEl.style.display = 'none';
            document.body.classList.remove('hide-overflow');
            document.body.classList.add('show-content');
            
            // Instantly reveal header & beranda
            gsap.set("header", { y: 0, opacity: 1 });
            gsap.set("section#beranda", { opacity: 1 });
            
            // Instantly show hero section items
            runHeroReveal();
            
            // Update active states in navigation to Outlet
            const navLinks = document.querySelectorAll('.nav-link');
            const mobLinks = document.querySelectorAll('.mob-nav-item');
            navLinks.forEach(l => l.classList.toggle('active', l.id === 'nav-outlet'));
            mobLinks.forEach(l => l.classList.toggle('active', l.id === 'mob-outlet'));
            
            // Perform robust, multi-tick scrolling to target to counteract dynamic layout shift
            if (window.location.hash) {
                const scrollTarget = () => {
                    const target = document.querySelector(window.location.hash);
                    if (target) {
                        target.scrollIntoView({ behavior: 'auto' });
                    }
                };
                scrollTarget();
                setTimeout(scrollTarget, 30);
                setTimeout(scrollTarget, 100);
                setTimeout(scrollTarget, 300);
                setTimeout(scrollTarget, 600);
                setTimeout(scrollTarget, 1000);
            }
        } else {
            // SPLASH TIMELINE
            const stl = gsap.timeline({
                onComplete: () => {
                    document.getElementById('welcome-splash').style.display = 'none';
                    document.body.classList.remove('hide-overflow');
                    document.body.classList.add('show-content');
                }
            });

            stl.to("#splashLogo", { scale: 1, opacity: 1, duration: 0.6, ease: "expo.out", filter: "brightness(2) contrast(1.5)" })
               .to("#splashLogo", { filter: "brightness(1) contrast(1)", duration: 0.4 }, "-=0.2")
               .to(".splash-char", { opacity: 1, y: 0, rotateX: 0, duration: 0.8, stagger: 0.08, ease: "power4.out" }, "-=0.4")
               .set("#energyRing", { opacity: 1 })
               .to("#energyRing", { rotate: 270, scale: 1.3, opacity: 0.6, duration: 1.2, ease: "power2.out" }, "-=0.5")
               .to("#splashText", { opacity: 0, scale: 0.8, duration: 0.4, ease: "power2.in" }, "+=0.3")
               .to("#splashLogo", { scale: 0, opacity: 0, duration: 0.5, ease: "back.in(1.5)" }, "-=0.2")
               .to("#energyRing", { scale: 2.5, opacity: 0, duration: 0.6, ease: "expo.out" }, "<")
               .to(".splash-panel.top", { yPercent: -100, duration: 1.4, ease: "expo.inOut" }, "+=0.1")
               .to(".splash-panel.bottom", { yPercent: 100, duration: 1.4, ease: "expo.inOut" }, "<")
               .to("section#beranda", { opacity: 1, duration: 0.8, ease: "power2.out" }, "-=1.0")
               
               // PRECISE SYNC: Trigger hero reveal earlier (1.2s before end)
               .add(() => {
                   console.log("[TWINS] Double-Trigger: Cinematic Reveal Started");
                   runHeroReveal();
               }, "-=1.2")

               .to("header", { y: 0, opacity: 1, duration: 1.0, ease: "expo.out" }, "-=0.6")
               .set("body", { onStart: () => {
                   document.body.classList.add('show-content');
                   if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.refresh();
               }}, "-=0.2");
        }
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initFinalReliability();
    } else {
        document.addEventListener('DOMContentLoaded', initFinalReliability);
    }
})();

/* ==========================================================================
   Page Layout UI & Navigation Handlers
   ========================================================================== */

const themeBtn = document.getElementById('themeBtn');
const sunIcon = document.getElementById('sunIcon');
const moonIcon = document.getElementById('moonIcon');
const body = document.body;
const cards = Array.from(document.querySelectorAll('.nft-card'));
const menuToggle = document.getElementById('menuToggle');
const mainNav = document.getElementById('mainNav');

let activeIndex = Math.floor(cards.length / 2);

function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    if (menu) menu.classList.toggle('show');
}

// Make globally accessible for inline onclick events
window.toggleUserMenu = toggleUserMenu;

window.addEventListener('click', function(e) {
    const menu = document.getElementById('userMenu');
    const btn = document.querySelector('.user-icon-btn');
    if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
});

let startX = 0;
let isDragging = false;
let dragOffset = 0;

function updateLayout(duration = 0.8) {
    const isMobile = window.innerWidth <= 768;
    const horizontalGap = isMobile ? 85 : 110;
    const radiusY = isMobile ? 25 : 40;
    const rotationAngle = isMobile ? 12 : 15;

    cards.forEach((card, i) => {
        const diff = i - activeIndex;
        const absDiff = Math.abs(diff);

        card.classList.remove('active');

        if (diff === 0) {
            card.classList.add('active');
            gsap.to(card, {
                left: '50%',
                top: '50%',
                xPercent: -50,
                yPercent: -50,
                scale: 1.2,
                rotation: 0,
                zIndex: 500,
                opacity: 1,
                duration: duration,
                ease: "power3.out"
            });
        } else {
            const x = 50 + (diff * (horizontalGap / 10));
            const yOffset = absDiff * absDiff * (radiusY / 10);

            const scale = 1 - (absDiff * 0.1);
            const rotate = diff * rotationAngle;
            const opacity = Math.max(1 - (absDiff * 0.2), 0.4);

            gsap.to(card, {
                left: `${x}%`,
                top: `calc(50% + ${yOffset}px)`,
                xPercent: -50,
                yPercent: -50,
                scale: scale,
                rotation: rotate,
                zIndex: 100 - absDiff,
                opacity: opacity,
                duration: duration,
                ease: "power3.out"
            });
        }
    });
}

const nftContainer = document.getElementById('nftContainer');
if (nftContainer) {
    const handleDragStart = (e) => {
        isDragging = true;
        startX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        dragOffset = 0;
        nftContainer.style.cursor = 'grabbing';
    };

    const handleDragMove = (e) => {
        if (!isDragging) return;
        const currentX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        dragOffset = currentX - startX;
    };

    const handleDragEnd = () => {
        if (!isDragging) return;
        isDragging = false;
        nftContainer.style.cursor = 'grab';

        const threshold = 50;
        if (dragOffset > threshold && activeIndex > 0) {
            activeIndex--;
        } else if (dragOffset < -threshold && activeIndex < cards.length - 1) {
            activeIndex++;
        }
        updateLayout();
    };

    nftContainer.addEventListener('mousedown', handleDragStart);
    window.addEventListener('mousemove', handleDragMove);
    window.addEventListener('mouseup', handleDragEnd);

    nftContainer.addEventListener('touchstart', handleDragStart, { passive: true });
    window.addEventListener('touchmove', handleDragMove, { passive: true });
    window.addEventListener('touchend', handleDragEnd);
    
    nftContainer.style.cursor = 'grab';
}

cards.forEach((card, index) => {
    card.addEventListener('click', () => {
        if (Math.abs(dragOffset) < 10) {
            activeIndex = index;
            updateLayout();
        }
    });
});

function switchPage(pageId) {
    const element = document.getElementById(pageId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Manual active for mobile bottom nav
    const mobItems = document.querySelectorAll('.mob-nav-item');
    mobItems.forEach(item => item.classList.remove('active'));
    
    // Find which one was clicked based on pageId
    if(pageId === 'beranda') document.getElementById('mob-home')?.classList.add('active');
    else if(pageId === 'promo-outlet') document.getElementById('mob-promo')?.classList.add('active');
    else if(pageId === 'keunggulan') document.getElementById('mob-features')?.classList.add('active');
}

window.switchPage = switchPage;

function scrollToCategory(id) {
    const element = document.getElementById(id);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Manual active for Outlet in bottom nav
    if(id === 'outlet') {
        document.querySelectorAll('.mob-nav-item').forEach(item => item.classList.remove('active'));
        document.getElementById('mob-outlet')?.classList.add('active');
    }
}

window.scrollToCategory = scrollToCategory;

window.addEventListener('scroll', () => {
    const header = document.querySelector('header');
    if (header) {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
});

cards.forEach((card, index) => {
    card.addEventListener('click', () => {
        activeIndex = index;
        updateLayout();
    });
});

// Theme Menu Logic
function toggleThemeMenu() {
    const menu = document.getElementById('themeMenu');
    if (menu) menu.classList.toggle('show');
}

window.toggleThemeMenu = toggleThemeMenu;

function setTheme(themeName) {
    body.setAttribute('data-theme', themeName);
    localStorage.setItem('twins_theme', themeName);
    const menu = document.getElementById('themeMenu');
    if (menu) menu.classList.remove('show');
    updateActiveThemeBtn(themeName);
}

window.setTheme = setTheme;

function updateActiveThemeBtn(themeName) {
    document.querySelectorAll('#themeMenu button').forEach(btn => {
        btn.classList.remove('active');
        if(btn.getAttribute('data-theme-val') === themeName) {
            btn.classList.add('active');
        }
    });
}

// Close dropdown when clicking outside
window.addEventListener('click', function(e) {
    const menu = document.getElementById('themeMenu');
    const btn = document.querySelector('.theme-btn');
    const userMenu = document.getElementById('userMenu');
    const userBtn = document.querySelector('.user-icon-btn');
    if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
    if (userMenu && userBtn && !userBtn.contains(e.target) && !userMenu.contains(e.target)) {
        userMenu.classList.remove('show');
    }
}, true); // Use capture phase to ensure it runs properly

// Initialize Theme from Storage
const savedTheme = localStorage.getItem('twins_theme') || 'dark';
setTheme(savedTheme);

// Intersection Observer for Animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('in-view');
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.anim-fade-up, .anim-zoom-in').forEach(el => observer.observe(el));

document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        if (mainNav) mainNav.classList.remove('active');
    });
});

window.addEventListener('resize', updateLayout);
updateLayout();

document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);

    if (params.get('verified') === '1') {
        Swal.fire({
            title: 'Verifikasi Berhasil!',
            text: 'Selamat bergabung di TWINS! Akun Anda sudah aktif.',
            icon: 'success',
            confirmButtonColor: '#0477bf',
            showClass: {
                popup: 'animate__animated animate__zoomIn'
            }
        });

        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});

/* ==========================================================================
   3D Background Interactive Swaying Cakes Engine
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function() {
    const items = ['🧁', '🥐', '🍰', '🥨', '🎂', '🍪', '🥖', '🥞', '🍩'];
    const bgContainer = document.getElementById('bakery-bg');
    let parallaxLayers = [];

    if(bgContainer) {
        const isMobile = window.innerWidth <= 768;
        const layerCount = isMobile ? 10 : 20;
        
        // Initialize 3D Engine for Background
        bgContainer.style.perspective = '1200px';
        bgContainer.style.transformStyle = 'preserve-3d';

        for(let i = 0; i < layerCount; i++) {
            const el = document.createElement('div');
            el.className = 'walking-cake';
            el.innerText = items[Math.floor(Math.random() * items.length)];
            
            // Spread coordinates across the entire viewport background
            el.style.left = (Math.random() * 90 + 5) + '%';
            el.style.top = (Math.random() * 85 + 5) + 'vh';
            
            // Gentle randomized float speeds
            el.style.animationDuration = (Math.random() * 6 + 4) + 's';
            el.style.animationDelay = '-' + (Math.random() * 5) + 's';
            el.style.fontSize = (Math.random() * 2.5 + 1.5) + 'rem';
            
            const wrapper = document.createElement('div');
            wrapper.style.position = 'absolute';
            wrapper.style.width = '100%';
            wrapper.style.height = '100vh';
            wrapper.style.top = '0';
            wrapper.style.left = '0';
            wrapper.style.pointerEvents = 'none';
            wrapper.style.transformStyle = 'preserve-3d';
            wrapper.style.willChange = 'transform';
            
            const depth = Math.random() * 200 - 100;
            wrapper.dataset.depthZ = depth;
            
            wrapper.appendChild(el);
            bgContainer.appendChild(wrapper);
            parallaxLayers.push(wrapper);
        }

        let targetX = 0, targetY = 0;
        let currentX = 0, currentY = 0;
        let mouseX = 0, mouseY = 0;
        let rafId = null;

        document.addEventListener("mousemove", (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            targetX = (e.clientX - window.innerWidth / 2) * 0.08;
            targetY = (e.clientY - window.innerHeight / 2) * 0.08;
        });

        function animate3D() {
            const dx = targetX - currentX;
            const dy = targetY - currentY;
            
            currentX += dx * 0.05;
            currentY += dy * 0.05;

            bgContainer.style.transform = `scale(1.1) rotateX(${-currentY * 0.4}deg) rotateY(${currentX * 0.4}deg)`;

            parallaxLayers.forEach((layer) => {
                const z = parseFloat(layer.dataset.depthZ);
                const moveX = currentX * (z / 50); 
                const moveY = currentY * (z / 50);
                layer.style.transform = `translate3d(${moveX}px, ${moveY}px, ${z}px)`;
            });

            // Proximity tracking for all walking background cakes
            const cakes = document.querySelectorAll('.walking-cake');
            cakes.forEach((cake) => {
                const rect = cake.getBoundingClientRect();
                const cakeCenterX = rect.left + rect.width / 2;
                const cakeCenterY = rect.top + rect.height / 2;
                
                const distanceX = mouseX - cakeCenterX;
                const distanceY = mouseY - cakeCenterY;
                const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);
                
                // Influence radius of 200px
                if (distance < 200) {
                    const intensity = (200 - distance) / 200; // 0 to 1
                    
                    // Pull cakes towards the cursor center (attraction pull)
                    const pullX = distanceX * intensity * 0.25;
                    const pullY = distanceY * intensity * 0.25;
                    
                    cake.style.transform = `translate3d(${pullX}px, ${pullY}px, 0) scale(${1 + intensity * 0.6}) rotate(${intensity * 45}deg)`;
                    cake.style.opacity = 0.15 + intensity * 0.45;
                } else {
                    cake.style.transform = '';
                    cake.style.opacity = '';
                }
            });

            // Keep running continuously to track floating cakes even if the mouse is stationary
            rafId = requestAnimationFrame(animate3D);
        }
        
        // Start continuous rendering loop
        rafId = requestAnimationFrame(animate3D);
    }

    const savedTheme = localStorage.getItem('twins_theme') || 'dark';
    setTheme(savedTheme);
});

/* ==========================================================================
   Dual-Row Testimonial Slider / Marquee Class
   ========================================================================== */

class TestimonialMarquee {
    constructor(rowSelector, speed = 1) {
        this.row = document.querySelector(rowSelector);
        this.speed = speed;
        this.isPaused = false;
        this.isDragging = false;
        this.startX = 0;
        this.scrollLeft = 0;
        this.init();
    }

    init() {
        this.row.addEventListener('mousedown', (e) => this.startDragging(e));
        window.addEventListener('mouseup', () => this.stopDragging());
        window.addEventListener('mousemove', (e) => this.drag(e));

        this.row.addEventListener('touchstart', (e) => this.startDragging(e.touches[0]), { passive: true });
        window.addEventListener('touchend', () => this.stopDragging());
        window.addEventListener('touchmove', (e) => this.drag(e.touches[0]));

        this.animate();
    }

    startDragging(e) {
        this.isDragging = true;
        this.isPaused = true;
        this.startX = e.pageX - this.row.offsetLeft;
        this.scrollLeft = this.row.scrollLeft;
    }

    stopDragging() {
        this.isDragging = false;
        this.isPaused = false; // Lanjut jalan setelah dilepas
    }

    drag(e) {
        if (!this.isDragging) return;
        const x = e.pageX - this.row.offsetLeft;
        const walk = (x - this.startX) * 1.5;
        this.row.scrollLeft = this.scrollLeft - walk;
    }

    animate() {
        if (!this.isPaused && !this.isDragging && this.isVisible) {
            this.row.scrollLeft += this.speed;

            const loopPoint = this.row.scrollWidth / 3;
            
            if (this.speed > 0 && this.row.scrollLeft >= loopPoint) {
                this.row.scrollLeft = 0;
            } else if (this.speed < 0 && this.row.scrollLeft <= 0) {
                this.row.scrollLeft = loopPoint;
            }
        }
        requestAnimationFrame(() => this.animate());
    }

    initObserver() {
        this.isVisible = false;
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                this.isVisible = entry.isIntersecting;
            });
        }, { threshold: 0.01 });
        obs.observe(this.row);
    }
}

// Modified init to include observer
const originalInit = TestimonialMarquee.prototype.init;
TestimonialMarquee.prototype.init = function() {
    this.initObserver();
    originalInit.call(this);
};

// Initialize Rows: ATAS KE KANAN (speed negatif), BAWAH KE KIRI (speed positif)
window.onload = () => {
    const rowTop = new TestimonialMarquee('.marquee-row-right', -0.8); 
    const rowBottom = new TestimonialMarquee('.marquee-row-left', 0.8);
    
    // Set posisi awal random agar tidak terlihat terlalu sinkron di awal
    const topTrack = document.querySelector('.marquee-row-right');
    const bottomTrack = document.querySelector('.marquee-row-left');
    if(topTrack) topTrack.scrollLeft = topTrack.scrollWidth / 3;
    if(bottomTrack) bottomTrack.scrollLeft = (bottomTrack.scrollWidth / 3) * 0.5;
};

/* ==========================================================================
   Modals, Outlets, and Session Alerts
   ========================================================================== */

function openReviewModal() {
    const modal = document.getElementById('reviewModal');
    if(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scroll
    }
}

window.openReviewModal = openReviewModal;

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    if(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

window.closeReviewModal = closeReviewModal;

function selectOutlet(id, element) {
    // Remove active class from all options
    document.querySelectorAll('.outlet-option-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add to clicked one
    element.classList.add('selected');
    
    // Set hidden value
    const storeInput = document.getElementById('selectedStoreId');
    if (storeInput) storeInput.value = id;
}

window.selectOutlet = selectOutlet;

// Close on overlay click
window.onclick = function(event) {
    const modal = document.getElementById('reviewModal');
    if (event.target == modal) {
        closeReviewModal();
    }
}

function smoothScroll(target) {
    const element = document.querySelector(target);
    if(element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

window.smoothScroll = smoothScroll;

// SweetAlert2 Session Messages
const _sessionSuccess = document.querySelector('meta[name="session-success"]')?.content || null;
const _sessionError   = document.querySelector('meta[name="session-error"]')?.content || null;
const _sessionErrorRole = document.querySelector('meta[name="session-error-role"]')?.content || null;

document.addEventListener('DOMContentLoaded', () => {
    if (_sessionSuccess) {
        Swal.fire({
            title: 'Berhasil!',
            text: _sessionSuccess,
            icon: 'success',
            background: 'var(--bg-color)',
            color: 'var(--text-color)',
            confirmButtonColor: 'var(--accent-purple)',
            timer: 3000,
            showConfirmButton: false
        });
    }

    if (_sessionError) {
        Swal.fire({
            title: 'Oops!',
            text: _sessionError,
            icon: 'error',
            background: 'var(--bg-color)',
            color: 'var(--text-color)',
            confirmButtonColor: 'var(--accent-pink)',
        });
    }

    if (_sessionErrorRole) {
        Swal.fire({
            title: 'Akses Dibatasi',
            text: _sessionErrorRole,
            icon: 'warning',
            background: 'var(--bg-color)',
            color: 'var(--text-color)',
            confirmButtonColor: 'var(--accent-purple)',
        });
    }

    // --- Scroll Spy & Nav Active Logic ---
    const navLinks = document.querySelectorAll('.nav-link');
    const mobLinks = document.querySelectorAll('.mob-nav-item');
    const spyTargets = [
        { section: 'beranda', linkId: 'nav-home', mobId: 'mob-home' },
        { section: 'promo-outlet', linkId: 'nav-promo', mobId: 'mob-promo' },
        { section: 'outlet', linkId: 'nav-outlet', mobId: 'mob-outlet' },
        { section: 'keunggulan', linkId: 'nav-features', mobId: 'mob-features' }
    ];

    let isScrolling = false;

    // --- High Performance Scroll Spy ---
    const observerOptions = {
        root: null,
        rootMargin: '-20% 0px -70% 0px', // Sweet spot for detection
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        if (isScrolling) return;

        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const sectionId = entry.target.id;
                const target = spyTargets.find(t => t.section === sectionId);
                
                if (target) {
                    navLinks.forEach(link => {
                        link.classList.toggle('active', link.id === target.linkId);
                    });
                    mobLinks.forEach(link => {
                        link.classList.toggle('active', link.id === target.mobId);
                    });
                }
            }
        });
    }, observerOptions);

    spyTargets.forEach(target => {
        const section = document.getElementById(target.section);
        if (section) observer.observe(section);
    });

    // Special case for very top
    window.addEventListener('scroll', () => {
        if (window.scrollY < 100 && !isScrolling) {
            navLinks.forEach(l => l.classList.toggle('active', l.id === spyTargets[0].linkId));
            mobLinks.forEach(l => l.classList.toggle('active', l.id === spyTargets[0].mobId));
        }
    }, { passive: true });

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                isScrolling = true;
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                
                const offset = 100;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: "smooth"
                });
                
                setTimeout(() => {
                    isScrolling = false;
                }, 1000);
            }

            if (window.innerWidth <= 768) {
                const mainNav = document.getElementById('mainNav');
                const menuToggleBtn = document.querySelector('.menu-toggle');
                if (mainNav) mainNav.classList.remove('active');
                if (menuToggleBtn) menuToggleBtn.classList.remove('active');
            }
        });
    });

});

function showLoaderAndNavigate(url) {
    const loader = document.getElementById('dashboard-transition-loader');
    if (loader) {
        loader.style.opacity = '1';
        loader.style.pointerEvents = 'auto';
    }
    setTimeout(() => {
        window.location.href = url;
    }, 50);
}

window.showLoaderAndNavigate = showLoaderAndNavigate;
