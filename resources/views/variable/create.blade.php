@extends('layouts.app')

@section('title', __('New Variable'))

@section('heading')
    {{ __('Variable') }}
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Create Variable'))

        @slot('cardBody')
            <form action="{{ route('variable.store') }}" method="POST" class="form-horizontal">
                @csrf

                @include('variable.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
