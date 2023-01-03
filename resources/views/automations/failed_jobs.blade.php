@extends('layouts.app')

@section('title', '执行队列')

@section('heading')
    执行队列
@endsection

@section('content')

    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('automations.queue.dispatch') ? 'active'  : '' }}"
               href="{{ route('automations.queue.dispatch') }}">发送队列</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('automations.webhook') ? 'active'  : '' }}"
               href="{{ route('automations.queue.webhook') }}">Webhook队列</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('automations.queue.failed') ? 'active'  : '' }}"
               href="{{ route('automations.queue.failed') }}">执行失败</a>
        </li>
    </ul>

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('id') }}</th>             
                    <th>{{ __('queue') }}</th>
                    <th>{{ __('payload') }}</th>
                    <th>{{ __('exception') }}</th>
                    <th>{{ __('failed_at') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($jobs as $job)
                    <tr>
                        <td><span >{{ $job->id }}</span></td>
                        <td>
                            <span >{{ $job->queue }}</span>
                        </td>
                        <td><span >{{ $job->attempts }}</span></td>
                        <td><p class="text-break">{{ $job->payload }}</p></td>
                        <td><span >{{ $job->exception }}</span></td>
                        <td><span >{{ $job->failed_at->diffForHumans() }}</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a href="javascript:;" class="dropdown-item">
                                            {{ __('Retry') }}
                                        </a>
                                        <a href="javascript:;" class="dropdown-item">
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
                                {{ __('You do not have any failed jobs.') }}
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
