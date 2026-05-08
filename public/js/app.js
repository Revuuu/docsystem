function showSection(section) {
    const signed = document.getElementById('section-signed');
    const users = document.getElementById('section-users');

    // Hide all sections
    if (signed) signed.style.display = 'none';
    if (users) users.style.display = 'none';

    // Show selected section
    if (section === 'signed' && signed) {
        signed.style.display = 'block';
    }

    if (section === 'users' && users) {
        users.style.display = 'block';
    }
}

// Mobile sidebar
document.addEventListener('DOMContentLoaded', () => {

    const hamburger = document.getElementById('hamburger');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('overlay');

    if (!hamburger || !sidebar || !overlay) return;

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        hamburger.classList.remove('open');
    }

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        hamburger.classList.add('open');
    }

    hamburger.addEventListener('click', () => {
        const isOpen = sidebar.classList.contains('open');

        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    // Auto close sidebar on mobile after nav click
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (window.innerWidth <= 860) {
                closeSidebar();
            }
        });
    });

});