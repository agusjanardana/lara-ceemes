# Lara Ceemes concepts

## Content model

### Set

A Set describes one family of Content, such as Pages, Products, Articles, or
Team Members. It owns routing/template settings and optional Fixed Fields.
Set definitions are global, including in multisite mode.

Use separate Sets when records have meaningfully different schemas, URL
patterns, templates, or editorial workflows. Do not create a new Set merely to
represent one page.

### Content

Content is one record inside a Set. Examples are Home and Contact in Pages, or
Red Shoes in Products. It has a title, slug, editable URI, status, field data,
SEO data, and UUID. Only `published` Content from a publishable Set is public.

Content is Site-scoped. Without multisite the default Site is used internally
and public URLs have no Site prefix.

### Fixed Field

A Fixed Field is part of every Content record in a Set. Use it for stable values
such as excerpt, SKU, price, date, featured image, category, or author relation.
Fixed Fields are configured directly on the Set.

### Repeater

A Repeater stores many rows with one consistent nested schema, such as feature
cards containing icon, title, and text. Editors add, remove, and reorder rows.
Use it when every repeated item has the same shape.

### Section Type and Section

A Section Type defines a reusable block schema such as Hero, Gallery, FAQ, or
CTA. A Section is an instance containing actual field values. Content attaches
Section instances through ordered placements and regions.

Use Sections for heterogeneous page building or content reused across multiple
Content records. Editing a shared Section changes every placement; detaching it
does not delete the shared Section.

## Supporting domains

- Categories are hierarchical global vocabularies attached through Category
  Fields. Keep their handles stable.
- Media is a global library. Field values store Media UUIDs rather than public
  URLs. The configured Laravel filesystem disk may be local, public, or S3.
- Navigation is Site-scoped and supports parent/child items. Content targets
  must belong to the same Site as the Navigation.
- Settings are global typed values used for application-wide defaults.
- SEO combines per-Content values with global Settings fallbacks.

## Multisite boundary

Site-scoped:

- Content
- reusable Section instances
- Navigation and its tree

Global/shared:

- Sets and Fixed Field schemas
- Section Types and their field schemas
- Categories
- Media
- Settings

The active Site is chosen from the public URL, the Admin site selector, the
`Site` Facade, or `--site` on supported Artisan commands. URI uniqueness is per
Site; slug uniqueness is per Site and Set.
