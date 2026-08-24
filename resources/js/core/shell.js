export const bootShell = () => {
    const sidebar = document.getElementById('sidebar');
    const shade = document.getElementById('sidebarShade');
    const menu = document.getElementById('mobileMenu');

    const closeSidebar = () => {
        sidebar?.classList.remove('open');
        shade?.classList.remove('open');
    };

    if (menu && !menu.dataset.flowtrackBound) {
        menu.dataset.flowtrackBound = '1';
        menu.addEventListener('click', () => {
            sidebar?.classList.add('open');
            shade?.classList.add('open');
        });
    }
    if (shade && !shade.dataset.flowtrackBound) {
        shade.dataset.flowtrackBound = '1';
        shade.addEventListener('click', closeSidebar);
    }

    // On phones/tablets, close the off-canvas navigation immediately after a
    // destination is chosen. This also works with Livewire wire:navigate.
    if (sidebar && !sidebar.dataset.flowtrackNavCloseBound) {
        sidebar.dataset.flowtrackNavCloseBound = '1';
        sidebar.addEventListener('click', (event) => {
            const link = event.target.closest('a.nav-btn, a.ft-system-brand');
            if (!link || window.matchMedia('(min-width: 901px)').matches) return;
            closeSidebar();
        });
    }
};
