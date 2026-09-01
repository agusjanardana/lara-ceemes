<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Unit;

use Illuminate\Support\Facades\Validator;
use LaraCeemes\Exceptions\FieldTypeAlreadyRegistered;
use LaraCeemes\Exceptions\UnknownFieldType;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Fields\Types\TextField;
use LaraCeemes\Tests\TestCase;

final class FieldRegistryTest extends TestCase
{
    public function test_all_core_field_types_are_registered(): void
    {
        $expected = [
            'text',
            'textarea',
            'richtext',
            'number',
            'boolean',
            'select',
            'date',
            'datetime',
            'email',
            'url',
            'color',
            'media',
            'category',
            'content',
            'group',
            'repeater',
            'sections',
            'seo',
        ];

        $registry = $this->app->make(FieldRegistry::class);

        self::assertSame($expected, array_keys($registry->all()));
    }

    public function test_field_rules_can_be_used_by_laravel_validator(): void
    {
        $field = $this->app->make(FieldRegistry::class)->get('text');

        self::assertTrue(Validator::make(
            ['headline' => 'Welcome'],
            ['headline' => $field->rules(['required' => true])],
        )->passes());

        self::assertTrue(Validator::make(
            ['headline' => null],
            ['headline' => $field->rules(['required' => true])],
        )->fails());
    }

    public function test_fields_normalize_values_for_json_storage(): void
    {
        $registry = $this->app->make(FieldRegistry::class);

        self::assertSame('Welcome', $registry->get('text')->serialize('  Welcome  '));
        self::assertSame(12, $registry->get('number')->serialize('12'));
        self::assertSame(12.5, $registry->get('number')->serialize('12.5'));
        self::assertTrue($registry->get('boolean')->serialize('1'));
        self::assertSame(
            ['cfbd89c0-e5df-4b64-acab-042622b25f78'],
            $registry->get('category')->serialize([
                'cfbd89c0-e5df-4b64-acab-042622b25f78',
                'cfbd89c0-e5df-4b64-acab-042622b25f78',
            ]),
        );
    }

    public function test_field_types_expose_an_existing_admin_view(): void
    {
        $field = $this->app->make(FieldRegistry::class)->get('text');

        self::assertTrue(view()->exists($field->adminView()));
    }

    public function test_unknown_field_type_throws_a_useful_exception(): void
    {
        $this->expectException(UnknownFieldType::class);
        $this->expectExceptionMessage('Field type [missing] is not registered.');

        $this->app->make(FieldRegistry::class)->get('missing');
    }

    public function test_duplicate_registration_requires_explicit_replacement(): void
    {
        $registry = $this->app->make(FieldRegistry::class);

        $this->expectException(FieldTypeAlreadyRegistered::class);

        $registry->register(TextField::class);
    }
}
