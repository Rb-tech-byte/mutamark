<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered ">
            <tr role="row">
                <th>{{ __('Title') }}</th>
                <td>{{ !empty($job_experience_candidate->title) ? $job_experience_candidate->title : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Organization ') }}</th>
                <td>{{ !empty($job_experience_candidate->organization) ? $job_experience_candidate->organization : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Start Date') }}</th>
                <td>{{ !empty($job_experience_candidate->start_date) ? company_date_formate($job_experience_candidate->start_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('End Date') }}</th>
                <td>{{ !empty($job_experience_candidate->end_date) ? company_date_formate($job_experience_candidate->end_date) : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Country') }}</th>
                <td>{{ !empty($job_experience_candidate->country) ? $job_experience_candidate->country : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('State') }}</th>
                <td>{{ !empty($job_experience_candidate->state) ? $job_experience_candidate->state : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('City') }}</th>
                <td>{{ !empty($job_experience_candidate->city) ? $job_experience_candidate->city : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('Reference') }}</th>
                <td>{{ !empty($job_experience_candidate->reference) ? $job_experience_candidate->reference == 'yes' ? 'Yes' : 'No' : '-' }}</td>
            </tr>
        </table>
    </div>
</div>