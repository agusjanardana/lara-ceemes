# Big Changes: Set, Content, dan Reusable Sections

## Tujuan

Lara Ceemes menghapus konsep schema per-content yang lama sepenuhnya. Model konten utama menjadi:

```text
Set
├── Fixed Fields (opsional)
├── Allowed Section Types
└── Contents
    └── Ordered reusable Sections
```

Istilah canonical yang digunakan di seluruh codebase adalah `Set`, `SetField`,
`Content`, `SectionType`, `Section`, `CategoryGroup`, dan `Category`. Tidak ada
compatibility layer atau migration konversi: Set langsung memiliki Set Fields
dan Content langsung memiliki `set_uuid`.

Package diperlakukan sebagai fresh start. Setiap tabel mempunyai satu migration
`create_*_table` tersendiri agar instalasi baru deterministik dan mudah diaudit.

## Set dan Content

Set mengelompokkan Content, misalnya Pages, Articles, atau Products. Set dapat
memiliki Fixed Fields secara langsung.

Content selalu mempunyai field sistem berikut: title, slug, public URL, status
`draft` atau `published`, dan SEO. Fixed Fields dipakai untuk nilai terstruktur
seperti SKU, price, excerpt, atau featured image. Sebuah Set boleh tidak memiliki
Fixed Fields dan sepenuhnya menggunakan Sections.

## Field, Section Type, dan Section

Ketiga konsep berikut tidak boleh dicampur:

```text
Field Type    Text, Media, Repeater, Content Relation, dan lainnya.
Section Type Template/schema seperti Hero, Gallery, FAQ, atau CTA.
Section      Konten nyata berdasarkan Section Type.
```

Section adalah entitas global reusable. Content memasangnya melalui placement
many-to-many yang menyimpan urutan, status enabled, dan optional key/region.

Satu Section dapat dipasang pada Home dan Contact. Mengubah Section shared akan
mengubah semua Content yang memakainya. Admin wajib menunjukkan daftar
penggunaan dan menyediakan dua aksi yang jelas: gunakan Section yang sudah ada,
atau duplikasi lalu gunakan sebagai salinan independen.

Menghapus placement hanya melepaskan Section dari Content. Menghapus Section
dari library harus ditolak selama masih digunakan dan menunjukkan lokasinya.

## Repeater dan Sections

Repeater mengulang banyak row dengan schema subfield yang sama, misalnya daftar
fitur berisi icon, title, dan description. Sections menyusun blok dengan struktur
berbeda, misalnya Hero, Gallery, FAQ, dan CTA. Repeater tidak menyimpan Sections
sebagai subfield.

## Categories

Category Group adalah wadah seperti Product Categories atau Topics. Category
adalah node hierarkis di dalamnya, misalnya Shoes atau Laravel. Label Admin boleh
disederhanakan menjadi Categories, tetapi domain tetap mempertahankan dua level.

## Frontend API

Facade utama:

```php
use LaraCeemes\Facades\Set;
use LaraCeemes\Facades\Content;
use LaraCeemes\Facades\Section;
use LaraCeemes\Facades\Category;

$pages = Set::find('pages');
$home = Content::find('pages', 'home');
$sections = Section::forContent($home)->enabled()->get();
```

Public URL tetap mendukung `/`, `/contact`, dan nested path. Resolver hanya
mengembalikan Content berstatus published dari Set yang publishable.

Set frontend memakai tiga mode: otomatis, custom, atau tanpa frontend. Mode
otomatis membuat `resources/views/pages.blade.php` untuk Pages dan
`resources/views/{handle}/show.blade.php` untuk Set lain tanpa pernah menimpa
file yang sudah ada. Mode tanpa frontend mengosongkan route/template dan membuat
Set non-publishable sehingga Set aman dipakai sebagai penyimpanan data saja.

```blade
@foreach ($content->sections() as $section)
    @include("sections.{$section->handle()}", ['section' => $section])
@endforeach
```

## Admin Navigation

```text
Content
├── Sets
├── Sections
├── Section Types
├── Categories
├── Navigation
└── Media
```

Editor Content menyediakan informasi dasar, Fixed Fields, ordered Sections, dan
SEO. Section Library menampilkan nama, type, lokasi penggunaan, waktu perubahan,
serta aksi edit, duplicate, dan delete.

## Strategi migrasi

Perubahan dilakukan sebagai breaking release dengan migrasi data satu arah.
Migration upgrade menyalin field dari schema instalasi lama ke
`ceemes_set_fields`, menghapus foreign key schema dari Content, lalu menghapus
tabel schema lama. Runtime, Admin, route, command, dan API baru tidak menyediakan
alias untuk konsep yang sudah dihapus tersebut.
