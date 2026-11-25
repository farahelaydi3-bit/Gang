(() => {
    const root = document.documentElement;
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
    const readPreference = () => document.cookie.match(/(?:^|;\s*)gang_theme=(light|dark)(?:;|$)/)?.[1];
    let preference = readPreference();

    const apply = () => {
        const theme = preference ?? (systemTheme.matches ? 'dark' : 'light');
        root.dataset.gangTheme = theme;
        document.querySelectorAll('[data-gang-theme-toggle]').forEach(button => {
            button.setAttribute('aria-pressed', String(theme === 'dark'));
        });
    };

    apply();
    document.addEventListener('DOMContentLoaded', apply, { once: true });
    systemTheme.addEventListener('change', apply);
    window.addEventListener('pageshow', () => {
        preference = readPreference() ?? preference;
        apply();
    });

    document.addEventListener('click', event => {
        if (!(event.target instanceof Element) || !event.target.closest('[data-gang-theme-toggle]')) return;

        preference = root.dataset.gangTheme === 'dark' ? 'light' : 'dark';
        const secure = location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `gang_theme=${preference}; Max-Age=31536000; Path=/; SameSite=Lax${secure}`;
        apply();
    });
})();
