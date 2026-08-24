<div class="ceemes-form-grid">
@foreach($fields as $field)
    @php
        $value = ($useOld ?? false) ? old($field['name'], $values[$field['name']] ?? ($field['default'] ?? null)) : ($values[$field['name']] ?? ($field['default'] ?? null));
        $type = $field['type'] ?? 'text';
        $inputId = ($formPrefix ?? 'form').'-'.$field['name'];
    @endphp
    <div class="ceemes-field {{ $type === 'textarea' ? 'ceemes-field-wide' : '' }}">
        @if($type === 'checkbox')
            <input type="hidden" name="{{ $field['name'] }}" value="0">
            <label class="ceemes-switch-row" for="{{ $inputId }}"><span><strong>{{ $field['label'] }}</strong>@isset($field['help'])<small>{{ $field['help'] }}</small>@endisset</span><span class="ceemes-switch"><input id="{{ $inputId }}" type="checkbox" name="{{ $field['name'] }}" value="1" @checked((bool)$value)><i></i></span></label>
        @else
            <label for="{{ $inputId }}">{{ $field['label'] }} @if($field['required'] ?? false)<em>*</em>@endif</label>
            @if($type === 'textarea')
                <textarea id="{{ $inputId }}" name="{{ $field['name'] }}" class="{{ str_contains(strtolower($field['label']), 'json') ? 'ceemes-code-input' : '' }}" placeholder="{{ $field['placeholder'] ?? '' }}">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value }}</textarea>
            @elseif($type === 'select')
                <select id="{{ $inputId }}" name="{{ $field['name'] }}">@foreach($field['options'] ?? [] as $optionValue => $optionLabel)<option value="{{ $optionValue }}" @selected((string)$value === (string)$optionValue)>{{ $optionLabel }}</option>@endforeach</select>
            @else
                <input id="{{ $inputId }}" type="{{ $type }}" name="{{ $field['name'] }}" value="{{ $value }}" placeholder="{{ $field['placeholder'] ?? '' }}">
            @endif
            @isset($field['help'])<small>{{ $field['help'] }}</small>@endisset
        @endif
    </div>
@endforeach
</div>
