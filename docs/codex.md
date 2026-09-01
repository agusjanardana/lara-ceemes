# Lara Ceemes Architecture

Dokumen ini adalah kontrak arsitektur aktif Lara Ceemes. Implementasi mengikuti
model sederhana berikut:

```text
Set
├── Fixed Fields (opsional)
└── Contents
    ├── system fields: title, slug, URI, status, SEO
    └── ordered reusable Sections

Section Type
└── Section Fields

Category Group
└── hierarchical Categories
```

## Prinsip domain

- Semua entitas CMS memakai UUID sebagai primary key.
- Handle unik di dalam scope pemiliknya.
- Set memiliki Fixed Fields secara langsung melalui `ceemes_set_fields`.
- Content merujuk Set secara langsung melalui `set_uuid`.
- Tidak ada lapisan schema per-Content yang harus dibuat atau dipilih editor.
- Content hanya publik ketika statusnya `published` dan Set publishable.
- User audit menggunakan foreign key ke model User aplikasi.
- Media yang masih digunakan tidak boleh dihapus; UI harus menampilkan seluruh
  lokasi pemakaiannya.

## Fixed Fields dan Sections

Fixed Field dipakai untuk nilai dengan posisi dan struktur tetap seperti SKU,
price, excerpt, tanggal, featured image, relasi Content, atau Category.

Section Type adalah schema blok reusable seperti Hero, FAQ, Gallery, atau CTA.
Section adalah instance kontennya. Content memasang Section lewat placement
many-to-many yang menyimpan region, key, urutan, dan status enabled. Satu Section
dapat digunakan beberapa Content.

Repeater mengulang row dengan schema subfield yang sama. Sections digunakan
untuk rangkaian blok yang dapat berbeda tipe. Repeater tidak menerima Section
sebagai subfield.

## Routing publik

Setiap Content mempunyai URI global unik. `/` digunakan untuk homepage,
`/contact` untuk halaman Contact, dan nested path seperti `/company/team` juga
didukung. Prefix Admin tidak boleh dipakai sebagai URI Content.

Resolver publik hanya mengambil Content published. Jika Set memiliki template
Blade yang valid, template menerima `$content`, `$content` sebagai alias storage,
dan `$seo`. Jika tidak, package memakai view publik bawaan.

## API utama

```php
use LaraCeemes\Facades\Set;
use LaraCeemes\Facades\Content;
use LaraCeemes\Facades\Section;
use LaraCeemes\Facades\Category;

$pages = Set::find('pages');
$home = Content::find('pages', 'home');
$sections = Section::forContent($home)->enabled()->get();
$categories = Category::categories('topics');
```

## Admin dan CLI

Admin memakai alur CRUD Set → Fixed Fields → Content. Field Type dan relasi
Content dipilih melalui modal searchable. Editor Content berisi system fields,
Fixed Fields, Sections, publishing, dan SEO.

Command domain utama:

```text
ceemes:make-set
ceemes:make-set-field
ceemes:make-content
ceemes:make-section
ceemes:make-section-field
ceemes:make-superadmin
```

## Upgrade instalasi lama

Migration upgrade memindahkan field dari struktur schema lama ke Set Fields,
menghapus foreign key schema dari Content, kemudian menghapus tabel lama.
Migration ini satu arah karena mengembalikan banyak schema menjadi satu schema
Set berisiko mengubah makna data. Backup database wajib dilakukan sebelum
upgrade versi breaking.
