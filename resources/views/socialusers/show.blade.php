@extends('layouts.app')

@section('title', __("Subscriber") . " : {$socialuser->full_name}")

@section('heading')
    Telegram用户
@stop

@section('content')

    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-light btn-md btn-flat" href="{{ route('socialusers.edit', $socialuser->id) }}">
                <i class="fa fa-edit mr-1"></i> {{ __('Edit Subscriber') }}
            </a>
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-table">
                    <div class="table">
                        <table class="table">
                            <tr>
                                <td><b>{{ __('Chat ID') }}</b></td>
                                <td>{{ $socialuser->chat_id }}</td>
                            </tr>
                            <tr>
                                <td><b>{{ __('User Name') }}</b></td>
                                <td>{{ $socialuser->username }}</td>
                            </tr>
                            <tr>
                                <td><b>{{ __('First Name') }}</b></td>
                                <td>{{ $socialuser->first_name }}</td>
                            </tr>
                            <tr>
                                <td><b>{{ __('Last Name') }}</b></td>
                                <td>{{ $socialuser->last_name }}</td>
                            </tr>
                            <tr>
                                <td><b>{{ __('Status') }}</b></td>
                                <td>
                                    @if($socialuser->unsubscribed_at)
                                        <span class="badge badge-danger">{{ __('Unsubscribed') }}</span>
                                        <span class="text-muted">{{ \App\Models\UnsubscribeEventType::findById($socialuser->unsubscribe_event_id) }}
                                            on {{ \App\Facades\Helper::displayDate($socialuser->unsubscribed_at)->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="badge badge-success">{{ __('Subscribed') }}</span> <span class="text-muted">{{ \App\Facades\Helper::displayDate($socialuser->created_at) }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            {{ __('Messages') }}
        </div>
        <div class="card-table">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Source') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
                </thead>
                <tbody>

                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('No Messages') }}</p>
                        </td>
                    </tr>
        
                </tbody>
            </table>
        </div>
    </div>

@stop
