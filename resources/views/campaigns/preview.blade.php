@extends('layouts.app')

@section('title', __('Confirm Campaign'))

@section('heading')
    {{ __('Preview Campaign') }}: {{ $campaign->name }}
@stop

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header card-header-accent">
                <div class="card-header-inner">
                    {{ __('Campaign') }}
                </div>
            </div>
            <div class="card-body">
                <form class="form-horizontal">
                    @if($campaign->is_send_mail)
                        <div class="pb-2"><b>{{ __('Email') }}</b></div>
                        <div class="row">
                            <label class="col-sm-2 col-form-label">{{ __('Email From') }}:</label>
                            <div class="col-sm-10">
                                <b>
                                    <span class="form-control-plaintext">{{ $campaign->email_service->from_name . ' <' . $campaign->email_service->from_email . '>' }}</span>
                                </b>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">{{ __('Email Subject') }}:</label>
                            <div class="col-sm-10">
                                <b>
                                    <span class="form-control-plaintext">{{ $campaign->subject }}</span>
                                </b>
                            </div>
                        </div>
                    @endif

                    @if($campaign->is_send_social)
                        <div class="pb-2"><b>{{ __('Social') }}</b></div>
                        <div class="row">
                            <label class="col-sm-2 col-form-label">{{ __('Social From') }}:</label>
                            <div class="col-sm-10">
                                <b>
                                    <span class="form-control-plaintext">@ {{ $campaign->social_service->bot_username }}</span>
                                </b>
                            </div>
                        </div>
                      @endif
                    <div class="pb-2"><b>{{ __('Content') }}</b></div>
                    <div style="border: 1px solid #ddd; height: 200px">
                        <iframe id="js-template-iframe" srcdoc="{{ $campaign->merged_content }}" class="embed-responsive-item" frameborder="0" style="height: 100%; width: 100%"></iframe>
                    </div>

                </form>

            </div>
             @if($campaign->is_send_mail)
            <div class="card-body border-top" >
                <form action="{{ route('campaigns.test', $campaign->id) }}" method="POST">
                    @csrf
                    <div class="pb-2"><b>{{ __('EMAIL RECIPIENT') }}</b></div>
                    <div class="form-group row form-group-schedule">
                        <div class="col-sm-12">
                            <input name="recipient_email" id="test-email-recipient" type="email" class="form-control" placeholder="{{ __('Recipient email address') }}">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-sm btn-secondary">{{ __('Send Test Email') }}</button>
                    </div>
                </form>
            </div>
            @endif
            @if($campaign->is_send_social)
            <div class="card-body border-top" >
                <form action="{{ route('campaigns.test', $campaign->id) }}" method="POST">
                    @csrf
                    <div class="pb-2"><b>{{ __('SOCIAL RECIPIENT') }}</b></div>
                    <div class="form-group row form-group-schedule">
                        <div class="col-sm-12">
                            <input name="recipient_email" type="text" class="form-control" placeholder="{{ __('Recipient chat id') }}">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-sm btn-secondary">{{ __('Send Test Social') }}</button>
                    </div>
                </form>
            </div>
             @endif
        </div>
        <form action="{{ route('campaigns.send', $campaign->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div>
                        <a href="{{ route('campaigns.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                        <a href="{{ route('campaigns.edit', $campaign->id) }}" class="btn btn-light">{{ __('Edit') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Send campaign') }}</button>
                    </div>

                </form>
    </div>

    <div class="col-md-4">





    </div>


</div>

@stop

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        var target = $('.tags-container');
        $('#id-field-recipients').change(function() {
            if (this.value == 'send_to_all') {
                target.addClass('hide');
            } else {
                target.removeClass('hide');
            }
        });

        var element = $('#input-field-scheduled_at');
        $('#id-field-schedule').change(function() {
            if (this.value == 'now') {
                element.addClass('hide');
            } else {
                element.removeClass('hide');
            }
        });

        $('#input-field-scheduled_at').flatpickr({
            enableTime: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i",
        });
    </script>
@endpush
