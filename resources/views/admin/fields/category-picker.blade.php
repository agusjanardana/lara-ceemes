@php
    $pickerId = $inputId.'-category-picker';
    $postedName = $inputName.($multiple ? '[]' : '');
    $allCategories = $categoryGroups->flatMap(fn ($group) => $group->categories->map(fn ($category) => ['category' => $category, 'group' => $group]));
    $selectedCategories = $allCategories->whereIn('category.uuid', $selectedValues);
@endphp

<div class="ceemes-relation-picker" data-relation-picker-field>
    <div class="ceemes-relation-values" data-relation-picker-values>
        @forelse($selectedCategories as $item)<span>{{ $item['category']->name }}<small>{{ $item['group']->name }}</small></span>@empty<span class="is-placeholder">Belum ada Category dipilih</span>@endforelse
    </div>
    <div data-relation-picker-inputs>@foreach($selectedCategories as $item)<input type="hidden" name="{{ $postedName }}" value="{{ $item['category']->uuid }}">@endforeach</div>
    <button class="ceemes-picker-trigger" type="button" data-dialog-open="{{ $pickerId }}"><span><strong>{{ $multiple ? 'Pilih beberapa Category' : 'Pilih Category' }}</strong><small>Cari berdasarkan nama atau Category Group</small></span><b>Browse</b></button>
</div>

<dialog class="ceemes-dialog ceemes-picker-dialog" id="{{ $pickerId }}" data-relation-picker data-input-name="{{ $postedName }}" data-empty-label="Belum ada Category dipilih">
    <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Categories</span><h2>{{ $pickerTitle }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
    <div class="ceemes-picker-toolbar"><label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari category atau group..." data-picker-search></label></div>
    <div class="ceemes-content-options">
        @foreach($allCategories as $item)<label class="ceemes-content-option" data-picker-item data-search="{{ strtolower($item['category']->name.' '.$item['group']->name) }}"><input type="{{ $multiple ? 'checkbox' : 'radio' }}" @if(!$multiple) name="picker-{{ $inputId }}" @endif value="{{ $item['category']->uuid }}" data-relation-choice data-label="{{ $item['category']->name }}" data-meta="{{ $item['group']->name }}" @checked(in_array($item['category']->uuid, $selectedValues, true))><span><strong>{{ $item['category']->name }}</strong><small>{{ $item['group']->name }}</small></span></label>@endforeach
        @if($allCategories->isEmpty())<div class="ceemes-no-results">Belum ada Category yang dapat dipilih.</div>@endif
    </div>
    <div class="ceemes-dialog-footer"><span class="ceemes-picker-count"><strong data-relation-selected-count>{{ $selectedCategories->count() }}</strong> dipilih</span><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button type="button" class="ceemes-button" data-relation-picker-apply>Terapkan</button></div>
</dialog>
