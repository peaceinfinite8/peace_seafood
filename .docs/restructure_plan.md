# restructure_plan.md

## Goal

Reduce duplicate files and tighten the project structure without breaking runtime entry points.

## Current Findings

- Safe exact duplicate already merged: `check.php` root copy was removed.
- Exact duplicate content remains for `.gitkeep` placeholder files, but these are intentionally separate directory markers.
- Same-name files still exist where names alone do not prove duplication, especially `index.php`, `create.php`, `app.php`, and `database.php`.
- `manifest.json` should be treated as follows:
  - Canonical source: [manifest.json](../manifest.json)
  - Duplicate source copy: [public/manifest.json](../public/manifest.json)
  - Generated build manifest: [public/build/manifest.json](../public/build/manifest.json)

### Recent actions

- Removed redundant root `check.php` copy; `public/check.php` remains the web-root placeholder.
- Removed unused duplicate `src/views/pages/check.php` copy.
- Deleted tracked `public/manifest.json`; `scripts/build-assets.mjs` now copies the canonical root `manifest.json` into `public/manifest.json` during builds.
- Updated `.docs/merge_prepare.md` to reflect current duplicates.

## Step-by-Step Execution Checklist

### Phase 1: Confirm source of truth

- [x] Keep [manifest.json](../manifest.json) as the canonical PWA manifest.
- [x] Decide whether [public/manifest.json](../public/manifest.json) should be removed or converted into a deployment-specific copy.
- [x] Keep [public/build/manifest.json](../public/build/manifest.json) as a generated build artifact.

### Phase 2: Remove only true duplicates

- [x] Keep [public/check.php](../public/check.php) as the surviving security placeholder.
- [x] Do not reintroduce the deleted root-level `check.php` copy.
- [ ] Leave `.gitkeep` files in place unless directory ownership changes.

### Phase 3: Review same-name groups

- [ ] Compare `index.php` files by purpose before touching them.
- [ ] Compare `create.php` files by feature area before touching them.
- [ ] Compare `app.php` and `database.php` groups only if there is a shared abstraction opportunity.

### Phase 4: Validate references

- [ ] Search the app for references to any file that is about to be deleted.
- [ ] Confirm layout links and build scripts still resolve after each removal.
- [ ] Update `.docs/merge_prepare.md` after each cleanup pass.

### Phase 5: Finalize documentation

- [ ] Record the canonical file decision in `.docs/restructure_plan.md`.
- [ ] Keep the duplicate scan current.
- [ ] Commit the restructure changes only after the reference search is clean.

## Directory Remodel Analysis & Proposed Layout

Tujuan: susun ulang folder agar jelas pemisahan tanggung jawab (source, public web root, build artifacts, konfigurasi, dokumentasi), memudahkan deploy, dan mengurangi ambiguitas file dengan nama sama.

Analisis singkat:
- Saat ini `public/` adalah web root yang dipakai; beberapa file dengan nama yang sama (mis. `manifest.json`, `check.php`, `index.php`) muncul di root atau subfolder view, menyebabkan kebingungan.
- Build artifacts ditulis ke `public/build/` (contoh: `public/build/manifest.json`) — biarkan tetap di sana.
- Sumber aplikasi, controllers, models, services, dan views sudah berada di `src/` — ini tepat, tetapi perlu konsistensi (pisahkan logic dan view lebih jelas jika perlu).

Rekomendasi struktur (jalankan migrasi bertahap):

- `/public/` — web root (file yang dilayani langsung oleh webserver)
  - `index.php` (front controller)
  - `manifest.json` (deployment copy atau symlink ke root canonical)
  - `assets/` (compiled static: css, js, icons)
  - `build/` (auto-generated build outputs)

- `/src/` — aplikasi server-side
  - `controllers/`
  - `models/`
  - `services/`
  - `middleware/`
  - `utils/`
  - `views/` (UI templates; prefer hanya templates, bukan entry-point index.php)

- `/config/` — konfigurasi environment-safe (app.php, database.php, roles.php)
- `/database/` — migrations, schema, seeders
- `/resources/` — design source, raw images, docs assets (compile/copy into `public/assets` during build)
- `/.docs/` — documentation, TOC, merge reports
- `/scripts/` and `/cli/` — developer scripts and maintenance CLI tasks
- `/storage/` — runtime storage (cache, uploads, logs)
- `/vendor/` and `/.venv/` — dependencies (excluded from refactor)

Mapping actionable changes:
1. Canonicalize `manifest.json` at repo root. During deploy/build, copy or rewrite a deployment-friendly `public/manifest.json` pointing to correct asset paths (or create small step in `scripts/build-assets.mjs`).
2. Keep `public/build/` as generated artifacts; do not track generated files in repo if possible (or keep only hashes/outputs useful for deployment).
3. Move any accidental runnable entry files out of `src/views/` if they are actually public entry points; only `public/` should contain web-facing front controllers.
4. Consolidate static assets source into `resources/` and add build step that emits to `public/assets/`.
5. Standardize `src/views/` to contain templates only; remove direct executable PHP from views (if present) and route through controllers in `src/controllers/`.

