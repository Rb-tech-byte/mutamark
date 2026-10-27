<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered ">
            <tr role="row">
                <th>{{ __('Title') }}</th>
                <td>{{ !empty($job_award->title) ? $job_award->title : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Organization ') }}</th>
                <td>{{ !empty($job_award->organization) ? $job_award->organization : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Start Date') }}</th>
                <td>{{ !empty($job_award->start_date) ? company_date_formate($job_award->start_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('End Date') }}</th>
                <td>{{ !empty($job_award->end_date) ? company_date_formate($job_award->end_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Country') }}</th>
                <td>{{ !empty($job_award->country) ? $job_award->country : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('State') }}</th>
                <td>{{ !empty($job_award->state) ? $job_award->state : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('City') }}</th>
                <td>{{ !empty($job_award->city) ? $job_award->city : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Reference') }}</th>
                <td>{{ !empty($job_award->reference) ? $job_award->reference == 'yes' ? 'Yes' : 'No' : '-' }}</td>
            </tr>
        </table>
    </div>
</div>