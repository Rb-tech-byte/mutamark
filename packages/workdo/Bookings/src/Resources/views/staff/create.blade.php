{{ Form::open(['route' => ['booking-staff.store'], 'method' => 'post']) }}
    <div class="modal-body">
        <div class="col-md-12">
            <div id="form-container">
                <div class="form-group-container">
                    <div class="row">
                        <div class="form-group col-md-12 ml-4">
                            {{ Form::label('staff', __('Staff'), ['class' => 'form-label']) }}
                            <select name="staff" class='form-control font-style' id="staff">
                                <option value="0" selected disabled>{{ __('Staff') }}</option>
                                @foreach ($staffs as $key => $staff)
                                    <option value="{{ $key }}">{{ $staff }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-12 ml-4">
                            {{ Form::label('service', __('Service'), ['class' => 'form-label']) }}
                            <select class="form-control choices" name="services[]" id="values_name" multiple>
                                <option value="">{{ __('Select Service') }}</option>
                                @foreach ($services as $key => $service)
                                    <option value="{{ $key }}">{{ $service }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Create'), ['class' => 'btn btn-primary'])}}
    </div>
{{ Form::close() }}
