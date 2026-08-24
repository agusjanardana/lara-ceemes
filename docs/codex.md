# Lara Ceemes

A lightweight, reusable, database-driven CMS package for Laravel.

Lara Ceemes is designed primarily for:

- Landing pages
- Company profile websites
- Content-driven websites
- Small to medium corporate websites
- Custom Laravel websites that need an admin CMS
- Projects where the frontend should remain fully controlled by the developer

The goal is **not** to clone Statamic completely.

The goal is to provide a reusable Laravel CMS engine that can be installed through Composer and provides:

- Collections
- Blueprints
- Fields
- Entries
- Sections
- Taxonomies
- Terms
- Navigations
- Media
- Settings
- SEO Settings
- Admin CMS
- Artisan CLI
- Public Facade API

The package must be developer-friendly and frontend-agnostic.

---

# Core Philosophy

Lara Ceemes should separate three responsibilities:

```text
Admin CMS
    │
    ├──────────────┐
    │              │
Artisan CLI     Public API
    │              │
    └──────┬───────┘
           │
       Actions /
       Managers
           │
           ▼
       Eloquent
           │
           ▼
        Database
```

The Admin CMS, Artisan CLI, and Facades must **not duplicate business logic**.

For example:

```text
Admin:
CreateCollectionController
        │
        ▼
CreateCollectionAction
        │
        ▼
Collection Model
```

and:

```text
CLI:
ceemes:make-collection
        │
        ▼
CreateCollectionAction
        │
        ▼
Collection Model
```

Both interfaces must use the same underlying action/service.

---

# Package Name

Repository name:

```text
lara-ceemes
```

Composer package format:

```text
<vendor>/lara-ceemes
```

Example:

```bash
composer require <vendor>/lara-ceemes
```

Do not hardcode the vendor name throughout the package.

The package namespace should be:

```php
LaraCeemes
```

Example:

```php
LaraCeemes\Models\Collection
LaraCeemes\Facades\Collection
LaraCeemes\Actions\Collections\CreateCollection
```

---

# Installation Experience

The intended installation flow is:

```bash
composer require <vendor>/lara-ceemes
```

Then:

```bash
php artisan ceemes:install
```

The installer should:

```text
✓ Publish configuration
✓ Publish CMS assets
✓ Run migrations
✓ Create default settings
✓ Create default navigation
✓ Create default Pages collection
✓ Clear Ceemes cache
✓ Verify storage configuration
```

After installation:

```text
/admin
```

should expose the CMS.

The admin URL must be configurable.

Example:

```env
CEEMES_ADMIN_PREFIX=admin
```

---

# Technical Stack

Target:

```text
Laravel 13
PHP version compatible with Laravel 13
MySQL / MariaDB / PostgreSQL
Blade
Livewire where useful
Laravel Filesystem
Laravel Cache
Laravel Validation
Laravel Authorization
```

Avoid requiring the consuming project to run a frontend build just to use the CMS.

If the package needs JavaScript or CSS for the admin panel, distribute compiled assets with the package.

Installation should ideally remain:

```bash
composer require ...
php artisan ceemes:install
```

without requiring:

```bash
npm install
npm run build
```

inside the consumer application.

---

# Package Structure

Recommended structure:

```text
lara-ceemes/
│
├── composer.json
├── README.md
├── LICENSE
│
├── config/
│   └── ceemes.php
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── views/
│   │   └── admin/
│   └── dist/
│       ├── ceemes.css
│       └── ceemes.js
│
├── routes/
│   └── web.php
│
├── src/
│   │
│   ├── LaraCeemesServiceProvider.php
│   │
│   ├── Models/
│   │   ├── Collection.php
│   │   ├── Blueprint.php
│   │   ├── BlueprintField.php
│   │   ├── Entry.php
│   │   ├── SectionType.php
│   │   ├── SectionField.php
│   │   ├── Section.php
│   │   ├── Taxonomy.php
│   │   ├── Term.php
│   │   ├── Navigation.php
│   │   ├── NavigationItem.php
│   │   ├── Setting.php
│   │   └── Media.php
│   │
│   ├── Actions/
│   │   ├── Collections/
│   │   ├── Blueprints/
│   │   ├── Entries/
│   │   ├── Sections/
│   │   ├── Taxonomies/
│   │   ├── Navigations/
│   │   ├── Settings/
│   │   └── Media/
│   │
│   ├── Managers/
│   │   ├── CollectionManager.php
│   │   ├── EntryManager.php
│   │   ├── TaxonomyManager.php
│   │   ├── NavigationManager.php
│   │   ├── SettingsManager.php
│   │   ├── MediaManager.php
│   │   └── SeoManager.php
│   │
│   ├── Facades/
│   │   ├── Collection.php
│   │   ├── Entry.php
│   │   ├── Taxonomy.php
│   │   ├── Navigation.php
│   │   ├── Settings.php
│   │   ├── Media.php
│   │   └── Seo.php
│   │
│   ├── Fields/
│   │   ├── FieldRegistry.php
│   │   └── Types/
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Middleware/
│   │
│   ├── Console/
│   │   └── Commands/
│   │
│   ├── Queries/
│   │   └── EntryQuery.php
│   │
│   ├── Support/
│   │
│   └── Contracts/
│
└── tests/
    ├── Feature/
    └── Unit/
```

