@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

{{-- Dashboard --}}
<div id="section-admin-dashboard" class="dashboard-section">

    {{-- KPI Cards --}}
    <div class="analytics-grid">

        <div class="analytics-card primary">
            <div class="analytics-value">{{ $totalDocuments }}</div>
            <div class="analytics-label">Total Documents</div>
        </div>

        <div class="analytics-card warning">
            <div class="analytics-value">{{ $pendingDocuments }}</div>
            <div class="analytics-label">Pending Approvals</div>
        </div>

        <div class="analytics-card success">
            <div class="analytics-value">{{ $approvedDocuments }}</div>
            <div class="analytics-label">Approved</div>
        </div>

        <div class="analytics-card danger">
            <div class="analytics-value">{{ $rejectedDocuments }}</div>
            <div class="analytics-label">Rejected</div>
        </div>

        <div class="analytics-card info">
            <div class="analytics-value">{{ $totalUsers }}</div>
            <div class="analytics-label">Users</div>
        </div>

        <div class="analytics-card dark">
            <div class="analytics-value">{{ $documentsThisMonth }}</div>
            <div class="analytics-label">Documents This Month</div>
        </div>

    </div>

    {{-- Workflow Status --}}
    <div class="dashboard-row">

        <div class="dashboard-panel">

            <h2>Workflow Status</h2>

            <div class="workflow-bars">

                <div class="workflow-item">
                    <span>Pending</span>
                    <div class="bar pending">
                        <div style="width: {{ $pendingDocuments }}%"></div>
                    </div>
                    <strong>{{ $pendingDocuments }}</strong>
                </div>

                <div class="workflow-item">
                    <span>Approved</span>
                    <div class="bar approved">
                        <div style="width: {{ $approvedDocuments }}%"></div>
                    </div>
                    <strong>{{ $approvedDocuments }}</strong>
                </div>

                <div class="workflow-item">
                    <span>Rejected</span>
                    <div class="bar rejected">
                        <div style="width: {{ $rejectedDocuments }}%"></div>
                    </div>
                    <strong>{{ $rejectedDocuments }}</strong>
                </div>

            </div>

        </div>

        <div class="dashboard-panel">

            <h2>System Summary</h2>

            <table class="summary-table">
                <tr>
                    <td>Documents Today</td>
                    <td>{{ $documentsToday }}</td>
                </tr>

                <tr>
                    <td>Documents This Month</td>
                    <td>{{ $documentsThisMonth }}</td>
                </tr>

                <tr>
                    <td>Total Users</td>
                    <td>{{ $totalUsers }}</td>
                </tr>

                <tr>
                    <td>Audit Logs</td>
                    <td>{{ $auditLogs->total() }}</td>
                </tr>
            </table>

        </div>

    </div>

    {{-- Recent Activities --}}
    <div class="dashboard-panel">

        <h2>Recent Activities</h2>

        <div class="activity-list">

            @forelse($recentActivities as $activity)

                <div class="activity-item">

                    <div class="activity-user">
                        {{ $activity->user?->name ?? 'System' }}
                    </div>

                    <div class="activity-desc">
                        {{ $activity->description }}
                    </div>

                    <div class="activity-time">
                        {{ $activity->created_at->diffForHumans() }}
                    </div>

                </div>

            @empty

                <div class="no-data">
                    No recent activity found.
                </div>

            @endforelse

        </div>

    </div>

</div>

