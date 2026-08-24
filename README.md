# Lara Ceemes

Lara Ceemes is a lightweight, database-driven CMS package for Laravel 13. It
provides dynamic Collections, Blueprints, Entries, structured Sections,
Taxonomies, Navigations, Settings, SEO, Media, an authenticated Admin CMS,
Artisan commands, and a public Facade API.

The consuming Laravel application keeps control of explicit frontend routes,
Blade templates, CSS, and page design. Lara Ceemes can automatically resolve
published Entry URLs for content-managed pages.

## Requirements

- PHP 8.3 or newer
- Laravel 13
- MySQL, MariaDB, PostgreSQL, or SQLite for development/testing
- An authenticated user model in the consuming application

## Installation

```bash
composer require janar/lara-ceemes
php artisan ceemes:install
php artisan ceemes:make-superadmin
```

The installer publishes configuration and compiled Admin assets, runs
migrations, creates the default Pages Collection, Header/Footer Navigations,
and minimum General/SEO/Cache/Media settings. It is safe to run more than once.

The superadmin command uses the consuming application's configured User model.
It asks for a name, email, and hidden password, creates the User when needed, and
grants access without adding a role column to the application's `users` table.

The package provides `/admin/login`, while the Admin CMS defaults to `/admin`
and uses the consuming application's `web` and `auth` middleware. It also
replaces Laravel's default `/` page with a small Lara Ceemes landing page.

The Admin CMS includes searchable CRUD tables, Entry status and Blueprint
filters, Blueprint-aware content inputs, per-Entry SEO, a Media Library, and
hierarchical Navigation using parent items. Its compiled CSS and JavaScript are
published by the installer, so the consuming application does not need a Node
build step.

```env
CEEMES_ADMIN_PREFIX=admin
CEEMES_MEDIA_DISK=public
CEEMES_CACHE_ENABLED=true
CEEMES_CACHE_TTL=3600
CEEMES_AUTH_ROUTES=true
CEEMES_HOMEPAGE_ENABLED=true
CEEMES_PUBLIC_ROUTING=true
```

Set `CEEMES_HOMEPAGE_ENABLED=false` when the consuming application is ready to
own its public `/` route while keeping other CMS URLs active. Set
`CEEMES_AUTH_ROUTES=false` when the application
already provides its own named `login` route and authentication screens.

Set `CEEMES_PUBLIC_ROUTING=false` if the consuming application wants to resolve
every public Entry through its own controllers. Explicit application routes are
registered before the CMS catch-all route and therefore keep priority.

## Authorization

Admin access is protected by the `access-ceemes` Gate. By default, only Users
registered by `ceemes:make-superadmin` are allowed. Applications can replace the
Gate in their own service provider:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('access-ceemes', function ($user): bool {
    return $user->is_admin;
});
```

Lara Ceemes does not create a separate user or authentication system.

## Content API

```php
use LaraCeemes\Facades\Collection;

$home = Collection::query('pages')
    ->published()
    ->whereSlug('home')
    ->firstOrFail();

$headline = $home->get('headline', 'Default headline');
$sections = $home->sections();
$banner = $home->section('banner');
```

Other public APIs:

```php
use LaraCeemes\Facades\Entry;
use LaraCeemes\Facades\Media;
use LaraCeemes\Facades\Navigation;
use LaraCeemes\Facades\Seo;
use LaraCeemes\Facades\Settings;
use LaraCeemes\Facades\Taxonomy;

$entry = Entry::find('pages', 'home');
$header = Navigation::get('header');
$categories = Taxonomy::terms('categories');
$siteName = Settings::get('general.site_name', 'My Website');
$image = Media::find($mediaUuid);
$seo = Seo::forEntry($entry);
```

All CMS entities use UUID primary keys. Entry visibility is controlled only by
`draft` or `published` status in v0.1.

## Blueprint Fields and Section Types

A Blueprint defines the complete editing model of an Entry. Use ordinary
Blueprint Fields for predictable values that every Entry should expose, such as
headline, excerpt, date, category, or featured image.

A Section Type defines one reusable content block, such as Hero, FAQ, Gallery,
or CTA. To use Section Types in an Entry, add a Blueprint Field with the
`sections` type and select which Section Types are allowed in that field. A
Blueprint may combine ordinary fields and one or more Sections areas. The Entry
editor only shows a Sections builder for areas declared by its Blueprint.

An `entry` field is scoped to one source Collection. Its editor uses a searchable
picker instead of a long select and supports either one Entry or multiple Entries
through the field's `multiple` option. Stored UUIDs are validated against the
configured Collection.

A `repeater` repeats one consistent group of subfields. In the Blueprint Field
form, choose Repeater and build one item from searchable subfield types, for
example `image + title + description`. You may set required subfields, widths,
and minimum/maximum item counts. The Entry editor renders each item as a normal
field card with add, remove, move-up, and move-down actions; editors never need
to write JSON.

Use `sections` instead when each block may have a different structure, for
example Hero followed by Gallery, FAQ, and CTA. In short: Repeater is many rows
of the same schema; Sections is an ordered page builder of different reusable
Section Types.

## Public Entry Routing

Every Entry has a globally unique Public URL. Set a published Homepage Entry to
`/`, Contact to `/contact`, or use nested paths such as `/company/team`. Draft
Entries and Entries in non-publishable Collections return 404.

The default Pages Collection uses `/{slug}` to suggest the initial URL, so an
Entry with slug `contact` starts as `/contact`. The URL remains editable and is
independent from the slug. Reserved Admin paths cannot be assigned to Entries.

When the Collection's `template` points to an existing Blade view, Lara Ceemes
renders that view with `$entry` and `$seo`. Otherwise it uses its minimal built-in
content view. An application can create `resources/views/pages/show.blade.php`
and set the Collection template to `pages.show`.

## Artisan Commands

```text
ceemes:install
ceemes:status
ceemes:cache:clear
ceemes:make-superadmin
ceemes:make-collection
ceemes:make-blueprint
ceemes:make-field
ceemes:make-entry
ceemes:make-section
ceemes:make-section-field
ceemes:make-taxonomy
ceemes:make-term
ceemes:make-navigation
ceemes:setting:get
ceemes:setting:set
```

Example:

```bash
php artisan ceemes:make-collection articles --name="Articles"
php artisan ceemes:make-blueprint article --collection=articles
php artisan ceemes:make-field headline --blueprint=article --type=text
php artisan ceemes:make-entry --collection=articles --blueprint=article --title="First Article" --status=published
```

For JSON options in PowerShell, pass arguments through a PowerShell array or use
the Admin CMS to avoid native-shell quote conversion.

## Local Package Development

Add a path repository to the Laravel playground:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../../lara-ceemes",
      "options": { "symlink": true }
    }
  ]
}
```

Then require the development package:

```bash
composer require janar/lara-ceemes:@dev
```

## Quality Checks

```bash
composer test
composer format:test
composer analyse
composer validate --strict
```

The package test suite uses Orchestra Testbench. The included playground also
contains an integration test covering auto-discovery, Actions, migrations, and
the public Collection Facade.

## Documentation

- [Product and architecture specification](docs/codex.md)
- [Implementation plan](docs/implementation-plan.md)
- [Testing and publishing guide](publish.md)
- [Changelog](CHANGELOG.md)

## License

Lara Ceemes is open-sourced software licensed under the MIT license.
