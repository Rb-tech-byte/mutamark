<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered ">
            <tr role="row">
                <th>{{ __('Name') }}</th>
                <td>{{ !empty($schedule->name) ? $schedule->name : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('Email ') }}</th>
                <td>{{ !empty($schedule->email) ? $schedule->email : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('Phone ') }}</th>
                <td>{{ !empty($schedule->phone) ? $schedule->phone : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('Date') }}</th>
                <td>{{ company_date_formate($schedule->date) }}</td>
            </tr>
            <tr>
                <th>{{ __('Start Time') }}</th>
                <td>{{ company_time_formate($schedule->start_time) }}</td>
            </tr>
            <tr>
                <th>{{ __('End Time') }}</th>
                <td>{{ company_time_formate($schedule->end_time) }}</td>
            </tr>
            <tr>
                <th>{{ __('Appointment') }}</th>
                <td>{{ !empty($schedule->appointment_id) ? $schedule->appointment->name : '' }}</td>
            </tr>
            <tr>
                <th>{{ __('Status') }}</th>
                <td>{{ !empty($schedule->status) ? $schedule->status : '' }}</td>
            </tr>
        </table>
    </div>
</div>
