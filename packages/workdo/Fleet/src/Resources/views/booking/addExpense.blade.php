{{ Form::open(['route' => ['AddExpense.store',$id], 'method' => 'post','class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Form::label('amount', __('Total Amount'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Amount')]) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}<x-required></x-required>
            {{ Form::textarea('description', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Description'), 'rows' => 3]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}
