<div class="py-12 bg-paper">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Card container --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            

            {{-- Content / Form or Table --}}
            <div class="login-right p-6 bg-paper-warm rounded-lg shadow-md">
                @if(auth()->user()->role === 'uploader')
                    @include('uploader.index')

                @elseif(auth()->user()->role === 'approver')
                    @include('approvals.index', ['approvals' => $approvals])

                @elseif(auth()->user()->role === 'admin')
                    @include('admin.index', ['pendingDocuments' => $pendingDocuments])
                @endif
            </div>

        </div>
    </div>
</div>

<style>
    /* Consistent typography and colors */
    .hero-left { display: flex; flex-direction: column; justify-content: center; }
    .hero-eyebrow-line { background-color: #c9a84c; }
    .hero-eyebrow-text { color: #c9a84c; font-family: 'DM Mono', monospace; font-weight: 500; }
    .hero-title { font-family: 'Playfair Display', serif; color: #0d1117; }
    .hero-subtitle { font-family: 'Libre Baskerville', serif; color: #6b7890; }
    .login-right { display: flex; flex-direction: column; gap: 1.5rem; }

    /* Cards for table/table-like layouts */
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 0.75rem 1rem; border-bottom: 1px solid #d4cfc3; text-align: left; }
    th { background-color: #f5f0e8; font-weight: 700; }
    tr:hover { background-color: #ede8dc; }
</style>