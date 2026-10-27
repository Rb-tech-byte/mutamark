@php
    $company_settings = getCompanyAllSetting();
@endphp
{{ Form::open(['url' => 'schedules/changeaction', 'method' => 'post']) }}
<div class="modal-body">
    <div class="card-body pb-0 pt-2">
        <dl class="row mb-0 align-items-center">
            <dt class="col-sm-4 h6 text-sm">{{ __('Name') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->name) ? $schedule->name : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Email') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->email) ? $schedule->email : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Phone') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->phone) ? $schedule->phone : '-' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Date') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->date) ? company_date_formate($schedule->date) : '-' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Start Time') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->start_time) ? company_time_formate($schedule->start_time) : '-' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('End Time') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->end_time) ? company_time_formate($schedule->end_time) : '-' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Appointment') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->appointment_id) ? $schedule->appointment->name : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Status') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->status) ? $schedule->status : '' }}
            </dd>
            <dt class="col-sm-4 h6 text-sm">{{ __('Meeting Type') }}</dt>
            <dd class="col-sm-8 text-sm">
                {{ !empty($schedule->meeting_type) ? $schedule->meeting_type : '-' }}<br>

                @if (
                    $schedule->meeting_type == 'Zoom Meeting' &&
                        (empty($company_settings['zoom_account_id']) ||
                            empty($company_settings['zoom_client_id']) ||
                            empty($company_settings['zoom_client_secret'])))
                    <span class="text-danger">{{ __('Please first add zoom meeting credential ') }}<a
                            href="{{ url('settings#zoom-sidenav') }}" target="_blank">{{ __('here') }}</a>.</span>
                @endif

                {{-- @if (!empty(check_file($company_settings['google_meet_json_file'])) && !empty($company_settings['google_meet_json_file']))
                    <span
                        class="text-danger">{{ __('You have not authorized your google account to Create Google Meeting. Click ') }}
                        <a href="{{ route('auth.googlemeet') }}"
                            target="_blank">{{ __('here') }}</a>{{ __(' to authorize.') }}</span>
                @endif --}}

                @if (
                    $schedule->meeting_type == 'Google Meet' &&
                        (empty(check_file(company_setting('google_meet_json_file'))) &&
                            empty(company_setting('google_meet_json_file'))))
                    <span class="text-danger">{{ __('Please first add Google meet credential ') }}<a
                            href="{{ url('settings#googlemeet-sidenav') }}"
                            target="_blank">{{ __('here') }}</a>.</span>
                @endif

            </dd>

        </dl>
    </div>
    @if (!empty($questions))
        <div class="card-body pb-0 pt-2">
            <hr style="color: #e3e3e3;">
            <h6 class="mb-4">{{ __('Questions') }}</h6>
            <dl class="row mb-0 align-items-center">
                @foreach ($questions as $key => $question)
                    <dt class="col-sm-6 h6 text-sm">{{ $key }}</dt>
                    <dd class="col-sm-6 text-sm">
                        @if (is_array($question))
                            {{ implode(', ', $question) }}
                        @else
                            {{ $question }}
                        @endif
                    </dd>
                @endforeach
            </dl>
        </div>
    @endif

    @if (!empty($users) && $schedule->status == 'Pending')
        <div class="card-body pb-0 pt-2">
            <hr style="color: #e3e3e3;">
            <h6 class="mb-3">{{ __('Assign User') }}</h6>
            <div class="col-md-10">
                <select class="form-control" name="user_id" required id="userDropdown">
                    <option value="">{{ __('Select Users') }}
                    </option>
                    @foreach ($users as $key => $user)
                        <option value="{{ $key }}">
                            {{ $user }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    <input type="hidden" value="{{ $schedule->id }}" name="schedule_id">
    @if ($schedule->status == 'Pending')
        @if (module_is_active('Calender') && !empty($company_settings['google_calendar_enable']) && $company_settings['google_calendar_enable'] == 'on')
            <hr style="color: #e3e3e3;">
            @include('calender::setting.synchronize')
        @endif
    @endif

    @if (module_is_active('Feedback') && $schedule->status == 'Approved')
        <hr style="color: #e3e3e3;">
        <div class="form-check custom-checkbox custom-control custom-control-inline">
            @php
                $sendFeedbackValue = old('send_feedback') ? 'yes' : 'no';
            @endphp
            <input type="hidden" name="send_feedback" value="no">
            <input type="checkbox" class="form-check-input" name="send_feedback" id="send_feedback" value="yes"
                {{ old('send_feedback') ? 'checked' : '' }}>
            <label class="form-check-label" for="send_feedback">{{ __('Send Feedback Form') }}</label>
        </div>
    @endif


</div>

@permission('schedule action')
    @if ($schedule->status == 'Pending')
        <div class="modal-footer">
            <input type="submit" value="Approved" class="btn btn-success rounded" name="status">
            <input type="submit" value="Reject" class="btn btn-danger rounded" name="status" id="rejectButton">
        </div>
    @elseif($schedule->status == 'Approved')
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
