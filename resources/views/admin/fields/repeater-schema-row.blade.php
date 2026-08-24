@php
    $schemaType = $schema['type'] ?? 'text';
    $schemaConfig = is_array($schema['config'] ?? null) ? $schema['config'] : [];
@endphp
<article class="ceemes-schema-row" data-repeater-schema-row>
    <div class="ceemes-schema-row-title"><span class="ceemes-drag-handle">::</span><strong>Subfield <span data-schema-number>{{ is_numeric($schemaIndex) ? ((int) $schemaIndex + 1) : '' }}</span></strong><button type="button" class="ceemes-icon-button is-danger" title="Hapus subfield" data-repeater-schema-remove>&times;</button></div>
    <div class="ceemes-field"><label>Label <em>*</em></label><input name="config[fields][{{ $schemaIndex }}][label]" value="{{ $schema['label'] ?? '' }}" placeholder="Judul" data-schema-label required></div>
    <div class="ceemes-field"><label>Handle</label><input name="config[fields][{{ $schemaIndex }}][handle]" value="{{ $schema['handle'] ?? '' }}" placeholder="judul" data-schema-handle></div>
    <div class="ceemes-field"><label>Field type <em>*</em></label><input type="hidden" name="config[fields][{{ $schemaIndex }}][type]" value="{{ $schemaType }}" data-repeater-schema-type><button type="button" class="ceemes-picker-trigger" data-repeater-type-open="repeater-type-{{ $formPrefix }}"><span><strong data-repeater-schema-type-label>{{ ucfirst($schemaType) }}</strong><small data-repeater-schema-type-description>{{ $typeDescriptions[$schemaType] ?? 'Field type' }}</small></span><b>Choose</b></button></div>
    <div class="ceemes-field"><label>Width</label><select name="config[fields][{{ $schemaIndex }}][width]"><option value="100" @selected(($schema['width'] ?? 100) == 100)>Full width</option><option value="50" @selected(($schema['width'] ?? 100) == 50)>Half width</option></select></div>
    <div class="ceemes-field ceemes-schema-placeholder"><label>Placeholder</label><input name="config[fields][{{ $schemaIndex }}][placeholder]" value="{{ $schemaConfig['placeholder'] ?? '' }}"></div>
    <label class="ceemes-check"><input type="hidden" name="config[fields][{{ $schemaIndex }}][required]" value="0"><input type="checkbox" name="config[fields][{{ $schemaIndex }}][required]" value="1" @checked((bool) ($schemaConfig['required'] ?? false))> Wajib diisi</label>
</article>
