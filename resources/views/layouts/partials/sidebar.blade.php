<div class="sidebar-inner mx-3">

    <ul class="nav flex-column mt-4">
        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fa-fw fas fa-home mr-2"></i><span>{{ __('Dashboard') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="javascript:;" role="button"  >
                <i class="fa-fw fas fa-paper-plane mr-2"></i><span>发信管理</span>
            </a>
            <ul>
              <li class="nav-item {{ request()->is('*campaigns*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('campaigns.index') }}">{{ __('Campaigns') }}</a></li>
              <li class="nav-item {{ request()->is('*templates*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('templates.index') }}">
                    <span>{{ __('Templates') }}</span>
                </a>
              </li>
              <li class="nav-item {{ request()->is('*email-services*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('email_services.index') }}">
                    <span>{{ __('Email_Services') }}</span>
                </a>
              </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="javascript:;">
                <i class="fa-fw fas fa-envelope mr-2"></i><span>收信管理</span>
            </a>
            <ul>
                <li class="nav-item {{ request()->is('*messages*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('messages.index') }}">{{ __('Messages') }}</a></li>
                <li class="nav-item {{ request()->is('*subscribers*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('subscribers.index') }}">
                        <span>{{ __('Subscribers') }}</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="fa-fw fas fa-sync-alt mr-2"></i><span>{{ __('Automations') }}</span>
            </a>
            <ul>
              <li class="nav-item {{ request()->is('*queue*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('automations.queue.dispatch') }}">
                    <span>执行队列</span>
                </a>
              </li>
              <li class="nav-item"><a class="nav-link" href="javascript:;">自动发信规则</a></li>
              <li class="nav-item"><a class="nav-link" href="javascript:;">自动回信规则</a></li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="javascript:;">
                <i class="fa-fw fas fa-file-alt mr-2"></i></i><span>全局日志</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="javascript:;">
                <i class="fa-fw fas fa-cog mr-2"></i></i><span>系统管理</span>
            </a>
            <ul>
              @auth()
              <li class="nav-item {{ request()->is('users*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('users.index') }}">{{ __('Manage_Users') }}</a></li>
               @endauth
              <li class="nav-item"><a class="nav-link" href="javascript:;">系统设置</a></li>
            </ul>
        </li>
    </ul>
</div>
