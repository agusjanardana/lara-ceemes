# Commands and public API

## Install and update

```bash
composer require janar/lara-ceemes
php artisan ceemes:install
php artisan ceemes:make-superadmin
php artisan ceemes:make-agent-skill
```

When updating published resources, compare application config before forcing an
overwrite:

```bash
php artisan vendor:publish --tag=ceemes-config
php artisan vendor:publish --tag=ceemes-assets --force
php artisan migrate
php artisan optimize:clear
php artisan ceemes:make-agent-skill --force
```

## Structure and content commands

```bash
php artisan ceemes:make-set pages --name="Pages"
php artisan ceemes:make-set-field excerpt --set=pages --type=textarea
php artisan ceemes:make-content --set=pages --title="Contact" --uri=/contact --status=published

php artisan ceemes:make-section hero --name="Hero"
php artisan ceemes:make-section-field heading --section=hero --type=text

php artisan ceemes:make-category-group topics --name="Topics"
php artisan ceemes:make-category laravel --group=topics --name="Laravel"
php artisan ceemes:make-navigation header
```

Other operational commands:

```text
ceemes:status
ceemes:cache:clear
ceemes:setting:get
ceemes:setting:set
ceemes:make-superadmin
ceemes:make-agent-skill
```

Use `php artisan help <command>` for the installed version's exact options.

## Multisite

```env
CEEMES_MULTISITE_ENABLED=true
CEEMES_DEFAULT_SITE=en
CEEMES_DEFAULT_SITE_NAME="English"
CEEMES_DEFAULT_SITE_LOCALE=en
```

```bash
php artisan ceemes:make-content --set=pages --site=id --title="Kontak" --uri=/contact
php artisan ceemes:make-navigation footer --site=id
```

After enabling multisite, migrate and clear cached configuration. Manage other
Sites under **Admin > Sites**.

## Facades

```php
use LaraCeemes\Facades\Category;
use LaraCeemes\Facades\Content;
use LaraCeemes\Facades\Media;
use LaraCeemes\Facades\Navigation;
use LaraCeemes\Facades\Seo;
use LaraCeemes\Facades\Set;
use LaraCeemes\Facades\Settings;
use LaraCeemes\Facades\Site;

$site = Site::current();
$home = Set::query('pages')->published()->whereSlug('home')->firstOrFail();
$sameRecord = Content::find('pages', 'home');
$header = Navigation::get('header');
$topics = Category::categories('topics');
$image = Media::find($mediaUuid);
$siteName = Settings::get('general.site_name', 'My Website');
$seo = Seo::forContent($home);
```

Facade Content and Navigation lookups honor the active Site.

## Public routing and templates

With multisite disabled, URI `/contact` resolves at `/contact`. With Site handle
`en`, it resolves at `/en/contact`, and Site homepage URI `/` resolves at `/en`.
Root `/` redirects to the default Site when package homepage routing is enabled.

New Sets use these conventions unless configured otherwise:

```text
Pages    -> resources/views/pages.blade.php
Products -> resources/views/products/show.blade.php
```

The Set may instead use a custom Blade view or be data-only. Public templates
receive `$content`, `$set`, `$fields`, `$sections`, and `$seo`. Prefer helpers
such as `$content->get('headline')`, `$content->sections()`,
`$content->section('hero')`, and `$content->media('featured_image')`.

Set `CEEMES_PUBLIC_ROUTING=false` when the consuming application owns all public
controllers. Explicit application routes have priority over the package
catch-all route.
