@extends('layouts.app')

@section('title', __('Automations'))

@section('heading')
    {{ __('Automations') }}
@endsection

@section('content')


    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('automations.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Automations') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    @if (request()->routeIs('automations.sent'))
                        <th>{{ __('Sent') }}</th>
                        <th>{{ __('Opened') }}</th>
                        <th>{{ __('Clicked') }}</th>
                    @endif
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($automations as $automation)
                    <tr>
                        <td>
                            @if ($automation->draft)
                                <a href="{{ route('automations.edit', $automation->id) }}">{{ $automation->name }}</a>
                            @elseif($automation->sent)
                                <a href="{{ route('automations.reports.index', $automation->id) }}">{{ $automation->name }}</a>
                            @else
                                <a href="{{ route('automations.status', $automation->id) }}">{{ $automation->name }}</a>
                            @endif
                        </td>
                        @if (request()->routeIs('automations.sent'))
                            <td>{{ $automationstats[$automation->id]['counts']['sent'] }}</td>
                            <td>{{ number_format($automationstats[$automation->id]['ratios']['open'] * 100, 1) . '%' }}</td>
                            <td>
                                {{ number_format($automationstats[$automation->id]['ratios']['click'] * 100, 1) . '%' }}
                            </td>
                        @endif
                        <td><span title="{{ $automation->created_at }}">{{ $automation->created_at->diffForHumans() }}</span></td>
                        <td>
                            @if($automation->draft)
                                <span class="badge badge-light">{{ $automation->status->name }}</span>
                            @elseif($automation->queued)
                                <span class="badge badge-warning">{{ $automation->status->name }}</span>
                            @elseif($automation->sending)
                                <span class="badge badge-warning">{{ $automation->status->name }}</span>
                            @elseif($automation->sent)
                                <span class="badge badge-success">{{ $automation->status->name }}</span>
                            @elseif($automation->cancelled)
                                <span class="badge badge-danger">{{ $automation->status->name }}</span>
                            @endif

                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    @if ($automation->draft)
                                        <a href="{{ route('automations.edit', $automation->id) }}"
                                           class="dropdown-item">
                                            {{ __('Edit') }}
                                        </a>
                                    @else
                                        <a href="{{ route('automations.reports.index', $automation->id) }}"
                                           class="dropdown-item">
                                            {{ __('View Report') }}
                                        </a>
                                    @endif

                                    <a href="{{ route('automations.duplicate', $automation->id) }}"
                                       class="dropdown-item">
                                        {{ __('Duplicate') }}
                                    </a>

                                    @if($automation->canBeCancelled())
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('automations.confirm-cancel', $automation->id) }}"
                                           class="dropdown-item">
                                            {{ __('Cancel') }}
                                        </a>
                                    @endif

                                    @if ($automation->draft)
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('automations.destroy.confirm', $automation->id) }}"
                                           class="dropdown-item">
                                            {{ __('Delete') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">
                                @if (request()->routeIs('automations.index'))
                                    {{ __('You do not have any draft automations.') }}
                                @else
                                    {{ __('You do not have any sent automations.') }}
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('layouts.partials.pagination', ['records' => $automations])

@endsection
