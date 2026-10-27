{{ Form::open(array('route' => array('insurance-booking.store',$insurances->id),'enctype' => 'multipart/form-data' , 'id' => 'yourFormId','class'=>'needs-validation','novalidate')) }}
{{ Form::hidden('insurances_id', $insurances->id, []) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('start_date', __('Start Date'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::date('start_date',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('end_date', __('End date'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::date('end_date',null, array('class' => 'form-control','required'=>'required')) }}
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::text('amount', null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Enter Amount'))) }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
    </div>

{{Form::close()}}
