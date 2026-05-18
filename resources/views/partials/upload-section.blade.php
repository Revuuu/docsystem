<div id="section-upload" class="dashboard-section">

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

                </div>

            </div>

            <button type="button"
                    class="btn-add-approver"
                    onclick="addApprover()">

                + Add Another Approver

            </button>

        </div>

        <button type="submit" class="btn-submit">
            Upload Document
        </button>

    </form>
</div>