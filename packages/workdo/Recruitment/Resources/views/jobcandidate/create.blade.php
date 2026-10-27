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
    <div class="row">
        <div class="col-sm-12">
            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end mb-4">
                <div class="col-md-6">
                    <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details" data-bs-toggle="pill"
                                data-bs-target="#details-tab" type="button">{{ __('Details') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="experience" data-bs-toggle="pill" data-bs-target="#experience-tab"
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
                        <div class="tab-pane fade show active" id="details-tab" role="tabpanel"
                            aria-labelledby="pills-user-tab-1">
                            {{ Form::open(['route' => 'job-candidates.store', 'enctype' => 'multipart/form-data']) }}
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
                                    {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
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
                                    {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                    <div class="d-flex radio-check">
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="g_male" value="Male" name="gender"
                                                class="form-check-input" required>
                                            <label class="form-check-label" for="g_male">{{ __('Male') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="g_female" value="Female" name="gender"
                                                class="form-check-input" required>
                                            <label class="form-check-label" for="g_female">{{ __('Female') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                                    <div class="input-group">
                                        {{ Form::textarea('address', null, ['class' => 'form-control', 'rows' => 2, 'required' => 'required', 'placeholder' => 'Enter Address']) }}
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
                                                onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])" required>
                                            <hr>
                                            <div class="mt-1">
                                                <img src="" id="blah" width="15%" />
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
                                                onchange="document.getElementById('blah1').src = window.URL.createObjectURL(this.files[0])" required>
                                            <hr>
                                            <div class="mt-1">
                                                <img src="" id="blah1" width="15%" />
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                    <textarea class="tox-target summernote" id="description" name="description" rows="8"></textarea>
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
                        <div class="tab-pane fade" id="experience-tab" role="tabpanel"
                            aria-labelledby="pills-user-tab-2">
                            <div class="row">
                                @permission('job experience manage')
                                    <div class="text-danger">
                                        <p class="items-center text-danger text-center">
                                            {{ __('Note : Please first create job candidate details') }}</p>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="card set-card">
                                            <div class="card-header">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <h5>{{ __('Experience') }}</h5>
                                                    </div>
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
                                                                @if (Laratrust::hasPermission('job experience show') ||
                                                                        Laratrust::hasPermission('job experience edit') ||
                                                                        Laratrust::hasPermission('job experience delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @include('layouts.nodatafound')
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
                                                                @if (Laratrust::hasPermission('job project show') ||
                                                                        Laratrust::hasPermission('job project edit') ||
                                                                        Laratrust::hasPermission('job project delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @include('layouts.nodatafound')
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
                                                        <h5>{{ __('Experience Candidate Jobs') }}</h5>
                                                    </div>
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
                                                                @if (Laratrust::hasPermission('experience candidate job show') ||
                                                                        Laratrust::hasPermission('experience candidate job edit') ||
                                                                        Laratrust::hasPermission('experience candidate job delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @include('layouts.nodatafound')
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
                                                                @if (Laratrust::hasPermission('job qualification show') ||
                                                                        Laratrust::hasPermission('job qualification edit') ||
                                                                        Laratrust::hasPermission('job qualification delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @include('layouts.nodatafound')
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
                                                                @if (Laratrust::hasPermission('job skill show') ||
                                                                        Laratrust::hasPermission('job skill edit') ||
                                                                        Laratrust::hasPermission('job skill delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @include('layouts.nodatafound')
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
                                                                @if (Laratrust::hasPermission('job award show') ||
                                                                        Laratrust::hasPermission('job award edit') ||
                                                                        Laratrust::hasPermission('job award delete'))
                                                                    <th>{{ __('Action') }}</th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @include('layouts.nodatafound')
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
