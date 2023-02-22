<div class="sidebar-inner mx-3">

    <ul class="nav flex-column mt-4">
        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fa-fw fas fa-home mr-2"></i><span>{{ __('Dashboard') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="javascript:;" role="button"  >
                <i class="fa-fw fas fa-envelope mr-2"></i><span>收发管理</span>
            </a>
            <ul>
              <li class="nav-item {{ request()->is('*campaigns*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('campaigns.index') }}">{{ __('Campaigns') }}</a>
              </li>
                <li class="nav-item {{ request()->is('*messages*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('messages.index') }}">{{ __('Messages') }}</a>
                </li>             
              <li class="nav-item {{ request()->is('*email-services*') || request()->is('*social-services*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('email_services.index') }}">
                    <span>发件服务器</span>
                </a>
              </li>
              <li class="nav-item {{ request()->is('*templates*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('templates.index') }}">
                    <span>{{ __('Content Templates') }}</span>
                </a>
              </li>
              <li class="nav-item {{ request()->is('*variable*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('variable.index') }}">
                    <span>{{ __('Variable') }}</span>
                </a>
              </li>
                <li class="nav-item {{ request()->is('*subscribers*') || request()->is('*socialusers*')  ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('subscribers.index') }}">
                        <span>{{ __('Subscribers') }}</span>
                    </a>
                </li>
               <li class="nav-item {{ request()->is('*queue*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('queue.dispatch') }}">
                    <span>执行队列</span>
                </a>
              </li>
            </ul>
        </li>     
        <li class="nav-item">
            <a class="nav-link" href="javascript:;" role="button"  >
                <i class="fa-fw fas fa-clipboard-list mr-2"></i><span>机器人管理</span>
            </a>
            <ul>
              <li class="nav-item {{ request()->is('*automations*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('automations.index') }}">
                    <span>{{ __('Automations') }}</span>
                </a>
              </li>
              <li class="nav-item {{ request()->is('*autotrigger*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('autotrigger.index',['type'=>'email']) }}">
                    <span>{{ __('Autotrigger') }}</span>
                </a>
              </li>
            </ul>
        </li>
        <!--
        <li class="nav-item">
            <a class="nav-link" href="javascript:;">
                <i class="fa-fw fas fa-file-alt mr-2"></i></i><span>全局日志</span>
            </a>
        </li>
    -->
        <li class="nav-item">
            <a class="nav-link" href="javascript:;">
                <i class="fa-fw fas fa-cog mr-2"></i></i><span>系统管理</span>
            </a>
            <ul>
              @auth()
              <li class="nav-item {{ request()->is('users*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('users.index') }}">{{ __('Manage_Users') }}</a></li>
               @endauth

               <li class="nav-item {{ request()->is('workspaces*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('workspaces.index') }}">{{ __('Workspaces') }}</a>
               <li class="nav-item {{ request()->is('api-tokens*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('api-tokens.index') }}">{{ __('API Tokens') }}</a>

              <li class="nav-item"><a class="nav-link" href="javascript:;">系统设置</a></li>
            </ul>
        </li>
    </ul>
</div>
