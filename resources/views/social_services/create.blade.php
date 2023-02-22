@extends('layouts.app')

@section('heading')
    Telegram服务
@stop

@section('content')

    @component('layouts.partials.card')
        @slot('cardHeader', __('Create Social Service'))

        @slot('cardBody')
            <form action="{{ route('social_services.store') }}" method="POST" class="form-horizontal">
                @csrf
                <x-sendportal.text-field name="name" :label="__('Name')" />
                <x-sendportal.select-field name="type_id" :label="__('Social Service')" :options="$socialServiceTypes" />

                <div id="services-fields"></div>

                <x-sendportal.submit-button :label="__('Save')" />
            </form>
        @endSlot
    @endcomponent

@stop

@push('js')
    <script>

        let url = '{{ route('social_services.ajax', 1) }}';

        $(function () {
            let type_id = $('select[name="type_id"]').val();

            createFields(type_id);

            $('#id-field-type_id').on('change', function () {
                createFields(this.value);
            });
        });

        function createFields(serviceTypeId) {
            url = url.substring(0, url.length - 1) + serviceTypeId;

            $.get(url, function (result) {
                $('#services-fields')
                  .html('')
                  .append(result.view);
            });
        }

    </script>
@endpush
