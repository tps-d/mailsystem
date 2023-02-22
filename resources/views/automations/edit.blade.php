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
                        <x-sendportal.select-field name="campaign_id" :label="__('Campaign')" :options="$campaigns" :value="$task->campaign_id ?? old('campaign_id')" />

                        <div class="form-group row form-group-auto_label">
                            <label for="id-field-auto_label" class="control-label col-sm-3">Type</label>
                            <div class="col-sm-9">
                                <div class="form-check">
                                  <input class="form-check-input" type="radio" name="type_id" id="type_timeat" value="1" @if($task->type_id == 1) checked @endif>
                                  <label class="form-check-label" for="type_timeat">
                                    定时执行
                                  </label>
                                </div>
                                <div class="form-check">
                                  <input class="form-check-input" type="radio" name="type_id" id="type_exp" value="2" @if($task->type_id == 2) checked @endif>
                                  <label class="form-check-label" for="type_exp">
                                    重复执行
                                  </label>
                                </div>
                            </div>
                        </div>

                        <x-sendportal.text-field name="expression" :label="__('CRON EXPRESSION')" :value="$task->expression ?? old('expression')" />


                        <div class="form-group row">
                            <div class="offset-sm-3 col-sm-9">
                                <a href="{{ route('automations.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>
                        </div>

                        @include('layouts.partials.summernote')

                        @push('js')
                            <script>



                            </script>
                        @endpush

                    </form>
                </div>
            </div>
        </div>
    </div>

@stop