---

# Database Convention

All Lara Ceemes tables must use the prefix:

```text
ceemes_
```

Do not use generic table names such as:

```text
entries
settings
media
```

because the consuming Laravel application may already have those tables.

All Lara Ceemes entities must use UUID primary keys. Do not add auto-incrementing
integer `id` columns to CMS tables. Foreign keys between CMS tables must also
store UUID values and use explicit names such as:

```text
collection_uuid
blueprint_uuid
entry_uuid
section_type_uuid
```

References to the consuming application's users table are the exception: they
must use the same key type as `users.id`.

---

# Collections

Collections define content types.

Examples:

```text
Pages
Articles
Services
Team Members
Testimonials
Products
```

Database:

```text
ceemes_collections
```

Suggested fields:

```text
uuid
name
handle
description
route nullable
template nullable
is_publishable
sort_order
created_at
updated_at
```

Example:

```text
name: Pages
handle: pages
route: /{slug}
```

`route` supplies the default URI pattern for new Entries and `template` may name
a consuming-application Blade view. Lara Ceemes registers an optional public
content resolver after application routes. Explicit Laravel routes retain
priority, while published CMS Entries can own paths such as `/` and `/contact`.
The resolver can be disabled through `CEEMES_PUBLIC_ROUTING=false`.

A Collection does not define all its fields directly.

Fields belong to a Blueprint.

---

# Blueprints

Blueprints define the structure of an Entry.

Database:

```text
ceemes_blueprints
```

Fields:

```text
uuid
collection_uuid
name
handle
created_at
updated_at
```

Example:

```text
Collection:
Pages

Blueprint:
Landing Page
```

Blueprint:

```text
Title       text
Slug        slug
Sections    sections
SEO         seo
```

A collection may have one or multiple Blueprints.

Example:

```text
Pages
├── Standard Page
├── Landing Page
└── Contact Page
```

---

# Blueprint Fields

Database:

```text
ceemes_blueprint_fields
```

Fields:

```text
uuid
blueprint_uuid
handle
label
type
config JSON
width
sort_order
created_at
updated_at
```

Example:

```json
{
  "handle": "featured_image",
  "label": "Featured Image",
  "type": "media",
  "config": {
    "max_files": 1
  }
}
```

---

# Field System

Fields should be extensible using a registry.

Initial supported types:

```text
text
textarea
richtext
number
boolean
select
date
datetime
email
url
color
media
taxonomy
entry
group
repeater
sections
seo
```

`repeater` stores an ordered list whose items all use the same subfield schema.
Its Blueprint configuration contains `fields`, plus optional `min_rows` and
`max_rows`. The Admin UI must render normal nested inputs and add, remove, and
reorder controls; editors must not edit Repeater values as raw JSON.

`sections` has a different responsibility: it is an ordered page-builder area
whose items may use different reusable Section Types. Use Repeater for repeated
rows such as features or team members, and Sections for heterogeneous blocks
such as Hero, Gallery, FAQ, and CTA.

Create:

```php
FieldRegistry
```

Example internal usage:

```php
$field = app(FieldRegistry::class)->get('text');
```

Each field type should eventually be responsible for:

```text
validation
normalization
serialization
admin rendering
display configuration
```

Every field type must implement a shared contract. The initial contract should
cover at least:

```php
namespace LaraCeemes\Contracts;

interface FieldType
{
    public function handle(): string;

    public function rules(array $config = []): array;

    public function normalize(mixed $value, array $config = []): mixed;

    public function serialize(mixed $value, array $config = []): mixed;

    public function resolve(mixed $value, array $config = []): mixed;

    public function adminView(): string;
}
```

`FieldRegistry` must register and resolve implementations of this contract.
Controllers, commands, and models must not contain field-type switch statements.

Do not implement one giant switch statement for every field type.

Use independent field classes.

Example:

```text
Fields/
└── Types/
    ├── TextField.php
    ├── TextareaField.php
    ├── MediaField.php
    ├── TaxonomyField.php
    ├── RepeaterField.php
    └── SectionsField.php
```

---

# Entries

Entries contain actual content.

Database:

```text
ceemes_entries
```

Suggested fields:

```text
uuid
collection_uuid
blueprint_uuid

title
slug

data JSON
seo JSON nullable

status

created_by nullable
updated_by nullable

created_at
updated_at
deleted_at nullable
```

Status should initially support:

```text
draft
published
```

Visibility is determined only by `status` for v0.1:

```text
status = published  -> visible to published queries
status = draft      -> excluded from published queries
```

Scheduled publishing is not part of v0.1, and timestamps must not alter these
rules.

Example:

```text
Collection:
Pages

Entry:
Home
```

Data:

```json
{
  "headline": "Welcome to Our Website",
  "description": "Company description"
}
```

Dynamic Blueprint field values should primarily be stored in:

```text
data JSON
```

Avoid creating a new database table whenever the developer creates a Collection.

---

# Entry Value API

Entries should expose developer-friendly methods:

```php
$entry->get('headline');
```

With optional default:

```php
$entry->get('headline', 'Default headline');
```

Also support:

