// Начальная тема выставляется инлайн-скриптом в <head> (без FOUC).
// Здесь — только переключатель.
window.toggleTheme = () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    window.dispatchEvent(new Event('theme-changed'));
};

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
