<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo->title ?: $content->title }}@if($seo->siteTitle && $seo->siteTitle !== $seo->title) {{ $seo->titleSeparator }} {{ $seo->siteTitle }}@endif</title>
    @if($seo->description)<meta name="description" content="{{ $seo->description }}">@endif
    <meta name="robots" content="{{ $seo->robotsIndex ? 'index' : 'noindex' }},{{ $seo->robotsFollow ? 'follow' : 'nofollow' }}">
    <link rel="canonical" href="{{ $seo->canonicalUrl ?: $content->publicUrl() }}">
    <link rel="stylesheet" href="{{ asset('vendor/ceemes/ceemes.css') }}">
</head>
<body class="ceemes-public-body">
<main class="ceemes-public-page">
    <header class="ceemes-public-header"><a href="/">{{ config('app.name', 'Lara Ceemes') }}</a>@auth<a href="{{ route('ceemes.admin.contents.edit', $content) }}">Edit Content</a>@endauth</header>
    <article class="ceemes-public-content">
        <span class="ceemes-eyebrow">{{ $content->set->name }}</span>
        <h1>{{ $content->title }}</h1>

        @foreach($content->set->fields as $field)
            @if($field->type === 'sections')
                <div class="ceemes-public-sections" data-section-area="{{ $field->handle }}">
                    @foreach($content->sections(fieldHandle: $field->handle) as $section)
                        <section class="ceemes-public-section" data-section-type="{{ $section->handle() }}">
                            @foreach($section->sectionType->fields as $sectionField)
                                @php($sectionValue = $section->get($sectionField->handle))
                                @if(is_scalar($sectionValue) && $sectionValue !== '')<div class="ceemes-public-field"><strong>{{ $sectionField->label }}</strong><p>{{ $sectionValue }}</p></div>@endif
                            @endforeach
                        </section>
                    @endforeach
                </div>
            @else
                @php($value = $content->get($field->handle))
                @if(is_scalar($value) && $value !== '')<div class="ceemes-public-field"><strong>{{ $field->label }}</strong><p>{{ $value }}</p></div>@endif
            @endif
        @endforeach
    </article>
</main>
</body>
</html>
