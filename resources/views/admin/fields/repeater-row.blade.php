<article class="ceemes-repeater-item" data-repeater-item>
    <header class="ceemes-repeater-item-header">
        <span class="ceemes-drag-handle">::</span>
        <strong>Item <span data-repeater-number>{{ is_numeric($rowIndex) ? ((int) $rowIndex + 1) : '' }}</span></strong>
        <div class="ceemes-section-actions">
            <button type="button" class="ceemes-icon-button" title="Naikkan" data-repeater-up>&uarr;</button>
            <button type="button" class="ceemes-icon-button" title="Turunkan" data-repeater-down>&darr;</button>
            <button type="button" class="ceemes-icon-button is-danger" title="Hapus" data-repeater-remove>&times;</button>
        </div>
    </header>
    <div class="ceemes-field-stack">
        @foreach($schemas as $schema)
            @php
                $nestedField = (object) [
                    'handle' => $schema['handle'],
                    'label' => $schema['label'],
                    'type' => $schema['type'],
                    'width' => $schema['width'] ?? 100,
                    'config' => $schema['config'] ?? [],
                ];
            @endphp
            @include('ceemes::admin.fields.input', [
                'field' => $nestedField,
                'namePrefix' => $inputName.'['.$rowIndex.']',
                'inputPrefix' => $inputId.'-'.$rowIndex,
                'value' => $row[$schema['handle']] ?? null,
            ])
        @endforeach
    </div>
</article>
