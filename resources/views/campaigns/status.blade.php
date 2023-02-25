@extends('layouts.app')

@section('title', __('Campaign Status'))

@section('heading')
    {{ __('Campaign Status') }}
@stop

@section('content')



<div class="card">
    <div class="card-header card-header-accent">
        <div class="card-header-inner">
            {{ __('Your campaign is currently') }} <strong>{{ strtolower($campaign->status->name) }}</strong>
        </div>
    </div>
    <div class="card-body">
        @if ($campaign->queued)
            Your campaign is queued and will be sent out soon.
        @elseif ($campaign->cancelled)
            Your campaign was cancelled.
        @elseif ($campaign->sent)
            <p>
                <i class="fas fa-check-square"></i>
                {{ $campaignStats[$campaign->id]['counts']['sent'] }} out of {{ $campaignStats[$campaign->id]['counts']['total'] }} messages sent.
            <p>

            <a href="{{ route('messages.index',['source_id'=>$campaign->id]) }}" class="btn btn-light">{{ __('Message Log') }}</a>
        @else
            <i class="fas fa-spinner fa-spin"></i>
            {{ $campaignStats[$campaign->id]['counts']['sent'] }} out of {{ $campaignStats[$campaign->id]['counts']['total'] }} messages sent.
        @endif
    </div>
</div>

@stop
