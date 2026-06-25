<div class="dashboard-wrapper">

    {{-- STATISTICS --}}
    <div class="stats-grid">

        {{-- TOTAL --}}
        <div class="stat-card green">

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
        <div class="stat-card yellow">

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
        <div class="stat-card darkgreen">

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
        <div class="stat-card red">

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
    <div class="dashboard-documents-card">

        <div class="dashboard-card-header">

            <h2>
                Recent Documents
            </h2>

        </div>

        <div class="table-wrapper">

            <table class="docs-table">

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

                                {{ $doc->title }}

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
                                @if($doc->status === 'approved')
                                    <span class="status-badge green">
                                        Approved
                                    </span>
                                @elseif($doc->status === 'pending')
                                    <span class="status-badge yellow">                                  
                                        Pending
                                    </span>
                                
                                @elseif($doc->status === 'in_progress')
                                    <span class="status-badge blue">                                  
                                        In Progress 
                                    </span>

                                @elseif($doc->status === 'rejected')
                                    <span class="status-badge red">
                                        Rejected
                                    </span>
                                @endif
                            </td>

                            <td>

                                <div class="progress-container">

                                    <div class="progress-track">

                                        @for($i = 1; $i < $totalApprovers; $i++)
                                            <span class="progress-marker"
                                                style="left: {{ ($i / $totalApprovers) * 100 }}%">
                                            </span>
                                        @endfor

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

