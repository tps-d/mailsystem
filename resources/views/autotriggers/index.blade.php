@extends('layouts.app')

@section('title', __('Autotrigger'))

@section('heading')
    {{ __('Autotrigger') }}
@endsection

@section('content')

    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ request()->is('autotrigger/email/index') ? 'active'  : '' }}"
               href="{{ route('autotrigger.index',['type'=>'email']) }}">邮件消息触发</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('autotrigger/social/index') ? 'active'  : '' }}"
               href="{{ route('autotrigger.index',['type'=>'social']) }}">Telegram消息触发</a>
        </li>
    </ul>

    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('autotrigger.create',['type'=>$type]) }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Autotrigger') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Condition') }}</th>
                    <th>{{ __('Reply Template') }}</th>
                    <th>{{ __('Sented') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($autotriggers as $autotrigger)
                    <tr>
                        <td>
                            <a href="{{ route('autotrigger.edit', $autotrigger->id) }}">{{ $autotrigger->name }}</a>
                        </td>
                        <td>{{ $autotrigger->condition_title }}</td>
                        <td>
                            <a href="{{ route('templates.edit', $autotrigger->template->id) }}">{{ $autotrigger->template->name }}</a>
                        </td>
                        <td>0</td>
                        

                        <td><span title="{{ $autotrigger->created_at }}">{{ $autotrigger->created_at->diffForHumans() }}</span></td>
                        <td>
                            @if($autotrigger->status_id == 1)
                                <span class="badge badge-success">{{ $autotrigger->status_title }}</span>
                            @elseif($autotrigger->status_id == 2)
                                <span class="badge badge-light">{{ $autotrigger->status_title }}</span>
                            @endif

                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                    <a href="{{ route('autotrigger.edit', $autotrigger->id) }}"
                                       class="dropdown-item">
                                        {{ __('Edit') }}
                                    </a>
                                    @if($autotrigger->canBeCancel())
                                    <form action="{{ route('autotrigger.cancel', $autotrigger->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            {{ __('Cancel') }}
                                        </button>
                                    </form>
                                    @endif

                                    @if($autotrigger->canBeActive())
                                    <form action="{{ route('autotrigger.active', $autotrigger->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            {{ __('Active') }}
                                        </button>
                                    </form>
                                    @endif

                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route('autotrigger.destroy.confirm', $autotrigger->id) }}" class="dropdown-item">
                                        {{ __('Delete') }}
                                    </a>

                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">
                                @if (request()->routeIs('autotriggers.index'))
                                    {{ __('You do not have any draft autotriggers.') }}
                                @else
                                    {{ __('You do not have any sent autotriggers.') }}
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('layouts.partials.pagination', ['records' => $autotriggers])

@endsection
