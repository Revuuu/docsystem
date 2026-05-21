function showSection(section) {

    const signed = document.getElementById('section-signed');
    const users = document.getElementById('section-users');
    const audit = document.getElementById('section-audit');

    if (signed) signed.style.display = 'none';
    if (users) users.style.display = 'none';
    if (audit) audit.style.display = 'none';

    if (section === 'signed' && signed) {
        signed.style.display = 'block';
    }

    if (section === 'users' && users) {
        users.style.display = 'block';
    }

    if (section === 'audit' && audit) {
        audit.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', () => {

    // =========================
    // SAFE ELEMENT REFERENCES
    // =========================
    const hamburger = document.getElementById('hamburger');
    const sidebar   = document.getElementById('sidebar');
    const overlay   = document.getElementById('overlay');
    const params = new URLSearchParams(window.location.search);
    const section = params.get('section');

    if (!hamburger || !sidebar || !overlay) {
        console.log("Sidebar elements not found on this page.");
        return;
    }

    //Sections
    if (section) {
        showSection(section);
    } else {
        showSection('signed');
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
let placed = false;

let currentPdf = null;
let currentPdfUrl = null;
let currentPage = 1;

const ghost = document.getElementById('sigGhost');
const layer = document.getElementById('pdfClickLayer');
const wrapper = document.getElementById('pdfWrapper');
const canvas = document.getElementById('pdfCanvas');
const btn = document.getElementById('btnConfirm');
const hint = document.getElementById('hintText');
const pageInput = document.getElementById('sigPageInput');

function checkSignatureAndOpenModal(hasSignature, docId, pdfUrl, signUrl) {
    if (!hasSignature) {
        alert('You do not have a signature uploaded yet. Please upload or draw your signature first.');
        return;
    }

    openSignModal(docId, pdfUrl, signUrl);
}

async function openSignModal(docId, pdfUrl, signUrl) {
    currentPdfUrl = pdfUrl;
    currentPage = 1;

    document.getElementById('signatureForm').action = signUrl;
    pageInput.value = 1;

    if (ghost) ghost.style.display = 'none';
    placed = false;
    if (btn) btn.disabled = true;

    if (hint) {
        hint.textContent = 'Click anywhere on the document to place your signature';
    }

    document.getElementById('signModal').classList.add('active');

    currentPdf = await pdfjsLib.getDocument(pdfUrl).promise;
    await renderPdfPage(currentPage);
}

async function renderPdfPage(pageNumber) {
    const page = await currentPdf.getPage(pageNumber);

    const scale = 1.5;
    const viewport = page.getViewport({ scale });

    const context = canvas.getContext('2d');

    canvas.width = viewport.width;
    canvas.height = viewport.height;

    canvas.style.width = viewport.width + 'px';
    canvas.style.height = viewport.height + 'px';

    wrapper.style.width = viewport.width + 'px';
    wrapper.style.height = viewport.height + 'px';

    layer.style.width = viewport.width + 'px';
    layer.style.height = viewport.height + 'px';

    await page.render({
        canvasContext: context,
        viewport: viewport
    }).promise;
}

function closeSignModal() {
    document.getElementById('signModal').classList.remove('active');

    if (canvas) {
        const context = canvas.getContext('2d');
        context.clearRect(0, 0, canvas.width, canvas.height);
    }
}

if (pageInput) {
    pageInput.addEventListener('change', async () => {
        if (!currentPdf) return;

        let page = parseInt(pageInput.value);

        if (page < 1) page = 1;
        if (page > currentPdf.numPages) page = currentPdf.numPages;

        currentPage = page;
        pageInput.value = page;

        if (ghost) ghost.style.display = 'none';
        placed = false;
        if (btn) btn.disabled = true;

        await renderPdfPage(currentPage);
    });
}

if (layer && wrapper && ghost && canvas) {
    layer.addEventListener('click', (e) => {
        const rect = canvas.getBoundingClientRect();

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

    const canvasW = canvas.clientWidth;
    const canvasH = canvas.clientHeight;

    const actualSigW = 300;
    const actualSigH = 180;

    document.getElementById('formSigX').value = (sigX / canvasW).toFixed(6);
    document.getElementById('formSigY').value = (sigY / canvasH).toFixed(6);
    document.getElementById('formSigW').value = (actualSigW / canvasW).toFixed(6);
    document.getElementById('formSigH').value = (actualSigH / canvasH).toFixed(6);
    document.getElementById('formSigPage').value = currentPage;

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

function showSection(section)
{
    document.querySelectorAll('.dashboard-section')
        .forEach((el) => {
            el.style.display = 'none';
        });

    const target = document.getElementById('section-' + section);

    if (target) {
        target.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', () => {

    if (document.getElementById('section-upload')) {
        showSection('upload');
    }

     if (document.getElementById('section-approvals')) {
        showSection('approvals');
    }

});

function addApprover()
{
    const container = document.getElementById('approver-container');

    if (!container) return;

    const firstRow = container.querySelector('.approver-row');

    if (!firstRow) return;

    const clone = firstRow.cloneNode(true);

    clone.querySelector('select').value = '';

    container.appendChild(clone);
}

document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('signatureCanvas');

    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let drawing = false;

    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';

    function getPosition(e) {
        const rect = canvas.getBoundingClientRect();

        if (e.touches && e.touches.length > 0) {
            return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top,
            };
        }

        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top,
        };
    }

    function startDrawing(e) {
        drawing = true;
        const pos = getPosition(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!drawing) return;

        e.preventDefault();

        const pos = getPosition(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function stopDrawing() {
        drawing = false;
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseleave', stopDrawing);

    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);
});

function clearSignatureCanvas() {
    const canvas = document.getElementById('signatureCanvas');

    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function saveDrawnSignature() {
    const canvas = document.getElementById('signatureCanvas');
    const input = document.getElementById('signatureData');

    if (!canvas || !input) return;

    input.value = canvas.toDataURL('image/png');
}

function openRejectModal(approvalId)
{
    const modal = document.getElementById('rejectModal');

    const form = document.getElementById('rejectForm');

    form.action = '/approvals/' + approvalId + '/reject';

    modal.style.display = 'flex';
}

function closeRejectModal()
{
    document.getElementById('rejectModal')
        .style.display = 'none';
}


function openPdfModal(pdfUrl) {
    document.getElementById('pdfFrame').src = pdfUrl;
    document.getElementById('pdfModal').style.display = 'block';
}

function closePdfModal() {
    document.getElementById('pdfFrame').src = '';
    document.getElementById('pdfModal').style.display = 'none';
}

// Optional: close when clicking outside modal
window.onclick = function(event) {
    const modal = document.getElementById('pdfModal');

    if (event.target === modal) {
        closePdfModal();
    }
}


