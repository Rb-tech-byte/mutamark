@php
    $arr = explode(', ', $appointment->week_day);
@endphp
{{ Form::model($appointment, ['route' => ['appointments.update', $appointment->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6 form-group">
            {{ Form::label('name', __('Appointment Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Appointment Name']) }}
        </div>

        <div class="col-md-6 form-group">
            {{ Form::label('appointment_type', __('Appointment Type'), ['class' => 'col-form-label']) }}
            {{ Form::select('appointment_type', $appointment_type, null, ['class' => 'form-control', 'required' => 'required', 'id' => 'appointment_id', 'placeholder' => 'Select Appointment Type']) }}
        </div>
        {{-- <div class="col-md-6 form-group">
            {!! Form::label('date', 'Appointment Date', ['class' => 'col-form-label']) !!}
            {!! Form::date('date', null, [ 'class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Appointment Date']) !!}
        </div> --}}
        <div class="col-md-12 form-group">
            {{ Form::label('value', __('Week Day'), ['class' => 'col-form-label']) }}
            <span id="weekday_id_span">
                <select class="multi-select weekday_data choices" id="week_day" data-toggle="select2" required
                    name="week_day[]" multiple="multiple" data-placeholder="{{ __('Select Week') }}">
                    @foreach ($week_days as $key => $q)
                        <option value="{{ $key }}" {{ in_array($key, $arr) ? 'selected' : '' }}>
                            {{ $q }}</option>
                    @endforeach
                </select>
            </span>
        </div>
        {{-- <div class="col-6">
            <div class="form-group">
                {{ Form::label('start_time', __('Start Time'), ['class' => 'col-form-label']) }}
                {{ Form::time('start_time', null, ['class' => 'form-control ', 'id' => 'start_time', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('end_time', __('End Time'), ['class' => 'col-form-label']) }}
                {{ Form::time('end_time', null, ['class' => 'form-control ', 'id' => 'end_time', 'required' => 'required']) }}
            </div>
        </div> --}}

        <hr>
        <h6>{{ __('Enable/Disable Field') }}</h6>
        <div class="form-check custom-checkbox form-group col-md-3">
            <input type="checkbox" class="form-check-input" name="enable_field[]" value="1" id="enable_field_1"
                @if (in_array(1, $enable_field)) checked @endif>
            <label class="form-check-label" for="enable_field_1">{{ __('Phone') }}</label>
        </div>
        <div class="form-check custom-checkbox form-group col-md-3">
            <input type="checkbox" class="form-check-input" name="enable_field[]" value="2" id="enable_field_2"
                @if (in_array(2, $enable_field)) checked @endif>
            <label class="form-check-label" for="enable_field_2">{{ __('Date') }}</label>
        </div>
        <div class="form-check custom-checkbox form-group col-md-3">
            <input type="checkbox" class="form-check-input" name="enable_field[]" value="3" id="enable_field_3"
                @if (in_array(3, $enable_field)) checked @endif>
            <label class="form-check-label" for="enable_field_3">{{ __('Start Time') }}</label>
        </div>
        <div class="form-check custom-checkbox form-group col-md-3">
            <input type="checkbox" class="form-check-input" name="enable_field[]" value="4" id="enable_field_4"
                @if (in_array(4, $enable_field)) checked @endif>
            <label class="form-check-label" for="enable_field_4">{{ __('End Time') }}</label>
        </div>

        <hr>
        <div class="form-group col-md-12">
            <h6>{{ __('Questions & Custom Field') }}</h6>
                @foreach ($question as $q)
                    <div class="form-check custom-checkbox">
                        <input type="checkbox" class="form-check-input" name="question_id[]"
                            value="{{ $q->id }}" @if ($q->is_required == 'on') required @endif
                            id="question_{{ $q->id }}" {{ in_array($q->id, $questions) ? 'checked' : '' }}>
                        <label class="form-check-label" for="question_{{ $q->id }}">{{ $q->question }}
                            @if ($q->is_required == 'on')
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                    </div>
                @endforeach
        </div>

        <hr>
        <div class="form-group col-md-6">
            {{ Form::label('meeting_type', __('Meeting Type'), ['class' => 'col-form-label']) }}
            @foreach ($meetings as $key => $meeting)
                @php
                    $alias_name = Module_Alias_Name($meeting);
                @endphp
                <div class="form-check custom-checkbox">
                    <input type="checkbox" class="form-check-input" name="meeting_type[]" value="{{ $meeting }}" id="{{ $key }}" {{ in_array($meeting, $meeting_type) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $key }}">{{ $alias_name }}
                    </label>
                </div>
            @endforeach
        </div>

        <div class="col-md-6 form-group">
            {{ Form::label('enable', __('Enable:'), ['class' => 'col-form-label']) }}
            <div class="form-check form-switch custom-switch-v1">
                <input type="hidden" name="is_enabled" value="off">
                <input type="checkbox" class="form-check-input input-primary" id="customswitchv1-1 is_enabled"
                    name="is_enabled"
                    {{ isset($appointment) && $appointment->is_enabled == 'on' ? 'checked="checked"' : '' }}>
            </div>
        </div>
        @if (module_is_active('CustomField') && !$customFields->isEmpty())
        <div class="col-md-6 form-group">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customfield::formBuilder', ['fildedata' => $appointment->customField])
                </div>
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Update') }}</button>
</div>

{{ Form::close() }}
