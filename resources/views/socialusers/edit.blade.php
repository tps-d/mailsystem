@extends('layouts.app')

@section('title', __("Edit Subscriber") . " : {$socialuser->full_name}")

@section('heading')
    {{ __('Subscribers') }}
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Edit Subscriber'))

        @slot('cardBody')
            <form action="{{ route('socialusers.update', $socialuser->id) }}" method="POST" class="form-horizontal">
                @csrf
                @method('PUT')

                @include('socialusers.partials.form')

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop
