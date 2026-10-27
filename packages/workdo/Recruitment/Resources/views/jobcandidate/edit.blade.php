@extends('layouts.main')
@section('page-title')
    {{ __('Manage Job Candidate') }}
@endsection
@section('page-breadcrumb')
    {{ __('Job Candidate') }}
@endsection

@push('css')
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush

@section('content')
@php
    $tab = session('experience-tab');
@endphp
    <div class="row">        
        <div class="col-sm-12">
            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end mb-4">
                <div class="col-md-6">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ ($tab == false) ? 'active' : '' }} " id="details" data-bs-toggle="pill"
                                data-bs-target="#details-tab" type="button">{{ __('Details') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ ($tab == true) ? 'active' : '' }}" id="experience" data-bs-toggle="pill" data-bs-target="#experience-tab"
                                type="button">{{ __('Experience') }}</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade  {{ ($tab == false) ? 'show active' : '' }}" id="details-tab" role="tabpanel"
                            aria-labelledby="pills-user-tab-1">
                            {{ Form::model($job_candidates, ['route' => ['job-candidates.update', $job_candidates->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<span
                                        class="text-danger">*</span>
                                    <div class="form-icon-user">
                                        {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Name']) }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}<span
                                        class="text-danger">*</span>
                                    <div class="form-icon-user">
                                        {{ Form::email('email', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Email']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}<span
                                    class="text-danger">*</span>
                                    {{ Form::text('phone', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Phone Number']) }}
                                    <div class=" text-xs text-danger">
                                        {{ __('Please add mobile number with country code. (ex. +91)') }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    {{ Form::date('dob', null, ['class' => 'form-control ', 'required' => 'required', 'autocomplete' => 'off', 'max' => date('Y-m-d')]) }}
                                </div>
                                <div class="form-group col-md-6 gender">
                                    {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}
                                    <div class="d-flex radio-check">
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="g_male" value="Male" name="gender"
                                                class="form-check-input"
                                                {{ isset($job_candidates->gender) && $job_candidates->gender == 'Male' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="g_female" value="Female" name="gender"
                                                class="form-check-input"
                                                {{ isset($job_candidates->gender) && $job_candidates->gender == 'Female' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}<span
                                    class="text-danger">*</span>
                                    <div class="input-group">
                                        {{ Form::textarea('address', null, ['class' => 'form-control', 'required' => 'required', 'rows' => 2, 'placeholder' => 'Enter Address']) }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}<span
                                        class="text-danger">*</span>
                                    <div class="form-icon-user">
                                        {{ Form::text('country', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Country']) }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('state', __('State'), ['class' => 'form-label']) }}<span
                                        class="text-danger">*</span>
                                    <div class="form-icon-user">
                                        {{ Form::text('state', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter State']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {{ Form::label('city', __('City'), ['class' => 'form-label']) }}<span
                                        class="text-danger">*</span>
                                    <div class="form-icon-user">
                                        {{ Form::text('city', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter City']) }}
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('profile', __('Profile'), ['class' => 'form-label']) }}<span
                                    class="text-danger">*</span>
                                    <div class="choose-file form-group">
                                        <label for="profile" class="form-label d-block">
                                            <input type="file" class="form-control file" name="profile" id="profile"
                                                data-filename="profile"
                                                onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                                            <hr>
                                            <div class="mt-1">
                                                <img src="@if ($job_candidates->profile) {{ get_file($job_candidates->profile) }} @endif" id="blah" width="15%" />
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('resume', __('CV / Resume'), ['class' => 'form-label']) }}<span
                                    class="text-danger">*</span>
                                    <div class="choose-file form-group">
                                        <label for="resume" class="form-label d-block">
                                            <input type="file" class="form-control file" name="resume" id="resume"
                                                data-filename="resume"
                                                onchange="document.getElementById('blah1').src = window.URL.createObjectURL(this.files[0])">
                                            <hr>
                                            <div class="mt-1">
                                                <img src="@if ($job_candidates->resume) {{ get_file($job_candidates->resume) }} @endif" id="blah1" width="15%" />
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                    <textarea class="tox-target summernote" id="description" name="description" rows="8">{!! $job_candidates->description !!}</textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col"></div>
                                <div class="col-6 text-end">
                                    <input type="submit" value="{{ __('Save Changes') }}" class="btn  btn-primary">
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                        <div class="tab-pane fade {{ ($tab == true) ? 'show active' : '' }}" id="experience-tab" role="tabpanel"
                            aria-labelledby="pills-user-tab-2">
                            <div class="row">
                                @permission('job experience manage')
                                    <div class="col-sm-12">
                                        <div class="card set-card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h5>{{ __('Experience') }}</h5>
                                                    </div>
                                                    @permission('job experience create')
                                                        <div class="col-6 text-end create_btn">
                                                            <a data-url="{{ route('job-experience.create', ['id' => $job_candidates->id]) }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-title="{{ __('Create Job Experience') }}"
                                                                data-bs-toggle="tooltip" title=""
                                                                class="btn btn-sm btn-primary"
                                                                data-bs-original-title="{{ __('Create') }}">
                                                                <i class="ti ti-plus"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                </div>
                                            </div>
                                            <div class="card-body table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Title') }}</th>
                                                                <th>{{ __('Organization') }}</th>
                                                                <th>{{ __('Start Date') }}</th>
                                                                <th>{{ __('End Date') }}</th>
                                                                <th>{{ __('Country') }}</th>
                                                                <th>{{ __('State') }}</th>
                                                                <th>{{ __('City') }}</th>
                                                                <th>{{ __('Experience Document') }}</th>
                                                                @if (Laratrust::hasPermission('job experience show') || Laratrust::hasPermission('job experience edit') || Laratrust::hasPermission('job experience delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($job_experiences as $job_experience)
                                                                <tr>
                                                                    <td>{{ !empty($job_experience->title) ? $job_experience->title : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_experience->organization) ? $job_experience->organization : '' }}
                                                                    </td>

                                                                    <td>{{ !empty($job_experience->start_date) ? company_date_formate($job_experience->start_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_experience->end_date) ? company_date_formate($job_experience->end_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_experience->country) ? $job_experience->country : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_experience->state) ? $job_experience->state : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_experience->city) ? $job_experience->city : '' }}
                                                                    </td>
                                                                    <td>
                                                                        @if (!empty($job_experience->experience_proof))
                                                                            <div class="action-btn bg-primary ms-2">
                                                                                <a class="mx-3 btn btn-sm align-items-center"
                                                                                    href="{{ get_file($job_experience->experience_proof) }}"
                                                                                    download>
                                                                                    <i class="ti ti-download text-white"></i>
                                                                                </a>
                                                                            </div>
                                                                            <div class="action-btn bg-secondary ms-2">
                                                                                <a class="mx-3 btn btn-sm align-items-center"
                                                                                    href="{{ get_file($job_experience->experience_proof) }}"
                                                                                    target="_blank">
                                                                                    <i class="ti ti-crosshair text-white"
                                                                                        data-bs-toggle="tooltip"
                                                                                        data-bs-original-title="{{ __('Preview') }}"></i>
                                                                                </a>
                                                                            </div>
                                                                        @else
                                                                            <p>-</p>
                                                                        @endif
                                                                    </td>
                                                                    @if (Laratrust::hasPermission('job experience show') || Laratrust::hasPermission('job experience edit') || Laratrust::hasPermission('job experience delete'))
                                                                        <td class="Action">
                                                                            <span>
                                                                                @permission('job experience show')
                                                                                    <div
                                                                                        class="action-btn bg-warning ms-2 show_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-experience.show', $job_experience->id) }}"
                                                                                            data-ajax-popup="true" data-size="lg"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Show Job Experience') }}"
                                                                                            data-bs-original-title="{{ __('Show Job Experience') }}">
                                                                                            <i class="ti ti-eye text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job experience edit')
                                                                                    <div class="action-btn bg-info ms-2 edit_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-experience.edit', $job_experience->id) }}"
                                                                                            data-ajax-popup="true" data-size="lg"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Edit Job Experience') }}"
                                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                                            <i class="ti ti-pencil text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job experience delete')
                                                                                    <div
                                                                                        class="action-btn bg-danger ms-2 delete_btn">
                                                                                        {{ Form::open(['route' => ['job-experience.destroy', $job_experience->id], 'class' => 'm-0']) }}
                                                                                        @method('DELETE')
                                                                                        <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-bs-original-title="Delete"
                                                                                            aria-label="Delete"
                                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                            data-confirm-yes="delete-form-{{ $job_experience->id }}"><i
                                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                                        {{ Form::close() }}
                                                                                    </div>
                                                                                @endpermission
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @empty
                                                                @include('layouts.nodatafound')
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endpermission
                            </div>
                            <div class="row">
                                @permission('job project manage')
                                    <div class="col-sm-6">
                                        <div class="card set-card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h5>{{ __('Project') }}</h5>
                                                    </div>
                                                    @permission('job project create')
                                                        <div class="col-6 text-end create_btn">
                                                            <a data-url="{{ route('job-project.create', ['id' => $job_candidates->id]) }}"
                                                                data-ajax-popup="true" data-title="{{ __('Create Job Project') }}"
                                                                data-bs-toggle="tooltip" title="" data-size="lg"
                                                                class="btn btn-sm btn-primary"
                                                                data-bs-original-title="{{ __('Create') }}">
                                                                <i class="ti ti-plus"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                </div>
                                            </div>
                                            <div class="card-body table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Title') }}</th>
                                                                <th>{{ __('Organization') }}</th>
                                                                <th>{{ __('Start Date') }}</th>
                                                                <th>{{ __('End Date') }}</th>
                                                                <th>{{ __('Reference') }}</th>
                                                                @if (Laratrust::hasPermission('job project show') || Laratrust::hasPermission('job project edit') || Laratrust::hasPermission('job project delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($job_projects as $job_project)
                                                                <tr>
                                                                    <td>{{ !empty($job_project->title) ? $job_project->title : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_project->organization) ? $job_project->organization : '' }}
                                                                    </td>

                                                                    <td>{{ !empty($job_project->start_date) ? company_date_formate($job_project->start_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_project->end_date) ? company_date_formate($job_project->end_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_project->reference) ? ($job_project->reference == 'yes' ? 'Yes' : 'No') : '' }}
                                                                    </td>
                                                                    @if (Laratrust::hasPermission('job project show') || Laratrust::hasPermission('job project edit') || Laratrust::hasPermission('job project delete'))
                                                                        <td class="Action">
                                                                            <span>
                                                                                @permission('job project show')
                                                                                    <div
                                                                                        class="action-btn bg-warning ms-2 show_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-project.show', $job_project->id) }}"
                                                                                            data-ajax-popup="true" data-size="md"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Show Job Project') }}"
                                                                                            data-bs-original-title="{{ __('Show Job Project') }}">
                                                                                            <i class="ti ti-eye text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job project edit')
                                                                                    <div class="action-btn bg-info ms-2 edit_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-project.edit', $job_project->id) }}"
                                                                                            data-ajax-popup="true" data-size="lg"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Edit Job Project') }}"
                                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                                            <i class="ti ti-pencil text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job project delete')
                                                                                    <div
                                                                                        class="action-btn bg-danger ms-2 delete_btn">
                                                                                        {{ Form::open(['route' => ['job-project.destroy', $job_project->id], 'class' => 'm-0']) }}
                                                                                        @method('DELETE')
                                                                                        <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-bs-original-title="Delete"
                                                                                            aria-label="Delete"
                                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                            data-confirm-yes="delete-form-{{ $job_project->id }}"><i
                                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                                        {{ Form::close() }}
                                                                                    </div>
                                                                                @endpermission
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @empty
                                                                @include('layouts.nodatafound')
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endpermission
                                @permission('experience candidate job manage')
                                    <div class="col-sm-6">
                                        <div class="card set-card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h5>{{ __('Jobs') }}</h5>
                                                    </div>
                                                    @permission('experience candidate job create')
                                                        <div class="col-6 text-end create_btn">
                                                            <a data-url="{{ route('job-experience-candidate.create', ['id' => $job_candidates->id]) }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-title="{{ __('Create Experience Candidate Job') }}"
                                                                data-bs-toggle="tooltip" title=""
                                                                class="btn btn-sm btn-primary"
                                                                data-bs-original-title="{{ __('Create') }}">
                                                                <i class="ti ti-plus"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                </div>
                                            </div>
                                            <div class="card-body table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Title') }}</th>
                                                                <th>{{ __('Organization') }}</th>
                                                                <th>{{ __('Start Date') }}</th>
                                                                <th>{{ __('End Date') }}</th>
                                                                <th>{{ __('Reference') }}</th>
                                                                @if (Laratrust::hasPermission('experience candidate job show') || Laratrust::hasPermission('experience candidate job edit') || Laratrust::hasPermission('experience candidate job delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($job_experience_candidates as $job_experience_candidate)
                                                                <tr>
                                                                    <td>{{ !empty($job_experience_candidate->title) ? $job_experience_candidate->title : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_experience_candidate->organization) ? $job_experience_candidate->organization : '' }}
                                                                    </td>

                                                                    <td>{{ !empty($job_experience_candidate->start_date) ? company_date_formate($job_experience_candidate->start_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_experience_candidate->end_date) ? company_date_formate($job_experience_candidate->end_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_experience_candidate->reference) ? ($job_experience_candidate->reference == 'yes' ? 'Yes' : 'No') : '' }}
                                                                    </td>
                                                                    @if (Laratrust::hasPermission('experience candidate job show') || Laratrust::hasPermission('experience candidate job edit') || Laratrust::hasPermission('experience candidate job delete'))
                                                                        <td class="Action">
                                                                            <span>
                                                                                @permission('experience candidate job show')
                                                                                    <div
                                                                                        class="action-btn bg-warning ms-2 show_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-experience-candidate.show', $job_experience_candidate->id) }}"
                                                                                            data-ajax-popup="true" data-size="md"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Show Experience Candidate Job') }}"
                                                                                            data-bs-original-title="{{ __('Show Experience Candidate Job') }}">
                                                                                            <i class="ti ti-eye text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('experience candidate job edit')
                                                                                    <div class="action-btn bg-info ms-2 edit_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-experience-candidate.edit', $job_experience_candidate->id) }}"
                                                                                            data-ajax-popup="true" data-size="lg"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Edit Experience Candidate Job') }}"
                                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                                            <i class="ti ti-pencil text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('experience candidate job delete')
                                                                                    <div
                                                                                        class="action-btn bg-danger ms-2 delete_btn">
                                                                                        {{ Form::open(['route' => ['job-experience-candidate.destroy', $job_experience_candidate->id], 'class' => 'm-0']) }}
                                                                                        @method('DELETE')
                                                                                        <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-bs-original-title="Delete"
                                                                                            aria-label="Delete"
                                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                            data-confirm-yes="delete-form-{{ $job_experience_candidate->id }}"><i
                                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                                        {{ Form::close() }}
                                                                                    </div>
                                                                                @endpermission
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @empty
                                                                @include('layouts.nodatafound')
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endpermission
                            </div>

                            <div class="row">
                                @permission('job qualification manage')
                                    <div class="col-sm-6">
                                        <div class="card set-card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h5>{{ __('Qualifications') }}</h5>
                                                    </div>
                                                    @permission('job qualification create')
                                                        <div class="col-6 text-end create_btn">
                                                            <a data-url="{{ route('job-qualification.create', ['id' => $job_candidates->id]) }}"
                                                                data-ajax-popup="true" data-size="lg"
                                                                data-title="{{ __('Create Job Qualification') }}"
                                                                data-bs-toggle="tooltip" title=""
                                                                class="btn btn-sm btn-primary"
                                                                data-bs-original-title="{{ __('Create') }}">
                                                                <i class="ti ti-plus"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                </div>
                                            </div>
                                            <div class="card-body table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Title') }}</th>
                                                                <th>{{ __('Organization') }}</th>
                                                                <th>{{ __('Start Date') }}</th>
                                                                <th>{{ __('End Date') }}</th>
                                                                <th>{{ __('Reference') }}</th>
                                                                @if (Laratrust::hasPermission('job qualification show') || Laratrust::hasPermission('job qualification edit') || Laratrust::hasPermission('job qualification delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($job_qualifications as $job_qualification)
                                                                <tr>
                                                                    <td>{{ !empty($job_qualification->title) ? $job_qualification->title : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_qualification->organization) ? $job_qualification->organization : '' }}
                                                                    </td>

                                                                    <td>{{ !empty($job_qualification->start_date) ? company_date_formate($job_qualification->start_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_qualification->end_date) ? company_date_formate($job_qualification->end_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_qualification->reference) ? ($job_qualification->reference == 'yes' ? 'Yes' : 'No') : '' }}
                                                                    </td>
                                                                    @if (Laratrust::hasPermission('job qualification show') || Laratrust::hasPermission('job qualification edit') || Laratrust::hasPermission('job qualification delete'))
                                                                        <td class="Action">
                                                                            <span>
                                                                                @permission('job qualification show')
                                                                                    <div
                                                                                        class="action-btn bg-warning ms-2 show_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-qualification.show', $job_qualification->id) }}"
                                                                                            data-ajax-popup="true" data-size="md"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Show Job Qualification') }}"
                                                                                            data-bs-original-title="{{ __('Show Job Qualification') }}">
                                                                                            <i class="ti ti-eye text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job qualification edit')
                                                                                    <div class="action-btn bg-info ms-2 edit_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-qualification.edit', $job_qualification->id) }}"
                                                                                            data-ajax-popup="true" data-size="lg"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Edit Job Qualification') }}"
                                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                                            <i class="ti ti-pencil text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job qualification delete')
                                                                                    <div
                                                                                        class="action-btn bg-danger ms-2 delete_btn">
                                                                                        {{ Form::open(['route' => ['job-qualification.destroy', $job_qualification->id], 'class' => 'm-0']) }}
                                                                                        @method('DELETE')
                                                                                        <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-bs-original-title="Delete"
                                                                                            aria-label="Delete"
                                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                            data-confirm-yes="delete-form-{{ $job_qualification->id }}"><i
                                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                                        {{ Form::close() }}
                                                                                    </div>
                                                                                @endpermission
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @empty
                                                                @include('layouts.nodatafound')
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endpermission
                                @permission('job skill manage')
                                    <div class="col-sm-6">
                                        <div class="card set-card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h5>{{ __('Skills') }}</h5>
                                                    </div>
                                                    @permission('job skill create')
                                                        <div class="col-6 text-end create_btn">
                                                            <a data-url="{{ route('job-skill.create', ['id' => $job_candidates->id]) }}" data-ajax-popup="true"
                                                                data-title="{{ __('Create Job Skill') }}"
                                                                data-bs-toggle="tooltip" title="" data-size="lg"
                                                                class="btn btn-sm btn-primary"
                                                                data-bs-original-title="{{ __('Create') }}">
                                                                <i class="ti ti-plus"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                </div>
                                            </div>
                                            <div class="card-body table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Type') }}</th>
                                                                <th>{{ __('Title') }}</th>
                                                                <th>{{ __('Organization') }}</th>
                                                                <th>{{ __('Start Date') }}</th>
                                                                <th>{{ __('End Date') }}</th>
                                                                <th>{{ __('Reference') }}</th>
                                                                @if (Laratrust::hasPermission('job skill show') || Laratrust::hasPermission('job skill edit') || Laratrust::hasPermission('job skill delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($job_skills as $job_skill)
                                                                <tr>
                                                                    <td>{{ !empty($job_skill->type) ? $job_skill->type : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_skill->title) ? $job_skill->title : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_skill->organization) ? $job_skill->organization : '' }}
                                                                    </td>

                                                                    <td>{{ !empty($job_skill->start_date) ? company_date_formate($job_skill->start_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_skill->end_date) ? company_date_formate($job_skill->end_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_skill->reference) ? ($job_skill->reference == 'yes' ? 'Yes' : 'No') : '' }}
                                                                    </td>
                                                                    @if (Laratrust::hasPermission('job skill show') || Laratrust::hasPermission('job skill edit') || Laratrust::hasPermission('job skill delete'))
                                                                        <td class="Action">
                                                                            <span>
                                                                                @permission('job skill show')
                                                                                    <div
                                                                                        class="action-btn bg-warning ms-2 show_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-skill.show', $job_skill->id) }}"
                                                                                            data-ajax-popup="true" data-size="md"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Show Job Skill') }}"
                                                                                            data-bs-original-title="{{ __('Show Job Skill') }}">
                                                                                            <i class="ti ti-eye text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job skill edit')
                                                                                    <div class="action-btn bg-info ms-2 edit_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-skill.edit', $job_skill->id) }}"
                                                                                            data-ajax-popup="true" data-size="lg"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Edit Job Skill') }}"
                                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                                            <i class="ti ti-pencil text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job skill delete')
                                                                                    <div
                                                                                        class="action-btn bg-danger ms-2 delete_btn">
                                                                                        {{ Form::open(['route' => ['job-skill.destroy', $job_skill->id], 'class' => 'm-0']) }}
                                                                                        @method('DELETE')
                                                                                        <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-bs-original-title="Delete"
                                                                                            aria-label="Delete"
                                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                            data-confirm-yes="delete-form-{{ $job_skill->id }}"><i
                                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                                        {{ Form::close() }}
                                                                                    </div>
                                                                                @endpermission
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @empty
                                                                @include('layouts.nodatafound')
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endpermission
                            </div>

                            <div class="row">
                                @permission('job award manage')
                                    <div class="col-sm-6">
                                        <div class="card set-card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h5>{{ __('Awards') }}</h5>
                                                    </div>
                                                    @permission('job award create')
                                                        <div class="col-6 text-end create_btn">
                                                            <a data-url="{{ route('job-award.create', ['id' => $job_candidates->id]) }}" data-ajax-popup="true"
                                                                data-title="{{ __('Create job Award') }}"
                                                                data-bs-toggle="tooltip" title="" data-size="lg"
                                                                class="btn btn-sm btn-primary"
                                                                data-bs-original-title="{{ __('Create') }}">
                                                                <i class="ti ti-plus"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                </div>
                                            </div>
                                            <div class="card-body table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Title') }}</th>
                                                                <th>{{ __('Organization') }}</th>
                                                                <th>{{ __('Start Date') }}</th>
                                                                <th>{{ __('End Date') }}</th>
                                                                <th>{{ __('Reference') }}</th>
                                                                @if (Laratrust::hasPermission('job award show') || Laratrust::hasPermission('job award edit') || Laratrust::hasPermission('job award delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($job_awards as $job_award)
                                                                <tr>
                                                                    <td>{{ !empty($job_award->title) ? $job_award->title : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_award->organization) ? $job_award->organization : '' }}
                                                                    </td>

                                                                    <td>{{ !empty($job_award->start_date) ? company_date_formate($job_award->start_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_award->end_date) ? company_date_formate($job_award->end_date) : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($job_award->reference) ? ($job_award->reference == 'yes' ? 'Yes' : 'No') : '' }}
                                                                    </td>
                                                                    @if (Laratrust::hasPermission('job award show') || Laratrust::hasPermission('job award edit') || Laratrust::hasPermission('job award delete'))
                                                                        <td class="Action">
                                                                            <span>
                                                                                @permission('job award show')
                                                                                    <div
                                                                                        class="action-btn bg-warning ms-2 show_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-award.show', $job_award->id) }}"
                                                                                            data-ajax-popup="true" data-size="md"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Show Job Award') }}"
                                                                                            data-bs-original-title="{{ __('Show Job Award') }}">
                                                                                            <i class="ti ti-eye text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job award edit')
                                                                                    <div class="action-btn bg-info ms-2 edit_btn">
                                                                                        <a class="mx-3 btn btn-sm  align-items-center"
                                                                                            data-url="{{ route('job-award.edit', $job_award->id) }}"
                                                                                            data-ajax-popup="true" data-size="lg"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-title="{{ __('Edit Job Award') }}"
                                                                                            data-bs-original-title="{{ __('Edit') }}">
                                                                                            <i class="ti ti-pencil text-white"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endpermission
                                                                                @permission('job award delete')
                                                                                    <div
                                                                                        class="action-btn bg-danger ms-2 delete_btn">
                                                                                        {{ Form::open(['route' => ['job-award.destroy', $job_award->id], 'class' => 'm-0']) }}
                                                                                        @method('DELETE')
                                                                                        <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                                            data-bs-toggle="tooltip"
                                                                                            title=""
                                                                                            data-bs-original-title="Delete"
                                                                                            aria-label="Delete"
                                                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                                            data-confirm-yes="delete-form-{{ $job_award->id }}"><i
                                                                                                class="ti ti-trash text-white text-white"></i></a>
                                                                                        {{ Form::close() }}
                                                                                    </div>
                                                                                @endpermission
                                                                            </span>
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @empty
                                                                @include('layouts.nodatafound')
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endpermission
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
