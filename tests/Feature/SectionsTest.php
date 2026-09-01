<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Sections\CreateSection;
use LaraCeemes\Actions\Sections\CreateSectionField;
use LaraCeemes\Actions\Sections\CreateSectionType;
use LaraCeemes\Actions\Sections\DeleteSection;
use LaraCeemes\Actions\Sections\DuplicateSection;
use LaraCeemes\Actions\Sections\ReorderSections;
use LaraCeemes\Actions\Sections\SetSectionEnabled;
use LaraCeemes\Actions\Sections\UpdateSection;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Facades\Section as SectionFacade;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\SectionType;
use LaraCeemes\Tests\TestCase;

final class SectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_allowed_section_can_be_created_with_validated_data(): void
    {
        [$content, $banner] = $this->makeSectionEnvironment();

        $section = $this->app->make(CreateSection::class)->execute($content, $banner, [
            'key' => 'hero',
            'data' => ['title' => '  Welcome  '],
        ]);

        self::assertSame('Welcome', $section->get('title'));
        self::assertSame('banner', $section->handle());
        self::assertTrue($section->type()->is($banner));
        self::assertSame($section->uuid, $content->section('hero')?->uuid);
        self::assertSame($section->uuid, $content->section('banner')?->uuid);
    }

    public function test_section_type_must_be_explicitly_allowed_by_set(): void
    {
        [$content] = $this->makeSectionEnvironment();
        $gallery = $this->app->make(CreateSectionType::class)->execute(['name' => 'Gallery']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Section Type [gallery] is not allowed');

        $this->app->make(CreateSection::class)->execute($content, $gallery, []);
    }

    public function test_section_data_honors_required_section_fields(): void
    {
        [$content, $banner] = $this->makeSectionEnvironment();

        $this->expectException(ValidationException::class);

        $this->app->make(CreateSection::class)->execute($content, $banner, ['data' => []]);
    }

    public function test_sections_can_be_updated_disabled_and_filtered(): void
    {
        [$content, $banner] = $this->makeSectionEnvironment();
        $section = $this->app->make(CreateSection::class)->execute($content, $banner, [
            'data' => ['title' => 'Welcome'],
        ]);

        $updated = $this->app->make(UpdateSection::class)->execute($section, [
            'data' => ['title' => 'Updated'],
        ]);
        $this->app->make(SetSectionEnabled::class)->execute($updated, false);

        self::assertSame('Updated', $updated->get('title'));
        self::assertCount(0, $content->sections());
        self::assertCount(1, $content->sections(includeDisabled: true));
        self::assertNull($content->section('banner'));
    }

    public function test_sections_can_be_duplicated_and_reordered(): void
    {
        [$content, $banner, $cta] = $this->makeSectionEnvironment();
        $create = $this->app->make(CreateSection::class);
        $hero = $create->execute($content, $banner, [
            'key' => 'hero',
            'data' => ['title' => 'Welcome'],
        ]);
        $callToAction = $create->execute($content, $cta, [
            'key' => 'bottom_cta',
            'data' => ['title' => 'Contact Us'],
        ]);
        $duplicate = $this->app->make(DuplicateSection::class)->execute($callToAction, 'top_cta');

        self::assertNotSame($callToAction->uuid, $duplicate->uuid);
        self::assertSame($callToAction->data(), $duplicate->data());
        self::assertCount(2, $content->sectionsOfType('cta'));

        $this->app->make(ReorderSections::class)->execute($content, [
            $duplicate->uuid,
            $hero->uuid,
            $callToAction->uuid,
        ]);

        self::assertSame(
            [$duplicate->uuid, $hero->uuid, $callToAction->uuid],
            $content->sections()->pluck('uuid')->all(),
        );
    }

    public function test_detached_section_can_be_deleted_without_changing_other_placements(): void
    {
        [$content, $banner, $cta] = $this->makeSectionEnvironment();
        $create = $this->app->make(CreateSection::class);
        $first = $create->execute($content, $banner, ['data' => ['title' => 'First']]);
        $second = $create->execute($content, $cta, ['data' => ['title' => 'Second']]);

        $content->placedSections()->detach($first->uuid);
        $this->app->make(DeleteSection::class)->execute($first);

        self::assertNull(Section::query()->find($first->uuid));
        self::assertSame(1, $content->placedSections()->whereKey($second->uuid)->sole()->pivot->sort_order);
    }

    public function test_one_shared_section_can_be_placed_on_multiple_contents(): void
    {
        [$home, $banner] = $this->makeSectionEnvironment();
        $contact = $this->app->make(CreateContent::class)->execute($home->set, ['title' => 'Contact']);
        $section = $this->app->make(CreateSection::class)->execute($home, $banner, [
            'name' => 'Shared Hero',
            'data' => ['title' => 'Hello everyone'],
        ]);

        $contact->placedSections()->attach($section->uuid, [
            'uuid' => (string) Str::uuid(),
            'region' => 'sections',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);

        self::assertSame($section->uuid, $home->sections()->sole()->uuid);
        self::assertSame($section->uuid, $contact->sections()->sole()->uuid);
        self::assertCount(2, $section->contents);
        self::assertSame($section->uuid, SectionFacade::forContent($contact)->enabled()->get()->sole()->uuid);
    }

    /** @return array{Content, SectionType, SectionType} */
    private function makeSectionEnvironment(): array
    {
        $set = $this->app->make(CreateSet::class)->execute(['name' => 'Pages']);
        $content = $this->app->make(CreateContent::class)->execute($set, ['title' => 'Home']);
        $banner = $this->app->make(CreateSectionType::class)->execute(['name' => 'Banner']);
        $cta = $this->app->make(CreateSectionType::class)->execute(['name' => 'CTA']);

        $this->app->make(CreateSetField::class)->execute($set, [
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

        return [$content, $banner, $cta];
    }
}
