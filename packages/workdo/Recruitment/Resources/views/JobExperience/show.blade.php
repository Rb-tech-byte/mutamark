<div class="modal-body">
    <div class="row">
        <div class="col-sm-6">
            <div class="card-body pb-0 pt-2">
                <dl class="row mb-0 align-items-center">
                    <dt class="col-sm-4 h6 text-sm">{{ __('Title') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ !empty($job_experience->title) ? $job_experience->title : '-' }}
                    </dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('Start Date') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ !empty($job_experience->start_date) ? company_date_formate($job_experience->start_date) : '-' }}
                    </dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('Country') }}</dt>
                    <dd class="col-sm-8 text-sm">{{ !empty($job_experience->country) ? $job_experience->country : '-' }}
                    </dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('City') }}</dt>
                    <dd class="col-sm-8 text-sm">{{ !empty($job_experience->city) ? $job_experience->city : '-' }}
                    </dd>
                </dl>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card-body pb-0 pt-2">
                <dl class="row mb-0 align-items-center">
                    <dt class="col-sm-4 h6 text-sm">{{ __('Organization') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ $job_experience->organization ? $job_experience->organization : '-' }}</dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('End Date') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ $job_experience->end_date ? $job_experience->end_date : '-' }}</dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('State') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ $job_experience->state ? $job_experience->state : '-' }}</dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('Zip Code') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ $job_experience->zipcode ? $job_experience->zipcode : '-' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-body pb-0 pt-2">
                <dl class="row mb-0 align-items-center">
                    <dt class="col-sm-4 h6 text-sm">{{ __('Address') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ !empty($job_experience->address) ? $job_experience->address : '-' }}
                    </dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('Phone') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ !empty($job_experience->phone) ? $job_experience->phone : '-' }}
                    </dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('Email') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ !empty($job_experience->email) ? $job_experience->email : '-' }}
                    </dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('Website') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ !empty($job_experience->website) ? $job_experience->website : '-' }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-6">
            <div class="card-body pb-0 pt-2">
                <dl class="row mb-0 align-items-center">
                    <dt class="col-sm-4 h6 text-sm">{{ __('Reference Full Name') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ !empty($job_experience->full_name) ? $job_experience->full_name : '-' }}
                    </dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('Reference Phone') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ !empty($job_experience->reference_phone) ? $job_experience->reference_phone : '-' }}
                    </dd>
                </dl>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card-body pb-0 pt-2">
                <dl class="row mb-0 align-items-center">
                    <dt class="col-sm-4 h6 text-sm">{{ __('Reference Job Position') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ $job_experience->job_position ? $job_experience->job_position : '-' }}
                    </dd>
                    <dt class="col-sm-4 h6 text-sm">{{ __('Reference Email') }}</dt>
                    <dd class="col-sm-8 text-sm">
                        {{ $job_experience->reference_email ? $job_experience->reference_email : '-' }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
