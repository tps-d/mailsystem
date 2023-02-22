@extends('layouts.app')

@section('heading')
    Telegram服务
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Edit Social Service'))

        @slot('cardBody')
            <form action="{{ route('social_services.update', $socialService->id) }}" method="POST" class="form-horizontal">
                @csrf
                @method('PUT')
                <x-sendportal.text-field name="name" :label="__('Name')" :value="$socialService->name" />

                @include('social_services.options.' . strtolower($socialServiceType->name), ['settings' => $socialService->settings])

                <x-sendportal.submit-button :label="__('Update')" />
            </form>
        @endSlot
    @endcomponent

@stop
