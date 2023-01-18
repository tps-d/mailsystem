@extends('layouts.app')

@section('title', '执行队列')

@section('heading')
    执行队列
@endsection

@section('content')

    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('queue.dispatch') ? 'active'  : '' }}"
               href="{{ route('queue.dispatch') }}">发送队列</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('queue.webhook') ? 'active'  : '' }}"
               href="{{ route('queue.webhook') }}">Webhook队列</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('queue.failed') ? 'active'  : '' }}"
               href="{{ route('queue.failed') }}">执行失败</a>
        </li>
    </ul>

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('id') }}</th>
                    <th>{{ __('payload') }}</th>
                    <th>{{ __('attempts') }}</th>
                    <th>{{ __('reserved_at') }}</th>
                    <th>{{ __('available_at') }}</th>
                    <th>{{ __('created_at') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($jobs as $job)
                    <tr>
                        <td><span >{{ $job->id }}</span></td>
                        <td>
                            <p class="text-break">{{ $job->payload }}</p>
                        </td>
                        <td><span >{{ $job->attempts }}</span></td>
                        <td>
                            <span >
                            @if ( !empty($job->reserved_at) )
                                {{  $job->reserved_at->diffForHumans() }} 
                            @endif
                            </span>
                        </td>
                        <td><span >{{ $job->available_at->diffForHumans() }}</span></td>
                        <td><span >{{ $job->created_at->diffForHumans() }}</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a href="javascript:;"
                                           class="dropdown-item">
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
                                @if (request()->routeIs('automations.dispatch'))
                                    {{ __('You do not have any dispatch jobs.') }}
                                @else
                                    {{ __('You do not have any webhook jobs.') }}
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('layouts.partials.pagination', ['records' => $jobs])

@endsection
