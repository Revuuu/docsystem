
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Card wrapper -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-center">Upload a Document</h4>
                </div>

                <div class="card-body">
                    <!-- Success message -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Validation errors -->
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Upload form -->
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Document Title</label>
                            <input type="text" name="title" class="form-control" id="title" placeholder="Enter document title" required>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Upload PDF</label>
                            <input type="file" name="file" class="form-control" id="file" accept="application/pdf" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Upload Document</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- End card -->
        </div>
    </div>
</div>