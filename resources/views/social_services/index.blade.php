@extends('layouts.app')

@section('title', 'Telegram服务')

@section('heading')
    发件服务器
@endsection

@section('content')

    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link {{ request()->is('*email-services*') ? 'active'  : '' }}"
               href="{{ route('email_services.index') }}">邮箱服务器</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('*social-services*') ? 'active'  : '' }}"
               href="{{ route('social_services.index') }}">Telegram服务</a>
        </li>
    </ul>

    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('social_services.create') }}">
                <i class="fa fa-plus mr-1"></i> 添加Telegram服务
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('From') }}</th>
                    <th>{{ __('Service') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($socialServices as $service)
                    <tr>
                        <td>{{ $service->name }}</td>
                        <td><a href="https://t.me/{{ $service->bot_username }}" target="_blank">{{ '@'.$service->bot_username }}</a> {{ '('.$service->bot_id.')' }}</td>
                        <td>{{ $service->type->name }}</td>
                        <td>
                            <!--
                            <a href="{{ route('social_services.test', $service->id) }}" class="btn btn-sm btn-light">
                                {{ __('Test') }}
                            </a>
                        -->
                            <a class="btn btn-sm btn-light"
                               href="{{ route('social_services.edit', $service->id) }}">{{ __('Edit') }}</a>
                               
                            <form action="{{ route('social_services.delete', $service->id) }}" method="POST"
                                  style="display: inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-light">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%">
                            <p class="empty-table-text">{{ __('You have not configured any social service.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
