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

let sigX = 0;
let sigY = 0;

const sigW = 200;
const sigH = 40;

let placed = false;

const ghost = document.getElementById('sigGhost');
const layer = document.getElementById('pdfClickLayer');
const wrapper = document.getElementById('pdfWrapper');
const btn = document.getElementById('btnConfirm');
const hint = document.getElementById('hintText');

function openSignModal(docId, pdfUrl, signUrl) {

    document.getElementById('pdfFrame').src = pdfUrl;

    document.getElementById('signatureForm').action = signUrl;

    document.getElementById('sigPageInput').value = 1;

    ghost.style.display = 'none';

    placed = false;

    btn.disabled = true;

    hint.textContent =
        'Click anywhere on the document to place your signature';

    document.getElementById('signModal')
        .classList.add('active');
}

function closeSignModal() {

    document.getElementById('signModal')
        .classList.remove('active');

    document.getElementById('pdfFrame').src = '';
}

if (layer) {

    layer.addEventListener('click', (e) => {

        const rect = wrapper.getBoundingClientRect();

        sigX = e.clientX - rect.left;
        sigY = e.clientY - rect.top;

        ghost.style.left = sigX + 'px';
        ghost.style.top = sigY + 'px';

        ghost.style.display = 'block';

        placed = true;

        btn.disabled = false;

        hint.textContent =
            'Signature placed! Click again to reposition, or click Approve & Sign.';
    });

}

function submitSignature() {

    if (!placed) return;

    const canvasW = wrapper.clientWidth;
    const canvasH = wrapper.clientHeight;

    document.getElementById('formSigX').value =
        (sigX / canvasW).toFixed(6);

    document.getElementById('formSigY').value =
        (sigY / canvasH).toFixed(6);

    document.getElementById('formSigW').value =
        (sigW / canvasW).toFixed(6);

    document.getElementById('formSigH').value =
        (sigH / canvasH).toFixed(6);

    document.getElementById('formSigPage').value =
        document.getElementById('sigPageInput').value;

    document.getElementById('signatureForm').submit();
}