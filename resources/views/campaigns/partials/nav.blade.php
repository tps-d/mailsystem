<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('campaigns.index') ? 'active'  : '' }}"
           href="{{ route('campaigns.index') }}">{{ __('Draft') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('campaigns.sent') ? 'active'  : '' }}"
           href="{{ route('campaigns.sent') }}">{{ __('Sent') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('campaigns.listen') ? 'active'  : '' }}"
           href="{{ route('campaigns.listen') }}">{{ __('Repeat') }}</a>
    </li>
</ul>
