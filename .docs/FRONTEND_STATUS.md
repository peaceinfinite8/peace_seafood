# 🎨 FRONTEND STYLING AUDIT & STATUS
**Date**: May 30, 2026  
**Status**: In Progress — Light mode improvements underway

---

## 📊 OVERVIEW

Peace Seafood uses a **CSS variable system with dark/light mode support** via `data-theme="light|dark"` attribute. The frontend infrastructure is well-established; remaining work is to standardize light mode styling across 21 view templates.

### Technology Stack
- **Framework**: Tailwind CSS (runtime via `tailwindcss.js`)
- **CSS System**: Custom variables (colors, spacing, typography)
- **Dark Mode**: Alpine.js toggle stored in localStorage
- **Icons**: Lucide Icons (CDN)
- **Charts**: Chart.js
- **Interactivity**: Alpine.js 3.x

---

## ✅ COMPLETED WORK

### CSS Infrastructure (Complete)
| Component | Location | Status |
|-----------|----------|--------|
| Variables system | `public/css/variables.css` | ✅ Defined (light mode) |
| Dark mode overrides | `public/css/dark-mode.css` | ✅ Comprehensive coverage |
| Global card styles | `src/views/layouts/app.php` | ✅ `.card` and `.stat-card` |
| Theme toggle | `resources/ui/js/theme.js` | ✅ localStorage persistence |
| Layout template | `src/views/layouts/app.php` | ✅ Tailwind + CSS var integration |

### View Pages (1 of 21 completed)
- **Dashboard** (`src/views/pages/dashboard.php`) — ✅ Light mode improved with gradient stat-card variants
  - Uses global `.card` and enhanced `.stat-card` styles
  - Color-coded metrics (success, warning, danger)
  - Ready for dark mode testing

---

## 🚧 IN PROGRESS: LIGHT MODE STANDARDIZATION

**Goal**: Apply consistent light mode styling to all 21 card-based view pages.

### Pages Pending Light Mode Refactor (20 pages)

#### **Master Data** (4 pages)
- [ ] `pembeli.view.php` — Customer/buyer management
- [ ] `produk.view.php` — Product catalog
- [ ] `supplier.view.php` — Supplier management
- [ ] `timbangan.view.php` — Scale/weighing records

#### **Financial/Reporting** (4 pages)
- [ ] `pages/login.php` — Login form (hardcoded colors)
- [ ] `keuangan/index.php` — Accounting/financial dashboard
- [ ] `penitipan/index.php` — Consignment tracking
- [ ] `laporan/index.php` — Reports dashboard

#### **Inventory Management** (3 pages)
- [ ] `penjualan/index.php` — Sales dashboard
- [ ] `retur/index.php` — Returns management
- [ ] `stok/index.php` — Stock management

#### **Template Normalization** (9 pages)
- [ ] 9x `index.php` view files lacking `.view.php` suffix
  - Currently ambiguous naming (collides conceptually with route index)
  - Should rename to `{feature}.index.view.php` or move to feature-specific locale

---

## 🎨 STYLING REFERENCE: LIGHT MODE PATTERN

### Example: Dashboard Cards (Template)
```php
<!-- Global card wrapper (light + dark aware) -->
<div class="card bg-white border border-gray-200 p-6 rounded-lg">
    <!-- Use CSS variables for text -->
    <h3 class="text-gray-900">{{ title }}</h3>
    <p class="text-gray-600">{{ subtitle }}</p>
</div>

<!-- Status-colored card -->
<div class="stat-card stat-card--success">
    <!-- Gradient background + icon -->
    <i class="lucide-check-circle"></i>
    <span>{{ value }}</span>
</div>
```

### CSS Variables (Light Mode)
Defined in `public/css/variables.css`:
```css
:root {
  --color-primary: #2563eb;
  --color-primary-light: #dbeafe;
  --bg-light: #ffffff;
  --bg-gray: #f8fafc;
  --text-primary: #1e293b;
  --text-secondary: #64748b;
  --border-color: #e2e8f0;
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-danger: #ef4444;
}
```

### Dark Mode Overrides
Applied via `[data-theme="dark"]` in `public/css/dark-mode.css`:
```css
[data-theme="dark"] {
  --bg-light: #1e293b;
  --text-primary: #f1f5f9;
  --border-color: #475569;
  /* ... */
}
```

---

## 📋 ACTION PLAN (Prioritized)

### **Phase 1: High-Priority Pages** (Est. 2-3 hours)
Apply light mode styling to 8 core pages that impact user experience:

1. **login.php** — First impression; minimal styling
2. **keuangan/index.php** — Financial summary; high visibility
3. **penjualan/index.php** — Sales tracking; daily use
4. **stok/index.php** — Inventory; high traffic
5. **penitipan/index.php** — Consignment tracking
6. **retur/index.php** — Returns management
7. **laporan/index.php** — Reports & exports
8. **pembeli.view.php** — Master data (customer)

**Per-page tasks**:
- [ ] Replace hardcoded colors with CSS variables
- [ ] Apply `.card` wrapper to card containers
- [ ] Use Lucide icons (already available)
- [ ] Test light + dark mode toggle

### **Phase 2: Remaining Master Data** (Est. 1 hour)
- [ ] produk.view.php
- [ ] supplier.view.php
- [ ] timbangan.view.php

### **Phase 3: Template Normalization** (Est. 2-3 hours)
- [ ] Audit 9x `index.php` naming convention
- [ ] Rename to `.view.php` suffix or feature-specific prefix
- [ ] Update route references in `routes/web.php`

### **Phase 4: Testing & Polish** (Est. 1-2 hours)
- [ ] Smoke test all pages in light + dark modes
- [ ] Verify color contrast (WCAG AA minimum)
- [ ] Check responsive design on mobile

---

## 📂 KEY FILES

| File | Purpose | Status |
|------|---------|--------|
| `public/css/variables.css` | Light mode color variables | ✅ Complete |
| `public/css/dark-mode.css` | Dark mode overrides | ✅ Complete |
| `public/css/ui-theme.css` | Additional theme-aware styles | ✅ Exists |
| `public/css/custom.css` | App-specific utilities | ✅ Exists |
| `src/views/layouts/app.php` | Master layout + Tailwind config | ✅ Complete |
| `resources/ui/js/theme.js` | Theme toggle logic | ✅ Complete |
| `.docs/resources/ui/palette.md` | Color palette reference | ✅ Reference doc |

---

## 🔍 VALIDATION CHECKLIST

Before marking page as "done":
- [ ] All hardcoded colors replaced with `var(--color-*)` or Tailwind classes
- [ ] `.card` or `.stat-card` classes applied appropriately
- [ ] Tested in light mode: colors, borders, text contrast
- [ ] Tested in dark mode: toggle works, no visual breaks
- [ ] Lucide icons render correctly
- [ ] Responsive layout maintained on mobile
- [ ] No console errors (check browser DevTools)

---

## 💡 NEXT STEPS

1. **Immediate**: Create task list for Phase 1 pages (8 high-priority)
2. **Week 1**: Complete Phase 1 + Phase 2 refactors
3. **Week 2**: Handle Phase 3 (template naming) if needed
4. **Week 3**: Full smoke test + polish

**Estimated Total Effort**: 6-8 hours developer time

---

## 📌 NOTES

- **Dashboard** serves as the proof-of-concept for light mode styling — use as template
- **CSS variables are global** — applying to one page doesn't affect others
- **Dark mode is working** — focus on light mode visual improvements only
- **No new colors needed** — palette is complete (primary, success, warning, danger, neutrals)

---

*Last Updated: May 30, 2026*
