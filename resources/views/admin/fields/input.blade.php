@php
    $config = is_array($field->config) ? $field->config : [];
    $type = $field->type;
    $inputName = $namePrefix.'['.$field->handle.']';
    $inputId = ($inputPrefix ?? 'field').'-'.$field->handle;
    $required = ($config['required'] ?? false) === true;
    $multiple = ($config['multiple'] ?? false) === true
        || ($type === 'category' && ($config['multiple'] ?? true) === true)
        || ($type === 'media' && (int) ($config['max_files'] ?? 1) !== 1);
    $selectedValues = is_array($value)
        ? array_map('strval', array_values(array_filter($value, 'is_scalar')))
        : [(string) $value];
    $visibility = is_array($config['visibility'] ?? null) ? $config['visibility'] : [];
    $fieldError = $errors->first($namePrefix.'.'.$field->handle) ?: $errors->first($field->handle);
@endphp

<div class="ceemes-field {{ ($editable ?? false) === true && isset($field->uuid) ? 'ceemes-fixed-field' : '' }} {{ $fieldError ? 'has-error' : '' }}" style="grid-column: span {{ max(1, min(12, (int) ceil($field->width / 8.34))) }}" data-field-handle="{{ $field->handle }}" @if(($visibility['enabled'] ?? false) === true) data-conditional-field data-visibility-source="{{ $visibility['field'] ?? '' }}" data-visibility-operator="{{ $visibility['operator'] ?? 'filled' }}" data-visibility-value="{{ $visibility['value'] ?? '' }}" @endif>
    <div class="ceemes-field-label-row">
        <label for="{{ $inputId }}">{{ $field->label }} @if($required)<em>*</em>@endif</label>
        @if(($editable ?? false) === true && isset($field->uuid))<button class="ceemes-field-settings" type="button" data-dialog-open="edit-fixed-field-{{ $field->uuid }}" title="Atur struktur {{ $field->label }}" aria-label="Atur struktur {{ $field->label }}"><span>⚙</span> Atur</button>@endif
    </div>

    @if(in_array($type, ['textarea', 'richtext'], true))
        @if($type === 'richtext')<div class="ceemes-editor-toolbar"><span>B</span><span><i>I</i></span><span>List</span><span>Link</span></div>@endif
        <textarea id="{{ $inputId }}" name="{{ $inputName }}" @required($required) @if(isset($config['min_length'])) minlength="{{ $config['min_length'] }}" @endif @if(isset($config['max_length'])) maxlength="{{ $config['max_length'] }}" @endif placeholder="{{ $config['placeholder'] ?? '' }}">{{ $value }}</textarea>
    @elseif($type === 'boolean')
        <input type="hidden" name="{{ $inputName }}" value="0">
        <label class="ceemes-switch-row" for="{{ $inputId }}"><span><strong>{{ ($value ?? false) ? 'Enabled' : 'Disabled' }}</strong><small>Aktifkan atau nonaktifkan nilai ini.</small></span><span class="ceemes-switch"><input id="{{ $inputId }}" type="checkbox" name="{{ $inputName }}" value="1" @checked((bool)$value)><i></i></span></label>
    @elseif($type === 'select')
        @php($options = is_array($config['options'] ?? null) ? $config['options'] : [])
        <select id="{{ $inputId }}" name="{{ $inputName }}" @required($required)><option value="">Pilih...</option>@foreach($options as $optionValue => $optionLabel)@php($actualValue = is_array($optionLabel) ? ($optionLabel['value'] ?? $optionValue) : $optionValue)@php($actualLabel = is_array($optionLabel) ? ($optionLabel['label'] ?? $actualValue) : $optionLabel)<option value="{{ $actualValue }}" @selected((string)$value === (string)$actualValue)>{{ $actualLabel }}</option>@endforeach</select>
    @elseif($type === 'media')
        @include('ceemes::admin.fields.media-picker', ['pickerTitle' => $field->label, 'inputName' => $inputName, 'inputId' => $inputId, 'multiple' => $multiple, 'selectedValues' => $selectedValues])
    @elseif($type === 'category')
        @include('ceemes::admin.fields.category-picker', ['pickerTitle' => $field->label, 'inputName' => $inputName, 'inputId' => $inputId, 'multiple' => $multiple, 'selectedValues' => $selectedValues])
    @elseif(in_array($type, ['content', 'content'], true))
        @include('ceemes::admin.fields.content-picker', ['config' => $config, 'inputName' => $inputName, 'inputId' => $inputId, 'multiple' => $multiple, 'required' => $required, 'selectedValues' => $selectedValues])
    @elseif($type === 'repeater')
        @include('ceemes::admin.fields.repeater', ['config' => $config, 'inputName' => $inputName, 'inputId' => $inputId, 'value' => $value])
    @elseif(in_array($type, ['group', 'seo'], true))
        <textarea id="{{ $inputId }}" class="ceemes-code-input" name="{{ $inputName }}" @required($required)>{{ json_encode(is_array($value) ? $value : [], JSON_PRETTY_PRINT) }}</textarea>
        <small>Structured field. Gunakan JSON valid untuk versi ini.</small>
    @elseif($type === 'sections')
        <div class="ceemes-inline-note">Section dikelola pada panel Sections di bawah editor.</div>
    @else
        @php($htmlType = match($type) { 'number' => 'number', 'date' => 'date', 'datetime' => 'datetime-local', 'email' => 'email', 'url' => 'url', 'color' => 'color', default => 'text' })
        <input id="{{ $inputId }}" type="{{ $htmlType }}" name="{{ $inputName }}" value="{{ $value }}" @required($required) @if(in_array($type, ['text', 'email', 'url'], true) && isset($config['min_length'])) minlength="{{ $config['min_length'] }}" @endif @if(in_array($type, ['text', 'email', 'url'], true) && isset($config['max_length'])) maxlength="{{ $config['max_length'] }}" @endif @if($type === 'number' && isset($config['min'])) min="{{ $config['min'] }}" @endif @if($type === 'number' && isset($config['max'])) max="{{ $config['max'] }}" @endif @if($type === 'number' && ($config['integer'] ?? false)) step="1" @endif @if(in_array($type, ['date', 'datetime'], true) && isset($config['after_or_equal'])) min="{{ $config['after_or_equal'] }}" @endif @if(in_array($type, ['date', 'datetime'], true) && isset($config['before_or_equal'])) max="{{ $config['before_or_equal'] }}" @endif placeholder="{{ $config['placeholder'] ?? '' }}">
    @endif
    @if(is_string($config['instructions'] ?? null))<small>{{ $config['instructions'] }}</small>@endif
    @if($fieldError)<small class="ceemes-field-error">{{ $fieldError }}</small>@endif
</div>