```php
$entry->has('headline');
```

and:

```php
$entry->data();
```

Media-aware values may expose:

```php
$entry->media('featured_image');
```

Taxonomy-aware values may expose:

```php
$entry->terms('category');
```

---

# Sections

Sections are a major feature of Lara Ceemes.

They are intended for building structured landing pages without forcing the frontend developer to use arbitrary HTML stored inside the CMS.

Example:

```text
Collection: Pages

Entry: Home

Sections
├── Banner
├── Help
├── Services
├── Testimonials
└── CTA
```

Each Section Type defines its own fields.

Example:

```text
Banner
├── Title           text
├── Subtitle        textarea
├── Background      media
├── Button Label    text
└── Button URL      url
```

Another type:

```text
Services
├── Title           text
├── Description     textarea
└── Items           repeater
```

---

# Section Types

Database:

```text
ceemes_section_types
```

Suggested fields:

```text
uuid
name
handle
description
icon nullable
created_at
updated_at
```

Example:

```text
name: Banner
handle: banner
```

---

# Section Fields

Database:

```text
ceemes_section_fields
```

Suggested fields:

```text
uuid
section_type_uuid
handle
label
type
config JSON
width
sort_order
created_at
updated_at
```

Example:

```text
banner.title
banner.subtitle
banner.background_image
banner.button_label
banner.button_url
```

---

# Section Instances

Actual Sections belonging to an Entry must be stored separately.

Database:

```text
ceemes_sections
```

Suggested fields:

```text
uuid
entry_uuid
section_type_uuid

field_handle
key nullable

data JSON

sort_order
is_enabled

created_at
updated_at
```

`field_handle` exists so Lara Ceemes can eventually support multiple section areas on a single Blueprint.

Example:

```text
main_sections
sidebar_sections
footer_sections
```

Default:

```text
field_handle = sections
```

---

# Section Key

A Section Type may occur multiple times.

Example:

```text
CTA
CTA
```

Therefore a section instance may optionally have a developer-defined key.

Example:

```text
type: cta
key: top_cta
```

and:

```text
type: cta
key: bottom_cta
```

This allows:

```php
$entry->section('top_cta');
```

while still supporting:

```php
$entry->sectionsOfType('cta');
```

---

# Section API

Entries should provide:

```php
$entry->sections();
```

Only enabled Sections should be returned by default.

Also:

```php
$entry->sections(includeDisabled: true);
```

Support:

```php
$entry->section('banner');
```

`section($identifier)` remains the single convenience API. Resolution must be
deterministic:

```text
1. Return an enabled Section whose key exactly matches the identifier.
2. Otherwise return the first enabled Section whose Section Type handle matches.
3. Return null when neither exists.
```

Use `sectionsOfType('cta')` whenever every Section of a type is required.

Support:

```php
$entry->sectionsOfType('cta');
```

Section instances should provide:

```php
$section->get('title');
```

```php
$section->has('title');
```

```php
$section->media('background_image');
```

```php
$section->type();
```

```php
$section->handle();
```

---

# Allowed Sections Per Blueprint

A Blueprint's `sections` field must be configurable.

Example:

```json
{
  "allowed": ["banner", "help", "services", "testimonials", "cta"]
}
```

Therefore:

```text
Landing Page
```

may allow:

```text
Banner
Services
Testimonials
CTA
```

while:

```text
Article
```

may allow different section types.

Do not allow every Section Type automatically unless the field configuration explicitly allows it.

---

# Sections Admin UX

Example:

```text
Edit Home

Title
[ Home ]

Slug
[ home ]

---------------------------------

Sections

☰ Banner
  Solusi Percetakan Terbaik
  [Edit] [Duplicate] [Disable] [Delete]

☰ Services
  Layanan Kami
  [Edit] [Duplicate] [Disable] [Delete]

☰ CTA
  Hubungi Kami
  [Edit] [Duplicate] [Disable] [Delete]

[ + Add Section ]

---------------------------------

SEO
```

Required actions:

```text
Create
Edit
Delete
Duplicate
Enable
Disable
Reorder
```

Reordering should update:

```text
sort_order
```

---

# Taxonomies

Taxonomies group Terms.

Examples:

```text
Categories
Tags
Topics
Product Categories
```

Database:

```text
ceemes_taxonomies
```

Suggested fields:

```text
uuid
name
handle
description
created_at
updated_at
```

---

# Terms

Database:

```text
ceemes_terms
```

Suggested fields:

```text
uuid
taxonomy_uuid
parent_uuid nullable

name
slug

data JSON nullable

sort_order

created_at
updated_at
```

Terms should support hierarchy.

Example:

```text
Technology
├── Software
│   ├── Laravel
│   └── React
└── Hardware
```

Pivot:

```text
ceemes_entry_term
```

Fields:

```text
entry_uuid
term_uuid
field_handle
```

`field_handle` identifies the Blueprint taxonomy field that owns the relation.
This allows an Entry to contain more than one taxonomy field, including multiple
fields that use the same Taxonomy.

---

# Navigations

Navigation is independent from Collections.

Database:

```text
ceemes_navigations
```

Fields:

```text
uuid
name
handle
created_at
updated_at
```

Example:

```text
Header
Footer
Mobile
Sidebar
```

