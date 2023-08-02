@extends('layouts.app')

@section('title', __('Edit Campaign'))

@section('heading')
    {{ __('Edit Campaign') }}
@stop

@section('content')

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    {{ __('Edit Campaign') }}
                </div>
                <form action="{{ route('campaigns.update', $campaign->id) }}" method="POST" class="form-horizontal">
                    @csrf
                    @method('PUT')
                    <div class="card-body">

                        <x-sendportal.text-field name="name" :label="__('Campaign Name')" :value="$campaign->name ?? old('name')" />
                        
                        <x-sendportal.select-field name="template_id" :label="__('Template')" :options="$templates" :value="$campaign->template_id ?? old('template_id')" />

                        <x-sendportal.checkbox-field name="is_send_mail" :label="__('Send Mail')" value="1" :checked="$campaign->is_send_mail ?? old('is_send_mail')" />
                        <x-sendportal.checkbox-field name="is_send_social" :label="__('Send Social')" value="1" :checked="$campaign->is_send_social ?? old('is_send_social')" />
                    </div>
                    <div class="card-body border-top" id="type_fields_email">
                        <div class="pb-2"><b>{{ __('Send Mail') }}</b></div>
                        <x-sendportal.text-field name="subject" :label="__('Email Subject')" :value="$campaign->subject ?? old('subject')" />

                        <x-sendportal.select-field name="email_service_id" :label="__('Email Service')" :options="$emailServices->pluck('formatted_name', 'id')" :value="$campaign->email_service_id ?? old('email_service_id')" />

                        <input type="hidden" name="is_open_tracking" value="1" />
                        <input type="hidden" name="is_click_tracking" value="1" />
                        <input type="hidden" name="content" value="" />

                        <div class="form-group row form-group-recipients">
                            <label for="id-field-recipients" class="control-label col-sm-3">{{ __('Email Recipients') }}</label>
                            <div class="col-sm-9">
                                <select id="id-field-recipients" class="form-control" name="recipients">
                                    <option value="send_to_all" {{ (old('recipients') ? old('recipients') == 'send_to_all' : $campaign->send_to_all) ? 'selected' : '' }}>
                                        {{ __('All subscribers') }} ({{ $subscriberCount }})
                                    </option>
                                    <option value="send_to_tags" {{ (old('recipients') ? old('recipients') == 'send_to_tags' : !$campaign->send_to_all) ? 'selected' : '' }}>
                                        {{ __('Select Tags') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="tags-container {{ (old('recipients') ? old('recipients') == 'send_to_tags' : !$campaign->send_to_all) ? '' : 'hide' }}">
                            <div class="form-group row">
                                <div class="col-sm-9 offset-sm-3">
                                @forelse($tags as $tag)
                                    <div class="checkbox">
                                        <label>
                                            <input name="tags[]" type="checkbox" value="{{ $tag->id }}" {{  $tag->selected ? 'checked' : '' }}>
                                            {{ $tag->name }} ({{ $tag->activeSubscribers()->count() }} {{ __('Subscribers') }})
                                        </label>
                                    </div>
                                @empty
                                    <div>{{ __('There are no tags to select') }}</div>
                                @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body border-top" id="type_fields_social">
                        <div class="pb-2"><b>{{ __('Send Social') }}</b></div>
                        <x-sendportal.select-field name="social_service_id" :label="__('Social Service')" :options="$socialServices->pluck('formatted_name', 'id')" :value="$campaign->social_service_id ?? old('social_service_id')" />

                        <div class="form-group row form-group-recipients">
                            <label for="id-field-recipients" class="control-label col-sm-3">{{ __('Social Recipients') }}</label>
                            <div class="col-sm-9">
                                <p>
                                    user count ( {{ $socialuserCount }} )
                                </p>
                            </div>
                        </div>
                    </div>  
                    <div class="card-body border-top"> 
                        <div class="form-group row">
                            <div class="offset-sm-3 col-sm-9">
                                <a href="{{ route('campaigns.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('Save and continue') }}</button>
                            </div>
                        </div>
                    </div>
                        @include('layouts.partials.summernote')

                            @push('js')
                                <script>

                                    $(function () {
                                        const smtp = {{
                                            $emailServices->filter(function ($service) {
                                                return $service->type_id === \App\Models\EmailServiceType::SMTP;
                                            })
                                            ->pluck('id')
                                        }};

                                        let service_id = $('select[name="email_service_id"]').val();

                                        toggleTracking(smtp.includes(parseInt(service_id, 10)));

                                        $('select[name="email_service_id"]').on('change', function () {
                                          toggleTracking(smtp.includes(parseInt(this.value, 10)));
                                        });

                                        var target = $('.tags-container');
                                        $('#id-field-recipients').change(function() {
                                            if (this.value == 'send_to_all') {
                                                target.addClass('hide');
                                            } else {
                                                target.removeClass('hide');
                                            }
                                        });

                                        

                                        if($('input[name="is_send_mail"]:checked').length){
                                            $('#type_fields_email').show();
                                        }else{
                                            $('#type_fields_email').hide();
                                        }
                                        
                                        if($('input[name="is_send_social"]:checked').length){
                                            $('#type_fields_social').show();
                                        }else{
                                            $('#type_fields_social').hide();
                                        }

                                        $('input[name="is_send_mail"]').on('change', function () {
                                            if ($(this).is(':checked')) {
                                                $('#type_fields_email').show();
                                            }else{
                                                $('#type_fields_email').hide();
                                            }
                                        });

                                        $('input[name="is_send_social"]').on('change', function () {
                                            if ($(this).is(':checked')) {
                                                $('#type_fields_social').show();
                                            }else{
                                                $('#type_fields_social').hide();
                                            }
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

@stop
