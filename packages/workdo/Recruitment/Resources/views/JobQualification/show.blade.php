<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered ">
            <tr role="row">
                <th>{{ __('Title') }}</th>
                <td>{{ !empty($job_qualification->title) ? $job_qualification->title : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Organization ') }}</th>
                <td>{{ !empty($job_qualification->organization) ? $job_qualification->organization : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Start Date') }}</th>
                <td>{{ !empty($job_qualification->start_date) ? company_date_formate($job_qualification->start_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('End Date') }}</th>
                <td>{{ !empty($job_qualification->end_date) ? company_date_formate($job_qualification->end_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Country') }}</th>
                <td>{{ !empty($job_qualification->country) ? $job_qualification->country : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('State') }}</th>
                <td>{{ !empty($job_qualification->state) ? $job_qualification->state : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('City') }}</th>
                <td>{{ !empty($job_qualification->city) ? $job_qualification->city : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Reference') }}</th>
                <td>{{ !empty($job_qualification->reference) ? $job_qualification->reference == 'yes' ? 'Yes' : 'No' : '-' }}</td>
            </tr>
        </table>
    </div>
</div>