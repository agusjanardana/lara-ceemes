@php
    $config = $field?->config ?? [];
    $options = is_array($config['options'] ?? null) ? collect($config['options'])->map(fn ($label, $value) => $value.': '.$label)->implode("\n") : '';
    $selectedType = $field?->type ?? 'text';
    $repeaterFields = array_values(array_filter($config['fields'] ?? [], 'is_array'));
    $repeaterTypes = array_values(array_diff($fieldTypes, ['repeater', 'sections', 'group', 'seo']));
    $typeDescriptions = [
        'text' => 'Teks pendek satu baris', 'textarea' => 'Teks panjang beberapa baris', 'richtext' => 'Konten teks dengan editor',
        'number' => 'Nilai angka', 'boolean' => 'Pilihan aktif atau tidak', 'select' => 'Pilihan dari daftar opsi',
        'date' => 'Tanggal', 'datetime' => 'Tanggal dan waktu', 'email' => 'Alamat email', 'url' => 'Alamat URL',
        'color' => 'Pemilih warna', 'media' => 'File dari Media Library', 'taxonomy' => 'Relasi ke Taxonomy',
        'entry' => 'Relasi ke Entry dari Collection', 'group' => 'Kelompok data terstruktur', 'repeater' => 'Data berulang',
        'sections' => 'Area page builder reusable', 'seo' => 'Data SEO terstruktur',
    ];
@endphp
<div class="ceemes-form-grid">
    <div class="ceemes-field"><label for="{{ $formPrefix }}-label">Label <em>*</em></label><input id="{{ $formPrefix }}-label" name="label" value="{{ $field?->label }}" placeholder="Headline" required data-slug-source="#{{ $formPrefix }}-handle"><small>Nama yang terlihat oleh editor.</small></div>
    <div class="ceemes-field"><label for="{{ $formPrefix }}-handle">Handle</label><input id="{{ $formPrefix }}-handle" name="handle" value="{{ $field?->handle }}" placeholder="headline" data-slug-target><small>Boleh kosong saat membuat; diisi otomatis.</small></div>
    <div class="ceemes-field"><label>Field type <em>*</em></label><input type="hidden" name="type" value="{{ $selectedType }}" data-field-type-select><button class="ceemes-picker-trigger" type="button" data-dialog-open="field-type-{{ $formPrefix }}"><span><strong data-field-type-label>{{ $selectedType === 'sections' ? 'Sections (Page Builder)' : ucfirst($selectedType) }}</strong><small data-field-type-description>{{ $typeDescriptions[$selectedType] ?? 'Field type' }}</small></span><b>Choose</b></button></div>
    <div class="ceemes-field"><label>Width</label><select name="width"><option value="100" @selected(($field?->width ?? 100) === 100)>Full width</option><option value="50" @selected($field?->width === 50)>Half width</option><option value="33" @selected($field?->width === 33)>One third</option><option value="67" @selected($field?->width === 67)>Two thirds</option></select></div>
    <div class="ceemes-field ceemes-field-wide"><label>Instructions</label><input name="config[instructions]" value="{{ $config['instructions'] ?? '' }}" placeholder="Bantuan singkat untuk editor"></div>
    <div class="ceemes-field"><label>Placeholder</label><input name="config[placeholder]" value="{{ $config['placeholder'] ?? '' }}" placeholder="Teks contoh di dalam input"></div>
    <div class="ceemes-field"><label>Sort order</label><input type="number" min="0" name="sort_order" value="{{ $field?->sort_order ?? $fields->count() }}"></div>
    <div class="ceemes-field ceemes-field-wide"><label>Options <small>(untuk Select)</small></label><textarea name="config[options_text]" placeholder="news: News&#10;article: Article">{{ $options }}</textarea><small>Satu pilihan per baris dengan format value: Label.</small></div>
    <input type="hidden" name="config[required]" value="0"><label class="ceemes-check"><input type="checkbox" name="config[required]" value="1" @checked((bool)($config['required'] ?? false))> Field wajib diisi</label>
    <input type="hidden" name="config[multiple]" value="0"><label class="ceemes-check"><input type="checkbox" name="config[multiple]" value="1" @checked((bool)($config['multiple'] ?? false))> Izinkan banyak pilihan <small>(Entry, Media, Taxonomy)</small></label>
    <div class="ceemes-sections-config ceemes-field-wide" data-sections-config>
        <div><strong>Section Types yang diizinkan</strong><p>Pilih blok yang boleh ditambahkan editor pada area ini.</p></div>
        @forelse($sectionTypes as $sectionType)
            <label class="ceemes-choice-card"><input type="checkbox" name="config[allowed][]" value="{{ $sectionType->handle }}" @checked(in_array($sectionType->handle, $config['allowed'] ?? [], true))><span><strong>{{ $sectionType->name }}</strong><small>{{ $sectionType->description ?: $sectionType->handle }}</small></span></label>
        @empty
            <div class="ceemes-inline-note">Belum ada Section Type. <a href="{{ route('ceemes.admin.section-types.index') }}">Buat Section Type dahulu</a>, lalu kembali ke Field ini.</div>
        @endforelse
    </div>
    <div class="ceemes-entry-config ceemes-field-wide" data-entry-config>
        <div><strong>Entry source</strong><p>Tentukan Collection asal Entry yang boleh dipilih editor.</p></div>
        <div class="ceemes-field"><label>Collection <em>*</em></label><select name="config[collection]" data-entry-collection-config required><option value="">Pilih Collection...</option>@foreach($collections as $collection)<option value="{{ $collection->handle }}" @selected(($config['collection'] ?? '') === $collection->handle)>{{ $collection->name }}</option>@endforeach</select></div>
        <div class="ceemes-inline-note">Aktifkan “Izinkan banyak pilihan” di atas jika satu field boleh menyimpan beberapa Entry.</div>
    </div>
    <div class="ceemes-repeater-config ceemes-field-wide" data-repeater-config data-next-index="{{ count($repeaterFields) }}">
        <div class="ceemes-repeater-config-header">
            <div><strong>Apa yang akan diulang?</strong><p>Susun subfield untuk satu item. Contoh: gambar, judul, dan deskripsi. Editor nanti menambah atau menghapus item tanpa menulis JSON.</p></div>
            <button type="button" class="ceemes-button ceemes-button-secondary ceemes-button-small" data-set-field-type="sections">Butuh blok berbeda? Gunakan Sections</button>
        </div>
        <div class="ceemes-schema-list" data-repeater-schema-list>
            @foreach($repeaterFields as $schemaIndex => $schema)
                @include('ceemes::admin.fields.repeater-schema-row', ['schemaIndex' => $schemaIndex, 'schema' => $schema, 'formPrefix' => $formPrefix, 'typeDescriptions' => $typeDescriptions])
            @endforeach
        </div>
        <div class="ceemes-repeater-empty" data-repeater-schema-empty @hidden($repeaterFields !== [])>Belum ada subfield. Tambahkan field yang ingin diulang.</div>
        <button type="button" class="ceemes-button ceemes-button-secondary ceemes-button-small" data-repeater-schema-add>+ Tambah subfield</button>
        <template data-repeater-schema-template>
            @include('ceemes::admin.fields.repeater-schema-row', ['schemaIndex' => '__INDEX__', 'schema' => ['type' => 'text', 'config' => []], 'formPrefix' => $formPrefix, 'typeDescriptions' => $typeDescriptions])
        </template>
        <div class="ceemes-repeater-limits">
            <div class="ceemes-field"><label>Minimum item</label><input type="number" min="0" name="config[min_rows]" value="{{ $config['min_rows'] ?? 0 }}"></div>
            <div class="ceemes-field"><label>Maximum item</label><input type="number" min="0" name="config[max_rows]" value="{{ $config['max_rows'] ?? '' }}" placeholder="Tanpa batas"></div>
        </div>
    </div>
    <details class="ceemes-advanced-config ceemes-field-wide"><summary>Advanced config JSON</summary><div class="ceemes-field"><label>Konfigurasi tambahan</label><textarea class="ceemes-code-input" name="advanced_config">{{ json_encode($config, JSON_PRETTY_PRINT) }}</textarea><small>Opsional untuk konfigurasi tipe field yang belum tersedia di form.</small></div></details>
