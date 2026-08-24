@php
    $pickerId = $inputId.'-picker';
    $configuredCollection = is_string($config['collection'] ?? null) ? $config['collection'] : '';
    $selectedEntries = $entryOptions->whereIn('uuid', $selectedValues);
    $initialCollection = $configuredCollection !== '' ? $configuredCollection : ($selectedEntries->first()?->collection?->handle ?? '');
    $postedName = $inputName.($multiple ? '[]' : '');
@endphp

<div class="ceemes-relation-picker" data-entry-picker-field>
    <div class="ceemes-relation-values" data-entry-picker-values>
        @forelse($selectedEntries as $selectedEntry)<span>{{ $selectedEntry->title }}<small>{{ $selectedEntry->collection->name }}</small></span>@empty<span class="is-placeholder">Belum ada Entry dipilih</span>@endforelse
    </div>
    <div data-entry-picker-inputs>@foreach($selectedEntries as $selectedEntry)<input type="hidden" name="{{ $postedName }}" value="{{ $selectedEntry->uuid }}">@endforeach</div>
    <button class="ceemes-picker-trigger" type="button" data-dialog-open="{{ $pickerId }}"><span><strong>{{ $multiple ? 'Pilih beberapa Entry' : 'Pilih Entry' }}</strong><small>{{ $configuredCollection !== '' ? 'Sumber: '.$entryCollections->firstWhere('handle', $configuredCollection)?->name : 'Pilih Collection lalu Entry' }}</small></span><b>Browse</b></button>
</div>

<dialog class="ceemes-dialog ceemes-picker-dialog" id="{{ $pickerId }}" data-entry-picker data-multiple="{{ $multiple ? 'true' : 'false' }}" data-input-name="{{ $postedName }}">
    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Entry relation</span><h2>{{ $field->label }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
    <div class="ceemes-picker-toolbar ceemes-picker-toolbar-stacked">
        <div class="ceemes-field"><label>1. Pilih Collection</label><select data-entry-picker-collection @if($configuredCollection !== '') data-locked @endif>
            <option value="">Pilih Collection...</option>
            @foreach($entryCollections as $pickerCollection)
                @if($configuredCollection === '' || $configuredCollection === $pickerCollection->handle)<option value="{{ $pickerCollection->handle }}" @selected($initialCollection === $pickerCollection->handle)>{{ $pickerCollection->name }}</option>@endif
            @endforeach
        </select></div>
        <div class="ceemes-field"><label>2. Cari Entry</label><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari title, slug, atau URL..." data-entry-picker-search></label></div>
    </div>
    <div class="ceemes-entry-options" data-entry-picker-options>
        @foreach($entryOptions as $option)
            <label class="ceemes-entry-option" data-entry-option data-collection="{{ $option->collection->handle }}" data-search="{{ strtolower($option->title.' '.$option->slug.' '.$option->uri) }}">
                <input type="{{ $multiple ? 'checkbox' : 'radio' }}" @if(!$multiple) name="picker-{{ $inputId }}" @endif value="{{ $option->uuid }}" data-entry-choice data-label="{{ $option->title }}" data-collection-label="{{ $option->collection->name }}" @checked(in_array($option->uuid, $selectedValues, true))>
                <span><strong>{{ $option->title }}</strong><small>{{ $option->uri ?: '/'.$option->slug }} · {{ $option->collection->name }}</small></span><em class="ceemes-badge {{ $option->status->value === 'published' ? 'is-success' : 'is-warning' }}">{{ ucfirst($option->status->value) }}</em>
            </label>
        @endforeach
        <div class="ceemes-no-results" data-entry-picker-empty hidden>Tidak ada Entry yang cocok.</div>
    </div>
    <div class="ceemes-dialog-footer"><span class="ceemes-picker-count"><strong data-entry-selected-count>{{ $selectedEntries->count() }}</strong> dipilih</span><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button type="button" class="ceemes-button" data-entry-picker-apply>Terapkan</button></div>
</dialog>
