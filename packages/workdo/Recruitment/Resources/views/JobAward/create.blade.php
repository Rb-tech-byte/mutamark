{{ Form::open(['route' => 'job-award.store', 'method' => 'post']) }}
{{ Form::hidden('candidate_id',$jobCandidateId, []) }}
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
                {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
                {{ Form::select('reference', $reference, null, ['class' => 'form-control amount_type ', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                <textarea class="tox-target summernote" id="description6" name="description" rows="8"></textarea>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}