Items:

```text
ceemes_navigation_items
```

Fields:

```text
uuid
navigation_uuid
parent_uuid nullable

label
type
target

data JSON nullable

sort_order

created_at
updated_at
```

Initial navigation item types:

```text
entry
url
route
```

Navigation items should support nesting and reordering.

Example:

```text
Header
├── Home
├── About
├── Services
│   ├── Printing
│   └── Publishing
└── Contact
```

---

# Settings

Settings must be generic.

Database:

```text
ceemes_settings
```

Suggested fields:

```text
uuid
group
key
value JSON
type
autoload
created_at
updated_at
```

Examples:

```text
general.site_name
general.logo
general.email

cache.enabled
cache.ttl

media.disk
media.max_upload_size

seo.site_title
seo.title_separator
seo.default_description
seo.default_og_image

analytics.google_analytics_id
```

Settings should be cached.

Changing a setting should automatically invalidate the relevant Ceemes settings cache.

---

# Cache Settings

The CMS should expose Ceemes-level cache configuration such as:

```text
Enable CMS Cache
Cache TTL
Cache Collections
Cache Navigation
Cache Settings
```

Do not attempt to rewrite Laravel infrastructure secrets or connection credentials through the CMS.

For example, Laravel Redis credentials and cache server connection information should remain application configuration / environment responsibility.

The CMS manages **Ceemes caching behavior**, not infrastructure credentials.

---

# SEO Settings

Global SEO configuration belongs under:

```text
Settings → SEO
```

Fields:

```text
Site Title
Title Separator
Default Meta Title
Default Meta Description
Default OpenGraph Image
Twitter Card
Default Robots Index
Default Robots Follow
Canonical Base URL
Google Site Verification
```

Entries may override global SEO through their:

```text
seo JSON
```

Resolution order:

```text
Entry SEO
    ↓
Global SEO Settings
```

SEO is configured per Entry through `seo JSON`. For example, Home, Help, and
other Entries in the Pages Collection each have independent SEO values. Missing
Entry values fall back directly to the global SEO settings.

Create a:

```text
SeoManager
```

Example:

```php
$seo = app(SeoManager::class)->forEntry($entry);
```

---

# Media Manager

The CMS must provide:

```text
/admin/media
```

Capabilities for MVP:

```text
Upload
List
Search
Preview
Edit metadata
Delete
Select media from field
Show media usages
```

Database:

```text
ceemes_media
```

Suggested fields:

```text
uuid

disk
directory
filename
original_filename

extension
mime_type
size

width nullable
height nullable

title nullable
alt nullable
caption nullable

uploaded_by nullable

created_at
updated_at
```

Use Laravel Filesystem.

Storage disk must be configurable:

```php
config('ceemes.media.disk');
```

Default:

```text
public
```

Do not store binary file contents directly in the database.

---

# Media References

Fields must reference Media records using UUIDs.

Example:

```json
{
  "hero_image": "0198f0de-7f73-7f4a-a2bf-2fd86f39be10"
}
```

Then:

```php
$entry->media('hero_image');
```

should resolve the Media object.

Media object should expose:

```php
$media->url();
$media->path();
$media->alt;
$media->title;
$media->mime_type;
```

Before deleting Media, `MediaManager` must discover its usages in Entry fields,
Entry SEO, Section fields, Settings, and any other registered media-aware field.
The Admin CMS must show a confirmation popup containing every known usage, such
as the Collection, Entry, field, and Section where applicable.

Media deletion must be blocked while references still exist. The user must first
replace or remove those references, then retry deletion. This prevents silent
broken content while leaving the final decision to the user.

---

# Admin CMS

Default route:

```text
/admin
```

Configurable through:

```php
config('ceemes.admin.prefix');
```

CMS navigation:

```text
Dashboard

Content
├── Collections
│   ├── Blueprints
│   └── Entries
│
├── Sections
│   └── Section Types
│
├── Taxonomies
│   └── Terms
│
├── Navigations
│
└── Media

System
└── Settings
    ├── General
    ├── SEO
    ├── Cache
    └── Media
```

---

# Authentication

Lara Ceemes must not create its own separate user system.

Use the consuming Laravel application's authentication.

Default middleware:

```php
[
    'web',
    'auth',
]
```

Config:

```php
'admin' => [
    'prefix' => 'admin',

    'middleware' => [
        'web',
        'auth',
    ],
],
```

Add an authorization Gate:

```text
access-ceemes
```

The project consuming the package must be able to customize authorization.

Content actor columns such as:

```text
created_by
updated_by
uploaded_by
```

must be nullable foreign keys to the consuming application's `users` table.
Their column type must match `users.id`. Use `nullOnDelete()` so deleting a user
does not delete CMS content, and keep these assignments easy to transfer to a
different user.

Future versions may add role/permission helpers, but this is not required for the first MVP.

---

# Action Layer

Business logic must live primarily inside Actions / Managers.

Example:

```text
src/Actions/Collections/
├── CreateCollection.php
├── UpdateCollection.php
└── DeleteCollection.php
```

Example:

```php
final class CreateCollection
{
    public function execute(array $data): CollectionModel
    {
        // Validate business rules.

        // Create collection.

        // Clear related cache.

        // Dispatch domain event if necessary.

        return $collection;
    }
}
```

