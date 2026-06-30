<div id="uploadModal" class="upload-modal">

    <div class="upload-modal-content">

        <button class="upload-modal-close"
                onclick="closeUploadModal()">
            <i class="bi bi-x"></i>
        </button>

        <div id="section-upload" class="upload-modal-body">

            <h1 class="section-title">
                Upload Document
            </h1>

            <form method="POST"
                    action="{{ route('documents.store') }}"
                    enctype="multipart/form-data"
                    class="upload-form">

                @csrf

                <div class="form-group">

                    <label>
                        Document Title
                    </label>

                    <input type="text"
                            name="title"
                            required>

                </div>

                <div class="form-group">

                    <label>
                        PDF File
                    </label>
  
                    <input type="file"
                            name="file"
                            accept=".pdf"
                            required>
                    
                            <div id="fileError"
                                class="validation-error"
                                style="display:none;">
                            </div>
                            
                            @error('file')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror

                </div>

                <div class="form-group">

                    <label>Select Approvers</label>

                    <div id="approver-container">

                        <div class="approver-row">

                            <select name="approvers[]" required>

                                <option value="">
                                    -- Select Approver --
                                </option>

                                @foreach($approvers as $approver)

                                    <option value="{{ $approver->id }}">

                                        {{ $approver->name }}
                                        ({{ ucfirst($approver->role) }})

                                    </option>

                                @endforeach

                            </select>
                            
                            <button type="button"
                                    class="btn-remove-approver"
                                    onclick="removeApprover(this)"
                                    style="display:none;">

                                <i class="bi bi-x-lg"></i>

                            </button>
                        </div>

                    </div>

                    <button type="button"
                            class="btn-add-approver"
                            onclick="addApprover()">

                    <i class="bi bi-plus"></i> Add Another Approver

                    </button>

                </div>

                <button type="submit"
                        class="btn-submit"
                        id="uploadSubmitBtn">
                    <span class="btn-text">Upload Document</span>
                    <span class="btn-spinner" aria-hidden="true"></span>
                </button>

            </form>

            @if ($errors->has('file'))
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    openUploadModal();
                });
                </script>
            @endif
            
        </div>
    </div>
</div>