@switch($icon)

    @case('check-circle')
        <i class="bi bi-check-circle"></i>
        @break

    @case('users')
        <i class="bi bi-people sidebar-icon"></i>
        @break

    @case('file-text')
        <i class="bi bi-file-earmark-text sidebar-icon"></i>
        @break

    @case('signature')
        <i class="bi bi-pen sidebar-icon"></i>
        @break

    @case('layout-dashboard')
        <i class="bi bi-grid sidebar-icon"></i>
        @break

    @case('folder')
        <i class="bi bi-folder2-open sidebar-icon"></i>
        @break

    @case('user-circle')
        <i class="bi bi-person-circle sidebar-icon"></i>
        @break

@endswitch