{{ Form::model($insurance, ['route' => ['insurance.update', $insurance->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('insurance_provider', __('Insurance Provider Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('insurance_provider', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Insurance Provider Name')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('vehicle_name', __('Vehicle Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('vehicle_name', $vehicle, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('start_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('end_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('scheduled_date', __('Recurring Date'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::date('scheduled_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('scheduled_period', __('Recurring Period'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('scheduled_period', $recurring, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('deductible', __('Insurance Deductible'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('deductible', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Deductible Number')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('charge_payable', __('Charge Payable'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('charge_payable', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Charge Payable')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('policy_number', __('Policy Number'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::number('policy_number', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Policy Number')]) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('policy_document', __('Policy Document'), ['class' => 'form-label']) }}<x-required></x-required>
                <input type="file"name="policy_document" class="form-control mb-2"
                    onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                @php
                    if (check_file($insurance->policy_document) == false) {
                        $path = asset('packages/workdo/Fleet/src/Resources/assets/image/img01.jpg');
                    } else {
                        $path = get_file($insurance->policy_document);
                    }
                @endphp
                <img id="blah" class="mt-2" width="25%" src="{{ $path }}" alt="your image" />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::textarea('notes', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Notes'), 'rows' => 3]) }}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
</div>

{{ Form::close() }}
