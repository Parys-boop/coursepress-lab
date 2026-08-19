(() => {
    const header = document.querySelector('[data-coursepress-header]');

    if (!header) {
        return;
    }

    const toggle = header.querySelector('.coursepress-menu-toggle');
    const navigation = header.querySelector('.coursepress-primary-navigation');
    const mobileQuery = window.matchMedia('(max-width: 47.9375rem)');

    if (!toggle || !navigation) {
        return;
    }

    document.documentElement.classList.add('coursepress-navigation-ready');

    const setOpen = (isOpen) => {
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute('aria-label', isOpen ? 'Fechar menu' : 'Abrir menu');
        navigation.hidden = !isOpen;
    };

    const syncViewport = () => {
        if (mobileQuery.matches) {
            setOpen(false);
            return;
        }

        navigation.hidden = false;
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir menu');
    };

    toggle.addEventListener('click', () => {
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });

    header.addEventListener('keydown', (event) => {
        if ('Escape' === event.key && mobileQuery.matches && 'true' === toggle.getAttribute('aria-expanded')) {
            setOpen(false);
            toggle.focus();
        }
    });

    if ('function' === typeof mobileQuery.addEventListener) {
        mobileQuery.addEventListener('change', syncViewport);
    } else {
        mobileQuery.addListener(syncViewport);
    }

    syncViewport();
})();
