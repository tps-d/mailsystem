@push('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/theme/monokai.min.css">
    <style>
        .CodeMirror {
            height: 600px;
        }

        .template-preview {
            height: 600px;
        }

        #variable_tags a{
            margin-right: 5px;
        }
    </style>
@endpush

<div class="form-group row form-group-content template-content">
    <label for="id-field-content" class="control-label col-sm-2">{{ __('Content') }}</label>
    <div class="col-sm-10">
       <div class="btn-group mb-2" id="variable_tags">
            <a class="btn btn-sm tag btn-default" data-variable="CAPTCHACODE_{{auth()->user()->currentWorkspace->name }}_0" href="javascript:;">
                注册验证码
            </a>
            <a class="btn btn-sm tag btn-default" data-variable="CAPTCHACODE_{{auth()->user()->currentWorkspace->name }}_1" href="javascript:;">
                密码找回验证码
            </a>
            <a class="btn btn-sm btn-default" data-toggle="modal" data-target="#cardViewModel">
                卡密
            </a>
            <a class="btn btn-sm btn-default" data-toggle="modal" data-target="#discountViewModel">
                优惠码
            </a>
            @foreach($variables as $variable)
            <a class="btn btn-md tag btn-default" data-variable="{{ $variable['name'] }}" href="javascript:;">
                {{ $variable['description'] }}
            </a>
            @endforeach
        </div>
        <textarea id="id-field-content" class="form-control" name="content" cols="50"
                  rows="20">{{ old('content', $template->content ?? null) }}</textarea>
    </div>
</div>

<div class="form-group row template-preview d-none">
    <div class="offset-sm-2 col-sm-10">
        <div class="border border-light h-100">
            <iframe width="100%" height="100%" scrolling="yes" frameborder="0"
                    srcdoc="{!! old('content', $template->content ?? null)  !!} "></iframe>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="cardViewModel" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="cardViewModelLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cardViewModelLabel">套餐列表</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-center">
          <div class="spinner-border"  style="width: 3rem; height: 3rem;"  role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="insert_card_code_btn" disabled>插入</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="discountViewModel" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="discountViewModelLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="discountViewModelLabel">优惠码</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <div class="form-horizontal" id="discountForm">
                <div class="form-group row form-group-day">
                    <label for="id-field-day" class="control-label col-sm-3">天</label>
                    <div class="col-sm-9">
                        <select name="day" id="id-field-day" class="form-control selectpicker">
                                <option value="1" >1天</option>
                                <option value="30" selected>30天</option>
                                <option value="90">90天</option>
                                <option value="360">360天</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row form-group-type">
                    <label for="id-field-type" class="control-label col-sm-3">类型</label>
                    <div class="col-sm-9">
                        <select name="type" id="id-field-type" class="form-control selectpicker">
                                <option value="0" selected>折扣</option>
                                <option value="1">金额</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row form-group-value">
                    <label for="id-field-value" class="control-label col-sm-3">值 ( <span id="unit_label">%</span> )</label>
                    <div class="col-sm-9">
                        <input type="number" name="value" value="" id="id-field-value" class="form-control">
                    </div>
                </div>
                <div class="form-group row form-group-use_type">
                    <label for="id-field-use_type" class="control-label col-sm-3">使用类型</label>
                    <div class="col-sm-9">
                        <select name="use_type" id="id-field-use_type" class="form-control selectpicker" >
                                <option value="0" selected>全部用户</option>
                                <option value="1">仅新用户</option>
                                <option value="2">仅老用户</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row form-group-expire_time">
                    <label for="id-field-expire_time" class="control-label col-sm-3">到期时间</label>
                    <div class="col-sm-9">
                        <input type="text" name="expire_time" value="" id="id-field-expire_time" class="form-control input-field-flatpickr">
                    </div>
                </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="insert_discount_code_btn">插入</button>
      </div>
    </div>
  </div>
</div>


