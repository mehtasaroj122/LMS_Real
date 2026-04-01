# UI Component Showcase - Implementation Prompt for Code Mode

## Quick Overview
Implement a UI component showcase page for the Library Management System that allows testing different color palettes. This page should be accessible at `/admin/ui-showcase` and display all common UI components with 6 different color palette options.

---

## Context About the Project

### Tech Stack
- **Framework**: Laravel 11.x
- **CSS**: Tailwind CSS (CDN in admin layout) + Custom CSS file
- **Icons**: FontAwesome 6.4.0 + Lucide
- **Layout**: `resources/views/Admin/layouts/app.blade.php` (custom admin layout)
- **Theme System**: Light/Dark mode via `body.light-theme` and `body.dark-theme` classes
- **Auth**: Uses `access-admin` Gate for admin routes

### Existing Files to Reference
- Layout: `resources/views/Admin/layouts/app.blade.php`
- Sample admin view: `resources/views/Admin/account-locks/index.blade.php`
- Admin CSS: `public/admin/CSS/admin-appLayout.css`
- Pagination component: `resources/views/shared/admin-table-pagination.blade.php`
- Routes: Look at existing admin routes in `routes/web.php`

### Existing CSS Classes Used
- Cards: `card` class (has light/dark theme support)
- Buttons: `btn`, `btn-primary`, `btn-secondary`, `btn-danger`, `btn-success`, `btn-sm`, etc.
- Forms: `form-control`, `form-label`, `is-invalid`, `is-valid`
- Tables: `table`, `table-hover`, `table-light`
- Text: `text-primary`, `text-secondary`, `text-muted`, `text-danger`
- Alerts: `alert`, `alert-success`, `alert-danger`, `alert-warning`, `alert-info`
- Badges: `badge`, `badge-bg-*` colors

---

## Required Files to Create

### 1. Controller: `app/Http/Controllers/Admin/UiShowcaseController.php`

Create a simple controller with `index()` method that:
- Authorizes with `Gate::authorize('access-admin')`
- Returns `palettes` array with 6 color options
- Returns `sampleData` for table demonstration
- Returns `view('Admin.ui-showcase.index')`

**Palette Data Structure (PHP array):**
```php
[
    'blue' => ['name' => 'Blue', 'primary' => '#2563eb', 'light' => '#3b82f6', 'dark' => '#1d4ed8', 'darkMode' => '#60a5fa'],
    'indigo' => ['name' => 'Indigo', 'primary' => '#4f46e5', 'light' => '#6366f1', 'dark' => '#4338ca', 'darkMode' => '#818cf8'],
    'purple' => ['name' => 'Purple', 'primary' => '#7c3aed', 'light' => '#8b5cf6', 'dark' => '#6d28d9', 'darkMode' => '#a78bfa'],
    'emerald' => ['name' => 'Emerald', 'primary' => '#059669', 'light' => '#10b981', 'dark' => '#047857', 'darkMode' => '#34d399'],
    'rose' => ['name' => 'Rose', 'primary' => '#e11d48', 'light' => '#f43f5e', 'dark' => '#be123c', 'darkMode' => '#fb7185'],
    'orange' => ['name' => 'Orange', 'primary' => '#ea580c', 'light' => '#f97316', 'dark' => '#c2410c', 'darkMode' => '#fb923c'],
]
```

### 2. Routes: Add to `routes/web.php`

Under the admin route group (around line 140-150), add:
```php
Route::get('/ui-showcase', [\App\Http\Controllers\Admin\UiShowcaseController::class, 'index'])->name('ui-showcase.index');
```

### 3. View: `resources/views/Admin/ui-showcase/index.blade.php`

Create a comprehensive Blade template extending `Admin.layouts.app` with:

#### Page Structure:
```blade
@extends('Admin.layouts.app')
@section('title', 'UI Component Showcase')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/CSS/ui-showcase.css') }}">
@endpush

@section('content')
    {{-- Page content here --}}
@endsection

@push('scripts')
    <script>
        {{-- JavaScript for palette switcher and modals --}}
    </script>
@endpush
```

#### Component Sections (in order):

**Section 1: Page Header**
- Large title: "UI Component Showcase"
- Subtitle: "Preview and test color palettes"
- Quick theme toggle button (reuse existing theme toggle if possible)

**Section 2: Color Palette Switcher (Sticky/Fixed)**
- 6 color buttons in a row
- Each button shows the palette name and primary color swatch
- Clicking applies `.palette-{color}` class to container
- Selected palette stored in localStorage
- Smooth CSS transitions when switching

