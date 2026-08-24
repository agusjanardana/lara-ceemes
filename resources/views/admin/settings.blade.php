@extends('ceemes::admin.layout', ['title' => 'Settings'])

@section('content')
    <div class="ceemes-page-header">
        <div><span class="ceemes-eyebrow">System</span><h1>Settings</h1><p>Konfigurasi global yang dapat dibaca melalui Settings Facade.</p></div>
        <button class="ceemes-button" type="button" data-dialog-open="new-setting">+ Tambah Setting</button>
    </div>

    <section class="ceemes-panel" data-resource-list>
        <div class="ceemes-toolbar">
            <label class="ceemes-search-box"><span>Search</span><input type="search" placeholder="Cari setting..." data-table-search></label>
            <span class="ceemes-result-count"><strong data-visible-count>{{ $settings->count() }}</strong> setting</span>
        </div>
        <div class="ceemes-table-wrap">
            <table class="ceemes-table">
                <thead><tr><th>Key</th><th>Type</th><th>Value</th><th>Autoload</th><th class="ceemes-actions-column">Aksi</th></tr></thead>
                <tbody>
                    @foreach($settings as $setting)
                        @php($displayValue = is_array($setting->value) ? json_encode($setting->value) : json_encode($setting->value, JSON_UNESCAPED_SLASHES))
                        <tr data-table-row data-search="{{ strtolower($setting->group.'.'.$setting->key.' '.$setting->type.' '.$displayValue) }}">
                            <td class="ceemes-cell-primary">{{ $setting->group }}.{{ $setting->key }}</td>
                            <td><span class="ceemes-badge">{{ $setting->type }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($displayValue, 70) }}</td>
                            <td><span class="ceemes-badge {{ $setting->autoload ? 'is-success' : 'is-muted' }}">{{ $setting->autoload ? 'Yes' : 'No' }}</span></td>
                            <td class="ceemes-row-actions"><button class="ceemes-button ceemes-button-secondary ceemes-button-small" type="button" data-dialog-open="setting-{{ $setting->uuid }}">Edit</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ceemes-no-results" data-no-results hidden>Tidak ada setting yang cocok.</div>
    </section>

    @foreach($settings as $setting)
        @php($displayValue = is_array($setting->value) ? json_encode($setting->value) : json_encode($setting->value, JSON_UNESCAPED_SLASHES))
        <dialog class="ceemes-dialog" id="setting-{{ $setting->uuid }}">
            <form method="POST" action="{{ route('ceemes.admin.settings.store') }}">@csrf
                <input type="hidden" name="key" value="{{ $setting->group }}.{{ $setting->key }}">
                <input type="hidden" name="type" value="{{ $setting->type }}">
                <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">Edit setting</span><h2>{{ $setting->group }}.{{ $setting->key }}</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
                <div class="ceemes-dialog-body">
                    <div class="ceemes-field"><label>Value</label><textarea class="{{ $setting->type === 'array' ? 'ceemes-code-input' : '' }}" name="value">{{ is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : trim($displayValue, '"') }}</textarea></div>
                    <label class="ceemes-check"><input type="checkbox" name="autoload" value="1" @checked($setting->autoload)> Autoload setting ini</label>
                </div>
                <div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Update</button></div>
            </form>
        </dialog>
    @endforeach

    <dialog class="ceemes-dialog" id="new-setting">
        <form method="POST" action="{{ route('ceemes.admin.settings.store') }}">@csrf
            <div class="ceemes-dialog-header"><div><span class="ceemes-eyebrow">New setting</span><h2>Tambah Setting</h2></div><button type="button" class="ceemes-dialog-close" data-dialog-close>&times;</button></div>
            <div class="ceemes-dialog-body">
                <div class="ceemes-form-grid">
                    <div class="ceemes-field"><label>Key (group.key)</label><input name="key" placeholder="general.site_name" required></div>
                    <div class="ceemes-field"><label>Type</label><select name="type"><option>string</option><option>integer</option><option>float</option><option>boolean</option><option>array</option><option>null</option></select></div>
                    <div class="ceemes-field ceemes-field-wide"><label>Value</label><textarea name="value"></textarea></div>
                </div>
                <label class="ceemes-check"><input type="checkbox" name="autoload" value="1"> Autoload setting ini</label>
            </div>
            <div class="ceemes-dialog-footer"><button type="button" class="ceemes-button ceemes-button-ghost" data-dialog-close>Batal</button><button class="ceemes-button" type="submit">Simpan</button></div>
        </form>
    </dialog>
@endsection
