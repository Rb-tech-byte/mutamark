<div class="card" id="journal-sidenav">
    {{ Form::open(array('route' => 'journal-entry.setting.store','method' => 'post')) }}
    <div class="card-header">
        <div class="row">
            <div class="col-lg-10 col-md-10 col-sm-10">
                <h5 class="">{{ __('Journal Settings') }}</h5>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mt-2">
            <div class="col-md-4">
                <div class="form-group">
                    {{Form::label('journal_prefix',__('Journal Prefix'),array('class'=>'form-label')) }}
                    {{Form::text('journal_prefix',!empty($settings['journal_prefix']) ? $settings['journal_prefix'] :'#JUR00000',array('class'=>'form-control', 'placeholder' => 'Enter Journal Prefix'))}}
                </div>
            </div>

        </div>
    </div>
    <div class="card-footer text-end">
        <input class="btn btn-print-invoice btn-primary m-r-10" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{Form::close()}}
</div>
