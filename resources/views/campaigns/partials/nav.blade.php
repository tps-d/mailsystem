<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('sendportal.campaigns.index') ? 'active'  : '' }}"
           href="{{ route('campaigns.index') }}">{{ __('Draft') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('sendportal.campaigns.sent') ? 'active'  : '' }}"
           href="{{ route('campaigns.sent') }}">{{ __('Sent') }}</a>
    </li>
</ul>
