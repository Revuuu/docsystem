@switch($icon)

    @case('check-circle')
        <i class="bi bi-check-circle"></i>
        @break
    
    @case('house-door')
    <i class="bi bi-house-door"></i>
    @break
    
    @case('people-fill')
        <i class="bi bi-people-fill"></i>
        @break

    @case('users')
        <i class="bi bi-person-gear"></i>
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

    @case('document-templates')
        <i class="bi bi-file-earmark-binary"></i>
        @break

    @case('folder')
        <i class="bi bi-folder2-open sidebar-icon"></i>
        @break

    @case('user-circle')
        <i class="bi bi-person-circle sidebar-icon"></i>
        @break
    @case('file-earmark-check')
        <i class="bi bi-file-earmark-check sidebar-icon"></i>
        @break
    @case('chat-dots-fill')
        <i class="bi bi-chat-dots-fill sidebar-icon"></i>
        @break
@endswitch