@include('layouts.partials.summernote')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('js')
<!--
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.52.2/mode/xml/xml.min.js"></script>
-->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function () {
            /*
            var codeMirror = CodeMirror.fromTextArea(document.getElementById('id-field-content'), {
                lineNumbers: true,
                lineWrapping: true,
                mode: 'xml',
                theme: 'monokai',
            });
*/
            var workspace_name = "{{auth()->user()->currentWorkspace->name }}";

            $('.btn-preview').click(function (e) {
                e.preventDefault();

                var elContent = $('.template-preview');
                var elPreview = $('.template-content');
                var elButton = $('.btn-preview');

                if (elContent.hasClass('d-none')) {
                    //$('.template-preview iframe').attr('srcdoc', codeMirror.getValue());

                    var content = $('#id-field-content').summernote('code');
                    $('.template-preview iframe').attr('srcdoc', content);
                    elContent.removeClass('d-none');
                    elPreview.addClass('d-none');
                    elButton.text('Show Design');
                } else {
                    elContent.addClass('d-none');
                    elPreview.removeClass('d-none');
                    elButton.text('Show Preview');
                }
            });


            $('#variable_tags').find('a.tag').each(function(){
                $(this).on('click',function(){
                    var variable = $(this).data('variable');
                    if(variable.length == 0){
                        return;
                    }

                    variable = "{"+variable+"}";

                    $('#id-field-content').summernote('editor.insertText', variable);

                    /*
                    var doc = codeMirror.getDoc();
                    var cursor = doc.getCursor();

                    var pos = {
                       line: cursor.line,
                       ch: cursor.ch
                    }

                    doc.replaceRange(variable, pos);
                    */
                });
            });


            $('#cardViewModel').on('show.bs.modal',function(){
                var model = $(this);
                model.find('.modal-body').load('/platform/card/list',function(){
                    model.find('input[type=radio]').on('click',function () {

                        $('#insert_card_code_btn').prop("disabled", false);
/*
                        var pid = $(this).val();
                        var day = $(this).data('day');
                        var exp = $(this).parent().parent().find('input[name=exp]').val();
                        alert(exp);
                        $('#insert_card_code_btn').data('pid',pid);
                        $('#insert_card_code_btn').data('day',day);
                        $('#insert_card_code_btn').data('exp',exp);
                        */
                    });

                });

            });

            $('#insert_card_code_btn').on('click',function(){
                var checkedInput = $("#cardViewModel .modal-body input[type='radio']:checked");
                if(!checkedInput.length){
                    return;
                }


                var pid = checkedInput.val();
                var day = checkedInput.data('day');
                var exp = checkedInput.parents('.list-group-item').find('input[name=exp]').val();

                variable = "{EXCHANGECODE_"+workspace_name+"_"+pid+"_"+day+"_"+exp+"}";
                $('#id-field-content').summernote('editor.insertText', variable);
                /*
                var doc = codeMirror.getDoc();
                var cursor = doc.getCursor();

                var pos = {
                   line: cursor.line,
                   ch: cursor.ch
                }

                doc.replaceRange(variable, pos);
                */
                $('#cardViewModel').modal('hide');
                
            });




            $('#discountViewModel').on('show.bs.modal',function(){
                var model = $(this);
 
                var date = new Date();

                model.find('.input-field-flatpickr').flatpickr({
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: "Y-m-d H:i",
                      minuteIncrement: 1,
                      inline: true,
                      defaultDate: date 
                });

                $("#discountForm select[name=type]").change(function(){

                    var this_value = $(this).val();
                    if(this_value == 1){
                        $('#unit_label').text('¥');
                    }else{
                        $('#unit_label').text('%');
                    }
                });
            });

            $('#insert_discount_code_btn').on('click',function(){
                var day = $("#discountForm select[name=day]").val();
                var type = $("#discountForm select[name=type]").val();
                var value = $("#discountForm input[name=value]").val();
                var use_type = $("#discountForm select[name=use_type]").val();
                var expire_data = $("#discountForm input[name=expire_time]").val();
                

                if(! /^\d+$/.test(value)){
                    alert("值应该是正整数");
                    $("#discountForm input[name=value]").focus();
                    return false;
                }

                var timestamp=new Date().getTime();
                var expire_time = new Date(expire_data+':00');

                if(timestamp >= expire_time){
                    alert("过期时间不能小于当前时间");
                    return false;
                }

                var time = expire_time / 1000;

                variable = "{DISCOUNTCODE_"+workspace_name+"_"+day+"_"+type+"_"+value+"_"+use_type+"_"+time+"}";
                $('#id-field-content').summernote('editor.insertText', variable);
         
                $('#discountViewModel').modal('hide');
                
            });
        });
    </script>
@endpush