**Section 3: Buttons Showcase**
Display all button variants in rows:
- Primary Button (main color)
- Secondary Button (outline style)
- Success Button (green `#16a34a`)
- Danger Button (red `#dc2626`)
- Warning Button (amber `#d97706`)
- Info Button (cyan `#0891b2`)
- Ghost Button (transparent + hover background)
- Icon Buttons (with FontAwesome icons: `<i class="fas fa-edit"></i>`)
- Button sizes: `.btn-sm`, `.btn`, `.btn-lg`

**Section 4: Cards Showcase**
- Basic card with title and content
- Card with header and footer
- Stats card (icon + large number + label)
- Alert/info card inside card
- Show 2 cards side-by-side in light and dark mode

**Section 5: Tables Showcase**
Create a sample table with:
- 5-6 sample rows (books or users data)
- Columns: ID, Name/Title, Status, Actions
- Status badges in rows (Active/Inactive/Pending)
- Action buttons (Edit icon, Delete icon)
- Column headers with sorting indicators
- Empty row at bottom showing "No more data"
- Responsive wrapper with overflow-x-auto

**Section 6: Forms Showcase**
Create a form grid showing:
- Text input with label
- Email input
- Password input
- Select dropdown with 3-4 options
- Textarea
- Checkbox (single)
- Checkbox group (3 options)
- Radio group (3 options - horizontal)
- File upload input
- Date input
- Toggle switch (custom styled)
- Valid input example (green border)
- Invalid input example (red border + error message)

**Section 7: Modals Showcase**
Include 3 modal containers (hidden by default):
- **Add Item Modal**: Medium size, form inside, save/cancel buttons
- **Edit Item Modal**: Same as add but pre-filled
- **Delete Confirmation Modal**: Small, warning icon, confirm/cancel

Each modal should have:
- Backdrop overlay
- Close (X) button in header
- Proper z-index layering
- Smooth open/close animations

JavaScript functions:
- `openModal(id)`
- `closeModal(id)`
- ESC key to close
- Click backdrop to close

**Section 8: Alerts Showcase**
Show 4 alert boxes (dismissible):
- Alert Success (green background)
- Alert Error (red background)
- Alert Warning (yellow/amber background)
- Alert Info (blue background)

Add close button on each that fades out the alert.

**Section 9: Badges Showcase**
Display badge variants in a flex row:
- Default badge (gray)
- Primary badge (current palette color)
- Success badge (green)
- Danger badge (red)
- Warning badge (amber)
- Info badge (blue)
- Also show "pill" style (rounded-full)

**Section 10: Search & Pagination**
- Search input with search icon
- Search with filter dropdown
- Include the existing pagination component:
```blade
@include('shared.admin-table-pagination', ['paginator' => $paginator])
```

**Section 11: Navigation Preview**
- Show 3 sidebar item examples (inactive, hover, active)
- Labels for each state

### 4. CSS: `public/admin/CSS/ui-showcase.css`

Create comprehensive CSS with:

#### CSS Variables for Palettes
```css
/* Default (Blue) palette variables */
:root {
  --primary-600: #2563eb;
  --primary-500: #3b82f6;
  --primary-700: #1d4ed8;
  --primary-400-dark: #60a5fa;
}

/* Palette classes that override variables */
.palette-blue { /* default */ }
.palette-indigo {
  --primary-600: #4f46e5;
  --primary-500: #6366f1;
  --primary-700: #4338ca;
  --primary-400-dark: #818cf8;
}
/* ... repeat for purple, emerald, rose, orange */
```

#### Component Styles
```css
/* Palette-aware button styles */
.btn-palette-primary {
  background-color: var(--primary-600);
  color: white;
  border: 1px solid var(--primary-600);
}
.btn-palette-primary:hover {
  background-color: var(--primary-700);
}
body.dark-theme .btn-palette-primary {
  background-color: var(--primary-500);
}

/* Palette-aware badges */
.badge-palette {
  background-color: var(--primary-600);
  color: white;
}
body.dark-theme .badge-palette {
  background-color: var(--primary-500);
}

/* Palette-aware text */
.text-palette {
  color: var(--primary-600);
}
body.dark-theme .text-palette {
  color: var(--primary-400-dark);
}

/* Palette-aware borders */
.border-palette {
  border-color: var(--primary-600);
}
```

#### Modal Styles
```css
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: none;
}
.modal-overlay.active { display: flex; }

.modal-container {
  background: white;
  border-radius: 0.75rem;
  max-width: 500px;
  width: 90%;
  margin: auto;
  max-height: 90vh;
  overflow: auto;
}
body.dark-theme .modal-container {
  background: #1e293b;
}
```

