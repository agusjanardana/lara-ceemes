---
name: lara-ceemes
description: Build, extend, integrate, or debug Laravel applications that use the janar/lara-ceemes CMS package. Use for Lara Ceemes Sets, Content, fields, Sections, templates, routing, multisite, Media, Categories, Navigation, SEO, Artisan commands, or public Facades.
---

# Lara Ceemes

Lara Ceemes is a database-driven CMS package for Laravel. The package owns the
content structures, editor UI, publishing state, public content resolution,
media metadata, and CMS APIs. The consuming Laravel application owns its custom
Blade markup, CSS, frontend components, and explicit application routes.

## Work safely

- Inspect `config/ceemes.php`, the application's routes and views, and the
  installed package version before changing an integration.
- Do not edit files under `vendor/`. Change the consuming application or the
  package source when it is a path dependency.
- Use Lara Ceemes Actions, Managers, Facades, and Eloquent relationships instead
  of duplicating persistence logic with raw table writes.
- Preserve UUID identifiers. Do not introduce integer IDs for CMS entities.
- Keep drafts private. Public queries and routes must return only published
  Content from publishable Sets.
- When multisite is enabled, resolve or select the intended Site before querying
  or creating site-scoped records. Never connect Content, Sections, or
  Navigation targets across Sites.
- Treat field and structure handles as stable API keys. Renaming a handle can
  break stored data, templates, CLI usage, and integrations.
- Avoid destructive migrations or automatic content rewrites unless the user
  explicitly requests and approves the migration strategy.

## Route the task

- Read [references/concepts.md](references/concepts.md) before designing content
  architecture or deciding between Fixed Fields, Repeaters, and Sections.
- Read [references/commands-and-api.md](references/commands-and-api.md) for
  installation, configuration, Artisan, Facades, templates, and routing.
- Read [references/best-practices.md](references/best-practices.md) before
  implementing Sets, Pages, Products, reusable Sections, relations, Media,
  Navigation, SEO, or multisite behavior.

## Implementation workflow

1. Identify whether the requested data is global or Site-scoped and whether it
   belongs in a Set Field, Repeater, Section, Category, Setting, or relation.
2. Prefer Admin configuration for editorial structures. Use Artisan commands
   for repeatable setup, automation, or development fixtures.
3. Keep the frontend template explicit. Use the Set's configured Blade view and
   the variables Lara Ceemes supplies rather than hard-coding CMS table access.
4. Validate server-side through the package Actions. Mirror useful feedback in
   the Admin UI, but never rely on browser validation alone.
5. Test draft versus published behavior, generated URLs, missing records, field
   validation, and—when enabled—at least two Sites with the same URI.
6. After package asset or config changes, publish only what is needed and clear
   Laravel's cached configuration/routes.
