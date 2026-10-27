<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered ">
            <tr role="row">
                <th>{{ __('Type') }}</th>
                <td>{{ !empty($job_skill->type) ? $job_skill->type : '-' }}</td>
            </tr>
            <tr role="row">
                <th>{{ __('Title') }}</th>
                <td>{{ !empty($job_skill->title) ? $job_skill->title : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Organization ') }}</th>
                <td>{{ !empty($job_skill->organization) ? $job_skill->organization : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Start Date') }}</th>
                <td>{{ !empty($job_skill->start_date) ? company_date_formate($job_skill->start_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('End Date') }}</th>
                <td>{{ !empty($job_skill->end_date) ? company_date_formate($job_skill->end_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Country') }}</th>
                <td>{{ !empty($job_skill->country) ? $job_skill->country : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('State') }}</th>
                <td>{{ !empty($job_skill->state) ? $job_skill->state : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('City') }}</th>
                <td>{{ !empty($job_skill->city) ? $job_skill->city : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Reference') }}</th>
                <td>{{ !empty($job_skill->reference) ? $job_skill->reference == 'yes' ? 'Yes' : 'No' : '-' }}</td>
            </tr>
        </table>
    </div>
</div>