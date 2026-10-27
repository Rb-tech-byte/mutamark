@php
    $company_settings = getCompanyAllSetting();
@endphp

{{ Form::open(['url' => 'callbacks/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="card-body pb-0 pt-2">
        <dl class="row mb-0 align-items-center">
            <dt class="col-sm-4 h6 text-sm">{{ __('Name') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($callbacks->schedule->name) ? $callbacks->schedule->name : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Email') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($callbacks->schedule->email) ? $callbacks->schedule->email : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Phone') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($callbacks->schedule->phone) ? $callbacks->schedule->phone : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Date') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ company_date_formate($callbacks->date) }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Start Time') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ company_time_formate($callbacks->start_time) }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('End Time') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ company_time_formate($callbacks->end_time) }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Appointment') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($callbacks->appointment_id) ? $callbacks->appointment->name : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Status') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($callbacks->status) ? $callbacks->status : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Meeting Type') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($callbacks->schedule->meeting_type) ? $callbacks->schedule->meeting_type : '-' }}<br>
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Assign User') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($callbacks->users->name) ? $callbacks->users->name : '-' }}<br>
            </dd>
        </dl>
    </div>

    <input type="hidden" value="{{ $callbacks->id }}" name="callback_id">
    @if ($callbacks->status == 'Pending')
        @if (module_is_active('Calender') && $company_settings['google_calendar_enable'] == 'on')
            <hr style="color: #e3e3e3;">
            @include('calender::setting.synchronize')
        @endif
    @endif

</div>

@permission('schedule action')
    @if ($callbacks->status == 'Pending')
        <div class="modal-footer">
            <input type="submit" value="Approved" class="btn btn-success rounded" name="status">
            <input type="submit" value="Reject" class="btn btn-danger rounded" name="status" id="rejectButton">
        </div>
    @elseif($callbacks->status == 'Approved')
        <div class="modal-footer">
            <input type="submit" value="Complete" class="btn btn-info rounded" name="status">
        </div>
    @endif
@endpermission

{{ Form::close() }}

<script>
    // Add JavaScript to disable the dropdown when the Reject button is clicked
    document.getElementById('rejectButton').addEventListener('click', function() {
        document.getElementById('userDropdown').disabled = true;
    });
</script>
