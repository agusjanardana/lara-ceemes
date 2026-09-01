@extends('ceemes::admin.layout', ['title' => $content ? $content->title : 'Create Content'])

@section('content')
    @php
        $formAction = $content ? route('ceemes.admin.contents.update', $content) : route('ceemes.admin.contents.store', $set);
    @endphp
    <form method="POST" action="{{ $formAction }}">@csrf @if($content) @method('PUT') @endif
        <div class="ceemes-page-header">
            <div><a class="ceemes-back" href="{{ route('ceemes.admin.contents.index', $set) }}">← {{ $set->name }}</a><span class="ceemes-eyebrow">{{ $content ? 'Edit Content' : 'New Content' }}</span><h1>{{ $content?->title ?: 'Buat Content baru' }}</h1><p>{{ $content ? 'Terakhir diperbarui '.$content->updated_at?->diffForHumans() : 'Isi informasi dan Fixed Fields yang tersedia.' }}</p></div>
            <div class="ceemes-header-actions">@if($content?->publicUrl())<a class="ceemes-button ceemes-button-secondary" href="{{ $content->publicUrl() }}" target="_blank">Visit URL</a>@endif<button class="ceemes-button" type="submit">{{ $content ? 'Simpan perubahan' : 'Buat Content' }}</button></div>
        </div>

        <div class="ceemes-editor-layout">
            <div class="ceemes-editor-main">
                <section class="ceemes-editor-card">
                    <div class="ceemes-editor-tabs"><span class="is-active">Content</span><span>SEO tersedia di panel kanan</span></div>
                    <div class="ceemes-field-stack" style="margin-top:16px">
                        <div class="ceemes-field" style="grid-column:span 8"><label for="title">Title <em>*</em></label><input id="title" name="title" value="{{ old('title', $content?->title) }}" required data-slug-source="#slug" placeholder="Judul halaman atau konten"></div>
                        <div class="ceemes-field" style="grid-column:span 4"><label for="slug">Slug <em>*</em></label><input id="slug" name="slug" value="{{ old('slug', $content?->slug) }}" required data-slug-target data-uri-source="#uri" placeholder="url-friendly-slug"></div>
                        <div class="ceemes-field" style="grid-column:span 12"><label for="uri">Public URL <em>*</em></label><div class="ceemes-url-input"><span>{{ url('/') }}</span><input id="uri" name="uri" value="{{ old('uri', $content?->uri) }}" required data-uri-target placeholder="/contact"></div><small>Gunakan <code>/</code> untuk homepage, <code>/contact</code> untuk halaman Contact, atau path bertingkat seperti <code>/company/team</code>.</small></div>
                    </div>
                </section>

                <section class="ceemes-editor-card">
                    <div class="ceemes-panel-header ceemes-editor-card-header">
                        <div><h2>Fixed Fields</h2><p>Field terstruktur yang berlaku untuk seluruh Content dalam Set {{ $set->name }}.</p></div>
                        <div class="ceemes-header-actions">
                            @if($content)<button class="ceemes-button ceemes-button-secondary ceemes-button-small" type="button" data-dialog-open="create-fixed-field">+ Tambah Field</button>@endif
                        </div>
                    </div>
                    <div class="ceemes-field-stack">@forelse($fields as $field)@include('ceemes::admin.fields.input', ['field' => $field, 'namePrefix' => 'data', 'inputPrefix' => 'content', 'editable' => (bool) $content, 'value' => old('data.'.$field->handle, $content?->data()[$field->handle] ?? ($field->config['default'] ?? null))])@empty<div class="ceemes-empty ceemes-field-stack-empty"><span>+</span><h3>Belum ada Fixed Field</h3><p>Tambahkan headline, featured image, relasi Content, atau data terstruktur lainnya.</p>@if($content)<button class="ceemes-button ceemes-button-secondary ceemes-button-small" type="button" data-dialog-open="create-fixed-field">Tambah Fixed Field pertama</button>@else<small>Simpan Content dahulu sebelum menambah struktur field.</small>@endif</div>@endforelse</div>
                </section>
            </div>

            <aside class="ceemes-editor-sidebar">
                <section class="ceemes-editor-card"><h3>Publishing</h3><div class="ceemes-field"><label for="status">Status</label><select id="status" name="status"><option value="draft" @selected(old('status', $content?->status->value ?? 'draft') === 'draft')>Draft</option><option value="published" @selected(old('status', $content?->status->value) === 'published')>Published</option></select><small>Hanya Content published yang muncul pada query publik.</small></div></section>
                <section class="ceemes-editor-card"><h3>SEO</h3><div class="ceemes-field"><label for="seo_title">Meta title</label><input id="seo_title" name="seo[title]" value="{{ old('seo.title', $content?->seo()['title'] ?? '') }}" maxlength="255"></div><div class="ceemes-field"><label for="seo_description">Meta description</label><textarea id="seo_description" name="seo[description]" maxlength="500">{{ old('seo.description', $content?->seo()['description'] ?? '') }}</textarea></div><div class="ceemes-field"><label for="canonical_url">Canonical URL</label><input id="canonical_url" type="url" name="seo[canonical_url]" value="{{ old('seo.canonical_url', $content?->seo()['canonical_url'] ?? '') }}"></div><div class="ceemes-field"><label>OG Image</label>@include('ceemes::admin.fields.media-picker', ['pickerTitle' => 'OG Image', 'inputName' => 'seo[og_image]', 'inputId' => 'seo-og-image', 'multiple' => false, 'selectedValues' => array_filter([(string) old('seo.og_image', $content?->seo()['og_image'] ?? '')])])</div><input type="hidden" name="seo[robots_index]" value="0"><label class="ceemes-check"><input type="checkbox" name="seo[robots_index]" value="1" @checked((bool) old('seo.robots_index', $content?->seo()['robots_index'] ?? true))> Izinkan indexing</label><input type="hidden" name="seo[robots_follow]" value="0"><label class="ceemes-check"><input type="checkbox" name="seo[robots_follow]" value="1" @checked((bool) old('seo.robots_follow', $content?->seo()['robots_follow'] ?? true))> Ikuti link</label></section>
            </aside>
        </div>
    </form>

    @if($content)
        <dialog class="ceemes-dialog" id="create-fixed-field" @if($errors->hasAny(['label', 'handle', 'type', 'config.*', 'advanced_config'])) data-open-on-error @endif>
            <form method="POST" action="{{ route('ceemes.admin.set-fields.store', $set) }}">@csrf
                <input type="hidden" name="redirect_content_uuid" value="{{ $content->uuid }}">
                <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">{{ $set->name }}</span><h2>Tambah Fixed Field</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
                <div class="ceemes-dialog-body">@include('ceemes::admin.fields.form', ['field' => null, 'formPrefix' => 'content-create-field'])</div>
                <div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Tambah Field</button></div>
            </form>
        </dialog>
    @endif

    @if($content)
        @foreach($fields as $field)
            <dialog class="ceemes-dialog" id="edit-fixed-field-{{ $field->uuid }}">
                <form method="POST" action="{{ route('ceemes.admin.set-fields.update', $field) }}">@csrf @method('PUT')
                    <input type="hidden" name="redirect_content_uuid" value="{{ $content->uuid }}">
                    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">{{ $set->name }}</span><h2>Edit {{ $field->label }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
                    <div class="ceemes-dialog-body">@include('ceemes::admin.fields.form', ['field' => $field, 'formPrefix' => 'content-edit-field-'.$field->uuid])</div>
                    <div class="ceemes-dialog-footer"><button class="ceemes-button ceemes-button-ghost is-danger" type="submit" form="delete-fixed-field-{{ $field->uuid }}">Hapus Field</button><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Simpan Field</button></div>
                </form>
            </dialog>
            <form id="delete-fixed-field-{{ $field->uuid }}" method="POST" action="{{ route('ceemes.admin.set-fields.destroy', $field) }}" data-confirm="Hapus Fixed Field {{ $field->label }}?">@csrf @method('DELETE')<input type="hidden" name="redirect_content_uuid" value="{{ $content->uuid }}"></form>
        @endforeach
    @endif

    @if($content && $sectionFields->isNotEmpty())
        @foreach($sectionFields as $sectionField)
            @php
                $allowedHandles = is_array($sectionField->config['allowed'] ?? null) ? $sectionField->config['allowed'] : [];
                $availableSectionTypes = $allowedHandles === [] ? $sectionTypes : $sectionTypes->whereIn('handle', $allowedHandles);
            @endphp
            <section class="ceemes-editor-card" style="margin-top:20px">
                <div class="ceemes-panel-header" style="padding:0 0 18px;border:0">
                    <div><span class="ceemes-eyebrow">Sections area</span><h2>{{ $sectionField->label }}</h2><p>{{ $sectionField->config['instructions'] ?? 'Susun blok konten reusable pada area '.$sectionField->handle.'.' }}</p></div>
                    <div class="ceemes-header-actions">@if($reusableSections->isNotEmpty())<button class="ceemes-button ceemes-button-secondary" type="button" data-dialog-open="reuse-section-{{ $sectionField->handle }}">Use existing</button>@endif @if($availableSectionTypes->isNotEmpty())<button class="ceemes-button ceemes-button-secondary" type="button" data-dialog-open="add-section-{{ $sectionField->handle }}">+ Create Section</button>@else<a class="ceemes-button ceemes-button-secondary" href="{{ route('ceemes.admin.section-types.index') }}">Create Section Type</a>@endif</div>
                </div>
                <div class="ceemes-section-list">
                    @forelse($content->sections(includeDisabled: true, fieldHandle: $sectionField->handle) as $section)
                        <article class="ceemes-section-card"><div class="ceemes-section-header"><span class="ceemes-drag-handle">::</span><div><strong>{{ $section->name ?: $section->sectionType->name }}</strong><small class="ceemes-muted">{{ $section->sectionType->name }} · shared in {{ $section->contents()->count() }} content(s)</small></div><span class="ceemes-badge {{ $section->pivot->is_enabled ? 'is-success' : 'is-muted' }}">{{ $section->pivot->is_enabled ? 'Enabled' : 'Disabled' }}</span><div class="ceemes-section-actions"><button class="ceemes-icon-button" type="button" data-dialog-open="section-{{ $section->uuid }}">Edit</button><form method="POST" action="{{ route('ceemes.admin.sections.duplicate', $section) }}">@csrf<input type="hidden" name="content_uuid" value="{{ $content->uuid }}"><button class="ceemes-icon-button" type="submit">Copy</button></form><form method="POST" action="{{ route('ceemes.admin.section-placements.destroy', [$content, $section]) }}" data-confirm="Lepaskan Section dari Content ini? Section tetap tersimpan di library.">@csrf @method('DELETE')<button class="ceemes-icon-button is-danger" type="submit">Detach</button></form></div></div></article>
                        <dialog class="ceemes-dialog" id="section-{{ $section->uuid }}"><form method="POST" action="{{ route('ceemes.admin.sections.update', $section) }}">@csrf @method('PUT')<input type="hidden" name="content_uuid" value="{{ $content->uuid }}"><div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Edit Section</span><h2>{{ $section->sectionType->name }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div><div class="ceemes-dialog-body"><div class="ceemes-field"><label>Key</label><input name="key" value="{{ $section->key }}"></div><div class="ceemes-field-stack" style="margin-top:16px">@foreach($section->sectionType->fields as $field)@include('ceemes::admin.fields.input', ['field' => $field, 'namePrefix' => 'data', 'inputPrefix' => 'section-'.$section->uuid, 'value' => $section->data()[$field->handle] ?? null])@endforeach</div></div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Simpan</button></div></form></dialog>
                    @empty
                        <div class="ceemes-empty"><span>+</span><h3>Belum ada Section di {{ $sectionField->label }}</h3>@if($availableSectionTypes->isNotEmpty())<p>Tambahkan {{ $availableSectionTypes->pluck('name')->join(', ') }} sesuai kebutuhan halaman.</p>@else<p>Pilih Section Type yang diizinkan pada konfigurasi field ini.</p>@endif</div>
                    @endforelse
                </div>
            </section>

            @if($reusableSections->isNotEmpty())
                <dialog class="ceemes-dialog" id="reuse-section-{{ $sectionField->handle }}"><form method="POST" action="{{ route('ceemes.admin.section-placements.store', $content) }}">@csrf<input type="hidden" name="region" value="{{ $sectionField->handle }}"><div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Shared Section</span><h2>Use existing Section</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div><div class="ceemes-dialog-body"><div class="ceemes-inline-note" style="margin-bottom:14px">Perubahan pada Section shared akan terlihat di semua Content yang menggunakannya.</div><div class="ceemes-field"><label>Section</label><select name="section_uuid" required><option value="">Pilih Section...</option>@foreach($reusableSections as $reusable)<option value="{{ $reusable->uuid }}">{{ $reusable->name ?: $reusable->handle }} · {{ $reusable->sectionType->name }}</option>@endforeach</select></div></div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Attach Section</button></div></form></dialog>
            @endif

            @if($availableSectionTypes->isNotEmpty())
                <dialog class="ceemes-dialog" id="add-section-{{ $sectionField->handle }}"><form method="POST" action="{{ route('ceemes.admin.sections.store', $content) }}">@csrf<input type="hidden" name="field_handle" value="{{ $sectionField->handle }}"><div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">{{ $sectionField->label }}</span><h2>Tambah Section</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div><div class="ceemes-dialog-body"><div class="ceemes-form-grid"><div class="ceemes-field"><label>Section Type</label><select name="section_type_uuid" data-dynamic-select>@foreach($availableSectionTypes as $type)<option value="{{ $type->uuid }}">{{ $type->name }}</option>@endforeach</select></div><div class="ceemes-field"><label>Optional key</label><input name="key" placeholder="hero, services, cta..."></div></div>@foreach($availableSectionTypes as $type)<div class="ceemes-field-stack" data-dynamic-fields="{{ $type->uuid }}" style="margin-top:18px">@foreach($type->fields as $field)@include('ceemes::admin.fields.input', ['field' => $field, 'namePrefix' => 'section_data['.$type->uuid.']', 'inputPrefix' => 'new-section-'.$sectionField->handle.'-'.$type->uuid, 'value' => $field->config['default'] ?? null])@endforeach</div>@endforeach</div><div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Tambah Section</button></div></form></dialog>
            @endif
        @endforeach
    @endif
@endsection
