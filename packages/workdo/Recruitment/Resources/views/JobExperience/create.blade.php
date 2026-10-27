{{ Form::open(['route' => 'job-experience.store', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
{{ Form::hidden('candidate_id', $jobCandidateId, []) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Title']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('organization', __('Organization'), ['class' => 'form-label']) }}
                {{ Form::text('organization', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Organization']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Date']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control ', 'required' => 'required', 'placeholder' => 'Select Date']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                {{ Form::text('country', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Country']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                {{ Form::text('state', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter State']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                {{ Form::text('city', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter City']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('zipcode', __('Zip Code'), ['class' => 'form-label']) }}
                {{ Form::text('zipcode', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Zip Code']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                {{ Form::textarea('address', null, ['class' => 'form-control', 'rows' => 2, 'required' => 'Address']) }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
            {{ Form::text('phone', null, ['class' => 'form-control', 'placeholder' => 'Enter Phone Number']) }}
            <div class=" text-xs text-danger">
                {{ __('Please add mobile number with country code. (ex. +91)') }}
            </div>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Enter Email']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('website', __('Website'), ['class' => 'form-label']) }}
            {{ Form::text('website', null, ['class' => 'form-control', 'placeholder' => 'Enter Website']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('experience_proof', __('Experience Document'), ['class' => 'form-label']) }}
            <div class="choose-files ">
                <label for="experience_proof">
                    <div class=" bg-primary "> <i class="ti ti-upload px-1"></i>{{ __('Choose file here') }}</div>
                    <input type="file" class="form-control file" name="experience_proof" id="experience_proof"
                        onchange="document.getElementById('blah2').src = window.URL.createObjectURL(this.files[0])">
                    <hr>
                    <img id="blah2" width="100" src="" />
                </label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group col">
                <div class="form-switch p-0">
                    {{ Form::label('is_reference_slider', __('Reference Enable'), ['class' => ' col-form-label py-0']) }}
                    <div class="float-end">
                        <input type="checkbox" class="form-check-input" id="is_reference_slider"
                            name="is_reference_slider" />
                        <label class="form-check-label form-label" for="is_reference_slider"></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 reference_div d-none">
            <div class="form-group">
                {{ Form::label('full_name', __('Reference Full Name'), ['class' => 'form-label']) }}
                {{ Form::text('full_name', null, ['class' => 'form-control', 'placeholder' => 'Enter Reference Full Name']) }}
            </div>
        </div>
        <div class="col-md-6 reference_div d-none">
            <div class="form-group">
                {{ Form::label('reference_email', __('Reference Email'), ['class' => 'form-label']) }}
                {{ Form::text('reference_email', null, ['class' => 'form-control', 'placeholder' => 'Enter Reference Email']) }}
            </div>
        </div>
        <div class="form-group col-md-6 reference_div d-none">
            {{ Form::label('reference_phone', __('Reference Phone'), ['class' => 'form-label']) }}
            {{ Form::text('reference_phone', null, ['class' => 'form-control', 'placeholder' => 'Enter Reference Phone Number']) }}
            <div class=" text-xs text-danger">
                {{ __('Please add mobile number with country code. (ex. +91)') }}
            </div>
        </div>
        <div class="col-md-6 reference_div d-none">
            <div class="form-group">
                {{ Form::label('job_position', __('Reference Job Position'), ['class' => 'form-label']) }}
                {{ Form::text('job_position', null, ['class' => 'form-control', 'placeholder' => 'Enter Reference Job Position']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                <textarea class="tox-target summernote" id="description1" name="description" rows="8"></textarea>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}

<script>
    $(document).on('change', '#is_reference_slider', function() {
        if ($(this).is(':checked')) {
            $('.reference_div').removeClass('d-none');
            $('#full_name').attr('required', true);
            $('#reference_email').attr('required', true);
            $('#reference_phone').attr('required', true);
            $('#job_position').attr('required', true);
        } else {
            $('.reference_div').addClass('d-none');
            $('#full_name').attr('required', false);
            $('#reference_email').attr('required', false);
            $('#reference_phone').attr('required', false);
            $('#job_position').attr('required', false);
        }
    });
</script>