Controller:

```php
$collection = $action->execute(
    $request->validated()
);
```

CLI:

```php
$collection = $action->execute([
    'name' => $name,
    'handle' => $handle,
]);
```

Never duplicate create/update/delete logic between HTTP and CLI.

Use database transactions where multiple records must be created together.

---

# Public Facade API

A major requirement is a clean PHP API that custom Laravel frontends can use without dealing directly with internal package implementation.

Provide these Facades:

```text
Collection
Entry
Taxonomy
Navigation
Settings
Media
Seo
```

Namespace:

```php
LaraCeemes\Facades
```

---

# Collection Facade

Usage:

```php
use LaraCeemes\Facades\Collection;
```

Retrieve Collection definition:

```php
$pages = Collection::get('pages');
```

`get()` should throw a useful exception if the Collection does not exist.

Nullable version:

```php
$pages = Collection::find('pages');
```

Check:

```php
Collection::exists('pages');
```

All:

```php
Collection::all();
```

Query Entries belonging to a Collection:

```php
Collection::query('pages');
```

Example:

```php
$home = Collection::query('pages')
    ->whereSlug('home')
    ->firstOrFail();
```

Published articles:

```php
$articles = Collection::query('articles')
    ->published()
    ->latest()
    ->get();
```

Paginate:

```php
$articles = Collection::query('articles')
    ->published()
    ->paginate(12);
```

Therefore:

```php
Collection::get('pages');
```

means:

```text
Get Collection definition
```

while:

```php
Collection::query('pages');
```

means:

```text
Query entries inside Pages
```

Do not overload `get()` with both meanings.

---

# Entry Facade

Examples:

```php
use LaraCeemes\Facades\Entry;
```

```php
$home = Entry::find('pages', 'home');
```

```php
$home = Entry::findOrFail('pages', 'home');
```

Support lookup by UUID:

```php
Entry::byUuid('0198f0de-7f73-7f4a-a2bf-2fd86f39be10');
```

---

# Taxonomy Facade

```php
use LaraCeemes\Facades\Taxonomy;
```

```php
$categories = Taxonomy::get('categories');
```

```php
$terms = Taxonomy::terms('categories');
```

```php
$technology = Taxonomy::term(
    'categories',
    'technology'
);
```

---

# Navigation Facade

```php
use LaraCeemes\Facades\Navigation;
```

```php
$header = Navigation::get('header');
```

Navigation object should expose:

```php
$header->items();
```

---

# Settings Facade

```php
use LaraCeemes\Facades\Settings;
```

Usage:

```php
Settings::get('general.site_name');
```

Default:

```php
Settings::get(
    'general.site_name',
    'My Website'
);
```

Set:

```php
Settings::set(
    'general.site_name',
    'Company Name'
);
```

Check:

```php
Settings::has('seo.default_description');
```

---

# Media Facade

```php
use LaraCeemes\Facades\Media;
```

```php
$image = Media::find('0198f0de-7f73-7f4a-a2bf-2fd86f39be10');
```

```php
$url = Media::find('0198f0de-7f73-7f4a-a2bf-2fd86f39be10')?->url();
```

---

# SEO Facade

```php
use LaraCeemes\Facades\Seo;
```

```php
$seo = Seo::forEntry($entry);
```

or global:

```php
$seo = Seo::global();
```

---

# Facade Implementation

Facades must proxy to Manager classes in the Laravel service container.

Example:

```php
namespace LaraCeemes\Facades;

use Illuminate\Support\Facades\Facade;
use LaraCeemes\Managers\CollectionManager;

class Collection extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CollectionManager::class;
    }
}
```

Register Managers in:

```text
LaraCeemesServiceProvider
```

Example:

```php
$this->app->singleton(
    CollectionManager::class
);
```

Do the same for other Managers.

---

# Collection Naming Collision

Laravel itself has:

```php
Illuminate\Support\Collection
```

Therefore Lara Ceemes must **not register a global class alias automatically**.

Developers explicitly import:

```php
use LaraCeemes\Facades\Collection;
```

If both are required in the same file:

```php
use Illuminate\Support\Collection as SupportCollection;
use LaraCeemes\Facades\Collection as CeemesCollection;
```

Then:

```php
$pages = CeemesCollection::get('pages');
```

This prevents hidden global namespace conflicts.

---

# Entry Query

Create a custom query abstraction:

```text
LaraCeemes\Queries\EntryQuery
```

It may internally wrap Eloquent Builder.

Example:

```php
Collection::query('articles')
    ->published()
    ->where('featured', true)
    ->latest()
    ->paginate(12);
```

Required methods for MVP:

```text
published()
draft()
whereSlug()
where()
orderBy()
latest()
first()
firstOrFail()
get()
paginate()
```

Avoid exposing internal database implementation more than necessary.

---

# Artisan CLI

All package commands must use:

```text
ceemes:
```

namespace.

---

# Install Command

```bash
php artisan ceemes:install
```

---

# Collection Commands

Create:

```bash
php artisan ceemes:make-collection pages
```

Example:

```bash
php artisan ceemes:make-collection articles \
    --name="Articles" \
    --route="/articles/{slug}"
```

