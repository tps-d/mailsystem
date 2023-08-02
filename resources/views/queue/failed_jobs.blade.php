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
            <a class="nav-link {{ request()->routeIs('webhook') ? 'active'  : '' }}"
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
                                    <form action="{{ route('queue.failed.retry', $job->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            {{ __('Retry') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('queue.failed.delete', $job->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
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
