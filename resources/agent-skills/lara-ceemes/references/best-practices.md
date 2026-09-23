# Best practices

## Pages

- Keep ordinary website pages in the global `pages` Set; create Content entries
  such as Home, Help, About, and Contact rather than a Set per page.
- Assign Homepage URI `/`, Contact URI `/contact`, and nested pages paths such as
  `/company/team`. Treat the editable URI as canonical routing data.
- Put stable page-wide attributes in Fixed Fields. Use Sections for ordered,
  heterogeneous page blocks and Repeaters for uniform lists.
- Keep SEO on each Content record. Let missing SEO values fall back to global
  settings rather than duplicating defaults into every page.
- Render custom design in the configured Blade view. Keep field handles stable
  and escape ordinary editor content; only render trusted rich text through the
  intended rich-text path.

## Products and other Sets

- Use a separate Set when records share a product-like schema and route, for
  example Products with SKU, price, image, and specifications.
- Prefer the conventional `products.show` template and `/products/{slug}` route,
  then override them only when the application genuinely needs another layout.
- A Set may be data-only when its records are consumed by another page or API.
  Mark it non-publishable and do not create fake public routes.
- Relation fields should select a source Set first. Enable multiple selection
  only when the domain is truly many-valued.

## Fields and validation

- Use explicit, semantic handles such as `featured_image`, `summary`, or
  `related_products`; never use labels as runtime keys.
- Configure required, length, numeric, date, and item-count validation close to
  the field definition. Return field-specific errors to editors.
- Conditional visibility improves editing but is not authorization and must not
  replace server-side validation.
- Avoid deeply nested Repeaters. Promote complex or reusable nested structures
  to Section Types.

## Sections

- Model each reusable visual/content block as one Section Type with a focused
  schema.
- Use named regions/Sections fields when a template has distinct areas.
- Reuse a Section instance only when shared edits are intentional. Duplicate it
  when pages should diverge later.
- Before deleting a Section Type or Section, inspect usages and preserve Content
  placements.

## Media, Categories, and Navigation

- Store and resolve Media UUIDs; do not persist generated disk URLs in Content.
- Use Media folders for editor organization, not as a business taxonomy.
- Check all-site Media usage before deletion because Media is global.
- Use Categories for reusable classification and filters, not freeform layout.
- Keep Navigation trees shallow and labels editorial. Prefer Content targets to
  copied internal URLs so generated Site prefixes remain correct.

## Multisite

- Enable multisite deliberately, publish/merge the latest config, migrate, and
  clear caches before creating additional Sites.
- Keep structure definitions global when Sites share the same editorial model.
  Store translated/localized values in each Site's Content and Sections.
- Test the same URI in at least two Sites, Site homepages, disabled Sites,
  locale changes, relation pickers, Navigation targets, and cache isolation.
- Use the Admin selector or `Site::use($handle)` before programmatic writes.
  Queue jobs must select their Site explicitly because they have no public route
  or Admin session from which to infer context.

## Package integration

- Publish compiled Admin assets after package UI updates. A Node build is not
  required in the consuming application for Lara Ceemes Admin.
- Do not blindly overwrite a customized `config/ceemes.php`; compare it with the
  package version or back it up before `vendor:publish --force`.
- Keep explicit application routes outside the package and verify they still
  win over catch-all content routing.
- Test Actions and observable behavior rather than writing directly to CMS
  tables. Include draft/public, validation failure, missing content, and
  multisite isolation cases proportional to the change.
