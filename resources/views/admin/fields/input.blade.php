@php
    $config = is_array($field->config) ? $field->config : [];
    $type = $field->type;
    $inputName = $namePrefix.'['.$field->handle.']';
    $inputId = ($inputPrefix ?? 'field').'-'.$field->handle;
    $required = ($config['required'] ?? false) === true;
    $multiple = ($config['multiple'] ?? false) === true
        || ($type === 'taxonomy' && ($config['multiple'] ?? true) === true)
        || ($type === 'media' && (int) ($config['max_files'] ?? 1) !== 1);
    $selectedValues = is_array($value)
        ? array_map('strval', array_values(array_filter($value, 'is_scalar')))
        : [(string) $value];
@endphp

<div class="ceemes-field" style="grid-column: span {{ max(1, min(12, (int) ceil($field->width / 8.34))) }}">
    <label for="{{ $inputId }}">{{ $field->label }} @if($required)<em>*</em>@endif</label>

    @if(in_array($type, ['textarea', 'richtext'], true))
        @if($type === 'richtext')<div class="ceemes-editor-toolbar"><span>B</span><span><i>I</i></span><span>List</span><span>Link</span></div>@endif
        <textarea id="{{ $inputId }}" name="{{ $inputName }}" @required($required) placeholder="{{ $config['placeholder'] ?? '' }}">{{ $value }}</textarea>
    @elseif($type === 'boolean')
        <input type="hidden" name="{{ $inputName }}" value="0">
        <label class="ceemes-switch-row" for="{{ $inputId }}"><span><strong>{{ ($value ?? false) ? 'Enabled' : 'Disabled' }}</strong><small>Aktifkan atau nonaktifkan nilai ini.</small></span><span class="ceemes-switch"><input id="{{ $inputId }}" type="checkbox" name="{{ $inputName }}" value="1" @checked((bool)$value)><i></i></span></label>
    @elseif($type === 'select')
        @php($options = is_array($config['options'] ?? null) ? $config['options'] : [])
        <select id="{{ $inputId }}" name="{{ $inputName }}" @required($required)><option value="">Pilih...</option>@foreach($options as $optionValue => $optionLabel)@php($actualValue = is_array($optionLabel) ? ($optionLabel['value'] ?? $optionValue) : $optionValue)@php($actualLabel = is_array($optionLabel) ? ($optionLabel['label'] ?? $actualValue) : $optionLabel)<option value="{{ $actualValue }}" @selected((string)$value === (string)$actualValue)>{{ $actualLabel }}</option>@endforeach</select>
    @elseif($type === 'media')
        <select id="{{ $inputId }}" name="{{ $inputName }}{{ $multiple ? '[]' : '' }}" @if($multiple) multiple size="5" @endif @required($required)><option value="">Pilih media...</option>@foreach($mediaItems as $media)<option value="{{ $media->uuid }}" @selected(in_array($media->uuid, $selectedValues, true))>{{ $media->title ?: $media->original_filename }}</option>@endforeach</select>
        <small>Pilih file dari Media Library. Buka menu Media untuk upload baru.</small>
    @elseif($type === 'taxonomy')
        <select id="{{ $inputId }}" name="{{ $inputName }}{{ $multiple ? '[]' : '' }}" @if($multiple) multiple size="6" @endif @required($required)><option value="">Pilih term...</option>@foreach($taxonomies as $taxonomy)<optgroup label="{{ $taxonomy->name }}">@foreach($taxonomy->terms as $term)<option value="{{ $term->uuid }}" @selected(in_array($term->uuid, $selectedValues, true))>{{ $term->name }}</option>@endforeach</optgroup>@endforeach</select>
    @elseif($type === 'entry')
        @include('ceemes::admin.fields.entry-picker', ['config' => $config, 'inputName' => $inputName, 'inputId' => $inputId, 'multiple' => $multiple, 'required' => $required, 'selectedValues' => $selectedValues])
    @elseif($type === 'repeater')
        @include('ceemes::admin.fields.repeater', ['config' => $config, 'inputName' => $inputName, 'inputId' => $inputId, 'value' => $value])
    @elseif(in_array($type, ['group', 'seo'], true))
        <textarea id="{{ $inputId }}" class="ceemes-code-input" name="{{ $inputName }}" @required($required)>{{ json_encode(is_array($value) ? $value : [], JSON_PRETTY_PRINT) }}</textarea>
        <small>Structured field. Gunakan JSON valid untuk versi ini.</small>
    @elseif($type === 'sections')
        <div class="ceemes-inline-note">Section dikelola pada panel Sections di bawah editor.</div>
    @else
        @php($htmlType = match($type) { 'number' => 'number', 'date' => 'date', 'datetime' => 'datetime-local', 'email' => 'email', 'url' => 'url', 'color' => 'color', default => 'text' })
        <input id="{{ $inputId }}" type="{{ $htmlType }}" name="{{ $inputName }}" value="{{ $value }}" @required($required) placeholder="{{ $config['placeholder'] ?? '' }}">
    @endif
    @if(is_string($config['instructions'] ?? null))<small>{{ $config['instructions'] }}</small>@endif
</div>
