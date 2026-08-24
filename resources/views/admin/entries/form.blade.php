@extends('ceemes::admin.layout', ['title' => $entry ? $entry->title : 'Create Entry'])

@section('content')
    @php
        $formAction = $entry ? route('ceemes.admin.entries.update', $entry) : route('ceemes.admin.entries.store', $collection);
    @endphp
    @if(!$entry && $blueprints->isEmpty())
        <div class="ceemes-page-header"><div><a class="ceemes-back" href="{{ route('ceemes.admin.entries.index', $collection) }}">&larr; {{ $collection->name }}</a><span class="ceemes-eyebrow">Setup required</span><h1>Siapkan Blueprint dahulu</h1><p>Entry membutuhkan Blueprint agar Lara Ceemes mengetahui field apa saja yang harus ditampilkan.</p></div></div>
        <section class="ceemes-editor-card ceemes-setup-empty"><span class="ceemes-setup-icon">BP</span><h2>Collection {{ $collection->name }} belum memiliki Blueprint</h2><p>Buat Blueprint, tambahkan field seperti Headline dan Content, lalu kembali untuk membuat Entry.</p><ol><li>Buat Blueprint, misalnya <strong>Standard Page</strong>.</li><li>Tambahkan field yang dibutuhkan.</li><li>Buat Entry dan isi kontennya.</li></ol><a class="ceemes-button" href="{{ route('ceemes.admin.blueprints.index', $collection) }}">Buat Blueprint sekarang &rarr;</a></section>
    @else
    <form method="POST" action="{{ $formAction }}">@csrf @if($entry) @method('PUT') @endif
        <div class="ceemes-page-header">
            <div><a class="ceemes-back" href="{{ route('ceemes.admin.entries.index', $collection) }}">← {{ $collection->name }}</a><span class="ceemes-eyebrow">{{ $entry ? 'Edit Entry' : 'New Entry' }}</span><h1>{{ $entry?->title ?: 'Buat Entry baru' }}</h1><p>{{ $entry ? 'Terakhir diperbarui '.$entry->updated_at?->diffForHumans() : 'Isi informasi dan content field yang tersedia.' }}</p></div>
            <div class="ceemes-header-actions">@if($entry?->publicUrl())<a class="ceemes-button ceemes-button-secondary" href="{{ $entry->publicUrl() }}" target="_blank">Visit URL</a>@endif<button class="ceemes-button" type="submit">{{ $entry ? 'Simpan perubahan' : 'Buat Entry' }}</button></div>
        </div>

        <div class="ceemes-editor-layout">
            <div class="ceemes-editor-main">
                <section class="ceemes-editor-card">
                    <div class="ceemes-editor-tabs"><span class="is-active">Content</span><span>SEO tersedia di panel kanan</span></div>
                    @unless($entry)
                        <div class="ceemes-field ceemes-field-wide"><label for="blueprint_uuid">Blueprint <em>*</em></label><select id="blueprint_uuid" name="blueprint_uuid" data-blueprint-select required>@foreach($blueprints as $blueprint)<option value="{{ $blueprint->uuid }}" @selected(old('blueprint_uuid') === $blueprint->uuid)>{{ $blueprint->name }}</option>@endforeach</select><small>Blueprint menentukan field yang tersedia untuk Entry ini.</small></div>
                    @endunless
                    <div class="ceemes-field-stack" style="margin-top:16px">
                        <div class="ceemes-field" style="grid-column:span 8"><label for="title">Title <em>*</em></label><input id="title" name="title" value="{{ old('title', $entry?->title) }}" required data-slug-source="#slug" placeholder="Judul halaman atau konten"></div>
                        <div class="ceemes-field" style="grid-column:span 4"><label for="slug">Slug <em>*</em></label><input id="slug" name="slug" value="{{ old('slug', $entry?->slug) }}" required data-slug-target data-uri-source="#uri" placeholder="url-friendly-slug"></div>
                        <div class="ceemes-field" style="grid-column:span 12"><label for="uri">Public URL <em>*</em></label><div class="ceemes-url-input"><span>{{ url('/') }}</span><input id="uri" name="uri" value="{{ old('uri', $entry?->uri) }}" required data-uri-target placeholder="/contact"></div><small>Gunakan <code>/</code> untuk homepage, <code>/contact</code> untuk halaman Contact, atau path bertingkat seperti <code>/company/team</code>.</small></div>
                    </div>
                </section>

                @if($entry)
                    <section class="ceemes-editor-card"><h2>{{ $entry->blueprint->name }} Fields</h2><div class="ceemes-field-stack">@forelse($entry->blueprint->fields as $field)@include('ceemes::admin.fields.input', ['field' => $field, 'namePrefix' => 'data', 'inputPrefix' => 'entry', 'value' => old('data.'.$field->handle, $entry->data()[$field->handle] ?? null)])@empty<div class="ceemes-empty"><h3>Blueprint belum memiliki field</h3><p>Tambahkan field melalui menu Collections → Blueprints.</p></div>@endforelse</div></section>
                @else
                    @foreach($blueprints as $blueprint)
                        <section class="ceemes-editor-card" data-blueprint-fields="{{ $blueprint->uuid }}"><h2>{{ $blueprint->name }} Fields</h2><div class="ceemes-field-stack">@forelse($blueprint->fields as $field)@include('ceemes::admin.fields.input', ['field' => $field, 'namePrefix' => 'blueprint_data['.$blueprint->uuid.']', 'inputPrefix' => 'blueprint-'.$blueprint->uuid, 'value' => old('blueprint_data.'.$blueprint->uuid.'.'.$field->handle, $field->config['default'] ?? null)])@empty<div class="ceemes-empty"><h3>Blueprint belum memiliki field</h3><p>Entry tetap dapat dibuat dengan Title dan Slug.</p></div>@endforelse</div></section>
                    @endforeach
                @endif
            </div>

            <aside class="ceemes-editor-sidebar">
                <section class="ceemes-editor-card"><h3>Publishing</h3><div class="ceemes-field"><label for="status">Status</label><select id="status" name="status"><option value="draft" @selected(old('status', $entry?->status->value ?? 'draft') === 'draft')>Draft</option><option value="published" @selected(old('status', $entry?->status->value) === 'published')>Published</option></select><small>Hanya Entry published yang muncul pada query publik.</small></div></section>
                <section class="ceemes-editor-card"><h3>SEO</h3><div class="ceemes-field"><label for="seo_title">Meta title</label><input id="seo_title" name="seo[title]" value="{{ old('seo.title', $entry?->seo()['title'] ?? '') }}" maxlength="255"></div><div class="ceemes-field"><label for="seo_description">Meta description</label><textarea id="seo_description" name="seo[description]" maxlength="500">{{ old('seo.description', $entry?->seo()['description'] ?? '') }}</textarea></div><div class="ceemes-field"><label for="canonical_url">Canonical URL</label><input id="canonical_url" type="url" name="seo[canonical_url]" value="{{ old('seo.canonical_url', $entry?->seo()['canonical_url'] ?? '') }}"></div><div class="ceemes-field"><label for="og_image">OG Image</label><select id="og_image" name="seo[og_image]"><option value="">Default global</option>@foreach($mediaItems as $media)<option value="{{ $media->uuid }}" @selected(old('seo.og_image', $entry?->seo()['og_image'] ?? '') === $media->uuid)>{{ $media->title ?: $media->original_filename }}</option>@endforeach</select></div><input type="hidden" name="seo[robots_index]" value="0"><label class="ceemes-check"><input type="checkbox" name="seo[robots_index]" value="1" @checked((bool) old('seo.robots_index', $entry?->seo()['robots_index'] ?? true))> Izinkan indexing</label><input type="hidden" name="seo[robots_follow]" value="0"><label class="ceemes-check"><input type="checkbox" name="seo[robots_follow]" value="1" @checked((bool) old('seo.robots_follow', $entry?->seo()['robots_follow'] ?? true))> Ikuti link</label></section>
            </aside>
        </div>
    </form>
    @endif

    @if($entry && $sectionFields->isNotEmpty())
        @foreach($sectionFields as $sectionField)
            @php
                $allowedHandles = is_array($sectionField->config['allowed'] ?? null) ? $sectionField->config['allowed'] : [];
                $availableSectionTypes = $sectionTypes->whereIn('handle', $allowedHandles);
            @endphp
            <section class="ceemes-editor-card" style="margin-top:20px">
                <div class="ceemes-panel-header" style="padding:0 0 18px;border:0">
                    <div><span class="ceemes-eyebrow">Sections area</span><h2>{{ $sectionField->label }}</h2><p>{{ $sectionField->config['instructions'] ?? 'Susun blok konten reusable pada area '.$sectionField->handle.'.' }}</p></div>
                    @if($availableSectionTypes->isNotEmpty())<button class="ceemes-button ceemes-button-secondary" type="button" data-dialog-open="add-section-{{ $sectionField->handle }}">+ Add Section</button>@else<a class="ceemes-button ceemes-button-secondary" href="{{ route('ceemes.admin.fields.index', $entry->blueprint) }}">Atur Section Types</a>@endif
                </div>
                <div class="ceemes-section-list">
                    @forelse($entry->sections(includeDisabled: true, fieldHandle: $sectionField->handle) as $section)
                        <article class="ceemes-section-card"><div class="ceemes-section-header"><span class="ceemes-drag-handle">::</span><div><strong>{{ $section->sectionType->name }}</strong><small class="ceemes-muted">{{ $section->key ?: $section->sectionType->handle }}</small></div><span class="ceemes-badge {{ $section->is_enabled ? 'is-success' : 'is-muted' }}">{{ $section->is_enabled ? 'Enabled' : 'Disabled' }}</span><div class="ceemes-section-actions"><button class="ceemes-icon-button" type="button" data-dialog-open="section-{{ $section->uuid }}">Edit</button><form method="POST" action="{{ route('ceemes.admin.sections.toggle', $section) }}">@csrf<button class="ceemes-icon-button" type="submit">{{ $section->is_enabled ? 'On' : 'Off' }}</button></form><form method="POST" action="{{ route('ceemes.admin.sections.duplicate', $section) }}">@csrf<button class="ceemes-icon-button" type="submit">Copy</button></form><form method="POST" action="{{ route('ceemes.admin.sections.destroy', $section) }}" data-confirm="Hapus Section ini?">@csrf @method('DELETE')<button class="ceemes-icon-button is-danger" type="submit">Hapus</button></form></div></div></article>
                        <dialog class="ceemes-dialog" id="section-{{ $section->uuid }}"><form method="POST" action="{{ route('ceemes.admin.sections.update', $section) }}">@csrf @method('PUT')<div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Edit Section</span><h2>{{ $section->sectionType->name }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div><div class="ceemes-dialog-body"><div class="ceemes-field"><label>Key</label><input name="key" value="{{ $section->key }}"></div><div class="ceemes-field-stack" style="margin-top:16px">@foreach($section->sectionType->fields as $field)@include('ceemes::admin.fields.input', ['field' => $field, 'namePrefix' => 'data', 'inputPrefix' => 'section-'.$section->uuid, 'value' => $section->data()[$field->handle] ?? null])@endforeach</div></div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Simpan</button></div></form></dialog>
                    @empty
                        <div class="ceemes-empty"><span>+</span><h3>Belum ada Section di {{ $sectionField->label }}</h3>@if($availableSectionTypes->isNotEmpty())<p>Tambahkan {{ $availableSectionTypes->pluck('name')->join(', ') }} sesuai kebutuhan halaman.</p>@else<p>Pilih Section Type yang diizinkan pada konfigurasi field ini.</p>@endif</div>
                    @endforelse
                </div>
            </section>

            @if($availableSectionTypes->isNotEmpty())
                <dialog class="ceemes-dialog" id="add-section-{{ $sectionField->handle }}"><form method="POST" action="{{ route('ceemes.admin.sections.store', $entry) }}">@csrf<input type="hidden" name="field_handle" value="{{ $sectionField->handle }}"><div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">{{ $sectionField->label }}</span><h2>Tambah Section</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div><div class="ceemes-dialog-body"><div class="ceemes-form-grid"><div class="ceemes-field"><label>Section Type</label><select name="section_type_uuid" data-blueprint-select>@foreach($availableSectionTypes as $type)<option value="{{ $type->uuid }}">{{ $type->name }}</option>@endforeach</select></div><div class="ceemes-field"><label>Optional key</label><input name="key" placeholder="hero, services, cta..."></div></div>@foreach($availableSectionTypes as $type)<div class="ceemes-field-stack" data-blueprint-fields="{{ $type->uuid }}" style="margin-top:18px">@foreach($type->fields as $field)@include('ceemes::admin.fields.input', ['field' => $field, 'namePrefix' => 'section_data['.$type->uuid.']', 'inputPrefix' => 'new-section-'.$sectionField->handle.'-'.$type->uuid, 'value' => $field->config['default'] ?? null])@endforeach</div>@endforeach</div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Tambah Section</button></div></form></dialog>
            @endif
        @endforeach
    @endif
@endsection
