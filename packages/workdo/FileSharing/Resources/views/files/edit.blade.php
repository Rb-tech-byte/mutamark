{{ Form::model($file, ['route' => ['files.update', $file->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}

<div class="modal-body">
    <div class="row">
        <div class="col-12 field" data-name="attachment">
            <div class="form-group">
                {{ Form::label('attachment', __('Attachment'), ['class' => 'form-label']) }}
                <input type="file" name="attachment" class="form-control mb-3"
                    onchange="document.getElementById('blah').src = window.URL.createObjectURL(this.files[0])">
                <img id="blah" class="mt-2" src="{{ check_file($file->file_path) ? get_file($file->file_path) : 'Modules/FileSharing/Resources/assets/img/gallery.png' }}"
                    style="width:9%;" />
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <div class="btn-box">

                    <label class="d-block form-label">{{ __('Select a method for share the file') }}</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input type" id="customRadio5" name="type"
                                    value="email" @if ($file->filesharing_type == 'email') checked @endif
                                    onclick="hide_show(this)">
                                <label class="custom-control-label form-label"
                                    for="customRadio5">{{ __('Assign Email') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-check-inline">
                                <input type="radio" class="form-check-input type" id="customRadio6" name="type"
                                    value="link" @if ($file->filesharing_type == 'link') checked @endif
                                    onclick="hide_show(this)">
                                <label class="custom-control-label form-label"
                                    for="customRadio6">{{ __('Shared Link') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 email">
                    {{ Form::label('email', __('email'), ['class' => 'form-label']) }}<span class="text-danger"></span>
                    {{ Form::email('email', null, ['class' => 'form-control', 'min' => '0']) }}
                </div>
            </div>
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('auto_destroy', __('Auto Destroy'), ['class' => 'form-check-label mb-3']) }}
            <div class="form-check form-switch custom-switch-v1 float-end">
                <input type="checkbox" class="form-check-input" role="switch" id="auto_destroy" name="auto_destroy"
                    {{ $file->auto_destroy == 'on' ? 'checked' : '' }}>
                <label class="form-check-label" for="auto_destroy"></label>
            </div>
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('password_switch', __('Secure the upload with a password (Optional)'), ['class' => 'form-check-label mb-3']) }}
            <div class="form-check form-switch custom-switch-v1 float-end">
                <input type="checkbox" name="password_switch" class="form-check-input input-primary pointer password_protect"
                    {{ $file->is_pass_enable == 1 ? 'checked' : '' }} id="password_switch">
                <label class="form-check-label" for="password_switch"></label>
            </div>
        </div>

        <div class="col-md-12 ps_div password">
            <div class="form-group">
                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                {{ Form::password('password', ['class' => 'form-control',  'placeholder' => __('Enter User Password'), 'minlength' => '6']) }}
                @error('password')
                    <small class="invalid-password" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="col-12 form-group">
            {{ Form::label('users_list', __('Users'), ['class' => 'col-form-label']) }}
            {{ Form::select('users_list[]', $users, explode(',', $file->user_id), ['class' => 'form-control choices', 'id' => 'choices-multiple1', 'multiple' => '']) }}
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary" id="submit">
</div>
{{ Form::close() }}

<script>
     $(document).ready(function() {
        if ($('.password_protect').is(':checked')) {
            $('.password').show();
        } else {
            $('.password').hide();
        }
    });
    $(document).on('change', '#password_switch', function() {
        if ($(this).is(':checked')) {
            $('.ps_div').removeClass('d-none');
            $('#password').attr("required", true);

        } else {
            $('.ps_div').addClass('d-none');
            $('#password').val(null);
            $('#password').removeAttr("required");
        }
    });
</script>
<script>
    $(document).ready(function() {
        if ($("input[value='link']").is(":checked")) {
            ;
            $('.email').addClass('d-none')
            $('.email').removeClass('d-block');
        }
    });

    //hide & show email
    $(document).on('click', '.type', function() {
        var type = $(this).val();
        if (type == 'email') {
            $('.email').removeClass('d-none')
            $('.email').addClass('d-block');
        } else {
            $('.email').addClass('d-none')
            $('.email').removeClass('d-block');
        }
    });
</script>