</div>

<dialog class="ceemes-dialog ceemes-picker-dialog" id="field-type-{{ $formPrefix }}" data-field-type-picker>
    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Blueprint Field</span><h2>Pilih Field Type</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
    <div class="ceemes-picker-toolbar"><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari text, media, entry..." data-picker-search></label></div>
    <div class="ceemes-type-grid">
        @foreach($fieldTypes as $type)
            <button type="button" class="ceemes-type-option {{ $selectedType === $type ? 'is-selected' : '' }}" data-field-type-option="{{ $type }}" data-label="{{ $type === 'sections' ? 'Sections (Page Builder)' : ucfirst($type) }}" data-description="{{ $typeDescriptions[$type] ?? 'Field type' }}" data-picker-item data-search="{{ strtolower($type.' '.($typeDescriptions[$type] ?? '')) }}"><span>{{ strtoupper(substr($type, 0, 2)) }}</span><div><strong>{{ $type === 'sections' ? 'Sections (Page Builder)' : ucfirst($type) }}</strong><small>{{ $typeDescriptions[$type] ?? 'Field type' }}</small></div></button>
        @endforeach
    </div>
</dialog>

<dialog class="ceemes-dialog ceemes-picker-dialog" id="repeater-type-{{ $formPrefix }}" data-repeater-type-picker>
    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Repeater subfield</span><h2>Pilih tipe subfield</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
    <div class="ceemes-picker-toolbar"><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari text, media, date..." data-picker-search></label></div>
    <div class="ceemes-type-grid">
        @foreach($repeaterTypes as $type)
            <button type="button" class="ceemes-type-option" data-repeater-type-option="{{ $type }}" data-label="{{ ucfirst($type) }}" data-description="{{ $typeDescriptions[$type] ?? 'Field type' }}" data-picker-item data-search="{{ strtolower($type.' '.($typeDescriptions[$type] ?? '')) }}"><span>{{ strtoupper(substr($type, 0, 2)) }}</span><div><strong>{{ ucfirst($type) }}</strong><small>{{ $typeDescriptions[$type] ?? 'Field type' }}</small></div></button>
        @endforeach
    </div>
</dialog>
