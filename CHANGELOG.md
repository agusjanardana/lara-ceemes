# Changelog

All notable changes to Lara Ceemes are documented in this file.

## [0.3.0] - 2026-09-23

### Added

- Field configuration tabs for validation and conditional visibility, including
  clear field-level errors and matching browser/server behavior.
- Media type filters, database-backed nested folders, and storage-prefix moves
  compatible with Laravel local/public disks and S3.
- Collapsible Set and Category Group shortcuts in the Admin sidebar.
- Cleaner inline Fixed Field cards and searchable Media/Category pickers.

### Changed

- Admin authorization now uses a configurable role directly on the consuming
  application's User model instead of a separate superadmin registry table.
- Section Fields now use the same guided field builder as Set Fields.
- Fresh-start migrations were reorganized into one canonical migration per
  current table without legacy cleanup migrations.

## [0.2.0] - 2026-09-01

### Added

- New Set and Content terminology with direct Set Fields; every Set
  owns one internal schema for optional Fixed Fields.
- Global reusable Section Library and ordered many-to-many Content placements,
  including attach/detach usage workflows and cross-Content usage visibility.
- Canonical Set, Content, Section, Category Group, and Category domain with
  `content` and `category` field handles.
- `ceemes:make-set`, `ceemes:make-set-field`, and `ceemes:make-content` commands.

- Laravel 13 package foundation with auto-discovery and UUID-only CMS schema.
- Configurable authenticated Admin CMS protected by the `access-ceemes` Gate.
- Sets, extensible Fields, Contents, and status-only publishing.
- Structured Section Types, Section Fields, Section instances, duplication,
  enable/disable, deterministic lookup, and reordering.
- Hierarchical Category Groups/Categories and field-scoped Content-Category relations.
- Hierarchical Navigations and configurable Settings with cache invalidation.
- Per-Content SEO with global Settings fallback.
- Filesystem Media Library with metadata, search, usage discovery, and protected
  deletion while content references remain.
- Public Set, Content, CategoryGroup, Navigation, Settings, Media, and SEO Facades.
- Shared Actions for Admin and Artisan, domain events, and cache management.
- Installer, status, cache, content-creation, and setting Artisan commands.
- Compiled Admin CSS requiring no frontend build in consuming applications.
- Built-in login/logout screens backed by the consuming application's User model.
- Superadmin registry and `ceemes:make-superadmin` Artisan command.
- Configurable Lara Ceemes landing page for the public `/` route.
- Responsive Admin shell with searchable CRUD tables, filters, confirmation
  dialogs, set shortcuts, and mobile navigation.
- Content editor, per-Content SEO panel, Section editor, Media grid,
  and parent/child Navigation presentation.
- Guided Set setup, dedicated Set Field management,
  automatic handles, and human-friendly common field configuration.
- Corrected nested search input focus and browser-clear styling in the Admin UI.
- Set-controlled Sections areas with explicit allowed Section Types and
  per-field builders.
- Published-only public Content routing with editable globally unique URIs,
  homepage support, nested paths, reserved Admin protection, and Blade template
  overrides.
- Searchable modal pickers for Set Field Types and Content relations,
  Set-scoped Content fields, and working single/multiple relation values.
- Visual Repeater schema builder with searchable subfield types, row limits,
  nested validation, and add/remove/reorder controls in the Content editor.
- Redesigned package login and public landing pages.
- PHPUnit, Testbench, Pint, PHPStan level 8, CI, and Laravel playground coverage.

## [0.1.0] - Pending

The `0.1.0` tag will be created after repository ownership, vendor name, and
release metadata are confirmed by the maintainer.
