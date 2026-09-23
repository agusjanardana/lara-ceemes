@php
    $config = $field?->config ?? [];
    $options = is_array($config['options'] ?? null) ? collect($config['options'])->map(fn ($label, $value) => $value.': '.$label)->implode("\n") : '';
    $selectedType = $field?->type ?? 'text';
    $visibility = is_array($config['visibility'] ?? null) ? $config['visibility'] : [];
    $visibilityFields = collect($fields ?? [])->reject(fn ($candidate) => isset($field->uuid) && $candidate->uuid === $field->uuid);
    $repeaterFields = array_values(array_filter($config['fields'] ?? [], 'is_array'));
    $repeaterTypes = array_values(array_diff($fieldTypes, ['repeater', 'sections', 'group', 'seo']));
    $typeDescriptions = [
        'text' => 'Teks pendek satu baris', 'textarea' => 'Teks panjang beberapa baris', 'richtext' => 'Konten teks dengan editor',
        'number' => 'Nilai angka', 'boolean' => 'Pilihan aktif atau tidak', 'select' => 'Pilihan dari daftar opsi',
        'date' => 'Tanggal', 'datetime' => 'Tanggal dan waktu', 'email' => 'Alamat email', 'url' => 'Alamat URL',
        'color' => 'Pemilih warna', 'media' => 'File dari Media Library',
        'content' => 'Relasi ke Content dari Set', 'category' => 'Relasi ke Category Group', 'group' => 'Kelompok data terstruktur', 'repeater' => 'Data berulang',
        'sections' => 'Area page builder reusable', 'seo' => 'Data SEO terstruktur',
    ];
