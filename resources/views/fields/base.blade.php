@php
    $inputType = $inputType ?? 'text';
@endphp

<label for="{{ $id }}">{{ $label }}</label>
<input
    id="{{ $id }}"
    name="{{ $name }}"
    type="{{ $inputType }}"
    value="{{ old($name, $value ?? null) }}"
    @required($required ?? false)
>
