@extends('layouts.app')

@section('title', __('Email Templates'))

@section('heading')
    {{ __('Email Templates') }}
@endsection

@section('content')

    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('templates.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('New Template') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($templates as $template)
                    <tr>
                        <td>
                            {{ $template->name }}
                        </td>
                        <td>
                                                                <form action="{{ route('templates.destroy', $template->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <a href="{{ route('templates.edit', $template->id) }}"
                                           class="btn btn-xs btn-light">{{ __('Edit') }}</a>
                                        <button type="submit" class="btn btn-xs btn-light">{{ __('Delete') }}</button>
                                    </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


{{ $templates->links() }}


@endsection
