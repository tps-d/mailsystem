@extends('layouts.app')

@section('title', __('New Subscriber'))

@section('heading')
    Telegram用户
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Create Subscriber'))

        @slot('cardBody')
            <form action="{{ route('socialusers.store') }}" class="form-horizontal" method="POST">
                @csrf
                @include('socialusers.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
