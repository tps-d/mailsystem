<div class="sidebar-inner mx-3">
    <ul class="nav flex-column mt-4">
        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fa-fw fas fa-home mr-2"></i><span>{{ __('lang.Dashboard') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('*campaigns*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('campaigns.index') }}">
                <i class="fa-fw fas fa-envelope mr-2"></i><span>{{ __('lang.Campaigns') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('*messages*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('messages.index') }}">
                <i class="fa-fw fas fa-paper-plane mr-2"></i><span>{{ __('lang.Messages') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('*automations*') ? 'active' : '' }}">
            <a class="nav-link" href="#">
                <i class="fa-fw fas fa-sync-alt mr-2"></i><span>{{ __('lang.Automations') }}</span>
            </a>
        </li>
 
        <li class="nav-item {{ request()->is('*templates*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('templates.index') }}">
                <i class="fa-fw fas fa-file-alt mr-2"></i><span>{{ __('lang.Templates') }}</span>
            </a>
        </li>
        <li class="nav-item {{ request()->is('*subscribers*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('subscribers.index') }}">
                <i class="fa-fw fas fa-user mr-2"></i><span>{{ __('lang.Subscribers') }}</span>
            </a>
        </li>

        <li class="nav-item {{ request()->is('*email-services*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('email_services.index') }}">
                <i class="fa-fw fas fa-envelope mr-2"></i><span>{{ __('lang.Email_Services') }}</span>
            </a>
        </li>

        @auth()
            @if (auth()->user()->ownsCurrentWorkspace())
                <li class="nav-item {{ request()->is('users*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('users.index') }}">
                        <i class="fa-fw fas fa-users mr-2"></i><span>{{ __('lang.Manage_Users') }}</span>
                    </a>
                </li>
            @endif
        @endauth

    </ul>
</div>
