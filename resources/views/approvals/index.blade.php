        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($approvals->count() > 0)
            <table class="w-full border border-gray-300 border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Title</th>
                        <th class="px-4 py-2 text-left">Uploaded By</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                   @foreach($approvals as $approval)
<tr class="border-t">
    <td class="px-4 py-2">{{ $approval->document->title ?? 'Document missing' }}</td>
    <td class="px-4 py-2">{{ $approval->document->uploader->name ?? 'Uploader missing' }}</td>
    <td class="px-4 py-2">{{ ucfirst($approval->status ?? 'N/A') }}</td>
    <td class="px-4 py-2 text-left border border-gray-300">
    @if($approval->status === 'pending')
        <form method="POST" action="{{ route('approvals.approve', $approval->id) }}">
            @csrf
            <button type="submit"
                style="background-color: #16a34a; color: white; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; border: none; cursor: pointer;">
                Approve
            </button>
        </form>
    @else
        <span class="text-gray-500">-</span>
    @endif
</td>
</tr>
@endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500">No pending approvals.</p>
        @endif
