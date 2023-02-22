@extends('layouts.app')

@section('title', __('Create Autotrigger'))

@section('heading', __('Autotrigger'))

@section('content')


        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card">
                    <div class="card-header">
                        @if($type == 'Telegram')
                        {{ __('Create Telegram Autotrigger') }}
                        @else
                        {{ __('Create Email Autotrigger') }}
                        @endif
                    </div>
                    <div class="card-body">
                        <form action="{{ route('autotrigger.store') }}" method="POST" class="form-horizontal">
                            @csrf
                            <x-sendportal.text-field name="name" :label="__('Name')" :value="$autotrigger->name ?? old('name')" />

                             <input type="hidden" name="from_type" value="{{ $type }}" />
                             <x-sendportal.select-field name="from_id" :label="__('From')" :options="$fromOptions" :value="$autotrigger->from_id ?? old('from_id')" />

                            <div class="form-group row form-group-auto_label">
                                <label for="id-field-auto_label" class="control-label col-sm-3">Condition</label>
                                <div class="col-sm-9">
                                    <div class="form-check">
                                      <input class="form-check-input" type="radio" name="condition" id="condition_all" value="all" checked>
                                      <label class="form-check-label" for="condition_all">
                                        所有内容
                                      </label>
                                    </div>
                                    <div class="form-check">
                                      <input class="form-check-input" type="radio" name="condition" id="condition_include" value="include">
                                      <label class="form-check-label" for="condition_include">
                                        包含
                                      </label>
                                    </div>
                                </div>
                            </div>

                            <x-sendportal.text-field name="match_content" :label="__('Match Content')" :value="$autotrigger->match_content ?? old('match_content')" />
                            <x-sendportal.select-field name="template_id" :label="__('Reply Template')" :options="$templates" :value="$autotrigger->template_id ?? old('template_id')" />


                            <div class="form-group row">
                                <div class="offset-sm-3 col-sm-9">
                                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

@stop
