# Multisite

Multisite Lara Ceemes bersifat opt-in dan menggunakan prefix URL berdasarkan
`handle` Site.

## Aktivasi

```env
CEEMES_MULTISITE_ENABLED=true
CEEMES_DEFAULT_SITE=en
CEEMES_DEFAULT_SITE_NAME="English"
CEEMES_DEFAULT_SITE_LOCALE=en
```

```bash
php artisan vendor:publish --tag=ceemes-config --force
php artisan migrate
php artisan optimize:clear
```

Migrasi membuat satu Site default. Data Content, Section, dan Navigation yang
sudah ada otomatis ditempatkan ke Site tersebut. Site selanjutnya dapat dibuat
melalui menu **Admin > Sites** dengan nama, handle URL, locale, status aktif,
dan status default.

## Aturan data

- Per Site: Content, reusable Section, dan Navigation.
- Global/shared: Set beserta Fixed Fields, Section Type, Category, Media, dan
  Settings.
- Slug Content unik per kombinasi Site + Set.
- URI Content unik per Site.
- Handle Section dan Navigation unik per Site.
- Site default tidak dapat dinonaktifkan atau dihapus.
- Site yang masih memiliki Content, Section, atau Navigation tidak dapat
  dihapus.

Selector Site pada top bar Admin menentukan data site-scoped yang sedang
dikelola. Relasi Content dan Section serta target Content pada Navigation juga
divalidasi agar tidak dapat menyeberang ke Site lain.

## Routing frontend

Saat multisite aktif:

```text
/                 -> redirect ke /en (Site default)
/en               -> Homepage English dengan URI /
/en/contact       -> Content English dengan URI /contact
/id               -> Homepage Indonesia dengan URI /
/id/contact       -> Content Indonesia dengan URI /contact
```

Hanya Content `published` dari Set publishable yang dapat diakses. Handle Site
yang tidak ada atau nonaktif menghasilkan 404. Locale Laravel otomatis mengikuti
locale Site aktif.

Saat multisite tidak aktif, perilaku tetap kompatibel dengan single-site:

```text
/
/contact
```

## API dan Artisan

```php
use LaraCeemes\Facades\Site;

$site = Site::current();
$allSites = Site::all();
Site::use('id');
```

```bash
php artisan ceemes:make-content --set=pages --site=id --title="Kontak" --uri=/contact
php artisan ceemes:make-navigation footer --site=id
```

Cache Content dan Navigation memakai namespace UUID Site, sehingga handle dan
slug yang sama pada dua Site tidak saling menimpa.
