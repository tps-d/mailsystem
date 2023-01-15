@extends('layouts.app')

@section('title', __('Variable'))

@section('heading')
    {{ __('Variable') }}
@endsection

@section('content')
    @component('layouts.partials.actions')

        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('variable.create') }}">
                <i class="fa fa-plus"></i> {{ __('New Variable') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Value Type') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($variables as $variable)
                    <tr>
                        <td>
                            <a href="{{ route('variable.edit', $variable->id) }}">
                                {{ $variable->name }}
                            </a>
                        </td>
                        <td>{{ $variable->description }}</td>
                        <td>{{ $variable->value_type_name }}</td>
                        <td>
                            @include('variable.partials.actions')
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('You have not created any variables.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('layouts.partials.pagination', ['records' => $variables])

@endsection
