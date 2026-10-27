<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered ">
            <tr role="row">
                <th>{{ __('Appointment Name') }}</th>
                <td>{{ !empty($appointment->name) ? $appointment->name : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('Appointment Type') }}</th>
                <td>{{ !empty($appointment->appointment_type) ? $appointment->appointment_type : '' }}</td>
            </tr>
            {{-- <tr>
                <th>{{ __('Date') }}</th>
                <td>{{ company_date_formate($appointment->date) }}</td>
            </tr>
            <tr>
                <th>{{ __('Starting Time') }}</th>
                <td>{{ company_time_formate($appointment->start_time) }}</td>
            </tr>
            <tr>
                <th>{{ __('Ending Time') }}</th>
                <td>{{ company_time_formate($appointment->end_time) }}</td>
            </tr> --}}
            <tr>
                <th>{{ __('Week Day') }}</th>
                <td>{{ !empty($appointment->week_day) ? $appointment->week_day : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('Is Enabled') }}</th>
                <td>{{ !empty($appointment->id) ? $appointment->is_enabled == 'on' ? 'Yes' : 'No' : '' }}</td>
            </tr>
            @if (!empty($customFields) && count($appointment->customField) > 0)
                @foreach ($customFields as $field)
                    <tr>
                        <th>{{ $field->name }}</th>
                        <td>
                            @if ($field->type == 'attachment')
                                <a href="{{ get_file($appointment->customField[$field->id]) }}" target="_blank">
                                    <img src="{{ get_file($appointment->customField[$field->id]) }}"
                                        class="wid-75 rounded me-3">
                                </a>
                            @else
                                {{ !empty($appointment->customField[$field->id]) ? $appointment->customField[$field->id] : '-' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
        </table>
    </div>
</div>
