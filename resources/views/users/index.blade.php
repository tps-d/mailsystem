@extends('layouts.app')

@section('heading')
    {{ __('Workspace Members') }}
@endsection

@section('content')

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    {{ __('Current Users') }}
                </div>

                <div class="card-table table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->id === auth()->user()->id)
                                        <button
                                            class="btn btn-sm btn-light"
                                            disabled
                                            title="{{ __('You cannot remove yourself from the workspace.') }}"
                                        >
                                            Remove
                                        </button>
                                    @else
                                        <form action="{{ route('users.destroy', $user->id) }}"
                                              method="post">
                                            @csrf
                                            @method('delete')
                                            <input type="submit" class="btn btn-sm btn-light"
                                                   value="{{ __('Remove') }}">
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
