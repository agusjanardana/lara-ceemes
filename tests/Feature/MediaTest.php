<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Contents\UpdateContent;
use LaraCeemes\Actions\Media\DeleteMedia;
use LaraCeemes\Actions\Media\UpdateMedia;
use LaraCeemes\Actions\Media\UploadMedia;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Exceptions\MediaInUse;
use LaraCeemes\Facades\Media as MediaFacade;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Media;
use LaraCeemes\Tests\TestCase;

final class MediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_image_can_be_uploaded_and_metadata_is_stored(): void
    {
        $media = $this->uploadImage();

        Storage::disk('public')->assertExists($media->path());
        self::assertSame('hero.jpg', $media->original_filename);
        self::assertSame('image/jpeg', $media->mime_type);
        self::assertSame(600, $media->width);
        self::assertSame(400, $media->height);
        self::assertSame($media->uuid, MediaFacade::find($media->uuid)?->uuid);
        self::assertStringContainsString($media->filename, $media->url());
    }

    public function test_invalid_mime_type_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $this->app->make(UploadMedia::class)->execute(
            UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload'),
        );
    }

    public function test_media_metadata_can_be_updated_and_searched(): void
    {
        $media = $this->uploadImage();
        $updated = $this->app->make(UpdateMedia::class)->execute($media, [
            'title' => 'Homepage Hero',
            'alt' => 'A welcoming office',
        ]);

        self::assertSame('Homepage Hero', $updated->title);
        self::assertSame($media->uuid, MediaFacade::search('Homepage')->first()?->uuid);
    }

    public function test_referenced_media_is_reported_and_cannot_be_deleted(): void
    {
        $media = $this->uploadImage();
        $content = $this->makeContentWithMedia($media);
        $usages = MediaFacade::usages($media);

        self::assertCount(1, $usages);
        self::assertSame('content', $usages[0]->sourceType);
        self::assertSame('featured_image', $usages[0]->fieldHandle);
        self::assertSame($media->uuid, $content->media('featured_image')?->uuid);

        try {
            $this->app->make(DeleteMedia::class)->execute($media);
            self::fail('Referenced Media was deleted.');
        } catch (MediaInUse $exception) {
            self::assertCount(1, $exception->usages);
        }

        Storage::disk('public')->assertExists($media->path());
        self::assertNotNull(Media::query()->find($media->uuid));
    }

    public function test_media_can_be_deleted_after_references_are_removed(): void
    {
        $media = $this->uploadImage();
        $content = $this->makeContentWithMedia($media);

        $this->app->make(UpdateContent::class)->execute($content, [
            'data' => ['featured_image' => null],
        ]);
        $this->app->make(DeleteMedia::class)->execute($media);

        Storage::disk('public')->assertMissing($media->path());
        self::assertNull(Media::query()->find($media->uuid));
        self::assertNotNull(Media::withTrashed()->find($media->uuid));
    }

    private function uploadImage(): Media
    {
        return $this->app->make(UploadMedia::class)->execute(
            UploadedFile::fake()->image('hero.jpg', 600, 400),
            ['title' => 'Hero'],
        );
    }

    private function makeContentWithMedia(Media $media): Content
    {
        $set = $this->app->make(CreateSet::class)->execute(['name' => 'Pages']);
        $this->app->make(CreateSetField::class)->execute($set, [
            'handle' => 'featured_image',
            'label' => 'Featured Image',
            'type' => 'media',
        ]);

        return $this->app->make(CreateContent::class)->execute($set, [
            'title' => 'Home',
            'data' => ['featured_image' => $media->uuid],
        ]);
    }
}
