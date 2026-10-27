@extends('layouts.main')
@section('page-title')
    {{ __('Manage Job Candidate') }}
@endsection

@section('page-breadcrumb')
    {{ __('Job Candidate') }}
@endsection
@push('css')
    <link href="{{ asset('Modules/Recruitment/Resources/assets/css/bootstrap-tagsinput.css') }}" rel="stylesheet" />
    <link href="{{ asset('Modules/Recruitment/Resources/assets/css/custom.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
@endpush

@section('page-action')
    <div>
        @permission('job candidate create')
            <a href="{{ route('job-candidates.create') }}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip"
                data-bs-placement="top" data-title="{{ __('Create New Job Candidate') }}" title="{{ __('Create') }}">
                <i class="ti ti-plus text-white"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row">

        <div class="col-sm-12 col-lg-12 col-xl-12 col-md-12">
            <div class=" mt-2 " id="multiCollapseExample1" style="">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['job-candidates.index'], 'method' => 'get', 'id' => 'applicarion_filter']) }}
                        <div class="d-flex align-items-center justify-content-end">
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 col-6 mx-1">
                                <label class="form-label">{{ __('Gender') }}</label> <br>
                                <div class="form-check form-check-inline form-group">
                                    <input type="radio" id="male" value="male" name="gender"
                                        class="form-check-input"
                                        {{ isset($_GET['gender']) && $_GET['gender'] == 'male' ? 'checked' : 'checked' }}>
                                    <label class="form-check-label" for="male">{{ __('Male') }}</label>
                                </div>
                                <div class="form-check form-check-inline form-group">
                                    <input type="radio" id="female" value="female" name="gender"
                                        class="form-check-input"
                                        {{ isset($_GET['gender']) && $_GET['gender'] == 'female' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="female">{{ __('Female') }}</label>
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 col-6 mx-1">
                                <div class="btn-box">
                                    {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                    {{ Form::select('country', $job_candidate_country, $filter['country'], ['class' => 'form-control select ']) }}
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 col-6 mx-1">
                                <div class="btn-box">
                                    {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                    {{ Form::select('state', $job_candidate_state, $filter['state'], ['class' => 'form-control select ']) }}
                                </div>
                            </div>

                            <div class="col-auto float-end ms-2 mt-4">
                                <a class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('applicarion_filter').submit(); return false;"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="apply">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route('job-candidates.index') }}" class="btn btn-sm btn-danger"
                                    data-bs-toggle="tooltip" title="" data-bs-original-title="Reset">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Gender') }}</th>
                                    <th>{{ __('Country') }}</th>
                                    <th>{{ __('State') }}</th>
                                    <th>{{ __('City') }}</th>
                                    <th>{{ __('Profile') }}</th>
                                    <th>{{ __('Resume') }}</th>
                                    <th>{{ __('Show Resume') }}</th>
                                    @if (Laratrust::hasPermission('job candidate edit') || Laratrust::hasPermission('job candidate delete'))
                                        <th>{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($job_candidates as $job_candidate)
                                    <tr>
                                        <td>{{ !empty($job_candidate->name) ? $job_candidate->name : '' }}
                                        </td>
                                        <td>{{ !empty($job_candidate->email) ? $job_candidate->email : '' }}
                                        </td>
                                        <td>{{ !empty($job_candidate->gender) ? $job_candidate->gender : '' }}
                                        </td>
                                        <td>{{ !empty($job_candidate->country) ? $job_candidate->country : '' }}
                                        </td>
                                        <td>{{ !empty($job_candidate->state) ? $job_candidate->state : '' }}
                                        </td>
                                        <td>{{ !empty($job_candidate->city) ? $job_candidate->city : '' }}
                                        </td>
                                        <td>
                                            @if (!empty($job_candidate->profile))
                                                <div class="action-btn bg-primary ms-2">
                                                    <a class="mx-3 btn btn-sm align-items-center"
                                                        href="{{ get_file($job_candidate->profile) }}" download>
                                                        <i class="ti ti-download text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-secondary ms-2">
                                                    <a class="mx-3 btn btn-sm align-items-center"
                                                        href="{{ get_file($job_candidate->profile) }}" target="_blank">
                                                        <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Preview') }}"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <p>-</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if (!empty($job_candidate->resume))
                                                <div class="action-btn bg-primary ms-2">
                                                    <a class="mx-3 btn btn-sm align-items-center"
                                                        href="{{ get_file($job_candidate->resume) }}" download>
                                                        <i class="ti ti-download text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn bg-secondary ms-2">
                                                    <a class="mx-3 btn btn-sm align-items-center"
                                                        href="{{ get_file($job_candidate->resume) }}" target="_blank">
                                                        <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Preview') }}"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <p>-</p>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('resume.pdf', Crypt::encrypt($job_candidate->id)) }}"
                                                    class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" data-title="{{ __('Show Resume') }}"
                                                    title="{{ __('Show Resume') }}" target="_blank">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>

                                        </td>
                                        @if (Laratrust::hasPermission('job candidate edit') || Laratrust::hasPermission('job candidate delete'))
                                            <td class="Action">
                                                <span>
                                                    @permission('job candidate edit')
                                                        <div class="action-btn bg-info ms-2 edit_btn">
                                                            <a href="{{ route('job-candidates.edit', Crypt::encrypt($job_candidate->id)) }}"
                                                                class="mx-3 btn btn-sm  align-items-center"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-title="{{ __('Edit Job Candidate') }}"
                                                                title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endpermission
                                                    @permission('job candidate delete')
                                                        <div class="action-btn bg-danger ms-2 delete_btn">
                                                            {{ Form::open(['route' => ['job-candidates.destroy', $job_candidate->id], 'class' => 'm-0']) }}
                                                            @method('DELETE')
                                                            <a class="mx-3 btn btn-sm  align-items-center bs-pass-para show_confirm"
                                                                data-bs-toggle="tooltip" title=""
                                                                data-bs-original-title="Delete" aria-label="Delete"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $job_candidate->id }}"><i
                                                                    class="ti ti-trash text-white text-white"></i></a>
                                                            {{ Form::close() }}
                                                        </div>
                                                    @endpermission
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
