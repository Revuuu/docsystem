<div class="staff-dashboard-wrapper">

    {{-- TOP HEADER --}}
    <div class="staff-dashboard-header">

        <div>

<h1>
    {{ $dashboardTitle }}
</h1>

<p>
    {{ $dashboardSubtitle }}
</p>


        </div>

        <div class="staff-dashboard-user">

            <div class="staff-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>

            <div>

                <strong>
                    {{ auth()->user()->name }}
                </strong>

                <small>
                {{ ucfirst($role) }}
                </small>

            </div>

        </div>

    </div>

    {{-- STATISTICS --}}
    <div class="staff-stats-grid">

        {{-- TOTAL --}}
        <div class="staff-stat-card green">

            <div>

                <h3>
                    Total Documents
                </h3>

                <strong>
                    {{ $documents->count() }}
                </strong>

            </div>

        </div>

        {{-- PENDING --}}
        <div class="staff-stat-card yellow">

            <div>

                <h3>
                    Pending Approvals
                </h3>

                <strong>
                    {{ $pendingDocuments->count() }}
                </strong>

            </div>

        </div>

        {{-- APPROVED --}}
        <div class="staff-stat-card darkgreen">

            <div>

                <h3>
                    Approved Documents
                </h3>

                <strong>
                    {{ $signedDocuments->count() }}
                </strong>

            </div>

        </div>

        {{-- DENIED --}}
        <div class="staff-stat-card red">

            <div>

                <h3>
                    Denied Documents
                </h3>

                <strong>
                    {{ $documents->where('status', 'rejected')->count() }}
                </strong>

            </div>

        </div>

    </div>

    {{-- RECENT DOCUMENTS --}}
    <div class="staff-documents-card">

        <div class="staff-card-header">

            <h2>
                Recent Documents
            </h2>

        </div>

        <div class="staff-table-wrapper">

            <table class="staff-dashboard-table">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Title</th>
                        <th>Current Step</th>
                        <th>Status</th>
                        <th>Progress</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($documents->take(8) as $doc)

                
                        @php

                            $totalApprovers =
                                $doc->approvals->count();

                            $signedApprovers =
                                $doc->approvals
                                    ->where('status', 'approved')
                                    ->count();

                            $progress =
                                $totalApprovers > 0
                                    ? round(($signedApprovers / $totalApprovers) * 100)
                                    : 0;

                        @endphp


                        <tr>

                            <td>
                                {{ $doc->id }}
                            </td>

                            <td>

                                <div class="doc-title">

                                    📄 {{ $doc->title }}

                                </div>

                            </td>

                            <td>
                                    @php

                                        $currentApproval =
                                            $doc->approvals
                                                ->where('status', 'pending')
                                                ->first();

                                    @endphp

                                    @if($doc->status === 'approved')

                                        <span class="step-complete">

                                            Complete

                                        </span>

                                    @elseif(
                                        $doc->status === 'pending'
                                        || $doc->status === 'in_progress'
                                    )

                                        @if($currentApproval)

                                            <span class="step-pending">

                                                Awaiting
                                                {{ $currentApproval->user->name }}

                                            </span>

                                        @else

                                            <span class="step-pending">

                                                In Progress

                                            </span>

                                        @endif

                                    @elseif($doc->status === 'rejected')

                                        <span class="step-rejected">

                                            Rejected Review

                                        </span>

                                    @else

                                        <span class="step-draft">

                                            Draft Stage

                                        </span>

                                    @endif

                            </td>


                            <td>

                                <span class="status-badge
                                    {{ $doc->status }}">

                                    {{ ucfirst($doc->status) }}

                                </span>

                            </td>

                            <td>

                                <div class="progress-container">

                                    <div class="progress-track">

                                        <div class="progress-fill"
                                             style="width:{{ $progress }}%">
                                        </div>

                                    </div>

                                    <small>

                                        {{ $progress }}%

                                    </small>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

<style>

.staff-dashboard-wrapper{
    padding:24px;
}

.staff-dashboard-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:28px;
}

.staff-dashboard-header h1{
    font-size:34px;
    font-weight:700;
    margin-bottom:6px;
}

.staff-dashboard-header p{
    color:#777;
}

.staff-dashboard-user{
    display:flex;
    align-items:center;
    gap:14px;
}

.staff-avatar{
    width:52px;
    height:52px;
    border-radius:50%;
    background:#0f6a36;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
}

.staff-stats-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    margin-bottom:30px;
}

.staff-stat-card{
    border-radius:18px;
    padding:24px;
    color:white;
    box-shadow:0 6px 16px rgba(0,0,0,0.08);
}

.staff-stat-card h3{
    font-size:16px;
    margin-bottom:12px;
    line-height:1.4;
}

.staff-stat-card strong{
    font-size:52px;
    font-weight:700;
}

.staff-stat-card.green{
    background:#138636;
}

.staff-stat-card.yellow{
    background:#e9ad21;
}

.staff-stat-card.darkgreen{
    background:#1b7f38;
}

.staff-stat-card.red{
    background:#c92d2d;
}

.staff-documents-card{
    background:white;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
}

.staff-card-header{
    padding:24px;
    border-bottom:1px solid #ececec;
}

.staff-card-header h2{
    font-size:28px;
    font-weight:700;
}

.staff-table-wrapper{
    overflow:auto;
}

.staff-dashboard-table{
    width:100%;
    border-collapse:collapse;
}

.staff-dashboard-table thead{
    background:#f5f1e7;
}

.staff-dashboard-table th{
    padding:18px;
    text-align:left;
    font-size:15px;
}

.staff-dashboard-table td{
    padding:18px;
    border-bottom:1px solid #f0f0f0;
    vertical-align:middle;
}

.doc-title{
    font-weight:600;
}

.status-badge{
    padding:8px 14px;
    border-radius:999px;
    font-size:13px;
    font-weight:600;
}

.status-badge.approved{
    background:#d8f0dd;
    color:#0f6a36;
}

.status-badge.pending{
    background:#f7e5b8;
    color:#946200;
}

.status-badge.rejected{
    background:#f3cccc;
    color:#9b1d1d;
}

.progress-container{
    display:flex;
    flex-direction:column;
    gap:6px;
}

.progress-track{
    width:180px;
    height:14px;
    background:#ececec;
    border-radius:999px;
    overflow:hidden;
}

.progress-fill{
    height:100%;
    background:#138636;
    border-radius:999px;
}

.status-badge.in_progress{
    background:#f7e5b8;
    color:#946200;
}

.step-complete{
    color:#138636;
    font-weight:600;
}

.step-pending{
    color:#946200;
    font-weight:600;
}

.step-rejected{
    color:#9b1d1d;
    font-weight:600;
}

.step-draft{
    color:#777;
    font-weight:600;
}


@media(max-width:1200px){

    .staff-stats-grid{
        grid-template-columns:repeat(2,1fr);
    }

}

@media(max-width:768px){

    .staff-dashboard-header{
        flex-direction:column;
        align-items:flex-start;
        gap:20px;
    }

    .staff-stats-grid{
        grid-template-columns:1fr;
    }

}

</style>