@endphp
<div class="ceemes-config-tabs" data-config-tabs>
    <div class="ceemes-editor-tabs" role="tablist"><button type="button" class="is-active" data-config-tab="general">General</button><button type="button" data-config-tab="validation">Validation</button><button type="button" data-config-tab="visibility">Visibility</button></div>
    <div data-config-panel="general"><div class="ceemes-form-grid">
    <div class="ceemes-field"><label for="{{ $formPrefix }}-label">Label <em>*</em></label><input id="{{ $formPrefix }}-label" name="label" value="{{ $field?->label }}" placeholder="Headline" required data-slug-source="#{{ $formPrefix }}-handle"><small>Nama yang terlihat oleh editor.</small></div>
    <div class="ceemes-field"><label for="{{ $formPrefix }}-handle">Handle</label><input id="{{ $formPrefix }}-handle" name="handle" value="{{ $field?->handle }}" placeholder="headline" data-slug-target><small>Boleh kosong saat membuat; diisi otomatis.</small></div>
    <div class="ceemes-field"><label>Field type <em>*</em></label><input type="hidden" name="type" value="{{ $selectedType }}" data-field-type-select><button class="ceemes-picker-trigger" type="button" data-dialog-open="field-type-{{ $formPrefix }}"><span><strong data-field-type-label>{{ $selectedType === 'sections' ? 'Sections (Page Builder)' : ucfirst($selectedType) }}</strong><small data-field-type-description>{{ $typeDescriptions[$selectedType] ?? 'Field type' }}</small></span><b>Choose</b></button></div>
    <div class="ceemes-field"><label>Width</label><select name="width"><option value="100" @selected(($field?->width ?? 100) === 100)>Full width</option><option value="50" @selected($field?->width === 50)>Half width</option><option value="33" @selected($field?->width === 33)>One third</option><option value="67" @selected($field?->width === 67)>Two thirds</option></select></div>
    <div class="ceemes-field ceemes-field-wide"><label>Instructions</label><input name="config[instructions]" value="{{ $config['instructions'] ?? '' }}" placeholder="Bantuan singkat untuk editor"></div>
    <div class="ceemes-field"><label>Placeholder</label><input name="config[placeholder]" value="{{ $config['placeholder'] ?? '' }}" placeholder="Teks contoh di dalam input"></div>
    <div class="ceemes-field"><label>Sort order</label><input type="number" min="0" name="sort_order" value="{{ $field?->sort_order ?? $fields->count() }}"></div>
    <div class="ceemes-field ceemes-field-wide"><label>Options <small>(untuk Select)</small></label><textarea name="config[options_text]" placeholder="news: News&#10;article: Article">{{ $options }}</textarea><small>Satu pilihan per baris dengan format value: Label.</small></div>
    <input type="hidden" name="config[multiple]" value="0"><label class="ceemes-check"><input type="checkbox" name="config[multiple]" value="1" @checked((bool)($config['multiple'] ?? false))> Izinkan banyak pilihan <small>(Content, Media, Category)</small></label>
    <div class="ceemes-sections-config ceemes-field-wide" data-sections-config>
        <div><strong>Section Types yang diizinkan</strong><p>Pilih blok yang boleh ditambahkan editor pada area ini.</p></div>
        @forelse($sectionTypes as $sectionType)
            <label class="ceemes-choice-card"><input type="checkbox" name="config[allowed][]" value="{{ $sectionType->handle }}" @checked(in_array($sectionType->handle, $config['allowed'] ?? [], true))><span><strong>{{ $sectionType->name }}</strong><small>{{ $sectionType->description ?: $sectionType->handle }}</small></span></label>
        @empty
            <div class="ceemes-inline-note">Belum ada Section Type. <a href="{{ route('ceemes.admin.section-types.index') }}">Buat Section Type dahulu</a>, lalu kembali ke Field ini.</div>
        @endforelse
    </div>
    <div class="ceemes-content-config ceemes-field-wide" data-content-config>
        <div><strong>Content source</strong><p>Tentukan Set asal Content yang boleh dipilih editor.</p></div>
        <div class="ceemes-field"><label>Set <em>*</em></label><select name="config[set]" data-content-set-config required><option value="">Pilih Set...</option>@foreach($sets as $set)<option value="{{ $set->handle }}" @selected(($config['set'] ?? '') === $set->handle)>{{ $set->name }}</option>@endforeach</select></div>
        <div class="ceemes-inline-note">Aktifkan “Izinkan banyak pilihan” di atas jika satu field boleh menyimpan beberapa Content.</div>
    </div>
    <div class="ceemes-repeater-config ceemes-field-wide" data-repeater-config data-next-index="{{ count($repeaterFields) }}">
        <div class="ceemes-repeater-config-header">
            <div><strong>Apa yang akan diulang?</strong><p>Susun subfield untuk satu item. Contoh: gambar, judul, dan deskripsi. Editor nanti menambah atau menghapus item tanpa menulis JSON.</p></div>
            @if(in_array('sections', $fieldTypes, true))<button type="button" class="ceemes-button ceemes-button-secondary ceemes-button-small" data-set-field-type="sections">Butuh blok berbeda? Gunakan Sections</button>@endif
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
    </div></div>

    <div data-config-panel="validation" hidden>
        <div class="ceemes-config-intro"><strong>Aturan input</strong><p>Aturan ini diperiksa di browser dan server saat editor menyimpan Content atau Section.</p></div>
        <div class="ceemes-form-grid">
            <div class="ceemes-field ceemes-field-wide"><input type="hidden" name="config[required]" value="0"><label class="ceemes-choice-card"><input type="checkbox" name="config[required]" value="1" @checked((bool)($config['required'] ?? false))><span><strong>Wajib diisi</strong><small>Content tidak dapat disimpan ketika field ini kosong dan sedang terlihat.</small></span></label></div>
            <div class="ceemes-validation-group ceemes-field-wide" data-validation-for="text textarea richtext email url"><div class="ceemes-form-grid"><div class="ceemes-field"><label>Panjang minimum</label><input type="number" min="0" name="config[min_length]" value="{{ $config['min_length'] ?? '' }}" placeholder="Tidak dibatasi"></div><div class="ceemes-field"><label>Panjang maksimum</label><input type="number" min="0" name="config[max_length]" value="{{ $config['max_length'] ?? '' }}" placeholder="{{ $selectedType === 'text' ? '255' : 'Tidak dibatasi' }}"></div></div></div>
            <div class="ceemes-validation-group ceemes-field-wide" data-validation-for="number"><div class="ceemes-form-grid"><div class="ceemes-field"><label>Nilai minimum</label><input type="number" step="any" name="config[min]" value="{{ $config['min'] ?? '' }}"></div><div class="ceemes-field"><label>Nilai maksimum</label><input type="number" step="any" name="config[max]" value="{{ $config['max'] ?? '' }}"></div><div class="ceemes-field ceemes-field-wide"><input type="hidden" name="config[integer]" value="0"><label class="ceemes-check"><input type="checkbox" name="config[integer]" value="1" @checked((bool)($config['integer'] ?? false))> Hanya izinkan bilangan bulat</label></div></div></div>
            <div class="ceemes-validation-group ceemes-field-wide" data-validation-for="media content category group seo"><div class="ceemes-form-grid"><div class="ceemes-field"><label>Minimum pilihan/item</label><input type="number" min="0" name="config[min_items]" value="{{ $config['min_items'] ?? '' }}"></div><div class="ceemes-field"><label>Maksimum pilihan/item</label><input type="number" min="0" name="config[max_items]" value="{{ $config['max_items'] ?? '' }}"></div></div></div>
            <div class="ceemes-validation-group ceemes-field-wide" data-validation-for="date datetime"><div class="ceemes-form-grid"><div class="ceemes-field"><label>Tidak sebelum</label><input type="{{ $selectedType === 'datetime' ? 'datetime-local' : 'date' }}" name="config[after_or_equal]" value="{{ $config['after_or_equal'] ?? '' }}"></div><div class="ceemes-field"><label>Tidak setelah</label><input type="{{ $selectedType === 'datetime' ? 'datetime-local' : 'date' }}" name="config[before_or_equal]" value="{{ $config['before_or_equal'] ?? '' }}"></div></div></div>
            <div class="ceemes-field ceemes-field-wide"><label>Pesan error khusus</label><input name="config[validation_message]" value="{{ $config['validation_message'] ?? '' }}" placeholder="Contoh: Headline harus diisi maksimal 80 karakter."><small>Kosongkan untuk memakai pesan otomatis berdasarkan label dan aturan yang gagal.</small></div>
        </div>
    </div>

    <div data-config-panel="visibility" hidden>
        <div class="ceemes-config-intro"><strong>Conditional visibility</strong><p>Tampilkan field hanya ketika nilai field lain memenuhi kondisi. Field tersembunyi tidak akan divalidasi atau disimpan.</p></div>
        <div class="ceemes-form-grid">
            <div class="ceemes-field ceemes-field-wide"><input type="hidden" name="config[visibility][enabled]" value="0"><label class="ceemes-choice-card"><input type="checkbox" name="config[visibility][enabled]" value="1" @checked((bool)($visibility['enabled'] ?? false)) data-visibility-enabled><span><strong>Aktifkan Visible when</strong><small>Cocok untuk field lanjutan setelah toggle, pilihan Select, atau relasi diisi.</small></span></label></div>
            <div class="ceemes-field" data-visibility-setting><label>Field sumber</label><select name="config[visibility][field]" data-visibility-source><option value="">Pilih field...</option>@foreach($visibilityFields as $candidate)<option value="{{ $candidate->handle }}" data-options="{{ json_encode(is_array($candidate->config['options'] ?? null) ? $candidate->config['options'] : []) }}" @selected(($visibility['field'] ?? '') === $candidate->handle)>{{ $candidate->label }} ({{ $candidate->handle }})</option>@endforeach</select><small>Gunakan field lain dalam Set atau Section Type yang sama.</small></div>
            <div class="ceemes-field" data-visibility-setting><label>Kondisi</label><select name="config[visibility][operator]" data-visibility-operator><option value="filled" @selected(($visibility['operator'] ?? '') === 'filled')>Sudah diisi</option><option value="empty" @selected(($visibility['operator'] ?? '') === 'empty')>Masih kosong</option><option value="equals" @selected(($visibility['operator'] ?? '') === 'equals')>Sama dengan</option><option value="not_equals" @selected(($visibility['operator'] ?? '') === 'not_equals')>Berbeda dengan</option><option value="contains" @selected(($visibility['operator'] ?? '') === 'contains')>Memuat pilihan/nilai</option><option value="not_contains" @selected(($visibility['operator'] ?? '') === 'not_contains')>Tidak memuat pilihan/nilai</option><option value="truthy" @selected(($visibility['operator'] ?? '') === 'truthy')>Aktif / Ya</option><option value="falsy" @selected(($visibility['operator'] ?? '') === 'falsy')>Nonaktif / Tidak</option></select></div>
            <div class="ceemes-field ceemes-field-wide" data-visibility-value><label>Nilai pembanding</label><input name="config[visibility][value]" value="{{ $visibility['value'] ?? '' }}" list="visibility-options-{{ $formPrefix }}" placeholder="Masukkan value opsi, misalnya business"><datalist id="visibility-options-{{ $formPrefix }}" data-visibility-options></datalist><small>Untuk Select gunakan value opsi, bukan label tampilannya.</small></div>
            @if($visibilityFields->isEmpty())<div class="ceemes-inline-note ceemes-field-wide">Buat minimal satu field lain terlebih dahulu sebelum memakai conditional visibility.</div>@endif
        </div>
    </div>
</div>

<dialog class="ceemes-dialog ceemes-picker-dialog" id="field-type-{{ $formPrefix }}" data-field-type-picker>
    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Set Field</span><h2>Pilih Field Type</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
    <div class="ceemes-picker-toolbar"><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari text, media, content..." data-picker-search></label></div>
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
