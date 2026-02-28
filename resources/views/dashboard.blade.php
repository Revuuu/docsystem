
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if(auth()->user()->role === 'uploader')
                Uploader Dashboard
            @elseif(auth()->user()->role === 'approver')
                Pending Approvals
            @elseif(auth()->user()->role === 'admin')
                Admin Dashboard
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h1 class="text-xl mb-4">Hello, {{ auth()->user()->name }}</h1>

                {{-- Uploader --}}
                @if(auth()->user()->role === 'uploader')
                    @include('uploader.index')

                {{-- Approver --}}
                @elseif(auth()->user()->role === 'approver')
    
    @include('approvals.index', ['approvals' => $approvals])
                {{-- Admin --}}
                @elseif(auth()->user()->role === 'admin')
                    @include('admin.index', ['pendingDocuments' => $pendingDocuments])
                @endif

            </div>
        </div>
    </div>
</x-app-layout>