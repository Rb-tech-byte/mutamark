{{ Form::model($question, ['route' => ['questions.update', $question->id], 'method' => 'PUT']) }}
<div class="modal-body custom-fields">
    <div class="row">
        <div class="col-md-6 form-group">
            {{ Form::label('question', __('Question Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('question', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Question Name']) }}
        </div>
        <div class="col-md-6 form-group">
            {{ Form::label('question_type', __('Question Type'), ['class' => 'col-form-label']) }}
            {{ Form::select('question_type', $question_type, null, ['class' => 'form-control', 'required' => 'required', 'id' => 'question_type', 'placeholder' => 'Select Question Type']) }}
        </div>
        <div class="col-md-12 form-group">
            <div class="field_wrapper">
                {{ Form::label('available_answer', __('Available Answer'), ['class' => 'col-form-label']) }}
                @if (count($questions) > 0)
                    <?php $i = 1; ?>
                    @foreach ($questions as $questionss)
                        <div class="d-flex gap-1 mb-4">

                            <input type="text" class="form-control" name="available_answer[]"
                                value="{{ $questionss }}" required/>
                            @if ($i == 1)
                                <a href="javascript:void(0);" class="add_button btn btn-primary" title="Add field"><i
                                        class="ti ti-plus"></i></a>
                            @else
                                <a href="javascript:void(0);" class="remove_button btn btn-danger"><i
                                        class="ti ti-trash"></i></a>
                            @endif
                        </div>
                        <?php $i++; ?>
                    @endforeach
                @else
                    <div class="d-flex gap-1 mb-4">
                        <input type="text" class="form-control " name="available_answer[]" value="" required/>

                        <a href="javascript:void(0);" class="add_button btn btn-primary" title="Add field"><i
                                class="ti ti-plus"></i></a>

                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-6 form-group">
            {{ Form::label('is_required', __('Required Answer:'), ['class' => 'col-form-label']) }}
            <div class="form-check form-switch custom-switch-v1">
                <input type="hidden" name="is_required" value="off">
                <input type="checkbox" class="form-check-input input-primary" id="customswitchv1-1 is_required"
                    name="is_required"
                    {{ isset($question) && $question->is_required == 'on' ? 'checked="checked"' : '' }}>
            </div>
        </div>

        <div class="col-md-6 form-group">
            {{ Form::label('is_enabled', __('Enabled:'), ['class' => 'col-form-label']) }}
            <div class="form-check form-switch custom-switch-v1">
                <input type="hidden" name="is_enabled" value="off">
                <input type="checkbox" class="form-check-input input-primary" id="customswitchv1-1 is_enabled"
                    name="is_enabled"
                    {{ isset($question) && $question->is_enabled == 'on' ? 'checked="checked"' : '' }}>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Update') }}</button>

</div>
{{ Form::close() }}

<script src="{{ asset('Modules/Appointment/Resources/assets/js/repeater.js') }}"></script>
<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initially hide the Available Answer field if the initial question type is 'text'
        if ($('#question_type').val() === 'text') {
            $('.field_wrapper').hide().find(':input').attr('required', false);
        }

        // Show/hide Available Answer based on Question Type selection
        $('#question_type').change(function() {
            if ($(this).val() === 'text') {
                $('.field_wrapper').hide().find(':input').attr('required', false);
            } else {
                $('.field_wrapper').show().find(':input').attr('required', true);
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        var maxField = 100; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.field_wrapper'); //Input field wrapper
        var fieldHTML =
            '<div class="d-flex gap-1 mb-4"><input type="text" class="form-control " name="available_answer[]" value="" required/><a href="javascript:void(0);" class="remove_button btn btn-danger"><i class="ti ti-trash"></i></a></div>'; //New input field html

        var x = 1; //Initial field counter is 1

        //Once add button is clicked
        $(addButton).click(function() {
            //Check maximum number of input fields
            if (x < maxField) {
                x++; //Increment field counter
                $(wrapper).append(fieldHTML); //Add field html
            }
        });

        //Once remove button is clicked
        $(wrapper).on('click', '.remove_button', function(e) {

            e.preventDefault();
            $(this).parent('div').remove(); //Remove field html
            x--; //Decrement field counter
        });
    });
</script>
