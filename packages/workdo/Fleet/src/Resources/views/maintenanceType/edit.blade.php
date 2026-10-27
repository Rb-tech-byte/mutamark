{{Form::model($maintenanceType,array('route' => array('maintenanceType.update', $maintenanceType->id), 'method' => 'PUT' ,'class'=>'needs-validation','novalidate')) }}

<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Form::label('name', __('Maintenance Type Name'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::text('name', null, array('class' => 'form-control','placeholder'=> __('Enter Maintenance Type Name') ,'required'=>'required')) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>

{{Form::close()}}
