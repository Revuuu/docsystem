<h2 class="text-lg font-semibold mb-2">Pending Documents for Admin</h2>
@if(session('error'))
    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
        {{ session('error') }}
    </div>
@endif
@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
        {{ session('success') }}
    </div>
@endif
@if(isset($pendingDocuments) && $pendingDocuments->count() > 0)
    <table class="w-full border border-gray-300 border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Title</th>
                <th class="border px-4 py-2">File</th>
                <th class="border px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingDocuments as $doc)
            <tr>
                <td class="border px-4 py-2">{{ $doc->title }}</td>
                <td class="border px-4 py-2">
                   <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" style="color: #2563eb; text-decoration: underline;">
    View Document
</a>
                </td>
                <td class="border px-4 py-2">
                    <form action="{{ route('documents.adminSign', $doc->id) }}" method="POST">
                        @csrf
                        <button type="submit" style="background-color: #2563eb; color: white; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; border: none; cursor: pointer;">
    Sign Document
</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No pending documents.</p>
@endif

{{-- Signed Documents --}}
@php
    $signedDocs = \App\Models\Document::whereNotNull('signed_file_path')->get();
@endphp

@if($signedDocs->count() > 0)
    <h2 class="text-lg font-semibold mt-6 mb-2">Signed Documents</h2>
    <table class="w-full border border-gray-300 border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2">Title</th>
                <th class="border px-4 py-2">Signed At</th>
                <th class="border px-4 py-2">Download</th>
            </tr>
        </thead>
        <tbody>
            @foreach($signedDocs as $doc)
            <tr>
                <td class="border px-4 py-2">{{ $doc->title }}</td>
                <td class="border px-4 py-2">{{ $doc->admin_signed_at }}</td>
                <td class="border px-4 py-2">
                    <a href="{{ asset('storage/' . $doc->signed_file_path) }}" target="_blank" style="color: #2563eb; text-decoration: underline;">
                        Download Signed PDF
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif