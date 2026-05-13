function showSection(section) {
    const signed = document.getElementById('section-signed');
    const users = document.getElementById('section-users');

    if (signed) signed.style.display = 'none';
    if (users) users.style.display = 'none';

    if (section === 'signed' && signed) {
        signed.style.display = 'block';
    }

    if (section === 'users' && users) {
        users.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', () => {

    // =========================
    // SAFE ELEMENT REFERENCES
    // =========================
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');

    if (!hamburger || !sidebar || !overlay) {
        console.log("Sidebar elements not found on this page.");
        return;
    }

    // =========================
    // SIDEBAR FUNCTIONS
    // =========================
    function openMenu() {
        sidebar.classList.add('open');
        hamburger.classList.add('open');
        overlay.classList.add('show');
    }

    function closeMenu() {
        sidebar.classList.remove('open');
        hamburger.classList.remove('open');
        overlay.classList.remove('show');
    }

    function toggleMenu() {
        sidebar.classList.contains('open') ? closeMenu() : openMenu();
    }

    // =========================
    // EVENTS
    // =========================
    hamburger.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', closeMenu);

    document.querySelectorAll('.sidebar-nav a, .nav-btn').forEach(el => {
        el.addEventListener('click', () => {
            if (window.innerWidth <= 860) {
                closeMenu();
            }
        });
    });

});


// =========================
// SIGNATURE MODULE
// =========================
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

    if (ghost) ghost.style.display = 'none';

    placed = false;

    if (btn) btn.disabled = true;

    if (hint) {
        hint.textContent = 'Click anywhere on the document to place your signature';
    }

    document.getElementById('signModal').classList.add('active');
}

function closeSignModal() {
    document.getElementById('signModal').classList.remove('active');
    document.getElementById('pdfFrame').src = '';
}

if (layer && wrapper && ghost) {

    layer.addEventListener('click', (e) => {

        const rect = wrapper.getBoundingClientRect();

        sigX = e.clientX - rect.left;
        sigY = e.clientY - rect.top;

        ghost.style.left = sigX + 'px';
        ghost.style.top = sigY + 'px';
        ghost.style.display = 'block';

        placed = true;

        if (btn) btn.disabled = false;

        if (hint) {
            hint.textContent = 'Signature placed! Click again to reposition or confirm.';
        }
    });
}

function submitSignature() {

    if (!placed) return;

    const canvasW = wrapper.clientWidth;
    const canvasH = wrapper.clientHeight;

    document.getElementById('formSigX').value = (sigX / canvasW).toFixed(6);
    document.getElementById('formSigY').value = (sigY / canvasH).toFixed(6);
    document.getElementById('formSigW').value = (sigW / canvasW).toFixed(6);
    document.getElementById('formSigH').value = (sigH / canvasH).toFixed(6);

    document.getElementById('formSigPage').value =
        document.getElementById('sigPageInput').value;

    document.getElementById('signatureForm').submit();
}
let approverStep = 1;

function addApprover() {

    approverStep++;

    const container = document.getElementById('approver-container');

    const row = document.createElement('div');

    row.classList.add('approver-row');

    row.innerHTML = `
        <div class="approver-step">
            Step ${approverStep}
        </div>

        <select name="approvers[]" required>

            <option value="">
                Select Approver
            </option>

            ${approverOptions}

        </select>

        <button type="button"
                class="remove-approver"
                onclick="removeApprover(this)">

            ✕

        </button>
    `;

    container.appendChild(row);

    updateSteps();
}

function removeApprover(button) {

    const rows = document.querySelectorAll('.approver-row');

    if (rows.length <= 1) {
        return;
    }

    button.parentElement.remove();

    updateSteps();
}

function updateSteps() {

    const rows = document.querySelectorAll('.approver-row');

    approverStep = rows.length;

    rows.forEach((row, index) => {

        row.querySelector('.approver-step').innerText =
            `Step ${index + 1}`;

    });
}