Delete may be added later but should require explicit confirmation.

---

# Blueprint Commands

```bash
php artisan ceemes:make-blueprint landing-page \
    --collection=pages
```

---

# Field Commands

Example:

```bash
php artisan ceemes:make-field title \
    --blueprint=landing-page \
    --type=text
```

Another:

```bash
php artisan ceemes:make-field hero-image \
    --blueprint=landing-page \
    --type=media
```

Commands may become interactive if options are omitted.

---

# Entry Commands

```bash
php artisan ceemes:make-entry \
    --collection=pages
```

Interactive example:

```text
Collection:
> Pages

Blueprint:
> Landing Page

Title:
> Home

Slug:
> home

Status:
> Published
```

---

# Section Type Commands

Create Section Type:

```bash
php artisan ceemes:make-section banner
```

Create Section Field:

```bash
php artisan ceemes:make-section-field title \
    --section=banner \
    --type=text
```

Example:

```bash
php artisan ceemes:make-section-field background-image \
    --section=banner \
    --type=media
```

---

# Taxonomy Commands

```bash
php artisan ceemes:make-taxonomy categories
```

---

# Term Commands

```bash
php artisan ceemes:make-term technology \
    --taxonomy=categories
```

---

# Navigation Commands

```bash
php artisan ceemes:make-navigation header
```

---

# Settings Commands

Read:

```bash
php artisan ceemes:setting:get seo.site_title
```

Set:

```bash
php artisan ceemes:setting:set \
    seo.site_title="My Website"
```

---

# Cache Commands

```bash
php artisan ceemes:cache:clear
```

Optional:

```bash
php artisan ceemes:cache:warm
```

---

# Status Command

Provide:

```bash
php artisan ceemes:status
```

Example output:

```text
Lara Ceemes

Version: 0.1.0

Database:
✓ Connected

Migrations:
✓ Up to date

Storage:
✓ public

Cache:
✓ Enabled

Collections: 4
Entries: 27
Taxonomies: 2
Media: 42
```

---

# CMS and CLI Parity

Anything fundamental that can be created through the CMS should preferably also be creatable through Artisan.

At minimum:

```text
Collection
Blueprint
Field
Entry
Section Type
Section Field
Taxonomy
Term
Navigation
Settings
```

However, both interfaces must call the same Actions.

---

# Package Configuration

Create:

```text
config/ceemes.php
```

Suggested configuration:

```php
return [

    'admin' => [

        'prefix' => env(
            'CEEMES_ADMIN_PREFIX',
            'admin'
        ),

        'middleware' => [
            'web',
            'auth',
        ],

    ],

    'cache' => [

        'enabled' => true,

        'ttl' => 3600,

    ],

    'media' => [

        'disk' => env(
            'CEEMES_MEDIA_DISK',
            'public'
        ),

        'max_upload_size' => 10240,

        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/svg+xml',
            'application/pdf',
        ],

    ],

];
```

---

# Service Provider

Main provider:

```text
src/LaraCeemesServiceProvider.php
```

Responsibilities:

```text
Register configuration
Register managers
Register Facades dependencies
Register routes
Register views
Register migrations
Register commands
Register publishable assets
Register authorization
Register field types
```

The package should support Laravel package auto-discovery through Composer.

---

# composer.json

Base example:

```json
{
  "name": "<vendor>/lara-ceemes",

  "description": "A lightweight and flexible CMS package for Laravel.",

  "type": "library",

  "license": "MIT",

  "autoload": {
    "psr-4": {
      "LaraCeemes\\": "src/"
    }
  },

  "extra": {
    "laravel": {
      "providers": ["LaraCeemes\\LaraCeemesServiceProvider"]
    }
  }
}
```

Add only necessary Laravel / Illuminate dependencies.

Do not unnecessarily require unrelated packages.

---

# Local Package Development

Recommended local directory:

```text
Projects/
├── lara-ceemes/
└── ceemes-playground/
```

`lara-ceemes`:

```text
actual Composer package
```

`ceemes-playground`:

```text
normal fresh Laravel application
used to test package installation
```

Inside:

```text
ceemes-playground/composer.json
```

add:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../lara-ceemes",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

Then:

```bash
composer require <vendor>/lara-ceemes:@dev
```

Composer should symlink the package when possible.

Changes inside:

```text
../lara-ceemes
```

should therefore immediately be reflected inside the playground package.

When autoload changes:

```bash
composer dump-autoload
```

---

# Local Testing Workflow

Example:

```bash
cd ceemes-playground
```

Install:

```bash
composer require <vendor>/lara-ceemes:@dev
```

Run installer:

```bash
php artisan ceemes:install
```

Create Collection:

```bash
php artisan ceemes:make-collection pages
```

Create Blueprint:

```bash
php artisan ceemes:make-blueprint landing-page \
    --collection=pages
```

Create Section Type:

```bash
php artisan ceemes:make-section banner
```

Test:

```bash
php artisan serve
```

Open:

```text
http://127.0.0.1:8000/admin
```

---

# Automated Testing

Use:

```text
Pest or PHPUnit
Laravel package testing environment
```

Required tests:

## Collections

