# 🎨 UI GUIDELINES — Peace Seafood Design System

---

## 🎭 Design Philosophy

- **Minimalist Modern**: Clean interface dengan maximum functionality
- **Accessibility First**: Support dark/light mode, keyboard navigation
- **Responsive**: Works seamlessly mobile, tablet, desktop
- **Consistency**: Unified component library & color system
- **User-Focused**: Reduce clicks, clear CTAs, smart defaults

---

## 🎨 COLOR PALETTE

### **Primary Colors**

```css
/* Light Mode */
--color-primary: #2563eb /* Brand blue */ --color-primary-light: #dbeafe
  /* Light blue background */ --color-primary-dark: #1e40af
  /* Dark blue for hover */ --color-secondary: #64748b /* Slate */
  --color-secondary-light: #f1f5f9 /* Dark Mode */ [data-theme= "dark"]
  {--color-primary: #60a5fa /* Lighter blue for dark */
  --color-primary-light: #1e3a8a --color-primary-dark: #3b82f6};
```

### **Status Colors**

```css
/* Universal - same in light & dark */
--color-success: #10b981 /* Green - success */ --color-warning: #f59e0b
  /* Amber - warning */ --color-danger: #ef4444 /* Red - error/critical */
  --color-info: #06b6d4 /* Cyan - information */ --color-neutral: #6b7280
  /* Gray - neutral */ /* With alpha for backgrounds */
  --color-success-light: rgba(16, 185, 129, 0.1)
  --color-warning-light: rgba(245, 158, 11, 0.1)
  --color-danger-light: rgba(239, 68, 68, 0.1);
```

### **Background & Text**

```css
/* Light Mode */
--bg-light: #ffffff --bg-gray: #f8fafc --bg-secondary: #f1f5f9
  --text-primary: #1e293b --text-secondary: #64748b --border-color: #e2e8f0
  /* Dark Mode */ [data-theme= "dark"] {--bg-light: #1e293b --bg-gray: #0f172a
  --bg-secondary: #334155 --text-primary: #f1f5f9 --text-secondary: #cbd5e1
  --border-color: #475569};
```

---

## 📏 SPACING & LAYOUT

### **Spacing Scale**

```css
--space-xs:
  0.25rem /* 4px */ --space-sm: 0.5rem /* 8px */ --space-md: 1rem /* 16px */
    --space-lg: 1.5rem /* 24px */ --space-xl: 2rem /* 32px */ --space-2xl: 3rem
    /* 48px */ --space-3xl: 4rem /* 64px */ /* Tailwind equivalent */ p-1 = 4px,
  p-2 = 8px, p-4 = 16px, p-6 = 24px, p-8 = 32px;
```

### **Layout Grid**

```css
/* Container max-width */
--container-max: 1280px /* Responsive breakpoints (Tailwind) */ sm: 640px
  /* Tablet */ md: 768px /* Tablet */ lg: 1024px /* Desktop */ xl: 1280px
  /* Desktop XL */ 2xl: 1536px /* Desktop XXL */;
```

---

## 🔤 TYPOGRAPHY

### **Font Family**

```css
/* System fonts (fast loading) */
--font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
--font-mono: "Monaco", "Courier New", monospace;
```

### **Font Sizes**

```css
--text-xs:
  0.75rem /* 12px */ --text-sm: 0.875rem /* 14px */ --text-base: 1rem /* 16px */
    --text-lg: 1.125rem /* 18px */ --text-xl: 1.25rem /* 20px */
    --text-2xl: 1.5rem /* 24px */ --text-3xl: 1.875rem /* 30px */
    --text-4xl: 2.25rem /* 36px */ /* Tailwind equivalent */ text-xs,
  text-sm, text-base, text-lg, text-xl, etc;
```

### **Font Weights**

```css
font-normal: 400     /* Body text */
font-medium: 500     /* Labels, secondary headings */
font-semibold: 600   /* Form labels, table headers */
font-bold: 700       /* Main headings */
```

### **Line Heights**

```css
leading-tight: 1.25   /* Headings */
leading-normal: 1.5   /* Body */
leading-relaxed: 1.625 /* Form fields */
```

