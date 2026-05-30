// Начальная тема выставляется инлайн-скриптом в <head> (без FOUC).
// Здесь — только переключатель.
window.toggleTheme = () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    window.dispatchEvent(new Event('theme-changed'));
};

// Scroll-reveal: показываем .reveal-элементы при попадании в вьюпорт.
function initReveal() {
    const els = document.querySelectorAll('.reveal');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('in'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    els.forEach((el) => io.observe(el));
}
document.addEventListener('DOMContentLoaded', initReveal);

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
