<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered ">
            <tr role="row">
                <th>{{ __('Title') }}</th>
                <td>{{ !empty($job_project->title) ? $job_project->title : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Organization ') }}</th>
                <td>{{ !empty($job_project->organization) ? $job_project->organization : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Start Date') }}</th>
                <td>{{ !empty($job_project->start_date) ? company_date_formate($job_project->start_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('End Date') }}</th>
                <td>{{ !empty($job_project->end_date) ? company_date_formate($job_project->end_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Country') }}</th>
                <td>{{ !empty($job_project->country) ? $job_project->country : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('State') }}</th>
                <td>{{ !empty($job_project->state) ? $job_project->state : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('City') }}</th>
                <td>{{ !empty($job_project->city) ? $job_project->city : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Reference') }}</th>
                <td>{{ !empty($job_project->reference) ? $job_project->reference == 'yes' ? 'Yes' : 'No' : '-' }}</td>
            </tr>
        </table>
    </div>
</div>