```text
Can create collection
Handle must be unique
Can update collection
Can query entries
```

## Blueprints

```text
Can create blueprint
Blueprint belongs to collection
Fields can be ordered
Field configuration persists
```

## Entries

```text
Can create entry
Slug unique inside collection
Entry data persists
Draft excluded from published query
Published query works
```

## Sections

```text
Can create Section Type
Can add fields
Can attach section to entry
Can reorder sections
Can disable section
Disabled section excluded by default
Can duplicate section
Allowed section validation works
```

## Taxonomies

```text
Can create taxonomy
Can create terms
Nested terms work
Entries can attach terms
```

## Navigation

```text
Can create navigation
Can add items
Nested items work
Can reorder items
```

## Settings

```text
Can read setting
Can update setting
Updating setting clears cache
Default value works
```

## Media

```text
Can upload file
Metadata stored
URL resolves
File can be deleted
Invalid mime rejected
```

## Facades

```text
Collection::get()
Collection::query()
Entry::find()
Taxonomy::get()
Navigation::get()
Settings::get()
Media::find()
Seo::forEntry()
```

## CLI

Test all important Artisan commands.

## Admin

Feature tests should verify important CRUD endpoints.

---

# Events

Prepare domain events for extension.

Examples:

```text
CollectionCreated
CollectionUpdated

EntryCreated
EntryUpdated
EntryPublished
EntryDeleted

MediaUploaded
MediaDeleted

SettingUpdated

SectionCreated
SectionUpdated
SectionDeleted
```

Do not overuse events, but allow consuming projects to hook into important CMS changes.

---

# Cache Strategy

Suggested cache keys:

```text
ceemes:settings
ceemes:collection:{handle}
ceemes:navigation:{handle}
ceemes:entry:{collection}:{slug}
```

Manager classes should control caching.

Example:

```text
CollectionManager
NavigationManager
SettingsManager
```

Models themselves should not contain complicated cache logic.

Writes must invalidate relevant caches.

---

# Slugs

Collection handles:

```text
pages
articles
services
```

must be unique.

Entry slug uniqueness should be scoped to Collection.

Therefore this is allowed:

```text
Pages:
about

Articles:
about
```

but this should not:

```text
Pages:
about
about
```

All structural handles must be unique in their logical scope:

```text
Collection handle        unique globally among Collections
Blueprint handle         unique within its Collection
Blueprint Field handle   unique within its Blueprint
Section Type handle      unique globally among Section Types
Section Field handle     unique within its Section Type
Taxonomy handle          unique globally among Taxonomies
Navigation handle        unique globally among Navigations
Setting key              unique within its group
```

Entry slugs remain unique within their Collection.

---

# UUID

Every Lara Ceemes entity must use a UUID as its primary key, including:

```text
Collections
Blueprints
Blueprint Fields
Entries
Section Types
Section Fields
Sections
Taxonomies
Terms
Navigations
Navigation Items
Settings
Media
```

Do not add parallel bigint IDs. Relationships, pivots, Facades, URLs, and public
APIs must consistently use UUID values. User foreign keys are allowed to follow
the consuming application's `users.id` type.

---

# Soft Deletes

Use soft deletes for important content:

```text
Entries
Media
```

Potentially Sections as well if needed.

Collections / Blueprints can initially use hard deletes with strict dependency validation.

Do not allow accidental deletion of a Collection containing Entries without explicit confirmation.

---

# Admin Safety

Destructive actions must require confirmation.

Examples:

```text
Delete Collection
Delete Blueprint
Delete Taxonomy
Delete Media
```

Deleting structures containing data should display useful warnings.

---

# Frontend Responsibility

Lara Ceemes is a content engine and admin CMS.

The consuming Laravel project remains responsible for:

```text
Website layout
Frontend design
CSS
Explicit application routes and route overrides
UI components
Theme
Animation
Responsive design
```

Lara Ceemes should expose clean content APIs without controlling how websites must look.

---

# Example Frontend Usage

Controller:

```php
use LaraCeemes\Facades\Collection;

class HomeController
{
    public function __invoke()
    {
        $page = Collection::query('pages')
            ->whereSlug('home')
            ->firstOrFail();

        return view('home', [
            'page' => $page,
        ]);
    }
}
```

Simple field:

```blade
<h1>
    {{ $page->get('title') }}
</h1>
```

Sections:

```php
$sections = $page->sections();
```

Navigation:

```php
use LaraCeemes\Facades\Navigation;

$navigation = Navigation::get('header');
```

Settings:

```php
use LaraCeemes\Facades\Settings;

$siteName = Settings::get(
    'general.site_name'
);
```

SEO:

```php
use LaraCeemes\Facades\Seo;

$seo = Seo::forEntry($page);
```

---

# Admin Editing Flow

Typical workflow:

```text
1. Developer creates Collection
2. Developer creates Blueprint
3. Developer configures Fields
4. Developer creates Section Types
5. Developer configures Section Fields
6. Editor creates Entry
7. Editor adds/reorders Sections
8. Editor uploads Media
9. Editor configures SEO
10. Frontend retrieves content via Facades
```

All structural operations should also be available from Artisan where practical.

---

# Initial Default Data

After:

```bash
php artisan ceemes:install
```

