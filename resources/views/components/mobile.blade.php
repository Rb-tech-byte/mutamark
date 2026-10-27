@props([
    'divClass' => 'form-group',
    'class' => 'form-control',
    'name' => 'mobile_no',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'id' => null,
    'type' => 'tel',
    'required' => false,
])

<div class="{{ $divClass }}">
    @if($label)
        <label for="{{ $id ?? $name }}" class="form-label">{{ $label }}</label>
        @if($required)
            <x-required></x-required>
        @endif
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        class="{{ $class }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder ?? $label }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->except(['divClass', 'class', 'name', 'label', 'placeholder', 'value', 'id', 'type', 'required']) }}
    >
</div>
