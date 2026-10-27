<div class="card" id="contract-sidenav">
    {{ Form::open(array('route' => 'contract.setting.store','method' => 'post')) }}
    <div class="card-header">
        <div class="row">
            <div class="col-lg-10 col-md-10 col-sm-10">
                <h5 class="">{{ __('Contract Settings') }}</h5>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="form-group">
                    {{Form::label('contract_prefix',__('Contract Prefix'),array('class'=>'form-label')) }}
                    {{Form::text('contract_prefix',!empty($settings['contract_prefix']) ? $settings['contract_prefix'] :'#CON',array('class'=>'form-control', 'placeholder' => 'Enter Contract Prefix'))}}
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <input class="btn btn-print-invoice  btn-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{Form::close()}}
</div>
