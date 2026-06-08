{{-- PASSWORD CONFIRMATION MODAL --}}
<div id="passwordModal"
     class="password-modal">
    <div class="password-modal-content">
        
        {{-- Header --}}
        <div class="password-modal-header">
            <div class="password-modal-icon">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div class="password-modal-text">
                <h2 class="password-modal-title">Confirm Password</h2>
                <p class="password-modal-subtitle">Please verify your account password to continue.</p>
            </div>
        </div>

        {{-- Input Body --}}
        <div class="password-modal-body">
            <label class="password-label" for="modalPassword">Account Password</label>
            <div class="password-input-wrapper">
                <input type="password"
                       id="modalPassword"
                       class="password-input"
                       placeholder="Enter your password"
                       autocomplete="current-password"
                       onkeydown="if(event.key === 'Enter') submitProtectedForm()">
                
                <button type="button" class="password-toggle-btn" onclick="togglePassword('modalPassword', 'eyeIcon')">
                    <i id="eyeIcon" class="bi bi-eye-fill"></i>
                </button>
            </div>
            <div id="modalErrorMessage"
                class="password-error-message"
                style="display:none;">
            </div>
            </div>
        

        {{-- Action Buttons --}}
        <div class="password-modal-actions">
            <button type="button" class="password-btn-cancel" onclick="closePasswordModal()">
                Cancel
            </button>
            <button type="button" class="password-btn-confirm" onclick="submitProtectedForm()">
                Confirm Action
            </button>
        </div>
    </div>
    </div>
</div>