{{-- Document Workflow Monitor --}}
<div id="section-workflow" class="dashboard-section">

    @if (session('document_success'))
        <div class="alert alert-success" role="alert">
            {{ session('document_success') }}
        </div>
    @endif

    @if (session('document_error'))
        <div class="alert alert-danger" role="alert">
            {{ session('document_error') }}
        </div>
    @endif

    <div
        id="purchaseOrderQueue"
        class="documents-card"
        data-index-url="{{ route('admin.purchase-orders.index') }}"
        data-forward-url-template="{{
            route(
                'admin.purchase-orders.forward',
                ['poNo' => '__PO_NUMBER__']
            )
        }}"
        data-csrf-token="{{ csrf_token() }}"
    >
        <div
            id="purchaseOrderQueueAlert"
            class="alert"
            role="alert"
            hidden
        ></div>

        <div class="card-header">
            <div class="user-filters-card">
                <div class="workflow-filters-card">
                    <div class="workflow-search-row">

                        <div class="workflow-search-box">
                            <i class="bi bi-search"></i>

                            <input
                                type="text"
                                id="purchaseOrderSearchInput"
                                placeholder="Search PO number or supplier..."
                                autocomplete="off"
                            >
                        </div>

                        <button
                            id="purchaseOrderRefreshButton"
                            class="workflow-clear-btn"
                            type="button"
                        >
                            Refresh
                        </button>
                    </div>

                    <div
                        class="results-info"
                        id="purchaseOrderResultsInfo"
                    >
                        Loading Purchase Orders...
                    </div>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-docs-table">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>PO Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Workflow</th>
                        <th>Progress</th>
                        <th>Current Signatory</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="purchaseOrderTableBody">
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            Loading Purchase Orders...
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                class="documents-footer"
                id="purchaseOrderPagination"
            ></div>
        </div>
    </div>
</div>

