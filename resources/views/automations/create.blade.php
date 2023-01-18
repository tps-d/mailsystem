@extends('layouts.app')

@section('title', __('Create Automations'))

@section('heading', __('Automations'))

@section('content')

    @if( ! $emailServices)
        <div class="callout callout-danger">
            <h4>{{ __('You haven\'t added any email service!') }}</h4>
            <p>{{ __('Before you can create a campaign, you must first') }} <a
                    href="{{ route('email_services.create') }}">{{ __('add an email service') }}</a>.
            </p>
        </div>
    @else
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card">
                    <div class="card-header">
                        {{ __('Create Automations') }}
                    </div>
                    <div class="card-body">
                        <form action="{{ route('automations.store') }}" method="POST" class="form-horizontal">
                            @csrf
                            <x-sendportal.text-field name="name" :label="__('Campaign Name')" :value="$campaign->name ?? old('name')" />
                            <x-sendportal.text-field name="auto_label" :label="__('Auto Label')" :value="$campaign->auto_label ?? old('auto_label')" />
                            
                            <x-sendportal.select-field name="template_id" :label="__('Template')" :options="$templates" :value="$campaign->template_id ?? old('template_id')" />

                            <x-sendportal.select-field name="email_service_id" :label="__('Email Service')" :options="$emailServices->pluck('formatted_name', 'id')" :value="$campaign->email_service_id ?? old('email_service_id')" />

                            <x-sendportal.text-field name="from_name" :label="__('From Name')" :value="$campaign->from_name ?? old('from_name')" />
                            <x-sendportal.text-field name="from_email" :label="__('From Email')" type="email" :value="$campaign->from_email ?? old('from_email')" />
  
                            <x-sendportal.checkbox-field name="is_open_tracking" :label="__('Track Opens')" value="1" :checked="$campaign->is_open_tracking ?? true" />
                            <x-sendportal.checkbox-field name="is_click_tracking" :label="__('Track Clicks')" value="1" :checked="$campaign->is_click_tracking ?? true" />

                            <div class="form-group row">
                                <div class="offset-sm-3 col-sm-9">
                                    <a href="{{ route('automations.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('Save and continue') }}</button>
                                </div>
                            </div>

                            @push('js')
                                <script>

                                    $(function () {
                                        const smtp = {{
                                            $emailServices->filter(function ($service) {
                                                return $service->type_id === \App\Models\EmailServiceType::SMTP;
                                            })
                                            ->pluck('id')
                                        }};

                                        const emailServices = @json($emailServices->pluck('name','id')->all())

                                        let service_id = $('select[name="email_service_id"]').val();

                                        toggleTracking(smtp.includes(parseInt(service_id, 10)));
                                          $('input[name="from_name"]').val(emailServices[service_id]);
                                          $('input[name="from_email"]').val(emailServices[service_id]);
                                        $('select[name="email_service_id"]').on('change', function () {
                                          toggleTracking(smtp.includes(parseInt(this.value, 10)));

                                          var service_id = $(this).val();
                                          $('input[name="from_name"]').val(emailServices[service_id]);
                                          $('input[name="from_email"]').val(emailServices[service_id]);
                                        });
                                    });

                                    function toggleTracking(disable) {
                                        let $open = $('input[name="is_open_tracking"]');
                                        let $click = $('input[name="is_click_tracking"]');

                                        if (disable) {
                                            $open.attr('disabled', 'disabled');
                                            $click.attr('disabled', 'disabled');
                                        } else {
                                            $open.removeAttr('disabled');
                                            $click.removeAttr('disabled');
                                        }
                                    }

                                </script>
                            @endpush

                        </form>
                    </div>
                </div>
            </div>
        </div>
	@endif
@stop
