@extends('layouts.app')

@section('title', __('Create Automations'))

@section('heading', __('Automations'))

@section('content')

    @if( ! $campaigns)
        <div class="callout callout-danger">
            <h4>{{ __('You haven\'t added any campaign!') }}</h4>
            <p>{{ __('Before you can create a Automation, you must first') }} <a
                    href="{{ route('campaigns.create') }}">{{ __('add an campaign') }}</a>.
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
                            <x-sendportal.select-field name="campaign_id" :label="__('Campaign')" :options="[null => '- None -'] + $campaigns" :value="$task->campaign_id ?? old('campaign_id')" />
                            

                            <div class="form-group row form-group-auto_label">
                                <label for="id-field-auto_label" class="control-label col-sm-3">Type</label>
                                <div class="col-sm-9">
                                    <div class="form-check">
                                      <input class="form-check-input" type="radio" name="type_id" id="type_timeat" value="1" checked>
                                      <label class="form-check-label" for="type_timeat">
                                        定时执行
                                      </label>
                                    </div>
                                    <div class="form-check">
                                      <input class="form-check-input" type="radio" name="type_id" id="type_exp" value="2">
                                      <label class="form-check-label" for="type_exp">
                                        重复执行
                                      </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row show " id="input-scheduled_at" >
                                <label for="id-field-scheduled_at" class="control-label col-sm-3">Scheduled at</label>
                                <div class="col-sm-9">
                                    <input id="input-field-scheduled_at" class="form-control mb-3" name="scheduled_at" type="text" value="{{ $task->scheduled_at ?? now() }}">
                                </div>
                            </div>
                            <div id="input-expression" class="hide">
                                <x-sendportal.text-field name="expression" :label="__('Cron expression')" :value="$task->expression ?? old('expression')" />
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-3 col-sm-9">
                                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                </div>
                            </div>

                            @push('css')
                                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                            @endpush
                            
                            @push('js')
                                <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                                <script>

                                    $(function () {

                                        $('input[name=type_id]').change(function() {
                                            if (this.value == '1') {
                                                $('#input-scheduled_at').removeClass('hide');
                                                $('#input-expression').addClass('hide');
                                            } else {
                                                $('#input-scheduled_at').addClass('hide');
                                                $('#input-expression').removeClass('hide');
                                            }
                                        });

                                        $('#input-field-scheduled_at').flatpickr({
                                            enableTime: true,
                                            time_24hr: true,
                                            dateFormat: "Y-m-d H:i",
                                        });
                                        
                                    });
                                </script>
                            @endpush

                        </form>
                    </div>
                </div>
            </div>
        </div>
	@endif
@stop
