
{{ Form::open(array('url' => 'contract','enctype'=>'multipart/form-data')) }}
<div class="modal-body">
    <div class="text-end">
        @if (module_is_active('AIAssistant'))
            @include('aiassistant::ai.generate_ai_btn',['template_module' => 'contract','module'=>'Contract'])
        @endif
    </div>
    <div class="row">
    {{ Form::hidden('contract_type','contract',null) }}
        <div class="col-md-6 form-group">
            {{ Form::label('subject', __('Subject'),['class'=>'col-form-label']) }}
            {{ Form::text('subject', '', array('class' => 'form-control','required'=>'required','placeholder'=>'Enter Subject')) }}
        </div>
        @if (\Auth::user()->type == 'company')
            <div class="col-md-6 form-group">
                {{ Form::label('user_id', __('User'),['class'=>'col-form-label']) }}
                {{ Form::select('user_id',$user,null, array('class' => 'form-control','id'=>'user_id','required'=>'required','placeholder' => 'Select User')) }}
            </div>
        @else
            {{ Form::hidden('user_id',\Auth::user()->id,null) }}
        @endif
        @if(module_is_active('Taskly'))
            <div class="col-md-6 form-group">
                {{ Form::label('project_id', __('Project'),['class'=>'col-form-label']) }}
                {{ Form::select('project_id',[],null, array('class' => 'form-control','id'=>'project_id','placeholder' => 'Select Project')) }}
            </div>
        @endif
        <div class="col-md-6 form-group">
            {{ Form::label('value', __('Value'),['class'=>'col-form-label']) }}
            {{ Form::number('value', '', array('class' => 'form-control','required'=>'required','min' => '1','placeholder'=>'Enter Amount')) }}
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('type', __('Type'),['class'=>'col-form-label']) }}
                {{ Form::select('type', $contractType,null, array('class' => 'form-control ','required'=>'required')) }}
                @if(count($contractType) <= 0)
                    <div class="text-muted text-xs">
                        {{__('Please create new contract type')}} <a href="{{route('contract_type.index')}}">{{__('here')}}</a>.
                    </div>
                @endif
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{Form::label('start_date',__('Start Date'),['class'=>'col-form-label']) }}
                {!!Form::date('start_date', date('Y-m-d'),array('class' => 'form-control','placeholder' => 'Start Date','required'=>'required')) !!}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{Form::label('end_date',__('End Date'),['class'=>'col-form-label']) }}
                {!!Form::date('end_date', date('Y-m-d'),array('class' => 'form-control','required'=>'required','placeholder' => 'End Date')) !!}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('notes', __('Description'),['class'=>'col-form-label']) }}
                {{ Form::textarea('notes', '', array('class' => 'form-control','placeholder'=>'Enter Description')) }}
            </div>
        </div>
        @if(module_is_active('CustomField') && !$customFields->isEmpty())
            <div class="col-md-12">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customfield::formBuilder')
                </div>
            </div>
        @endif
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Close')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>

</div>
{{ Form::close() }}
<script>
     @if(module_is_active('Taskly'))
        $(document).on('change', 'select[name=user_id]', function() {
            var user_id = $(this).val();
        getproject(user_id);
        });

        function getproject(did) {
        $.ajax({
            url: '{{ route('getproject') }}',
            type: 'POST',
            data: {
                "user_id": did,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                $('#project_id').empty();
                $('#project_id').append(
                    '<option value="">{{ __('Select Project') }}</option>');
                $.each(data, function(key, value) {
                    $('#project_id').append('<option value="' + key + '">' + value +
                        '</option>');
                });
            }
        });
        }
    @endif
</script>

