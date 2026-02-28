/**
 * ERP Propietario RD — Interacciones Globales
 */
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar Accordion Logic
    const navItems = document.querySelectorAll('.nav-item');
    const groupHeaders = document.querySelectorAll('.nav-group-header');

    const closeAllGroups = () => {
        document.querySelectorAll('.nav-group').forEach(group => {
            group.classList.remove('open');
        });
    };

    groupHeaders.forEach(header => {
        header.addEventListener('click', (e) => {
            e.preventDefault();
            const group = header.closest('.nav-group');

            // If it's already open, click it to close (toggle functionality is better than forcing open)
            if (group.classList.contains('open')) {
                group.classList.remove('open');
                return;
            }

            // Close others and open this one
            closeAllGroups();
            group.classList.add('open');
        });
    });

    // If clicking a simple nav-item (like Dashboard), close all groups
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            closeAllGroups();
        });
    });


    // Mobile Menu Logic
    const sidebar = document.getElementById('sidebar');
    const mobileToggle = document.getElementById('mobileToggle');
    const mobileClose = document.getElementById('mobileClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    const toggleSidebar = (show) => {
        if (show) {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        } else {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
            document.body.style.overflow = '';
        }
    };

    const toggleDesktopSidebar = () => {
        const appLayout = document.querySelector('.app-layout');
        if (appLayout) {
            appLayout.classList.toggle('sidebar-collapsed');
        }
    };

    // Solo para abrir completo en movil
    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            if (window.innerWidth > 992) {
                toggleDesktopSidebar();
            } else {
                toggleSidebar(true);
            }
        });
    }

    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth > 992) {
                toggleDesktopSidebar();
            } else {
                // On mobile, the hamburger was moved inside the sidebar header, 
                // but what about opening the sidebar if it's already closed?
                // Let's rely on standard logic: 
                toggleSidebar(true);
            }
        });
    }

    if (mobileClose) {
        mobileClose.addEventListener('click', () => toggleSidebar(false));
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => toggleSidebar(false));
    }

    // Auto-open active group
    const activeLink = document.querySelector('.nav-group-items a.active');
    if (activeLink) {
        activeLink.closest('.nav-group').classList.add('open');
    }

    console.log('ERP System Ready');
});
