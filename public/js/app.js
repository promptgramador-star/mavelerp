/**
 * ERP Propietario RD — Interacciones Globales
 */
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar Accordion Logic
    const groupHeaders = document.querySelectorAll('.nav-group-header');
    groupHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const group = header.parentElement;
            const isOpen = group.classList.contains('open');

            // Close all other groups (optional accordion behavior)
            document.querySelectorAll('.nav-group').forEach(otherGroup => {
                if (otherGroup !== group) {
                    otherGroup.classList.remove('open');
                }
            });

            // Toggle current group
            group.classList.toggle('open');
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
