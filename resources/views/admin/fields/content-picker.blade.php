@php
    $pickerId = $inputId.'-picker';
    $configuredSet = is_string($config['set'] ?? null) ? $config['set'] : '';
    $selectedContents = $contentOptions->whereIn('uuid', $selectedValues);
    $initialSet = $configuredSet !== '' ? $configuredSet : ($selectedContents->first()?->set?->handle ?? '');
    $postedName = $inputName.($multiple ? '[]' : '');
@endphp

<div class="ceemes-relation-picker" data-content-picker-field>
    <div class="ceemes-relation-values" data-content-picker-values>
        @forelse($selectedContents as $selectedContent)<span>{{ $selectedContent->title }}<small>{{ $selectedContent->set->name }}</small></span>@empty<span class="is-placeholder">Belum ada Content dipilih</span>@endforelse
    </div>
    <div data-content-picker-inputs>@foreach($selectedContents as $selectedContent)<input type="hidden" name="{{ $postedName }}" value="{{ $selectedContent->uuid }}">@endforeach</div>
    <button class="ceemes-picker-trigger" type="button" data-dialog-open="{{ $pickerId }}"><span><strong>{{ $multiple ? 'Pilih beberapa Content' : 'Pilih Content' }}</strong><small>{{ $configuredSet !== '' ? 'Sumber: '.$contentSets->firstWhere('handle', $configuredSet)?->name : 'Pilih Set lalu Content' }}</small></span><b>Browse</b></button>
</div>

<dialog class="ceemes-dialog ceemes-picker-dialog" id="{{ $pickerId }}" data-content-picker data-multiple="{{ $multiple ? 'true' : 'false' }}" data-input-name="{{ $postedName }}">
    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Content relation</span><h2>{{ $field->label }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
    <div class="ceemes-picker-toolbar ceemes-picker-toolbar-stacked">
        <div class="ceemes-field"><label>1. Pilih Set</label><select data-content-picker-set @if($configuredSet !== '') data-locked @endif>
            <option value="">Pilih Set...</option>
            @foreach($contentSets as $pickerSet)
                @if($configuredSet === '' || $configuredSet === $pickerSet->handle)<option value="{{ $pickerSet->handle }}" @selected($initialSet === $pickerSet->handle)>{{ $pickerSet->name }}</option>@endif
            @endforeach
        </select></div>
        <div class="ceemes-field"><label>2. Cari Content</label><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari title, slug, atau URL..." data-content-picker-search></label></div>
    </div>
    <div class="ceemes-content-options" data-content-picker-options>
        @foreach($contentOptions as $option)
            <label class="ceemes-content-option" data-content-option data-set="{{ $option->set->handle }}" data-search="{{ strtolower($option->title.' '.$option->slug.' '.$option->uri) }}">
                <input type="{{ $multiple ? 'checkbox' : 'radio' }}" @if(!$multiple) name="picker-{{ $inputId }}" @endif value="{{ $option->uuid }}" data-content-choice data-label="{{ $option->title }}" data-set-label="{{ $option->set->name }}" @checked(in_array($option->uuid, $selectedValues, true))>
                <span><strong>{{ $option->title }}</strong><small>{{ $option->uri ?: '/'.$option->slug }} · {{ $option->set->name }}</small></span><em class="ceemes-badge {{ $option->status->value === 'published' ? 'is-success' : 'is-warning' }}">{{ ucfirst($option->status->value) }}</em>
            </label>
        @endforeach
        <div class="ceemes-no-results" data-content-picker-empty hidden>Tidak ada Content yang cocok.</div>
    </div>
    <div class="ceemes-dialog-footer"><span class="ceemes-picker-count"><strong data-content-selected-count>{{ $selectedContents->count() }}</strong> dipilih</span><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button type="button" class="ceemes-button" data-content-picker-apply>Terapkan</button></div>
</dialog>
