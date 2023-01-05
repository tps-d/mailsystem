@extends('layouts.app')

@section('title', __("Edit Variable"))

@section('heading')
    {{ __('Variable') }}
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Edit Variable'))

        @slot('cardBody')
            <form action="{{ route('variable.update', $variable->id) }}" method="POST" class="form-horizontal">
                @csrf
                @method('PUT')

                @include('variable.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
