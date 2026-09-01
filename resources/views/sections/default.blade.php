<section data-section-type="{{ $section->handle() }}">
    @foreach($section->sectionType->fields as $field)
        @php($value = $section->get($field->handle))
        @if(is_scalar($value) && $value !== '')
            <div data-field="{{ $field->handle }}">
                <strong>{{ $field->label }}</strong>
                <p>{{ $value }}</p>
            </div>
        @endif
    @endforeach
</section>
