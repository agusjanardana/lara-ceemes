# Changelog

All notable changes to Lara Ceemes are documented in this file.

## [Unreleased]

### Added

- Laravel 13 package foundation with auto-discovery and UUID-only CMS schema.
- Configurable authenticated Admin CMS protected by the `access-ceemes` Gate.
- Collections, Blueprints, extensible Fields, Entries, and status-only publishing.
- Structured Section Types, Section Fields, Section instances, duplication,
  enable/disable, deterministic lookup, and reordering.
- Hierarchical Taxonomies/Terms and field-scoped Entry-Term relations.
- Hierarchical Navigations and configurable Settings with cache invalidation.
- Per-Entry SEO with global Settings fallback.
- Filesystem Media Library with metadata, search, usage discovery, and protected
  deletion while content references remain.
- Public Collection, Entry, Taxonomy, Navigation, Settings, Media, and SEO Facades.
- Shared Actions for Admin and Artisan, domain events, and cache management.
- Installer, status, cache, content-creation, and setting Artisan commands.
- Compiled Admin CSS requiring no frontend build in consuming applications.
- Built-in login/logout screens backed by the consuming application's User model.
- Superadmin registry and `ceemes:make-superadmin` Artisan command.
- Configurable Lara Ceemes landing page for the public `/` route.
- Responsive Admin shell with searchable CRUD tables, filters, confirmation
  dialogs, collection shortcuts, and mobile navigation.
- Blueprint-aware Entry editor, per-Entry SEO panel, Section editor, Media grid,
  and parent/child Navigation presentation.
- Guided Collection-to-Blueprint setup, dedicated Blueprint Field management,
  automatic handles, and human-friendly common field configuration.
- Corrected nested search input focus and browser-clear styling in the Admin UI.
- Blueprint-controlled Sections areas with explicit allowed Section Types,
  per-field builders, and hidden Section UI for fixed-field-only Blueprints.
- Published-only public Entry routing with editable globally unique URIs,
  homepage support, nested paths, reserved Admin protection, and Blade template
  overrides.
- Searchable modal pickers for Blueprint Field Types and Entry relations,
  Collection-scoped Entry fields, and working single/multiple relation values.
- Visual Repeater schema builder with searchable subfield types, row limits,
  nested validation, and add/remove/reorder controls in the Entry editor.
- Redesigned package login and public landing pages.
- PHPUnit, Testbench, Pint, PHPStan level 8, CI, and Laravel playground coverage.

## [0.1.0] - Pending

The `0.1.0` tag will be created after repository ownership, vendor name, and
release metadata are confirmed by the maintainer.