Migration checklist (stepwise):
- [ ] Inventory files that will move and create mapping table (source path → target path).
- [ ] Update code references using automated search/replace where safe (e.g., change `/manifest.json` path, asset prefixes).
- [ ] Update `scripts/build-assets.mjs` to produce or copy `public/manifest.json` from canonical root during build.
- [ ] Move files in small commits (1 logical change per commit) and run smoke tests after each commit.
- [ ] Update `.htaccess` and `public/index.php` routing to reflect any basePath or public path changes.

Risiko & mitigasi:
- Risiko: broken references to files after move. Mitigasi: run repository-wide search for references and update paths before committing deletions.
- Risiko: deployment scripts expecting files in previous locations. Mitigasi: add compatibility copy steps in build script for one release cycle.

Operasional notes:
- Jangan hapus `public/build/manifest.json` — itu adalah output build dan harus diproduksi oleh build pipeline.
- Untuk backward-compatibility, prefer menyalin (copy) alih-alih memindahkan file pada tahap awal, dan hapus asal setelah verifikasi.

Tambahkan ke checklist aksi di atas ke `.docs/restructure_plan.md` sebagai tugas terukur sebelum melakukan refactor besar.

## Merge Candidates

### High confidence

- `public/check.php` and `src/views/pages/check.php` are exact duplicates by content.
  - Keep the copy that is actually used by routing.
  - Remove the non-essential duplicate only after confirming references.

### Needs review

- `manifest.json` vs `public/manifest.json` vs `public/build/manifest.json`
  - Source: root [manifest.json](../manifest.json)
  - Public copy: [public/manifest.json](../public/manifest.json) should be treated as redundant unless deployment requires it
  - Build output: [public/build/manifest.json](../public/build/manifest.json) remains generated

- `README.md` files
  - These are different documents with different purposes; do not merge blindly.

- `index.php` and `create.php` groups
  - These are likely repeated filenames across different feature folders, not duplicates.

## Do Not Merge Yet

- `app.php` group
- `database.php` group
- `index.php` group
- `create.php` group
- `.gitkeep` group

## Next Steps

1. Compare `manifest.json` files and decide the canonical file.
2. Search for runtime references before deleting any remaining duplicate copy.
3. Extract a shortlist of truly identical files from the report and process them one by one.
4. Update `.docs/merge_prepare.md` after each cleanup pass.

## Classification of Remaining Same-Name Groups

Below are the same-name groups discovered in the duplicate scan, classified with a recommended action and rationale. Use these as the next refactor tickets.

- `.htaccess` (root, `public/.htaccess`)
  - Classification: keep `public/.htaccess` as authoritative; root `.htaccess` is informational.
  - Action: leave `public/.htaccess` active; optionally move root `.htaccess` to `.docs/` if it's only documentation about DocumentRoot.
  - Priority: low

- `manifest.json` (root, `public/build/manifest.json`)
  - Classification: canonical at repo root; `public/build/manifest.json` is generated.
  - Action: keep root manifest; keep build manifest as generated; do not reintroduce tracked `public/manifest.json`.
  - Priority: done/complete

- `README.md` (root, `.docs/README.md`, `resources/ui/README.md`)
  - Classification: distinct documents for different audiences (project, consolidated docs, UI resources).
  - Action: keep separate; consider linking them from `.docs/README.md`.
  - Priority: low

- `app.php` (config/app.php, src/views/layouts/app.php)
  - Classification: different roles (config vs layout template).
  - Action: keep separate; rename view layout to `layout_app.php` only if naming collisions cause automation confusion.
  - Priority: low

- `database.php` (config/database.php, src/utils/Database.php)
  - Classification: config vs DB helper class/file.
  - Action: keep separate; no merge.
  - Priority: low

- `index.php` (public/index.php and many `src/views/*/index.php`)
  - Classification: front controller vs view index templates.
  - Action: ensure `public/index.php` remains the only executable front-controller; convert view `index.php` into template-only files if they contain executable PHP; consider renaming view index files to `index.tpl.php` or moving into feature folders under `views/`.
  - Priority: medium

- `.gitkeep` placeholders (multiple storage directories)
  - Classification: intentional empty-file markers.
  - Action: keep as-is; remove only when directory is removed or consolidated.
  - Priority: low

- `Pembeli.php`, `Produk.php`, `Supplier.php`, `Timbangan.php` (model vs view filenames)
  - Classification: models vs views sharing names.
  - Action: prefer distinct naming conventions: keep model classes under `src/models/` and move/rename view files (e.g., `pembeli_view.php`) to reduce confusion; low-risk refactor.
  - Priority: medium

- `create.php` across `src/views/*/create.php`
  - Classification: feature-specific create pages (expected same filename across modules).
  - Action: leave in place; if automation cares about filename collisions, rename to `create.form.php` or similar to indicate template role.
  - Priority: low

Notes:
- Where the action says "rename" or "convert to template-only", do so in small commits and update references. Use `git mv` to preserve history where possible.
- Medium-priority items are good candidates for a focused refactor sprint: `index.php` view templates and model/view name collisions. Low-priority items can be cleaned opportunistically.