#### Palette Switcher Styles
```css
.palette-switcher {
  position: sticky;
  top: 0;
  background: inherit;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  z-index: 10;
  display: flex;
  gap: 0.5rem;
}
.palette-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
}
.palette-btn.active {
  border-color: currentColor;
  box-shadow: 0 0 0 3px rgba(0,0,0,0.1);
}
.palette-swatch {
  width: 20px;
  height: 20px;
  border-radius: 50%;
}
```

#### Section Styles
```css
.showcase-section {
  margin-bottom: 2rem;
  padding: 1.5rem;
  border-radius: 0.75rem;
  border: 1px solid #e5e7eb;
}
.showcase-section-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--primary-600);
}
/* Dark mode support */
body.dark-theme .showcase-section {
  border-color: #334155;
}
```

#### Smooth Transitions
```css
/* Smooth color transitions when switching palettes */
.showcase-container {
  transition: background-color 0.3s ease, color 0.3s ease;
}
.btn-palette-primary,
.badge-palette,
.text-palette,
.border-palette {
  transition: all 0.3s ease;
}
```

---

## JavaScript Requirements

In the view file's `@push('scripts')`:

```javascript
// Palette switching
document.querySelectorAll('.palette-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const palette = e.currentTarget.dataset.palette;
        document.querySelector('.showcase-container').className = `showcase-container palette-${palette}`;
        localStorage.setItem('selected-palette', palette);
        
        // Update active state
        document.querySelectorAll('.palette-btn').forEach(b => b.classList.remove('active'));
        e.currentTarget.classList.add('active');
    });
});

// Load saved palette on page load
const savedPalette = localStorage.getItem('selected-palette') || 'blue';
document.querySelector(`.palette-btn[data-palette="${savedPalette}"]`).click();

// Modal functions
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

// Close modals on ESC key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
        document.body.style.overflow = '';
    }
});

// Close modals on backdrop click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// Alert dismiss
function dismissAlert(btn) {
    btn.closest('.alert').style.opacity = '0';
    setTimeout(() => btn.closest('.alert').remove(), 300);
}
```

---

## Sample Data for Tables

Use this data for the table demo:

```php
$sampleBooks = [
    ['id' => 1, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'status' => 'available', 'category' => 'Fiction'],
    ['id' => 2, 'title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'status' => 'issued', 'category' => 'Fiction'],
    ['id' => 3, 'title' => '1984', 'author' => 'George Orwell', 'status' => 'reserved', 'category' => 'Science Fiction'],
    ['id' => 4, 'title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'status' => 'available', 'category' => 'Romance'],
    ['id' => 5, 'title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'status' => 'issued', 'category' => 'Fiction'],
];
```

Status badges:
- `available` → green badge
- `issued` → blue badge
- `reserved` → amber/orange badge

---

## Responsive Breakpoints

Ensure the page is responsive:
- Mobile: < 640px (single column, stacked layouts)
- Tablet: 640px - 1024px (2 columns for cards, tables scroll)
- Desktop: > 1024px (full layout)

Use Tailwind classes or custom CSS media queries:
```css
@media (max-width: 640px) {
    .palette-switcher { flex-wrap: wrap; }
    .showcase-section { padding: 1rem; }
}
```

---

## Testing Checklist (for verification)

After implementation, verify:
- [ ] `/admin/ui-showcase` loads without errors
- [ ] All 6 palette buttons work and change colors
- [ ] Color selection persists after page refresh
- [ ] Light/dark mode toggle works
- [ ] All modals open and close properly
- [ ] Tables are responsive (horizontal scroll on mobile)
- [ ] Forms show proper validation states
- [ ] Buttons have hover effects
- [ ] Alerts can be dismissed
- [ ] Page works on mobile screen size

---

## Integration Notes

The chosen palette can be applied to the whole admin panel by:
1. Taking the selected palette's CSS variables
2. Adding them to `public/admin/CSS/admin-appLayout.css`
3. Or creating a new theme CSS file

The plan document at `plans/ui-component-showcase-plan.md` has detailed integration instructions.

---

## START IMPLEMENTATION

Switch to Code mode and implement:
1. `app/Http/Controllers/Admin/UiShowcaseController.php`
2. Add route to `routes/web.php`
3. `resources/views/Admin/ui-showcase/index.blade.php`
4. `public/admin/CSS/ui-showcase.css`

All files should follow the existing Laravel and CSS conventions in the project.
