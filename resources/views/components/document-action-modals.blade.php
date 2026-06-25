<div class="modal-overlay" id="rejectModal" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Reject Document</h3>
            <button onclick="closeRejectModal()" class="modal-close">×</button>
        </div>

        <form method="POST" id="rejectForm">
            @csrf

            <div class="form-group">
                <label>Reason for rejection</label>
                <textarea name="remarks" rows="5" required
                          style="width:100%; padding:12px; border:1px solid #ccc; border-radius:8px;"></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn-reject">Reject</button>
            </div>
        </form>
    </div>
</div>

<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
        <button type="button" class="pdf-close" onclick="closePdfModal()">×</button>
        <iframe id="pdfFrame" class="pdf-frame-viewer"></iframe>
    </div>
</div>