create only useful minimum defaults.

## Collection

```text
Pages
handle: pages
```

## Navigation

```text
Header
Footer
```

## Setting Groups

```text
General
SEO
Cache
Media
```

Do not automatically create unnecessary demo Collections.

---

# Git Repository Setup

Initialize repository:

```bash
git init
```

Add files:

```bash
git add .
```

Commit:

```bash
git commit -m "Initial Lara Ceemes package"
```

Main branch:

```bash
git branch -M main
```

Create an empty GitHub repository:

```text
lara-ceemes
```

Then:

```bash
git remote add origin git@github.com:<github-user>/lara-ceemes.git
```

Push:

```bash
git push -u origin main
```

---

# Release Versioning

Start with:

```text
v0.1.0
```

Tag:

```bash
git tag v0.1.0
```

Push:

```bash
git push origin v0.1.0
```

Use semantic versioning:

```text
MAJOR.MINOR.PATCH
```

Examples:

```text
0.1.0 initial MVP
0.2.0 new functionality
0.2.1 bug fix
1.0.0 stable public API
```

Do not manually maintain a Composer `"version"` property unless specifically required.

Use Git tags as package versions.

---

# Packagist

After the GitHub repository is public and contains a valid:

```text
composer.json
```

submit the repository to Packagist.

The package should then become installable using:

```bash
composer require <vendor>/lara-ceemes
```

For early stable release:

```bash
composer require <vendor>/lara-ceemes:^0.1
```

For local development:

```bash
composer require <vendor>/lara-ceemes:@dev
```

---

# Updating Lara Ceemes

Package repository:

```bash
git add .
git commit -m "Add feature"
git push
```

Create release:

```bash
git tag v0.2.0
git push origin v0.2.0
```

Consumer project:

```bash
composer update <vendor>/lara-ceemes
```

---

# MVP Scope

Version:

```text
0.1.0
```

must include:

```text
Package foundation

Service Provider
Configuration
Installation command

Collections
Blueprints
Fields
Entries

Section Types
Section Fields
Section Instances

Taxonomies
Terms

Navigations

Media Manager

Settings
SEO Settings
Cache Settings

Admin CMS

Artisan CLI

Facade Public API

Basic caching

Tests
```

---

# Not Required For v0.1

Do not expand initial development with:

```text
Multi-language
Multi-site
Plugin marketplace
Theme marketplace
Revision history
Workflow approvals
Scheduled publishing
Form builder
Full-text search engine
GraphQL
Multi-tenant CMS
Visual drag-and-drop website designer
```

Architecture may leave space for those features later, but they must not delay the MVP.

---

# Future Roadmap

## v0.2

```text
Group field
Entry relations
Advanced Media fields
Scheduled publishing
Draft preview
```

## v0.3

```text
Reusable sections
Global sections
Section presets
Section duplication improvements
```

## v0.4

```text
Roles
Permissions
Editor
Author
Admin
```

## v0.5

```text
Entry revisions
Autosave
Preview
Restore previous version
```

## v1.0

```text
Stable public API
Upgrade documentation
Extension API
Production-ready package
```

---

# Important Architecture Rules

Codex must follow these rules during implementation.

## Rule 1

Do not put business logic inside Controllers.

Use:

```text
Actions
Managers
Services
```

---

## Rule 2

Do not duplicate logic between Admin and Artisan CLI.

Both must call the same underlying Actions.

---

## Rule 3

Do not hardcode content types such as:

```text
Article
Service
Page
```

Collections must remain dynamic.

---

## Rule 4

Do not create a database table for every Collection.

Entries belong in:

```text
ceemes_entries
```

with dynamic content stored in JSON.

---

## Rule 5

Blueprints define Entry structure.

---

## Rule 6

Sections are structured content units.

Each Section Type defines its own fields.

---

## Rule 7

Do not store arbitrary frontend HTML inside Sections as the primary architecture.

Sections contain structured data.

---

## Rule 8

Frontend remains controlled by the consuming Laravel application.

---

## Rule 9

Public frontend integrations should prefer Lara Ceemes Facades / Managers over directly depending on internal Models.

Preferred:

```php
Collection::query('pages');
```

instead of requiring developers to understand internal table structure.

---

## Rule 10

Avoid breaking the public Facade API after stable releases.

---

# Definition of Done for v0.1

A fresh Laravel application must be able to:

```bash
composer require <vendor>/lara-ceemes
```

then:

```bash
php artisan ceemes:install
```

then create content through either:

```text
/admin
```

or:

```bash
php artisan ceemes:...
```

The developer must be able to write:

```php
use LaraCeemes\Facades\Collection;

$pages = Collection::get('pages');
```

and:

```php
$home = Collection::query('pages')
    ->whereSlug('home')
    ->firstOrFail();
```

Then:

```php
$home->sections();
```

must return the ordered enabled Sections for that page.

Likewise these APIs must work:

```php
Navigation::get('header');

Taxonomy::get('categories');

Settings::get('general.site_name');

Media::find('0198f0de-7f73-7f4a-a2bf-2fd86f39be10');

Seo::forEntry($home);
```

The package must remain independent from the frontend design of the consuming Laravel application.

This is the core objective of Lara Ceemes.
