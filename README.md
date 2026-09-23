# Lara Ceemes

Lara Ceemes is a lightweight, database-driven CMS package for Laravel 13. It
provides dynamic Sets, Contents, reusable structured Sections, Categories,
Navigations, Settings, SEO, Media, an authenticated Admin CMS,
Artisan commands, and a public Facade API.

The consuming Laravel application keeps control of explicit frontend routes,
Blade templates, CSS, and page design. Lara Ceemes can automatically resolve
published Content URLs for content-managed pages.

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
migrations, creates the default Pages Set, Header/Footer Navigations,
and minimum General/SEO/Cache/Media settings. It is safe to run more than once.

The superadmin command uses the consuming application's configured User model.
For a new User it asks for a name, email, and hidden password, then stores the
`superadmin` role directly on that User. Promoting an existing User preserves
their name and password unless `--force` is supplied.

The package provides `/admin/login`, while the Admin CMS defaults to `/admin`
and uses the consuming application's `web` and `auth` middleware. It also
replaces Laravel's default `/` page with a small Lara Ceemes landing page.

The Admin CMS includes searchable CRUD tables, Content status filters, Set
Fields, per-Content SEO, a reusable Section Library, a Media Library, and
hierarchical Navigation using parent items. Its compiled CSS and JavaScript are
published by the installer, so the consuming application does not need a Node
build step.

Set Fields and Section Fields provide dedicated General, Validation, and
Visibility tabs. Validation supports required values, text length, numeric
ranges, collection limits, date ranges, and custom messages. Conditional
visibility can depend on another field being filled, empty, equal to a value,
containing a selection, or representing an enabled/disabled state. The same
rules run in the Admin UI and on the server.

```env
CEEMES_ADMIN_PREFIX=admin
CEEMES_MEDIA_DISK=public
CEEMES_CACHE_ENABLED=true
CEEMES_CACHE_TTL=3600
CEEMES_AUTH_ROUTES=true
CEEMES_HOMEPAGE_ENABLED=true
CEEMES_PUBLIC_ROUTING=true
CEEMES_MANAGE_USER_ROLE_COLUMN=true
CEEMES_USER_ROLE_ATTRIBUTE=role
CEEMES_DEFAULT_USER_ROLE=user
CEEMES_SUPERADMIN_ROLE=superadmin
```

By default, the package migration adds an indexed `role` column to `users`.
When the application already owns a compatible role column, set
`CEEMES_MANAGE_USER_ROLE_COLUMN=false` before migrating and point
`CEEMES_USER_ROLE_ATTRIBUTE` to that column. Lara Ceemes never creates a
separate superadmin table.

Set `CEEMES_HOMEPAGE_ENABLED=false` when the consuming application is ready to
own its public `/` route while keeping other CMS URLs active. Set
`CEEMES_AUTH_ROUTES=false` when the application
already provides its own named `login` route and authentication screens.

Set `CEEMES_PUBLIC_ROUTING=false` if the consuming application wants to resolve
every public Content through its own controllers. Explicit application routes are
registered before the CMS catch-all route and therefore keep priority.

## Authorization

Admin access is protected by the `access-ceemes` Gate. By default, a User is
allowed when the configured role attribute equals the configured superadmin
role. `ceemes:make-superadmin` sets that value. Applications can replace the
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
use LaraCeemes\Facades\Set;

$home = Set::query('pages')
    ->published()
    ->whereSlug('home')
    ->firstOrFail();

$headline = $home->get('headline', 'Default headline');
$sections = $home->sections();
$banner = $home->section('banner');
```

Other public APIs:

```php
use LaraCeemes\Facades\Content;
use LaraCeemes\Facades\Category;
use LaraCeemes\Facades\Media;
use LaraCeemes\Facades\Navigation;
use LaraCeemes\Facades\Seo;
use LaraCeemes\Facades\Settings;
use LaraCeemes\Facades\Section;

