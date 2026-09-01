<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo->title ?: $content->title }}@if($seo->siteTitle && $seo->siteTitle !== $seo->title) {{ $seo->titleSeparator }} {{ $seo->siteTitle }}@endif</title>
    @if($seo->description)<meta name="description" content="{{ $seo->description }}">@endif
    <meta name="robots" content="{{ $seo->robotsIndex ? 'index' : 'noindex' }},{{ $seo->robotsFollow ? 'follow' : 'nofollow' }}">
    <link rel="canonical" href="{{ $seo->canonicalUrl ?: $content->publicUrl() }}">
</head>
<body>
<main data-ceemes-set="{{ $set->handle }}">
    <article>
        <header><h1>{{ $content->title }}</h1></header>

        @foreach($fields->where('type', '!=', 'sections') as $field)
            @php($value = $content->get($field->handle))
            @if(is_scalar($value) && $value !== '')
                <div data-field="{{ $field->handle }}">
                    <strong>{{ $field->label }}</strong>
                    <p>{{ $value }}</p>
                </div>
            @endif
        @endforeach

        @foreach($fields->where('type', 'sections') as $field)
            <div data-section-area="{{ $field->handle }}">
                @foreach($content->sections(fieldHandle: $field->handle) as $section)
                    @includeFirst([
                        "sections.{$section->handle()}",
                        'ceemes::sections.default',
                    ], ['section' => $section])
                @endforeach
            </div>
        @endforeach
    </article>
</main>
</body>
</html>