---

## 🔘 COMPONENTS

### **Buttons**

```html
<!-- Primary Button -->
<button class="btn btn-primary">Primary Action</button>
<!-- CSS: bg-primary text-white hover:bg-primary-dark -->

<!-- Secondary Button -->
<button class="btn btn-secondary">Secondary Action</button>
<!-- CSS: bg-gray-200 text-gray-900 hover:bg-gray-300 -->

<!-- Danger Button -->
<button class="btn btn-danger">Delete</button>
<!-- CSS: bg-danger text-white hover:bg-red-700 -->

<!-- Disabled -->
<button class="btn btn-primary" disabled>Disabled</button>
<!-- CSS: opacity-50 cursor-not-allowed -->

<!-- Loading State -->
<button class="btn btn-primary" x-bind:disabled="loading">
  <span x-show="!loading">Save</span>
  <span x-show="loading"> <i class="animate-spin">⏳</i> Loading... </span>
</button>
```

### **Forms**

```html
<!-- Form Group -->
<div class="form-group">
  <label class="form-label">Email Address</label>
  <input type="email" class="form-input" placeholder="you@example.com" />
  <p class="form-error" x-show="errors.email">Email tidak valid</p>
</div>

<!-- CSS Classes -->
.form-input: border rounded px-3 py-2 focus:outline-none focus:ring .form-label:
block font-semibold mb-2 text-sm .form-error: text-danger text-sm mt-1
.form-hint: text-secondary text-xs mt-1
```

### **Tables**

```html
<table class="table">
  <thead class="table-header">
    <tr>
      <th>No</th>
      <th>Item</th>
      <th>Qty</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody class="table-body">
    <tr class="table-row hover:bg-gray">
      <td>1</td>
      <td>Item A</td>
      <td>100 kg</td>
      <td><a href="#" class="link">Edit</a></td>
    </tr>
  </tbody>
</table>

/* CSS */ .table: border-collapse w-full .table-header: bg-gray font-semibold
.table-row: border-b hover:bg-gray-light
```

### **Cards**

```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Card Title</h3>
  </div>
  <div class="card-body">Content here...</div>
  <div class="card-footer">
    <button class="btn btn-primary">Action</button>
  </div>
</div>

/* CSS */ .card: border rounded-lg shadow bg-light .card-header: border-b px-6
py-4 .card-body: px-6 py-4 .card-footer: border-t px-6 py-4 bg-gray
```

### **Alerts & Notifications**

```html
<!-- Alert -->
<div class="alert alert-info">
  <i class="lucide-info"></i>
  <span>This is information message</span>
</div>

<!-- Status Badge -->
<span class="badge badge-success">✓ Active</span>
<span class="badge badge-warning">⚠ Pending</span>
<span class="badge badge-danger">✗ Inactive</span>

<!-- Toast (via Izitoast) -->
<script>
  iziToast.success({
    title: "Berhasil",
    message: "Data tersimpan",
    position: "topRight",
  });
</script>
```

### **Modal**

```html
<div class="modal" x-show="showModal" @click.away="showModal = false">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Modal Title</h2>
      <button @click="showModal = false">&times;</button>
    </div>
    <div class="modal-body">Content...</div>
    <div class="modal-footer">
      <button class="btn btn-secondary" @click="showModal = false">
        Close
      </button>
      <button class="btn btn-primary">Save</button>
    </div>
  </div>
</div>

/* CSS */ .modal: fixed inset-0 bg-black/50 flex items-center justify-center
z-50 .modal-content: bg-light rounded-lg shadow-lg max-w-md w-full
```

---

## 🌙 DARK MODE IMPLEMENTATION

### **CSS Variables Toggle**

```html
<body data-theme="light">
  <!-- or -->
  <body data-theme="dark"></body>

  <style>
    :root {
      /* Light mode defaults */
      --bg-light: #ffffff;
      --text-primary: #1e293b;
    }

    [data-theme="dark"] {
      --bg-light: #1e293b;
      --text-primary: #f1f5f9;
    }
  </style>
</body>
```