$content = Content::find('pages', 'home');
$sharedSections = Section::forContent($content)->enabled()->get();
$header = Navigation::get('header');
$categories = Category::categories('categories');
$siteName = Settings::get('general.site_name', 'My Website');
$image = Media::find($mediaUuid);
$seo = Seo::forContent($content);
```

## Media folders and S3

The Media Library supports searchable virtual folders and filters for images,
documents, and other file types. Folder paths are stored as storage prefixes,
so the same feature works with Laravel's `local`, `public`, and `s3` disks.

For S3, install Laravel's Flysystem adapter in the consuming application,
configure the `s3` disk in `config/filesystems.php`, and select it through the
environment:

```bash
composer require league/flysystem-aws-s3-v3 "^3.0" --with-all-dependencies
```

```env
CEEMES_MEDIA_DISK=s3
CEEMES_MEDIA_DIRECTORY=ceemes
```

The database keeps empty folders visible; Lara Ceemes does not create fake
placeholder objects in the S3 bucket. Moving Media between folders uses the
configured Laravel filesystem disk.

All CMS entities use UUID primary keys. Content visibility is controlled only by
`draft` or `published` status in v0.1.

## Set Fields and Reusable Sections

A Set owns optional Fixed Fields directly. Fixed Fields suit predictable values such as SKU, price, excerpt,
date, category, or featured image.

A Section Type defines the fields for a block such as Hero, FAQ, Gallery, or
CTA. A Section is the actual reusable content. Content attaches Sections through
ordered placements, so the same shared Hero can appear on Home and Contact.
Editing it updates every usage; detaching it only removes that placement.

A `content` field is scoped to one source Set. Its searchable picker supports
one or multiple Content values.

A `repeater` repeats one consistent group of subfields. In the Set Field
form, choose Repeater and build one item from searchable subfield types, for
example `image + title + description`. You may set required subfields, widths,
and minimum/maximum item counts. The Content editor renders each item as a normal
field card with add, remove, move-up, and move-down actions; editors never need
to write JSON.

Use `sections` instead when each block may have a different structure, for
example Hero followed by Gallery, FAQ, and CTA. In short: Repeater is many rows
of the same schema; Sections is an ordered page builder of different reusable
Section Types.

## Public Content Routing

Every Content has a globally unique Public URL. Set a published Homepage Content to
`/`, Contact to `/contact`, or use nested paths such as `/company/team`. Draft
Contents in non-publishable Sets return 404.

The default Pages Set uses `/{slug}` to suggest the initial URL, so Content with
slug `contact` starts as `/contact`. Reserved Admin paths cannot be assigned.

### Automatic Set Views

New Sets use automatic frontend conventions by default:

```text
Pages    -> resources/views/pages.blade.php       -> view: pages
Products -> resources/views/products/show.blade.php -> view: products.show
```

Pages use `/{slug}`. Other Sets use `/{set-handle}/{slug}`, so a published
Product with slug `red-shoes` resolves at `/products/red-shoes`. Generated Blade
files receive `$content`, `$set`, `$fields`, `$sections`, and `$seo`, loop Fixed
Fields and Section areas, and are never overwritten after creation.

The Set form also supports a custom Blade view or `Tanpa frontend (data only)`.
Data-only Sets store structured Content but are made non-publishable and receive
no route or generated Blade file.

When the Set's `template` points to an existing Blade view, Lara Ceemes renders
that view with `$content` and `$seo`. An application can create `resources/views/pages/show.blade.php`
and set the Set template to `pages.show`.

## Artisan Commands

```text
ceemes:install
ceemes:status
ceemes:cache:clear
ceemes:make-superadmin
ceemes:make-set
ceemes:make-set-field
ceemes:make-content
ceemes:make-section-type
ceemes:make-section-field
ceemes:make-category-group
ceemes:make-category
ceemes:make-navigation
ceemes:setting:get
ceemes:setting:set
```

Example:

```bash
php artisan ceemes:make-set articles --name="Articles"
php artisan ceemes:make-set-field excerpt --set=articles --type=textarea
php artisan ceemes:make-content --set=articles --title="First Article" --status=published
php artisan ceemes:make-set products
php artisan ceemes:make-set inventory --no-template
php artisan ceemes:make-set products --template=store.products.show
```

The older Set and Content commands remain as storage-oriented aliases.
Content schemas are not exposed as a separate model or command.

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
the public Set Facade.

## Documentation

- [Product and architecture specification](docs/codex.md)
- [Implementation plan](docs/implementation-plan.md)
- [Testing and publishing guide](publish.md)
- [Changelog](CHANGELOG.md)

## License

Lara Ceemes is open-sourced software licensed under the MIT license.
