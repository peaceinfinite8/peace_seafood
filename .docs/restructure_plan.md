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

## Step-by-Step Execution Checklist

### Phase 1: Confirm source of truth

- [ ] Keep [manifest.json](../manifest.json) as the canonical PWA manifest.
- [ ] Decide whether [public/manifest.json](../public/manifest.json) should be removed or converted into a deployment-specific copy.
- [ ] Keep [public/build/manifest.json](../public/build/manifest.json) as a generated build artifact.

### Phase 2: Remove only true duplicates

- [ ] Keep [public/check.php](../public/check.php) as the surviving security placeholder.
- [ ] Do not reintroduce the deleted root-level `check.php` copy.
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
