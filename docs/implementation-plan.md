# Lara Ceemes v0.1 Implementation Plan

This plan divides the complete v0.1 scope into eight finishable phases. A phase
is complete only after its automated tests pass and its public behavior is
documented.

Implementation status: the domain layer, database, public API, CLI, installer,
and functional Admin CRUD are complete as a local `v0.1.0` release candidate.
Field-aware Admin editors, Section reorder UI, Media picker/modal polish, broader
database compatibility runs, publishing, committing, and tagging remain.

## 1. Package Foundation and Test Harness

Status: Complete.

- Create Composer package metadata, PSR-4 autoloading, and Laravel auto-discovery.
- Add the Service Provider, configuration, migrations, factories, and exceptions.
- Configure Orchestra Testbench with MySQL-compatible migration coverage.
- Establish UUID model conventions and the configurable user foreign-key strategy.
- Add CI checks for tests, static analysis, and code style.

Exit criterion: the package installs in a fresh test application and all empty
migrations can run and roll back.

## 2. Field Engine and Shared Application Layer

Status: Complete.

- Define the `FieldType` contract and implement `FieldRegistry`.
- Implement validation, normalization, serialization, resolution, and admin-view
  hooks.
- Establish Actions for writes and Managers/Queries for reads and caching.
- Add shared domain exceptions, transactions, events, and cache invalidation rules.
- Implement and test the initial field types required by the specification.

Exit criterion: field types can be registered, validated, stored, and resolved
without switch statements or HTTP/CLI-specific logic.

## 3. Collections, Blueprints, and Entries

Status: Complete.

- Implement UUID models and migrations for Collections, Blueprints, Blueprint
  Fields, and Entries.
- Enforce handle and slug uniqueness in their defined scopes.
- Implement Entry data access, per-Entry SEO storage, draft/published behavior,
  soft deletes, Actions, Managers, and domain events.
- Implement `EntryQuery`, Collection/Entry Facades, and their cache behavior.
- Add feature and unit tests for all public APIs and constraints.

Exit criterion: a developer can define a content type and create/query draft or
published Entries entirely through Actions and Facades.

## 4. Structured Sections

Status: Complete.

- Implement Section Types, Section Fields, and Section instances with UUIDs.
- Validate allowed Section Types per Blueprint sections field.
- Implement create, edit, duplicate, enable, disable, delete, and reorder Actions.
- Implement deterministic `section($identifier)`, `sections()`, and
  `sectionsOfType()` APIs.
- Cover multiple section areas through `field_handle` and test ordering rules.

Exit criterion: an Entry returns its enabled Sections in order and every Section
operation works through the shared application layer.

## 5. Taxonomies, Navigations, Settings, SEO, and Cache

Status: Complete.

- Implement hierarchical Taxonomies and Terms with a `field_handle` on Entry-Term
  relations.
- Implement hierarchical Navigation Items for entry, URL, and named-route targets.
- Implement typed Settings, autoloading, cache invalidation, and setting groups.
- Implement `SeoManager` resolution from Entry SEO to global SEO settings.
- Complete Taxonomy, Navigation, Settings, and SEO Facades and tests.

Exit criterion: all four domains are manageable through Actions and readable
through their documented Facades with correct caching.

## 6. Media Library and Reference Safety

Status: Complete.

- Implement UUID Media records, configurable filesystem disks, uploads, metadata,
  MIME validation, search, preview, URLs, and soft deletes.
- Implement media-aware field resolution.
- Build usage discovery across Entries, Entry SEO, Sections, Settings, and other
  registered media fields.
- Block deletion while references exist and return structured usage details for
  the Admin popup.
- Add filesystem and deletion-safety tests.

Exit criterion: Media can be safely uploaded and selected, and referenced Media
cannot be deleted until its usages are removed.

## 7. Artisan CLI, Installer, and Admin CMS

Status: Core complete; final Admin UX pending.

- Implement every required `ceemes:` command using the existing Actions.
- Build an idempotent installer, default data, status command, and cache commands.
- Build authenticated and authorized Admin CRUD screens for every v0.1 domain.
- Implement Section sorting and editing, Media selection, and Media usage popup.
- Distribute compiled Admin assets so consuming projects need no frontend build.

The underlying Section reorder and Media usage Actions are complete. The current
Admin exposes functional CRUD with JSON textareas for dynamic Entry/Section data;
field-aware widgets, a Media picker, visual Section reorder controls, and a modal
usage presentation remain before this phase fully meets its UX acceptance scope.

Exit criterion: structural and content workflows work from both Admin and Artisan
without duplicated business logic.

## 8. Integration Hardening and v0.1 Release

Status: In progress; local SQLite/Testbench and playground verification pass.
Cross-database CI, final Admin UX acceptance, the Git tag, and Packagist
publication remain.

- Test installation and upgrades in the local Laravel playground.
- Complete authorization, destructive confirmations, dependency checks, database
  indexes, transactions, and cache invalidation audits.
- Run the complete unit, feature, CLI, Admin, and Facade test suites on supported
  PHP and database versions.
- Finish user documentation, upgrade notes, changelog, license, and Packagist
  metadata.
- Tag `v0.1.0` only after the documented Definition of Done passes end to end.

Exit criterion: a fresh Laravel application can install Lara Ceemes, manage all
v0.1 content through Admin or CLI, and consume it through the stable Facade API.
