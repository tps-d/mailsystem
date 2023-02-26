<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('campaigns.index') ? 'active'  : '' }}"
           href="{{ route('campaigns.index') }}">未执行</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('campaigns.sent') ? 'active'  : '' }}"
           href="{{ route('campaigns.sent') }}">已执行</a>
    </li>
    <!--
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('campaigns.delayed') ? 'active'  : '' }}"
           href="{{ route('campaigns.delayed') }}">定时执行</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('campaigns.listen') ? 'active'  : '' }}"
           href="{{ route('campaigns.listen') }}">重复执行</a>
    </li>
-->
</ul>
