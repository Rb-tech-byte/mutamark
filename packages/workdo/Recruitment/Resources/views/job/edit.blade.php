@extends('layouts.main')
@section('page-title')
    {{ __('Edit Job') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('Modules/Recruitment/Resources/assets/css/custom.css') }}">
    <link href="{{ asset('Modules/Recruitment/Resources/assets/css/bootstrap-tagsinput.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}" rel="stylesheet">
@endpush
@section('page-action')
    <div class="">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn', [
                'template_module' => 'job',
                'module' => 'Recruitment',
            ])
        @endif
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    <script src="//cdn.ckeditor.com/4.12.1/basic/ckeditor.js"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/editorplaceholder.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/bootstrap-tagsinput.min.js') }}"></script>

    <script>
        var e = $('[data-toggle="tags"]');
        e.length && e.each(function() {
            $(this).tagsinput({
                tagClass: "badge badge-primary"
            })
        });
    </script>
@endpush

@section('page-breadcrumb')
    {{ __('Manage Job') }},
    {{ __('Edit Job') }}
@endsection
@php
    $company_settings = getCompanyAllSetting();
@endphp
@section('content')
    <div class="row">

        {{ Form::model($job, ['route' => ['job.update', $job->id], 'method' => 'PUT']) }}
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 ">
                    <div class="card card-fluid jobs-card">
                        <div class="card-body ">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('title', __('Job Title'), ['class' => 'col-form-label']) !!}
                                    {!! Form::text('title', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {{ Form::label('recruitment_type', __('Recruitment Type'), ['class' => 'col-form-label']) }}
                                    {{ Form::select('recruitment_type', $recruitment_type, null, ['class' => 'form-control select', 'id' => 'recruitment_type']) }}
                                </div>

                                @if (module_is_active('Hrm'))
                                    <div class="form-group col-md-6" id="branch" style="display: none;">
                                        {!! Form::label(
                                            'branch',
                                            !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'),
                                            ['class' => 'form-label'],
                                        ) !!}
                                        {{ Form::select('branch', $branches, null, ['class' => 'form-control ', 'placeholder' => 'Select Branch']) }}
                                    </div>
                                @endif

                                <div class="form-group col-md-6">
                                    {!! Form::label('location', __('Location'), ['class' => 'form-label']) !!}
                                    {!! Form::text('location', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('category', __('Job Category'), ['class' => 'form-label']) !!}
                                    {{ Form::select('category', $categories, null, ['class' => 'form-control ', 'placeholder' => 'Select Job Category', 'required' => 'required']) }}
                                </div>

                                <div class="form-group col-md-6" id="users" style="display: none;">
                                    {{ Form::label('user_id', __('Client'), ['class' => 'form-label']) }}
                                    {{ Form::select('user_id', $users, null, ['class' => 'form-control select2']) }}
                                    @if (empty($users->count()))
                                        <div class="text-muted text-xs">
                                            {{ __('Please create new client') }} <a
                                                href="{{ route('users.index') }}">{{ __('here') }}</a>.
                                        </div>
                                    @endif
                                </div>
    
                                <div class="form-group col-md-6">
                                    {!! Form::label('status', __('Status'), ['class' => 'form-label']) !!}
                                    {{ Form::select('status', $status, null, ['class' => 'form-control ', 'placeholder' => 'Select Status', 'required' => 'required']) }}
                                </div>
                                
                                
                                <div class="form-group col-md-6">
                                    {{ Form::label('job_type', __('Job Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('job_type', $job_type, null, ['class' => 'form-control select']) }}
                                </div>
                                
                                <div class="form-group col-md-6">
                                    {!! Form::label('salary_from', __('Salary From'), ['class' => 'form-label']) !!}
                                    {!! Form::number('salary_from', old('salary_from'), [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'step' => '1',
                                        'placeholder' => 'Enter Amount',
                                    ]) !!}
                                </div>

                                <div class="form-group col-md-6">
                                    {!! Form::label('position', __('No. of Positions'), ['class' => 'form-label']) !!}
                                    {!! Form::text('position', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Position',]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('salary_to', __('Salary To'), ['class' => 'form-label']) !!}
                                    {!! Form::number('salary_to', old('salary_to'), [
                                        'class' => 'form-control',
                                        'required' => 'required',
                                        'step' => '1',
                                        'placeholder' => 'Enter Amount',
                                    ]) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('start_date', __('Start Date'), ['class' => 'form-label']) !!}
                                    {!! Form::date('start_date', null, ['class' => 'form-control ', 'autocomplete' => 'off']) !!}
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('end_date', __('End Date'), ['class' => 'form-label']) !!}
                                    {!! Form::date('end_date', null, ['class' => 'form-control ', 'autocomplete' => 'off']) !!}
                                </div>

                                <div class="form-group col-md-12">
                                    <label class="col-form-label" for="skill">{{ __('Skill Box') }}</label>
                                    <input type="text" class="form-control" value="{{ $job->skill }}"
                                        data-toggle="tags" name="skill" placeholder="Skill" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="card card-fluid jobs-card">
                        <div class="card-body ">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h6>{{ __('Need to Ask ?') }}</h6>
                                        <div class="my-4">
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="applicant[]"
                                                    value="gender" id="check-gender"
                                                    {{ in_array('gender', $job->applicant) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="check-gender">{{ __('Gender') }}
                                                </label>
                                            </div>
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="applicant[]"
                                                    value="dob" id="check-dob"
                                                    {{ in_array('dob', $job->applicant) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="check-dob">{{ __('Date Of Birth') }}</label>
                                            </div>
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="applicant[]"
                                                    value="country" id="check-country"
                                                    {{ in_array('country', $job->applicant) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="check-country">{{ __('Country') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h6>{{ __('Need to show Option ?') }}</h6>
                                        <div class="my-4">
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="visibility[]"
                                                    value="profile" id="check-profile"
                                                    {{ in_array('profile', $job->visibility) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="check-profile">{{ __('Profile Image') }} </label>
                                            </div>
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="visibility[]"
                                                    value="resume" id="check-resume"
                                                    {{ in_array('resume', $job->visibility) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="check-resume">{{ __('Resume') }}</label>
                                            </div>
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="visibility[]"
                                                    value="letter" id="check-letter"
                                                    {{ in_array('letter', $job->visibility) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="check-letter">{{ __('Cover Letter') }}</label>
                                            </div>
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="visibility[]"
                                                    value="terms" id="check-terms"
                                                    {{ in_array('terms', $job->visibility) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="check-terms">{{ __('Terms And Conditions') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <h6>{{ __('Custom Questions') }}</h6>
                                    <div class="my-4">
                                        @foreach ($customQuestion as $question)
                                            <div class="form-check custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="custom_question[]"
                                                    value="{{ $question->id }}"@if ($question->is_required == 'yes') required @endif
                                                    id="custom_question_{{ $question->id }}"
                                                    {{ in_array($question->id, $job->custom_question) ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="custom_question_{{ $question->id }}">{{ $question->question }}
                                                    @if ($question->is_required == 'yes')
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-fluid jobs-summernote-card">
                        <div class="card-body ">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::label('description', __('Job Description'), ['class' => 'form-label']) !!}
                                    <textarea name="description"
                                        class="form-control summernote  {{ !empty($errors->first('description')) ? 'is-invalid' : '' }}" required
                                        id="description">{{ $job->description }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-fluid jobs-summernote-card">
                        <div class="card-body ">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    {!! Form::label('requirement', __('Job Requirement'), ['class' => 'form-label']) !!}
                                    <textarea name="requirement"
                                        class="form-control summernote  {{ !empty($errors->first('requirement')) ? 'is-invalid' : '' }}" required
                                        id="requirement">{{ $job->requirement }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="termsandcondition">
                    <div class="card card-fluid jobs-summernote-card">
                        <div class="card-body ">
                            <div class="row">
                                <div class="form-group terms_val col-md-12">
                                    {!! Form::label('terms_and_conditions', __('Terms And Conditions'), ['class' => 'col-form-label']) !!}
                                    <textarea name="terms_and_conditions"
                                        class="form-control summernote  {{ !empty($errors->first('terms_and_conditions')) ? 'is-invalid' : '' }}"
                                        id="terms_and_conditions">{{ $job->terms_and_conditions }}</textarea>
                                    <p class="text-danger d-none" id="terms_val">{{ __('This filed is required.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-end">
                    <div class="form-group">
                        <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>

        </div>
    @endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var checkbox = $('#check-terms');
            var termsDiv = $('#termsandcondition');
            var textarea = $('#terms_and_conditions');
            var validationMessage = $('#terms_val');

            checkbox.change(function() {
                if (checkbox.is(':checked')) {
                    termsDiv.show();
                } else {
                    termsDiv.hide();
                }
            });

            if (!checkbox.is(':checked')) {
                termsDiv.hide();
            }

            $('form').submit(function(event) {
                if (checkbox.is(':checked') && textarea.val().trim() === '') {
                    validationMessage.removeClass('d-none');
                    event.preventDefault();
                }
            });
            textarea.on('input', function() {
                if (textarea.val().trim() !== '') {
                    validationMessage.addClass('d-none');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            function toggleFormGroups() {
                var selectedType = $('#recruitment_type').val();
                if (selectedType === 'internal') {
                    $('#branch').show();
                    $('#users').hide();
                    $('#branch').prop('required', true);
                    $('#users').prop('required', false);
                } else if (selectedType === 'client') {
                    $('#branch').hide();
                    $('#users').show();
                    $('#users').prop('required', true);
                    $('#branch').prop('required', false);
                } else {
                    $('#branch').hide();
                    $('#users').hide();
                    $('#users').prop('required', false);
                    $('#branch').prop('required', false);
                }
            }

            toggleFormGroups();

            $('#recruitment_type').change(toggleFormGroups);
        });
    </script>
@endpush
