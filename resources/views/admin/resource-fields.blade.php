<div class="ceemes-form-grid">
@foreach($fields as $field)
    @php
        $value = ($useOld ?? false) ? old($field['name'], $values[$field['name']] ?? ($field['default'] ?? null)) : ($values[$field['name']] ?? ($field['default'] ?? null));
        $type = $field['type'] ?? 'text';
        $inputId = ($formPrefix ?? 'form').'-'.$field['name'];
    @endphp
    <div class="ceemes-field {{ $type === 'textarea' ? 'ceemes-field-wide' : '' }}" data-resource-field="{{ $field['name'] }}">
        @if($type === 'checkbox')
            <input type="hidden" name="{{ $field['name'] }}" value="0">
            <label class="ceemes-switch-row" for="{{ $inputId }}"><span><strong>{{ $field['label'] }}</strong>@isset($field['help'])<small>{{ $field['help'] }}</small>@endisset</span><span class="ceemes-switch"><input id="{{ $inputId }}" type="checkbox" name="{{ $field['name'] }}" value="1" @checked((bool)$value)><i></i></span></label>
        @else
            <label for="{{ $inputId }}">{{ $field['label'] }} @if($field['required'] ?? false)<em>*</em>@endif</label>
            @if($type === 'textarea')
                <textarea id="{{ $inputId }}" name="{{ $field['name'] }}" class="{{ str_contains(strtolower($field['label']), 'json') ? 'ceemes-code-input' : '' }}" placeholder="{{ $field['placeholder'] ?? '' }}">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value }}</textarea>
            @elseif($type === 'field_type_picker')
                @php
                    $typeOptions = $field['options'] ?? [];
                    $selectedLabel = $typeOptions[$value] ?? ucfirst((string) $value);
                    $typePickerId = 'field-type-'.$inputId;
                @endphp
                <input id="{{ $inputId }}" type="hidden" name="{{ $field['name'] }}" value="{{ $value ?: array_key_first($typeOptions) }}" data-field-type-select>
                <button class="ceemes-picker-trigger" type="button" data-dialog-open="{{ $typePickerId }}"><span><strong data-field-type-label>{{ $selectedLabel }}</strong><small data-field-type-description>{{ $field['descriptions'][$value] ?? 'Pilih cara data disimpan dan diisi.' }}</small></span><b>Choose</b></button>
                <dialog class="ceemes-dialog ceemes-picker-dialog" id="{{ $typePickerId }}" data-field-type-picker>
                    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Section Field</span><h2>Pilih Field Type</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
                    <div class="ceemes-picker-toolbar"><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari text, media, content..." data-picker-search></label></div>
                    <div class="ceemes-type-grid">@foreach($typeOptions as $optionValue => $optionLabel)<button type="button" class="ceemes-type-option {{ (string)$value === (string)$optionValue ? 'is-selected' : '' }}" data-field-type-option="{{ $optionValue }}" data-label="{{ $optionLabel }}" data-description="{{ $field['descriptions'][$optionValue] ?? 'Field type' }}" data-picker-item data-search="{{ strtolower($optionValue.' '.$optionLabel.' '.($field['descriptions'][$optionValue] ?? '')) }}"><span>{{ strtoupper(substr($optionValue, 0, 2)) }}</span><div><strong>{{ $optionLabel }}</strong><small>{{ $field['descriptions'][$optionValue] ?? 'Field type' }}</small></div></button>@endforeach</div>
                </dialog>
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
