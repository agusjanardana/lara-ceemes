<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Blueprints\CreateBlueprint;
use LaraCeemes\Actions\Blueprints\CreateBlueprintField;
use LaraCeemes\Actions\Collections\CreateCollection;
use LaraCeemes\Actions\Entries\CreateEntry;
use LaraCeemes\Actions\Sections\CreateSection;
use LaraCeemes\Actions\Sections\CreateSectionField;
use LaraCeemes\Actions\Sections\CreateSectionType;
use LaraCeemes\Actions\Sections\DeleteSection;
use LaraCeemes\Actions\Sections\DuplicateSection;
use LaraCeemes\Actions\Sections\ReorderSections;
use LaraCeemes\Actions\Sections\SetSectionEnabled;
use LaraCeemes\Actions\Sections\UpdateSection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\SectionType;
use LaraCeemes\Tests\TestCase;

final class SectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_allowed_section_can_be_created_with_validated_data(): void
    {
        [$entry, $banner] = $this->makeSectionEnvironment();

        $section = $this->app->make(CreateSection::class)->execute($entry, $banner, [
            'key' => 'hero',
            'data' => ['title' => '  Welcome  '],
        ]);

        self::assertSame('Welcome', $section->get('title'));
        self::assertSame('banner', $section->handle());
        self::assertTrue($section->type()->is($banner));
        self::assertSame($section->uuid, $entry->section('hero')?->uuid);
        self::assertSame($section->uuid, $entry->section('banner')?->uuid);
    }

    public function test_section_type_must_be_explicitly_allowed_by_blueprint(): void
    {
        [$entry] = $this->makeSectionEnvironment();
        $gallery = $this->app->make(CreateSectionType::class)->execute(['name' => 'Gallery']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Section Type [gallery] is not allowed');

        $this->app->make(CreateSection::class)->execute($entry, $gallery, []);
    }

    public function test_section_data_honors_required_section_fields(): void
    {
        [$entry, $banner] = $this->makeSectionEnvironment();

        $this->expectException(ValidationException::class);

        $this->app->make(CreateSection::class)->execute($entry, $banner, ['data' => []]);
    }

    public function test_sections_can_be_updated_disabled_and_filtered(): void
    {
        [$entry, $banner] = $this->makeSectionEnvironment();
        $section = $this->app->make(CreateSection::class)->execute($entry, $banner, [
            'data' => ['title' => 'Welcome'],
        ]);

        $updated = $this->app->make(UpdateSection::class)->execute($section, [
            'data' => ['title' => 'Updated'],
        ]);
        $this->app->make(SetSectionEnabled::class)->execute($updated, false);

        self::assertSame('Updated', $updated->get('title'));
        self::assertCount(0, $entry->sections());
        self::assertCount(1, $entry->sections(includeDisabled: true));
        self::assertNull($entry->section('banner'));
    }

    public function test_sections_can_be_duplicated_and_reordered(): void
    {
        [$entry, $banner, $cta] = $this->makeSectionEnvironment();
        $create = $this->app->make(CreateSection::class);
        $hero = $create->execute($entry, $banner, [
            'key' => 'hero',
            'data' => ['title' => 'Welcome'],
        ]);
        $callToAction = $create->execute($entry, $cta, [
            'key' => 'bottom_cta',
            'data' => ['title' => 'Contact Us'],
        ]);
        $duplicate = $this->app->make(DuplicateSection::class)->execute($callToAction, 'top_cta');

        self::assertNotSame($callToAction->uuid, $duplicate->uuid);
        self::assertSame($callToAction->data(), $duplicate->data());
        self::assertCount(2, $entry->sectionsOfType('cta'));

        $this->app->make(ReorderSections::class)->execute($entry, [
            $duplicate->uuid,
            $hero->uuid,
            $callToAction->uuid,
        ]);

        self::assertSame(
            [$duplicate->uuid, $hero->uuid, $callToAction->uuid],
            $entry->sections()->pluck('uuid')->all(),
        );
    }

    public function test_deleting_section_closes_the_sort_order_gap(): void
    {
        [$entry, $banner, $cta] = $this->makeSectionEnvironment();
        $create = $this->app->make(CreateSection::class);
        $first = $create->execute($entry, $banner, ['data' => ['title' => 'First']]);
        $second = $create->execute($entry, $cta, ['data' => ['title' => 'Second']]);

        $this->app->make(DeleteSection::class)->execute($first);

        self::assertNull(Section::query()->find($first->uuid));
        self::assertSame(1, $second->refresh()->sort_order);
    }

    /** @return array{Entry, SectionType, SectionType} */
    private function makeSectionEnvironment(): array
    {
        $collection = $this->app->make(CreateCollection::class)->execute(['name' => 'Pages']);
        $blueprint = $this->app->make(CreateBlueprint::class)->execute($collection, ['name' => 'Landing Page']);
        $entry = $this->app->make(CreateEntry::class)->execute($blueprint, ['title' => 'Home']);
        $banner = $this->app->make(CreateSectionType::class)->execute(['name' => 'Banner']);
        $cta = $this->app->make(CreateSectionType::class)->execute(['name' => 'CTA']);

        $this->app->make(CreateBlueprintField::class)->execute($blueprint, [
            'handle' => 'sections',
            'label' => 'Sections',
            'type' => 'sections',
            'config' => ['allowed' => ['banner', 'cta']],
        ]);
        $this->app->make(CreateSectionField::class)->execute($banner, [
            'handle' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'config' => ['required' => true],
        ]);
        $this->app->make(CreateSectionField::class)->execute($cta, [
            'handle' => 'title',
            'label' => 'Title',
            'type' => 'text',
            'config' => ['required' => true],
        ]);

        return [$entry, $banner, $cta];
    }
}
