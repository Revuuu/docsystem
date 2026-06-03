@switch($icon)

    @case('check-circle')
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M9 12.75L11.25 15L15.5 9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
        </svg>
        @break

    @case('users')
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M16 11C17.6569 11 19 9.65685 19 8C19 6.34315 17.6569 5 16 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11Z" stroke="currentColor" stroke-width="2"/>
            <path d="M2.5 19C3.25 16.5 5.25 15 8 15C10.75 15 12.75 16.5 13.5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M14.5 15.5C16.75 15.75 18.25 17 19 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        @break

    @case('file-text')
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7 3H14L19 8V21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M14 3V8H19" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M8 13H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <path d="M8 17H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        @break

    @case('signature')
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 17C5.5 13.5 7.5 13.5 9 17C10.5 20.5 13 19 15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14 14L19.5 8.5C20.3284 7.67157 20.3284 6.32843 19.5 5.5C18.6716 4.67157 17.3284 4.67157 16.5 5.5L11 11L10 15L14 14Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M3 21H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        @break

    @case('layout-dashboard')
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4 4H10V10H4V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M14 4H20V13H14V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M4 14H10V20H4V14Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M14 17H20V20H14V17Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>
        @break

    @case('folder')
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 7C3 5.89543 3.89543 5 5 5H9L11 7H19C20.1046 7 21 7.89543 21 9V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        </svg>
        @break

    @case('user-circle')
        <svg class="sidebar-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12Z" stroke="currentColor" stroke-width="2"/>
            <path d="M5 20C6.25 16.5 8.75 15 12 15C15.25 15 17.75 16.5 19 20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        @break

@endswitch