{{-- Users Section --}}
<div id="section-users" class="dashboard-section role-section">

    @if(session('role_success'))
        <div class="alert alert-success">
            {{ session('role_success') }}
        </div>
    @endif

    @if(session('role_error'))
        <div class="alert alert-error">
            {{ session('role_error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="role-layout">

        <div class="role-form-card">

            <div class="role-card-header">
                <h3>Add New User</h3>
                <p>
                    Fill out the user details and assign their workflow role.
                </p>
            </div>

            <form method="POST"
                action="{{ route('users.store') }}"
                class="user-form role-user-form">

                @csrf

                <div class="role-form-row">

                    <div class="form-group">
                        <label>First Name</label>

                        <input type="text"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>

                        <input type="text"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            required>
                    </div>

                </div>

                <div class="form-group">
                    <label>Middle Name</label>

                    <div class="middle-name-wrapper">
                        <input type="text"
                            id="middle_name"
                            name="middle_name"
                            value="{{ old('middle_name') }}">

                        <div class="checkbox-inline">
                            <input type="checkbox"
                                id="no_middle_name"
                                class="middle-name-checkbox">

                            <span>No Middle Name</span>
                        </div>
                    </div>
                </div>

                <div class="role-form-row">

                    <div class="form-group">
                        <label>Gender</label>

                        <select name="gender" required>
                            <option value="">
                                -- Select Gender --
                            </option>

                            <option value="male"
                                {{ old('gender') === 'male' ? 'selected' : '' }}>
                                Male
                            </option>

                            <option value="female"
                                {{ old('gender') === 'female' ? 'selected' : '' }}>
                                Female
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Assign Role</label>

                        <select name="role" required>
                            <option value="">
                                -- Select Role --
                            </option>

                            <option value="staff"
                                {{ old('role') === 'staff' ? 'selected' : '' }}>
                                Staff
                            </option>

                            <option value="supervisor"
                                {{ old('role') === 'supervisor' ? 'selected' : '' }}>
                                Supervisor
                            </option>

                            <option value="depthead"
                                {{ old('role') === 'depthead' ? 'selected' : '' }}>
                                Department Head
                            </option>

                            <option value="division"
                                {{ old('role') === 'division' ? 'selected' : '' }}>
                                Division Head
                            </option>

                            <option value="executive"
                                {{ old('role') === 'executive' ? 'selected' : '' }}>
                                Executive
                            </option>
                        </select>
                    </div>

                </div>

                <div class="form-group">
                    <label>Email</label>

                    <input type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required>
                </div>

                <button type="submit"
                        class="btn-submit role-create-btn">
                    Create User
                </button>

            </form>

        </div>

        <aside class="role-side-panel">

            <div class="role-guide-card">
                <h3>Role Guide</h3>

                <p>
                    Use these roles to control each user's access in the document workflow.
                </p>

                <div class="role-guide-list">

                    <div class="role-guide-item">
                        <span class="role-dot staff"></span>
                        <div>
                            <strong>Staff</strong>
                            <small>Uploads documents and tracks requests.</small>
                        </div>
                    </div>

                    <div class="role-guide-item">
                        <span class="role-dot supervisor"></span>
                        <div>
                            <strong>Supervisor</strong>
                            <small>Handles the first approval stage.</small>
                        </div>
                    </div>

                    <div class="role-guide-item">
                        <span class="role-dot depthead"></span>
                        <div>
                            <strong>Department Head</strong>
                            <small>Reviews department-level documents.</small>
                        </div>
                    </div>

                    <div class="role-guide-item">
                        <span class="role-dot division"></span>
                        <div>
                            <strong>Division Head</strong>
                            <small>Reviews before final approval.</small>
                        </div>
                    </div>

                    <div class="role-guide-item">
                        <span class="role-dot executive"></span>
                        <div>
                            <strong>Executive</strong>
                            <small>Handles final approval stage.</small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="role-guide-card security-card">
                <h3>Security Note</h3>

                <p>
                    Passwords are securely hashed. User creation and account updates are recorded in the audit trail.
                </p>
            </div>

        </aside>

    </div>

</div>
{{-- User Directory --}}
<div id="section-user-management" class="dashboard-section">
    @if(session('user_success'))
        <div class="alert alert-success">
            {{ session('user_success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div id="userTableContainer" class="documents-card" style="margin-top:24px;">
        <div class="card-header">
    <div class="user-filters-card">

        {{-- Search Row --}}
        <div class="search-row">

            <div class="search-box">

                <i class="bi bi-search" id="search-icon"></i>

                <input
                    type="text"
                    id="userSearch"
                    placeholder="Search by Name or Email">

            </div>
            
               <button
                    id="clearFilters"
                    class="clear-filters-btn"
                    type="button">
                    Clear All
                </button>
        </div>

        {{-- Filter Controls --}}
        <div class="filter-grid">

            <select id="roleFilter">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="supervisor">Supervisor</option>
                <option value="depthead">Department Head</option>
                <option value="division">Division Head</option>
                <option value="executive">Executive</option>
            </select>

            <select id="statusFilter">
                <option value="">All Status</option>
                <option value="verified">Verified</option>
                <option value="unverified">Unverified</option>
            </select>

            <input type="date" id="dateFilter">

        </div>

        <div
            class="results-info"
            id="userResultsInfo"
            data-total="{{ $users->count() }}"
        >
            Showing {{ $users->count() }} of {{ $users->count() }} users
        </div>

    </div>

        </div>

        <div class="table-wrapper">

            <table class="admin-user-table">

                <thead>

                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Email Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody id="userTable">

                    @forelse($users as $user)

                        <tr>

                            <td>
                                {{ $user->name }}
                            </td>

                            <td>
                                {{ $user->email }}
                            </td>

                            <td>
                                @if($user->roles->count())
                                    @foreach($user->roles as $role)
                                        <span class="role-badge">
                                            {{ ucfirst($role->name) }}
                                        </span>

                                    @endforeach

                                @elseif($user->role)

                                    <span class="role-badge">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                @else
                                    <span class="role-badge">
                                        -
                                    </span>
                                @endif

                            </td>

                            <td>

                                @if($user->email_verified_at)

                                    <span class="status-pill approved">
                                        Verified
                                    </span>

                                @else

                                    <span class="status-pill rejected">
                                        Unverified
                                    </span>

                                @endif

                            </td>

                            <td>

                                {{ $user->created_at->format('M d, Y') }}

                            </td>

                            <td>

                                <div class="more-menu">

                                    <button type="button"
                                            class="table-action-btn btn-clear"
                                            onclick="toggleMoreMenu(event, this)">
                                          <i class="bi bi-three-dots-vertical"></i>
                                    </button>

                                    <div class="more-dropdown">

                                        <button type="button"
                                                class="dropdown-item"
                                                onclick="editUser(
                                                        {{ $user->id }},
                                                        '{{ addslashes($user->name) }}',
                                                        '{{ $user->email }}',
                                                        '{{ $user->role }}'
                                                    )">
                                            Edit User
                                        </button>

                                        <form method="POST"
                                            action="{{ route('users.resetPassword', $user) }}">

                                            @csrf

                                            <button type="submit"
                                                    class="dropdown-item"
                                                    onclick="return confirm('Send password reset email?')">

                                            Reset Password

                                            </button>

                                        </form>
                                        
                                        @if($user->email_verified_at)
                                            <form method="POST"
                                                action="{{ route('users.unverify', $user) }}">

                                                @csrf
                                                @method('PATCH')

                                                

                                                    <button type="submit"
                                                            class="dropdown-item danger"
                                                            onclick="return confirm('Mark this user as deactivated?')">

                                                        Deactivate User

                                                    </button>
                                            </form>
                                            
                                        @elseif(!$user->email_verified_at)

                                            <form method="POST"
                                                action="{{ route('users.resetPassword', $user) }}">

                                                @csrf
                                                
                                                    <button type="submit"
                                                            class="dropdown-item danger"
                                                            onclick="return confirm('Send an activation and reset password email to this user?')">

                                                        Activate User

                                                    </button>
                                            </form>
                                        @endif

                                        </form>

                                    </div>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>

                            <td colspan="6">

                                No users found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>
</div>

<div
    id="section-document-templates"
    class="dashboard-section"
>
    <div class="container-fluid px-0">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Document Templates</h2>

                <p class="text-muted mb-0">
                    Upload, version, and activate document templates.
                </p>
            </div>
        </div>

        {{-- Default approvers by document type --}}
@if (session('workflow_success'))
    <div class="alert alert-success" role="alert">
        {{ session('workflow_success') }}
    </div>
@endif

@if (
    old('workflow_form') === '1'
    && $errors->any()
)
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-1">
            Default approvers could not be saved
        </div>

        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $workflowRoleLabels = [
        'staff' => 'Staff',
        'supervisor' => 'Supervisor',
        'depthead' => 'Department Head',
        'division' => 'Division Head',
        'executive' => 'Executive',
    ];
@endphp

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <strong>
            Default Approvers by Document Type
        </strong>
    </div>

    <div class="card-body">
        <p class="text-muted">
            Select the specific users who will approve documents
            imported automatically. Roles that are left empty will
            not be included in that document type's workflow.
        </p>

        <div class="accordion" id="approvalWorkflowAccordion">

            @foreach (
                $signatureTemplates
                as $templateKey => $definition
            )
                @php
                    $workflow =
                        $documentApprovalWorkflows->get(
                            $templateKey
                        );

                    $templateBlocks = collect(
                        $definition['blocks'] ?? []
                    )
                        ->filter(
                            fn (mixed $block): bool =>
                                is_array($block)
                                && is_string(
                                    $block['role'] ?? null
                                )
                                && trim(
                                    $block['role']
                                ) !== ''
                                && is_numeric(
                                    $block['sequence'] ?? null
                                )
                        )
                        ->sortBy(
                            fn (array $block): int =>
                                (int) $block['sequence']
                        )
                        ->unique(
                            fn (array $block): string =>
                                trim($block['role'])
                        )
                        ->values();

                    $useWorkflowOldInput =
                        old('workflow_form') === '1'
                        && old('template_key') ===
                            $templateKey;

                    $accordionId =
                        'workflow-' .
                        str_replace(
                            '_',
                            '-',
                            $templateKey
                        );
                @endphp

                <div class="accordion-item">

                    <h2
                        class="accordion-header"
                        id="{{ $accordionId }}-heading"
                    >
                        <button
                            type="button"
                            class="accordion-button
                                {{ $templateKey !== 'purchase_order'
                                    ? 'collapsed'
                                    : ''
                                }}"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $accordionId }}-body"
                            aria-expanded="{{ $templateKey === 'purchase_order'
                                ? 'true'
                                : 'false'
                            }}"
                            aria-controls="{{ $accordionId }}-body"
                        >
                            <span>
                                {{ $definition['name']
                                    ?? ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $templateKey
                                        )
                                    )
                                }}

                                @if ($workflow?->is_active)
                                    <span
                                        class="badge bg-success ms-2"
                                    >
                                        Active
                                    </span>
                                @elseif ($workflow)
                                    <span
                                        class="badge bg-secondary ms-2"
                                    >
                                        Inactive
                                    </span>
                                @else
                                    <span
                                        class="badge bg-warning text-dark ms-2"
                                    >
                                        Not Configured
                                    </span>
                                @endif
                            </span>
                        </button>
                    </h2>

                    <div
                        id="{{ $accordionId }}-body"
                        class="accordion-collapse collapse
                            {{ $templateKey === 'purchase_order'
                                ? 'show'
                                : ''
                            }}"
                        aria-labelledby="{{ $accordionId }}-heading"
                        data-bs-parent="#approvalWorkflowAccordion"
                    >
                        <div class="accordion-body">

                            <form
                                method="POST"
                                action="{{ route(
                                    'document-approval-workflows.store'
                                ) }}"
                            >
                                @csrf

                                <input
                                    type="hidden"
                                    name="workflow_form"
                                    value="1"
                                >

                                <input
                                    type="hidden"
                                    name="template_key"
                                    value="{{ $templateKey }}"
                                >

                                <div class="row g-3">

                                    @forelse (
                                        $templateBlocks
                                        as $block
                                    )
                                        @php
                                            $requiredRole = trim(
                                                $block['role']
                                            );

                                            $roleLabel =
                                                $block['label']
                                                ?? $workflowRoleLabels[
                                                    $requiredRole
                                                ]
                                                ?? ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $requiredRole
                                                    )
                                                );

                                            $savedStep =
                                                $workflow?->steps
                                                    ->firstWhere(
                                                        'required_role',
                                                        $requiredRole
                                                    );

                                            $selectedUserId =
                                                $useWorkflowOldInput
                                                    ? old(
                                                        "approvers.{$requiredRole}"
                                                    )
                                                    : $savedStep?->user_id;

                                            $eligibleRoleUsers =
                                                $workflowUsers->filter(
                                                    function (
                                                        $user
                                                    ) use (
                                                        $requiredRole
                                                    ): bool {
                                                        $userRole =
                                                            is_string(
                                                                $user->role
                                                            )
                                                            && trim(
                                                                $user->role
                                                            ) !== ''
                                                                ? trim(
                                                                    $user->role
                                                                )
                                                                : $user
                                                                    ->roles
                                                                    ->pluck(
                                                                        'name'
                                                                    )
                                                                    ->first();

                                                        return $userRole ===
                                                            $requiredRole;
                                                    }
                                                );
                                        @endphp

                                        <div class="col-md-6">

                                            <label
                                                for="{{ $accordionId }}-{{ $requiredRole }}"
                                                class="form-label"
                                            >
                                                Step
                                                {{ $block['sequence'] }}:
                                                {{ $roleLabel }}
                                            </label>

                                            <select
                                                id="{{ $accordionId }}-{{ $requiredRole }}"
                                                name="approvers[{{ $requiredRole }}]"
                                                class="form-select
                                                    @error(
                                                        "approvers.{$requiredRole}"
                                                    )
                                                        is-invalid
                                                    @enderror"
                                            >
                                                <option value="">
                                                    Not included
                                                </option>

                                                @foreach (
                                                    $eligibleRoleUsers
                                                    as $workflowUser
                                                )
                                                    <option
                                                        value="{{ $workflowUser->id }}"
                                                        @selected(
                                                            (string) $selectedUserId
                                                            ===
                                                            (string) $workflowUser->id
                                                        )
                                                    >
                                                        {{ $workflowUser->name }}
                                                        —
                                                        {{ $workflowUser->email }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @error(
                                                "approvers.{$requiredRole}"
                                            )
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror

                                            @if (
                                                $eligibleRoleUsers->isEmpty()
                                            )
                                                <small
                                                    class="text-danger"
                                                >
                                                    No verified
                                                    {{ strtolower(
                                                        $roleLabel
                                                    ) }}
                                                    user is available.
                                                </small>
                                            @endif

                                        </div>

                                    @empty
                                        <div class="col-12">
                                            <div
                                                class="alert alert-warning mb-0"
                                            >
                                                This document type has no
                                                configured signature blocks.
                                            </div>
                                        </div>
                                    @endforelse

                                    <div class="col-12">
                                        <div class="form-check">

                                            <input
                                                type="checkbox"
                                                id="{{ $accordionId }}-active"
                                                name="is_active"
                                                value="1"
                                                class="form-check-input"
                                                @checked(
                                                    $useWorkflowOldInput
                                                        ? old('is_active')
                                                        : ($workflow?->is_active
                                                            ?? true)
                                                )
                                            >

                                            <label
                                                for="{{ $accordionId }}-active"
                                                class="form-check-label"
                                            >
                                                Enable automatic approver
                                                assignment
                                            </label>

                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                            @disabled(
                                                $templateBlocks->isEmpty()
                                            )
                                        >
                                            <i
                                                class="bi bi-save me-1"
                                            ></i>

                                            Save Default Approvers
                                        </button>
                                    </div>

                                </div>
                            </form>

                            @if (
                                $workflow
                                && $workflow->steps->isNotEmpty()
                            )
                                <hr>

                                <div class="fw-semibold mb-2">
                                    Current approval sequence
                                </div>

                                <ol class="mb-0">
                                    @foreach (
                                        $workflow->steps
                                        as $workflowStep
                                    )
                                        <li>
                                            {{ $workflowRoleLabels[
                                                $workflowStep->required_role
                                            ]
                                                ?? ucfirst(
                                                    $workflowStep
                                                        ->required_role
                                                )
                                            }}

                                            —

                                            {{ $workflowStep->user?->name
                                                ?? 'Assigned user missing'
                                            }}
                                        </li>
                                    @endforeach
                                </ol>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>

        {{-- Upload form --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <strong>Upload New Template Version</strong>
            </div>

            <div class="card-body">

                @if (old('workflow_form') !== '1' && $errors->any())
                    <div class="alert alert-danger" role="alert">
                        <div class="fw-semibold mb-1">
                            Template upload failed
                        </div>

                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form
                    method="POST"
                    action="{{ route('document-templates.store') }}"
                    enctype="multipart/form-data"
                >
                    @csrf

                    <div class="row g-3">

                        {{-- Template type --}}
                        <div class="col-md-6">
                            <label
                                for="templateKey"
                                class="form-label"
                            >
                                Template Type
                            </label>

                            <select
                                id="templateKey"
                                name="template_key"
                                class="form-select
                                    @error('template_key') is-invalid @enderror"
                                required
                            >
                                <option value="" disabled
                                    @selected(!old('template_key'))
                                >
                                    Select template type
                                </option>

                                @foreach (
                                    $signatureTemplates
                                    as $key => $definition
                                )
                                    <option
                                        value="{{ $key }}"
                                        @selected(
                                            old('template_key') === $key
                                        )
                                    >
                                        {{ $definition['name']
                                            ?? ucwords(
                                                str_replace('_', ' ', $key)
                                            )
                                        }}
                                    </option>
                                @endforeach
                            </select>

                            @error('template_key')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <small class="text-muted">
                                Template types are configured in
                                <code>config/signature_templates.php</code>.
                            </small>
                        </div>

                        {{-- PDF file --}}
                        <div class="col-md-6">
                            <label
                                for="templateFile"
                                class="form-label"
                            >
                                PDF Template
                            </label>

                            <input
                                type="file"
                                id="templateFile"
                                name="template_file"
                                class="form-control
                                    @error('template_file') is-invalid @enderror"
                                accept="application/pdf,.pdf"
                                required
                            >

                            @error('template_file')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <small class="text-muted">
                                Maximum file size: 20 MB.
                            </small>
                        </div>

                        {{-- Activate immediately --}}
                        <div class="col-12">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    id="activateNow"
                                    name="activate_now"
                                    class="form-check-input"
                                    value="1"
                                    @checked(old('activate_now'))
                                >

                                <label
                                    for="activateNow"
                                    class="form-check-label"
                                >
                                    Activate this version immediately
                                </label>
                            </div>

                            <small class="text-muted">
                                The currently active version of the selected
                                template type will be deactivated.
                            </small>
                        </div>

                        <div class="col-12">
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-upload me-1"></i>
                                Upload Template
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- All template versions --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Template Versions</strong>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Template Type</th>
                                <th>Version</th>
                                <th>File Path</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Last Modified By</th>
                                <th>Updated</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($documentTemplates as $template)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $template->name }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $template->template_key }}
                                        </small>
                                    </td>

                                    <td>
                                        Version {{ $template->version }}
                                    </td>

                                    <td>
                                        <code>
                                            {{ $template->file_path }}
                                        </code>
                                    </td>

                                    <td>
                                        @if ($template->is_active)
                                            <span class="badge bg-success">
                                                Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $template->createdBy?->name
                                            ?? 'System'
                                        }}
                                    </td>

                                    <td>
                                        {{ $template->updatedBy?->name
                                            ?? 'System'
                                        }}
                                    </td>

                                    <td>
                                        {{ $template->updated_at?->format(
                                            'M d, Y h:i A'
                                        ) }}
                                    </td>

                                    <td class="text-end">
                                        @if (!$template->is_active)
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'document-templates.activate',
                                                    $template
                                                ) }}"
                                                class="d-inline"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="return confirm(
                                                        'Activate {{ addslashes($template->name) }} version {{ $template->version }}?'
                                                    )"
                                                >
                                                    Activate
                                                </button>
                                            </form>
                                        @else
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-success"
                                                disabled
                                            >
                                                Current
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="8"
                                        class="text-center text-muted py-4"
                                    >
                                        No document templates uploaded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
{{-- Audit Trail Section --}}
<div id="section-audit"
    class="dashboard-section">

    <div class="documents-card">

        @if($auditLogs->count())

            <div class="table-wrapper">

                <table class="audit-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Event</th>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>IP Address</th>
                            <th>User</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($auditLogs as $log)

                            <tr>

                                <td>{{ $log->id }}</td>

                                <td>
                                    {{ $log->description ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->event ?? '-' }}
                                </td>

                                <td>
                                    <span class="status-pill ongoing">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $log->table_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->table_id ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->ip_address ?? '-' }}
                                </td>

                                <td>
                                    {{ $log->user?->name ?? 'System' }}
                                </td>

                                <td>
                                    {{ $log->created_at?->format('M d, Y h:i:s A') ?? '-' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="documents-footer">

                {{ $auditLogs->appends(['section' => 'audit'])->links() }}

            </div>

        @else

            <div style="padding:24px;">

                <p class="no-data">
                    No audit logs available.
                </p>

            </div>

        @endif

    </div>

</div>

{{-- Signature Section --}}
<div id="section-signature"
    class="dashboard-section"
    >
    
    <div class="form-card">

        @if(auth()->user()->signature_path)

            <div style="margin-bottom:20px;">

                <h3>Current Signature</h3>

                <img src="{{ route(
                        'signatures.view',
                        encrypt(auth()->id())
                    ) }}"
                    alt="Signature"
                    class="signature-preview">

            </div>

        @else

            <p class="alert alert-error">
                No signature uploaded yet.
            </p>

        @endif

        <form method="POST"
            action="{{ route('signature.upload') }}"
            enctype="multipart/form-data"
            class="user-form">

            @csrf

            <div class="form-group">

                <label>Upload Signature</label>

                <input type="file"
                    name="signature"
                    accept="image/*"
                    required>

            </div>

            <button type="submit"
                    class="btn-submit">

                Upload Signature

            </button>

            
        </form>
    </div>
</div>

<!-- PDF Modal -->
<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">

        <span class="close-modal" onclick="closePdfModal()">
            &times;
        </span>

        <iframe id="pdfFrame"
            src=""
            width="100%"
            height="100%">
        </iframe>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal-overlay" style="display:none;">

    <div class="modal-card">

        <div class="modal-header">
            <h3>Edit User</h3>

            <button type="button"
                    onclick="closeEditUserModal()">
                ✕
            </button>
        </div>

        <form id="editUserForm"
              method="POST">

            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Name</label>

                <input type="text"
                       id="edit_name"
                       name="name"
                       required>
            </div>

            <div class="form-group">
                <label>Email</label>

                <input type="email"
                       id="edit_email"
                       name="email"
                       required>
            </div>

            <div class="form-group">
                <label>Role</label>

                <select id="edit_role"
                        name="role"
                        required>

                    <option value="staff">Staff</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="depthead">Department Head</option>
                    <option value="division">Division Head</option>
                    <option value="executive">Executive</option>
                    <option value="admin">Admin</option>

                </select>
            </div>

            <button type="submit"
                    class="btn-submit">
                Save Changes
            </button>

        </form>

    </div>

</div>
@include('partials.password-modal')
@endsection