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
        header.addEventListener('click', () => {
            const group = header.parentElement;

            // If it's already open, do nothing (stays open)
            if (group.classList.contains('open')) return;

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

    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => toggleSidebar(true));
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
