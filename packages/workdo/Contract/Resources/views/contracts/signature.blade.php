<form id='form_pad' method="post" enctype="multipart/form-data">
    @method('POST')
    <div class="modal-body" id="">
        <div class="row">
            <input type="hidden" name="contract_id" value="{{ $contract->id }}">
            <div class="form-control">
                <canvas id="signature-pad" class="signature-pad" height=200 ></canvas>
                <input type="hidden"
                    @if (Auth::user()->type == 'company') name="owner_signature" @else name="client_signature" @endif
                    id="SignupImage1">
            </div>
            <div class="mt-1">
                <button type="button" class="btn-sm btn-danger" id="clearSig">{{ __('Clear') }}</button>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-secondary btn-light" data-bs-dismiss="modal">
        <input type="button" id="addSig" value="{{ __('Sign') }}" class="btn btn-primary ms-2">
    </div>
</form>

<script src="{{ asset('Modules/Contract/Resources/assets/js/signature_pad/signature_pad.min.js') }}"></script>
<script>
    var signature = {
        canvas: null,
        clearButton: null,

        init: function init() {

            this.canvas = document.querySelector(".signature-pad");
            this.clearButton = document.getElementById('clearSig');
            this.saveButton = document.getElementById('addSig');
            signaturePad = new SignaturePad(this.canvas);

            this.clearButton.addEventListener('click', function(event) {

                signaturePad.clear();
            });

            this.saveButton.addEventListener('click', function(event) {
                var data = signaturePad.toDataURL('image/png');
                $('#SignupImage1').val(data);

                $.ajax({
                    url: '{{ route('signaturestore') }}',
                    type: 'POST',
                    data: $("form").serialize(),
                    success: function(data) {
                        if(data.Success){

                            toastrs('{{ __('Success') }}', 'Contract Signed Successfully',
                            'success');
                        }else{
                            toastrs('{{ __('Error') }}', data.message,'error');
                        }
                        $('#commonModal').modal('hide');
                        location.reload();
                    },
                    error: function(data) {}
                });

            });

        }
    };

    signature.init();
</script>
