@permission('whatsappapi manage')
<div class="card" id="whatsappapi-sidenav">
    {{ Form::open(array('route' => 'whatsappapi.setting.store', 'enctype' => "multipart/form-data")) }}
    <div class="card-header">
        <div class="row">
            <div class="col-lg-10 col-md-10 col-sm-10">
                <h5 class="">{{ __('WhatsApp API') }}</h5>
                <small>{{ __('Edit your WhatsApp API settings') }}</small>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2 text-end">
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="whatsappapi_notification_is" class="form-check-input input-primary" id="whatsappapi_notification_is" {{ company_setting('whatsappapi_notification_is')=='on'?' checked ':'' }} >
                    <label class="form-check-label" for="whatsappapi_notification_is"></label>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="form-group col-md-6">
                <label class="form-label ">{{ __('WhatsApp Phone number ID') }}</label> <br>
                <input class="form-control" placeholder="{{ __('WhatsApp Phone number ID') }}" name="whatsapp_phone_number_id" type="text" value="{{ company_setting('whatsapp_phone_number_id') }}" id="whatsapp_phone_number_id" {{ company_setting('whatsappapi_notification_is') == 'on' ? '' : ' disabled' }}>
            </div>

            <div class="form-group col-md-6">
                <label class="form-label ">{{ __('WhatsApp Access Token') }}</label> <br>
                <input class="form-control" placeholder="{{ __('Enter WhatsApp Access Token') }}" name="whatsapp_access_token" type="text" value="{{ company_setting('whatsapp_access_token') }}" id="whatsapp_access_token" {{ company_setting('whatsappapi_notification_is') == 'on' ? '' : ' disabled' }}>
            </div>
        </div>
            <ul class="nav nav-pills pt-5 pb-3" id="pills-tab" role="tablist">
                @php
                    $active = 'active';
                @endphp
                @foreach ($notification_modules as $module)
                    @if(module_is_active($module) || strtolower($module) == 'general')
                            <li class="nav-item">
                                <a class="nav-link text-capitalize {{ $active }}" id="whatsapp-{{ strtolower($module) }}-tab" data-bs-toggle="pill" href="#whatsapp-{{ strtolower($module) }}" role="tab" aria-controls="pills-{{ strtolower($module) }}" aria-selected="true">{{ Module_Alias_Name($module) }}</a>
                            </li>
                            @php
                                $active = '';
                            @endphp
                    @endif
                @endforeach
            </ul>
            <div class="tab-content" id="pills-tabContent">
                @php
                    $active = 'active';
                @endphp
                @foreach ($notification_modules as $module)
                    @if(module_is_active($module) || strtolower($module) == 'general')
                    <div class="tab-pane fade {{ $active }} show" id="whatsapp-{{ strtolower($module) }}" role="tabpanel" aria-labelledby="whatsapp-{{ strtolower($module) }}-tab">
                            <div class="row">
                            @foreach ($notify as $key => $action)
                                @if (Gate::check($action->permissions) || $action->permissions == null)
                                    @if($action->module == $module)
                                        <div class="col-md-4 ">
                                            <div class="d-flex align-items-center justify-content-between list_colume_notifi pb-2 mb-3">
                                                <div class="mb-3 mb-sm-0">
                                                    <h6>
                                                        <label for="{{ 'whatsappapi_notificaation_'.$key }}" class="form-label">{{ $action->action }}</label>
                                                    </h6>
                                                </div>
                                                <div class="text-end">
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input type="hidden"
                                                            name="whatsappapi[{{'Whatsappapi '.$action->action }}]"
                                                            value="0" />
                                                        <input class="form-check-input"
                                                            {{ company_setting('Whatsappapi '.$action->action) == true ? 'checked' : '' }}
                                                            id="whatsappapi_notificaation_{{$key}}"
                                                            name="whatsappapi[{{'Whatsappapi '.$action->action }}]"
                                                            type="checkbox" value="1">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            @endforeach
                            </div>
                        </div>
                        @php
                            $active = '';
                        @endphp
                    @endif
                @endforeach
            </div>
    </div>
    <div class="card-footer d-flex justify-content-between flex-wrap "style="gap:10px">

        <button type="button" data-url="{{route('test.setting.massage')}}"  data-title="{{__('Test WhatsApp Massage')}}" class="btn btn-print-invoice  btn-primary m-r-10 send_email test-whatsapp-massage">{{__('Send Test Massage')}}</button>

        <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{__('Save Changes')}}">
    </div>

</div>
    {{Form::close()}}
@endpermission


<script>
    /* Open Test whatsapp-massage Modal */
    $(document).on('click', '.test-whatsapp-massage', function (e) {
            e.preventDefault();
            var title = $(this).attr('data-title');
            var size = 'md';
            var url = $(this).attr('data-url');
            if (typeof url != 'undefined') {
                $("#commonModal .modal-title").html(title);
                $("#commonModal .modal-dialog").addClass('modal-' + size);
                $("#commonModal").modal('show');

                $.post(url, {
                    whatsapp_phone_number_id: $("#whatsapp_phone_number_id").val(),
                    whatsapp_access_token: $("#whatsapp_access_token").val(),
                    _token: "{{ csrf_token() }}",
                }, function (data) {
                    $('#commonModal .body').html(data);
                });
            }
        })
</script>

<script>
    $(document).on('click','#whatsappapi_notification_is',function(){
        if( $('#whatsappapi_notification_is').prop('checked') )
        {
            $("#whatsapp_phone_number_id").removeAttr("disabled");
            $("#whatsapp_access_token").removeAttr("disabled");
        } else {
            $('#whatsapp_phone_number_id').attr("disabled", "disabled");
            $('#whatsapp_access_token').attr("disabled", "disabled");
        }
    });
</script>


