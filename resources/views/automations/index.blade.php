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
                    <th>{{ __('Campaign') }}</th>
                    <th>{{ __('LAST RUN') }}</th>
                    <th>{{ __('NEXT RUN') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($automations as $automation)
                    <tr>
                        <td>
                            <a href="{{ route('campaigns.edit', $automation->campaign_id) }}">{{ $automation->campaign->name }}</a>
                        </td>
                        <td></td>
                        <td>
                            @if($automation->type_id == 2)
                                 {{ $automation->upcoming }}
                            @else
                                 {{ $automation->scheduled_at }}
                            @endif
                        </td>

                        <td><span title="{{ $automation->created_at }}">{{ $automation->created_at->diffForHumans() }}</span></td>
                        <td>
                            @if($automation->status_id == 2)
                                <span class="badge badge-light">{{ $automation->status_title }}</span>
                            @elseif($automation->status_id == 3)
                                <span class="badge badge-warning">{{ $automation->status_title }}</span>
                            @elseif($automation->status_id == 1)
                                <span class="badge badge-success">{{ $automation->status_title }}</span>
                            @elseif($automation->status_id == 4)
                                <span class="badge badge-danger">{{ $automation->status_title }}</span>
                            @endif

                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                    <a href="{{ route('automations.edit', $automation->id) }}"
                                       class="dropdown-item">
                                        {{ __('Edit') }}
                                    </a>

                                    @if($automation->canBeStop())
                                    <form action="{{ route('automations.stop', $automation->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            {{ __('Stop') }}
                                        </button>
                                    </form>
                                    @endif

                                    @if($automation->canBeStart())
                                    <form action="{{ route('automations.start', $automation->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            {{ __('Start') }}
                                        </button>
                                    </form>
                                    @endif

                                    <div class="dropdown-divider"></div>
                                    <a href="{{ route('automations.destroy.confirm', $automation->id) }}"
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
