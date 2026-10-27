{{ Form::open(['route' => ['send.test.massage'], 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="name" class="form-label">{{ __('Mobile No') }}</label>
                <input class="form-control" placeholder="{{ __('Enter Mobile No') }}" required="required" name="mobile"
                    type="text" id="name">
                <input type="hidden" name="whatsapp_phone_number_id" value="{{ $data['whatsapp_phone_number_id'] }}" />
                <input type="hidden" name="whatsapp_access_token" value="{{ $data['whatsapp_access_token'] }}" />
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button class="btn btn-primary pull-right" type="button" id="test-send-mail">{{ __('send') }}</button>
</div>
{{ Form::close() }}

@push('scripts')

