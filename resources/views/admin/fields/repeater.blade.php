@php
    $schemas = array_values(array_filter($config['fields'] ?? [], 'is_array'));
    $rows = is_array($value) ? array_values($value) : [];
    $minimum = max(0, (int) ($config['min_rows'] ?? 0));
    while (count($rows) < $minimum) $rows[] = [];
@endphp

<div class="ceemes-repeater" data-repeater data-next-index="{{ count($rows) }}" data-min-rows="{{ $minimum }}" data-max-rows="{{ $config['max_rows'] ?? '' }}">
    <div class="ceemes-repeater-list" data-repeater-list>
        @foreach($rows as $rowIndex => $row)
            @include('ceemes::admin.fields.repeater-row', ['rowIndex' => $rowIndex, 'row' => $row, 'schemas' => $schemas, 'inputName' => $inputName, 'inputId' => $inputId])
        @endforeach
    </div>
    <div class="ceemes-repeater-empty" data-repeater-empty @hidden($rows !== [])>Belum ada item. Klik tombol di bawah untuk mulai mengisi.</div>
    <button type="button" class="ceemes-button ceemes-button-secondary" data-repeater-add>+ Tambah item</button>
    <template data-repeater-template>
        @include('ceemes::admin.fields.repeater-row', ['rowIndex' => '__INDEX__', 'row' => [], 'schemas' => $schemas, 'inputName' => $inputName, 'inputId' => $inputId])
    </template>
</div>
