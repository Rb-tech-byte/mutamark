{{ Form::open(['route' => ['Addpayment.store',$bookings->id], 'method' => 'post','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('total_payment', __('Total Payment'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::number('total_payment', $bookings->total_price, array('class' => 'form-control' ,'required'=>'required','disabled')) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('pending_amount', __('Pending Amount'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::number('pending_amount', $paid_amount, array('class' => 'form-control' ,'required'=>'required','disabled')) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('pay_amount', __('Pay Amount'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::number('pay_amount','', array('class' => 'form-control' ,'required'=>'required','placeholder'=>__('Enter Pay Amount'))) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::textarea('description','', array('class' => 'form-control' ,'required'=>'required','placeholder'=>__('Enter Description'),'rows'=>3)) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>

{{ Form::close() }}
