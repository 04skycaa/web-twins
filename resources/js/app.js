import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// ====== Web Twins Dashboard Script ======
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    function updateDateTime() {
        const now = new Date();
        const optionsDate = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const dateStr = now.toLocaleDateString('id-ID', optionsDate);
        const timeStr = now.toLocaleTimeString('id-ID', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        }).replace(/\./g, ':');

        const dateEl = document.getElementById('date-text');
        const timeEl = document.getElementById('time-text');
        const greetEl = document.getElementById('greeting-text');

        if (dateEl) dateEl.innerText = dateStr;
        if (timeEl) timeEl.innerText = timeStr;

        const hour = now.getHours();
        let greeting = "Selamat Malam";
        if (hour < 11) greeting = "Selamat Pagi";
        else if (hour < 15) greeting = "Selamat Siang";
        else if (hour < 19) greeting = "Selamat Sore";
        if (greetEl) greetEl.innerText = greeting;
    }

    // Only run interval if the elements exist
    if (document.getElementById('time-text')) {
        setInterval(updateDateTime, 1000);
        updateDateTime();
    }
});

// Make setActive available globally
window.setActive = function(element, title, iconName) {
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });
    element.classList.add('active');

    const titleEl = document.getElementById('topbar-title');
    const topIcon = document.getElementById('topbar-icon');

    if (titleEl) titleEl.innerText = title;
    if (topIcon) {
        topIcon.setAttribute('data-lucide', iconName);
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
};
