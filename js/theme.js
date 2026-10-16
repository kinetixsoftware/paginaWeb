(() => {
    const root = document.documentElement;
    const toggle = document.querySelector('.theme-toggle');
    const language = root.lang.toLowerCase();
    const isEnglish = language.startsWith('en');

    function applyTheme(theme) {
        const selectedTheme = theme === 'light' ? 'light' : 'dark';
        root.dataset.theme = selectedTheme;

        if (toggle) {
            const isDark = selectedTheme === 'dark';
            toggle.textContent = isDark ? '☀️' : '🌙';
            toggle.setAttribute(
                'aria-label',
                isEnglish
                    ? (isDark ? 'Switch to light mode' : 'Switch to dark mode')
                    : (isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro')
            );
        }
    }

    applyTheme(localStorage.getItem('theme') || 'dark');

    toggle?.addEventListener('click', () => {
        const nextTheme = root.dataset.theme === 'light' ? 'dark' : 'light';
        applyTheme(nextTheme);
        localStorage.setItem('theme', nextTheme);
    });
})();
