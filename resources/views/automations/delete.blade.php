@extends('layouts.app')

@section('title', __('Delete Automations'))

@section('heading')
    @lang('Delete Automations') - {{ $automations->name }}
@endsection

@section('content')

    <div class="card">
        <div class="card-header card-header-accent">
            <div class="card-header-inner">
                {{ __('Confirm Delete') }}
            </div>
        </div>
        <div class="card-body">
            <p>
                {!! __('Are you sure that you want to delete the <b>:name</b> automation?', ['name' => $automations->name]) !!}
            </p>
            <form action="{{ route('automations.destroy', $automations->id) }}" method="post">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" value="{{ $automations->id }}">
                <a href="{{ route('automations.index') }}" class="btn btn-md btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-md btn-danger">{{ __('DELETE') }}</button>
            </form>
        </div>
    </div>

@endsection
