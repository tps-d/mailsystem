@extends('layouts.app')

@section('title', __('Campaigns'))

@section('heading')
    {{ __('Campaigns') }}
@endsection

@section('content')

    @include('campaigns.partials.nav')

    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('campaigns.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Campaign') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    @if (request()->routeIs('campaigns.sent'))
                        <th>{{ __('Sent') }}</th>
                    @endif
                    <th>{{ __('Created') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($campaigns as $campaign)
                    <tr>
                        <td>
                            <a href="{{ route('campaigns.preview', $campaign->id) }}">{{ $campaign->name }}</a>
                        </td>
                        @if (request()->routeIs('campaigns.sent') || request()->routeIs('campaigns.listen'))
                            <td>{{ $campaignStats[$campaign->id]['counts']['sent'] }}</td>
                        @endif
                        <td><span title="{{ $campaign->created_at }}">{{ $campaign->created_at->diffForHumans() }}</span></td>
                        <td>
                            @if($campaign->draft)
                                <span class="badge badge-light">{{ $campaign->status->name }}</span>
                            @elseif($campaign->queued)
                                <span class="badge badge-warning">{{ $campaign->status->name }}</span>
                            @elseif($campaign->sending)
                                <span class="badge badge-warning">{{ $campaign->status->name }}</span>
                            @elseif($campaign->sent)
                                <span class="badge badge-success">{{ $campaign->status->name }}</span>
                            @elseif($campaign->cancelled)
                                <span class="badge badge-danger">{{ $campaign->status->name }}</span>
                            @elseif($campaign->delayed)
                                <span class="badge badge-warning">{{ $campaign->status->name }}</span>
                            @elseif($campaign->repeated)
                                <span class="badge badge-warning">{{ $campaign->status->name }}</span>
                            @endif

                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm btn-wide" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                    @if ($campaign->draft)
                                        <a href="{{ route('campaigns.edit', $campaign->id) }}"
                                           class="dropdown-item">
                                            {{ __('Edit') }}
                                        </a>

                                    @else
                                        <a href="{{ route('campaigns.preview', $campaign->id) }}"
                                           class="dropdown-item">
                                            {{ __('View') }}
                                        </a>
                                    @endif


                                    @if($campaign->canBeCancelled())
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('campaigns.confirm-cancel', $campaign->id) }}"
                                           class="dropdown-item">
                                            {{ __('Cancel') }}
                                        </a>
                                    @endif

                                    @if ($campaign->draft)
                                        <div class="dropdown-divider"></div>
                                        <a href="{{ route('campaigns.destroy.confirm', $campaign->id) }}"
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
                                @if (request()->routeIs('campaigns.index'))
                                    {{ __('You do not have any draft campaigns.') }}
                                @else
                                    {{ __('You do not have any sent campaigns.') }}
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('layouts.partials.pagination', ['records' => $campaigns])

@endsection
