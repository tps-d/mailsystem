@extends('layouts.app')

@section('title', __("Content Templates"))

@section('heading')
    {{ __('Content Templates') }}
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            {{ __('Edit Template') }}
        </div>
        <div class="card-body">
            <form action="{{ route('templates.update', $template->id) }}" method="POST" class="form-horizontal">
                @csrf
                @method('PUT')
                @include('templates.partials.form')
            </form>
        </div>
    </div>



@stop
