<div class="modal-overlay" id="signModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Place Signature</h3>
            <button onclick="closeSignModal()" class="modal-close">×</button>
        </div>

        <div class="page-selector">
            <label for="sigPageInput">Page:</label>
            <input type="number" id="sigPageInput" value="1" min="1">
        </div>

        <div class="pdf-wrapper" id="pdfWrapper">
            <canvas id="pdfCanvas"></canvas>
            <div class="pdf-click-layer" id="pdfClickLayer"></div>
            <div class="sig-ghost" id="sigGhost">✍ {{ auth()->user()->name }}</div>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeSignModal()">Cancel</button>
            
            <button class="btn-confirm" id="btnConfirm" onclick="submitSignature()" disabled>
                ✔ Approve & Sign
            </button>
        </div>
        <span class="hint" id="hintText">Click anywhere on the document to place your signature</span>
    </div>
</div>

<div class="modal-overlay" id="signatureChoiceModal" style="display:none;">
    <div class="modal-box" style="max-width:420px;height:auto;">

        <div class="modal-header">
            <h3>Choose Signature Method</h3>

            <button type="button"
                    onclick="closeSignatureChoiceModal()"
                    class="modal-close">
                ×
            </button>
        </div>

        <div class="modal-footer signature-modal-footer">
            <button type="button"
                    class="btn-submit"
                    onclick="openSignatureUploadModal()">
                Upload Signature
            </button>

            <button type="button"
                    class="btn-submit"
                    onclick="openSignatureDrawModal()">
                Draw Signature
            </button>
        </div>

    </div>
</div>

<div class="modal-overlay"
     id="signatureUploadModal"
     style="display:none;">

    <div class="modal-box"
         style="max-width:500px;height:auto;">

        <div class="modal-header">
            <h3>Upload Signature</h3>

            <button type="button"
                    onclick="closeSignatureUploadModal()"
                    class="modal-close">
                ×
            </button>
        </div>

        <form method="POST"
              action="{{ route('signature.upload') }}"
              enctype="multipart/form-data"
              class="user-form"
              style="padding:24px;"
              onsubmit="return openPasswordModal(event, this)">

            @csrf

            <div class="form-group">
                <label>Signature Image</label>

                <input type="file"
                       name="signature"
                       accept="image/*"
                       required>
            </div>

            <input type="hidden"
                   name="password"
                   class="password-hidden-input">

            <button type="submit"
                    class="btn-submit">
                Upload Signature
            </button>

        </form>

    </div>
</div>

<div class="modal-overlay" id="signatureDrawModal" style="display:none;">
    <div class="modal-box" style="max-width:650px;height:auto;">

        <div class="modal-header">
            <h3>Draw Signature</h3>

            <button type="button"
                    onclick="closeSignatureDrawModal()"
                    class="modal-close">
                ×
            </button>
        </div>

        
        <form id="drawSignatureForm"
            method="POST"
            action="{{ route('signature.draw') }}"
            class="user-form"
            onsubmit="return openPasswordModal(event, this);">

            @csrf

            <canvas id="signatureCanvas"
                    width="500"
                    height="200"
                    class="signature-canvas"></canvas>

            <input type="hidden"
                name="signature_data"
                id="signatureData">
            
                <input type="hidden"
            name="password"
            class="password-hidden-input">

            <div class="signature-btn-row">

                <button type="button"
                        class="btn-clear-signature"
                        onclick="clearSignatureCanvas()">
                    Clear
                </button>

                <button type="submit"
                        class="btn-submit">
                    Save Drawn Signature
                </button>

            </div>
        </form>
    </div>
</div>

<form id="signatureForm" method="POST" action="">
    @csrf
    <input type="hidden" name="sig_x" id="formSigX">
    <input type="hidden" name="sig_y" id="formSigY">
    <input type="hidden" name="sig_w" id="formSigW">
    <input type="hidden" name="sig_h" id="formSigH">
    <input type="hidden" name="sig_page" id="formSigPage">
</form>