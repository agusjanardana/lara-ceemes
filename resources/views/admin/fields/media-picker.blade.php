@php
    $pickerId = $inputId.'-media-picker';
    $postedName = $inputName.($multiple ? '[]' : '');
    $selectedMedia = $mediaItems->whereIn('uuid', $selectedValues);
@endphp

<div class="ceemes-relation-picker" data-relation-picker-field>
    <div class="ceemes-relation-values" data-relation-picker-values>
        @forelse($selectedMedia as $media)<span>{{ $media->title ?: $media->original_filename }}<small>{{ $media->mime_type }}</small></span>@empty<span class="is-placeholder">Belum ada Media dipilih</span>@endforelse
    </div>
    <div data-relation-picker-inputs>@foreach($selectedMedia as $media)<input type="hidden" name="{{ $postedName }}" value="{{ $media->uuid }}">@endforeach</div>
    <button class="ceemes-picker-trigger" type="button" data-dialog-open="{{ $pickerId }}"><span><strong>{{ $multiple ? 'Pilih beberapa Media' : 'Pilih Media' }}</strong><small>Browse Media Library tanpa meninggalkan editor</small></span><b>Browse</b></button>
</div>

<dialog class="ceemes-dialog ceemes-picker-dialog" id="{{ $pickerId }}" data-relation-picker data-media-picker data-input-name="{{ $postedName }}" data-empty-label="Belum ada Media dipilih">
    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Media Library</span><h2>{{ $pickerTitle }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
    <div class="ceemes-picker-toolbar ceemes-media-picker-toolbar"><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari nama file, title, atau alt..." data-media-picker-search></label><select data-media-picker-type><option value="">Semua jenis</option><option value="image">Gambar</option><option value="document">Dokumen</option><option value="other">Lainnya</option></select><select data-media-picker-folder><option value="">Semua folder</option><option value="root">Tanpa folder</option>@foreach($mediaFolders as $folder)<option value="{{ $folder->uuid }}">{{ $folder->name }}</option>@endforeach</select></div>
    <div class="ceemes-media-picker-grid">
        @foreach($mediaItems as $media)
            @php($mediaType = str_starts_with($media->mime_type, 'image/') ? 'image' : ((str_starts_with($media->mime_type, 'application/') || str_starts_with($media->mime_type, 'text/')) ? 'document' : 'other'))
            <label class="ceemes-media-picker-option" data-media-picker-option data-media-type="{{ $mediaType }}" data-media-folder="{{ $media->folder_uuid ?: 'root' }}" data-search="{{ strtolower(($media->title ?: '').' '.$media->original_filename.' '.($media->alt ?: '').' '.$media->mime_type) }}">
                <input type="{{ $multiple ? 'checkbox' : 'radio' }}" @if(!$multiple) name="picker-{{ $inputId }}" @endif value="{{ $media->uuid }}" data-relation-choice data-label="{{ $media->title ?: $media->original_filename }}" data-meta="{{ $media->mime_type }}" @checked(in_array($media->uuid, $selectedValues, true))>
                <span class="ceemes-media-picker-preview">@if(str_starts_with($media->mime_type, 'image/'))<img src="{{ $media->url() }}" alt="">@else<span>{{ strtoupper($media->extension ?: 'FILE') }}</span>@endif</span>
                <span><strong>{{ $media->title ?: $media->original_filename }}</strong><small>{{ $media->mime_type }}</small></span>
            </label>
        @endforeach
        @if($mediaItems->isEmpty())<div class="ceemes-no-results">Media Library masih kosong. Upload file melalui menu Media.</div>@endif
    </div>
    <div class="ceemes-dialog-footer"><span class="ceemes-picker-count"><strong data-relation-selected-count>{{ $selectedMedia->count() }}</strong> dipilih</span><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button type="button" class="ceemes-button" data-relation-picker-apply>Terapkan</button></div>
</dialog>
