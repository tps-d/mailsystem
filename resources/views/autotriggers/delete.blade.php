@extends('layouts.app')

@section('title', __('Delete Autotrigger'))

@section('heading')
    @lang('Delete Autotrigger') - {{ $autotrigger->name }}
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
                {!! __('Are you sure that you want to delete the <b>:name</b> autotrigger?', ['name' => $autotrigger->name]) !!}
            </p>
            <form action="{{ route('autotrigger.destroy', $autotrigger->id) }}" method="post">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" value="{{ $autotrigger->id }}">
                <a href="{{ route('autotrigger.index',['type'=>$autotrigger->from_type]) }}" class="btn btn-md btn-light">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-md btn-danger">{{ __('DELETE') }}</button>
            </form>
        </div>
    </div>

@endsection
