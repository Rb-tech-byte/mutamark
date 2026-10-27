@if ($customFields)
    @foreach ($customFields as $customField)
        <div class="row">
            @if ($customField->type == 'text')
                <div class="form-group">

                    @if ($customField->is_required == 1)
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                        {{ Form::text('customField[' . $customField->id . ']', null, ['class' => 'form-control', 'required' => 'required']) }}
                    @else
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}
                        {{ Form::text('customField[' . $customField->id . ']', null, ['class' => 'form-control']) }}
                    @endif

                </div>
            @elseif($customField->type == 'email')
                <div class="form-group">
                    @if ($customField->is_required == 1)
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                        {{ Form::email('customField[' . $customField->id . ']', null, ['class' => 'form-control', 'required' => 'required']) }}
                    @else
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}
                        {{ Form::email('customField[' . $customField->id . ']', null, ['class' => 'form-control']) }}
                    @endif
                </div>
            @elseif($customField->type == 'number')
                <div class="form-group">

                    @if ($customField->is_required == 1)
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                        {{ Form::number('customField[' . $customField->id . ']', null, ['class' => 'form-control', 'required' => 'required']) }}
                    @else
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}
                        {{ Form::number('customField[' . $customField->id . ']', null, ['class' => 'form-control']) }}
                    @endif
                </div>
            @elseif($customField->type == 'date')
                <div class="form-group">
                    @if ($customField->is_required == 1)
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}<span class="text-danger pl-1"> *</span>
                        {{ Form::date('customField[' . $customField->id . ']', null, ['class' => 'form-control', 'required' => 'required']) }}
                    @else
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}
                        {{ Form::date('customField[' . $customField->id . ']', null, ['class' => 'form-control']) }}
                    @endif
                </div>
            @elseif($customField->type == 'textarea')
                <div class="form-group">
                    @if ($customField->is_required == 1)
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                        {{ Form::textarea('customField[' . $customField->id . ']', null, ['class' => 'form-control', 'required' => 'required']) }}
                    @else
                        {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}
                        {{ Form::textarea('customField[' . $customField->id . ']', null, ['class' => 'form-control']) }}
                    @endif
                </div>
            @elseif($customField->type == 'attachment')
                <div class="attachment-upload">
                    <div class="attachment-button">
                        <div class="form-group">
                            @if ($customField->is_required == 1)
                                {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}<span class="text-danger pl-1">*</span>
                            @else
                                {{ Form::label('customField-' . $customField->id, __($customField->name), ['class' => 'form-label']) }}
                            @endif
                            <input type="file" name="customField[{{ $customField->id }}]" class="form-control mb-3"
                                onchange="document.getElementById('blah11').src = window.URL.createObjectURL(this.files[0])"
                                @if ($customField->is_required == 1) required @endif>
                            <img id="blah11" src="{{ isset($fildedata[$customField->id]) ? get_file($fildedata[$customField->id]) : '' }}" width="10%">
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endforeach
@endif
