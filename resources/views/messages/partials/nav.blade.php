<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('sendportal.messages.index') ? 'active'  : '' }}"
           href="{{ route('messages.index') }}">{{ __('Sent') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('sendportal.messages.draft') ? 'active'  : '' }}"
           href="{{ route('messages.draft') }}">{{ __('Draft') }}</a>
    </li>
</ul>
