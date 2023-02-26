@extends('layouts.app')

@section('title', __('Edit Automations'))

@section('heading')
    {{ __('Automations') }}
@stop

@section('content')

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    {{ __('Edit Automations') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('automations.update', $task->id) }}" method="POST" class="form-horizontal">
                        @csrf
                        @method('PUT')
  
                        <div class="form-group row ">
                            <label class="control-label col-sm-3">Campaign</label>
                            <div class="col-sm-9">
                                <a href="{{ route('campaigns.preview',$task->campaign_id) }}">{{ $task->campaign->name }}</a>
                                <input type="hidden" name="campaign_id" value="{{ $task->campaign_id }}" >
                            </div>
                        </div>

                        <div class="form-group row form-group-auto_label">
                            <label for="id-field-auto_label" class="control-label col-sm-3">Type</label>
                            <div class="col-sm-9">
                                @if($task->type_id == 1)
                               
                                  <input class="form-check-input" type="hidden" name="type_id" value="1" >
                                  <p>定时执行</p>
                                
                                @else
                                  <input class="form-check-input" type="hidden" name="type_id" value="2" >
                                  <p>重复执行</p>
                                @endif
                            </div>
                        </div>
                        @if($task->type_id == 1)
                            <div class="form-group row" id="input-scheduled_at" >
                                <label for="id-field-scheduled_at" class="control-label col-sm-3">Scheduled at</label>
                                <div class="col-sm-9">
                                    <input id="input-field-scheduled_at" class="form-control mb-3" name="scheduled_at" type="text" value="{{ $task->scheduled_at ?? now() }}">
                                </div>
                            </div>
                          @else
                            <div id="input-expression">
                                <x-sendportal.text-field name="expression" :label="__('Cron expression')" :value="$task->expression ?? old('expression')" />
                            </div>
                            @endif
                        <div class="form-group row">
                            <div class="offset-sm-3 col-sm-9">
                                <a href="{{ route('automations.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>
                        </div>

                        @include('layouts.partials.summernote')

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

@stop
