<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('messages.index') ? 'active'  : '' }}"
           href="{{ route('messages.index',request()->get('source_id') ? ['source_id'=>request()->get('source_id')] : []) }}">{{ __('Sent') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('messages.draft') ? 'active'  : '' }}"
           href="{{ route('messages.draft',request()->get('source_id') ? ['source_id'=>request()->get('source_id')] : []) }}">{{ __('Draft') }}</a>
    </li>
</ul>