### **Alpine.js Toggle**

```html
<div
  x-data="{ theme: localStorage.getItem('theme') || 'light' }"
  x-init="$watch('theme', v => {
       localStorage.setItem('theme', v);
       document.documentElement.setAttribute('data-theme', v);
     })"
  x-bind:data-theme="theme"
>
  <!-- Toggle Button in Navbar -->
  <button
    @click="theme = theme === 'light' ? 'dark' : 'light'"
    class="p-2 rounded hover:bg-gray"
  >
    <i x-show="theme === 'light'" class="lucide-moon"></i>
    <i x-show="theme === 'dark'" class="lucide-sun"></i>
  </button>
</div>
```

---

## 📱 RESPONSIVE DESIGN

### **Breakpoints Usage**

```html
<!-- Responsive Classes (Tailwind) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
  <!-- 1 col mobile, 2 col tablet, 4 col desktop -->
</div>

<!-- Responsive Text -->
<h1 class="text-2xl md:text-3xl lg:text-4xl">Heading</h1>

<!-- Responsive Spacing -->
<div class="p-4 md:p-6 lg:p-8">Content with responsive padding</div>

<!-- Hide/Show per Breakpoint -->
<div class="hidden lg:block">Desktop only</div>
<div class="lg:hidden">Mobile/tablet only</div>
```

---

## 🎯 COMPONENT STATES

### **Visual Feedback**

```css
/* Hover State */
.btn:hover {
  background-color: --color-primary-dark;
}

/* Focus State */
.form-input:focus {
  outline: none;
  box-shadow: 0 0 0 3px --color-primary-light;
}

/* Disabled State */
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Loading State */
.btn.loading {
  pointer-events: none;
  opacity: 0.7;
}
.btn.loading::after {
  content: "...";
  animation: dots 1.5s infinite;
}

/* Active State (for nav links) */
.nav-link.active {
  border-bottom: 2px solid --color-primary;
}
```

---

## 📐 ICONOGRAPHY

### **Lucide Icons Usage**

```html
<!-- From CDN -->
<script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>

<!-- Usage -->
<i class="lucide-home"></i>
<!-- Home icon -->
<i class="lucide-edit"></i>
<!-- Edit icon -->
<i class="lucide-trash-2"></i>
<!-- Delete icon -->
<i class="lucide-check-circle"></i>
<!-- Success -->
<i class="lucide-alert-circle"></i>
<!-- Warning -->
<i class="lucide-x-circle"></i>
<!-- Error -->

<!-- With sizing -->
<i class="lucide-home w-5 h-5"></i>
<i class="lucide-home w-6 h-6"></i>
```

### **Icon Placement**

```html
<!-- Button with icon + text -->
<button class="btn btn-primary">
  <i class="lucide-plus mr-2"></i>
  Add New
</button>

<!-- Icon only button -->
<button class="btn btn-secondary p-2">
  <i class="lucide-edit"></i>
</button>

<!-- Left aligned in input -->
<div class="relative">
  <i class="lucide-search absolute left-3 top-3 text-secondary"></i>
  <input type="text" class="form-input pl-10" />
</div>
```

---

## ✨ ANIMATION & TRANSITIONS

```css
/* Smooth transitions */
.btn {
  transition: all 0.2s ease;
}

/* Loading spinner */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 1s linear infinite;
}

/* Fade in */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
.animate-fade-in {
  animation: fadeIn 0.3s ease-in;
}

/* Slide in */
@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
.animate-slide-in {
  animation: slideInRight 0.3s ease-out;
}
```

---

## 📋 CSS FILE STRUCTURE

```
assets/css/
├── variables.css        /* Color, spacing, font system */
├── dark-mode.css        /* Dark mode overrides */
├── components.css       /* Button, form, table, card CSS */
├── layout.css           /* Grid, container, sidebar */
├── animations.css       /* Transitions & keyframes */
├── responsive.css       /* Media queries & breakpoints */
├── custom.css           /* App-specific styles */
└── utilities.css        /* Helper classes */
```

---

**Next**: Baca `11-exports-and-output.md` untuk format laporan →
