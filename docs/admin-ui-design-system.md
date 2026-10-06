# Admin and Staff UI design system

The Admin and Staff layouts load `public/admin/CSS/admin-design-system.css` after existing styles and `public/admin/JS/admin-ui.js` after existing scripts. Both are scoped to `body.admin-portal`; Staff also uses `body.staff-portal` for its dashboard-specific list cards and white popup headers. Student, authentication and public pages retain their existing UI.

## Reuse

Keep existing Blade components and module classes. The shared presentation adapter maps their buttons and badges to the same reusable styles, including content inserted by AJAX. New controls can use the existing `btn btn-primary`, `btn btn-outline`, `btn btn-secondary` or `btn btn-danger` classes.

| Presentation class | Use |
| --- | --- |
| `admin-ui-button admin-ui-primary` | Main action |
| `admin-ui-button admin-ui-secondary` | Blue outline action |
| `admin-ui-button admin-ui-neutral` | Cancel or neutral action |
| `admin-ui-button admin-ui-danger` | Destructive confirmation |
| `admin-ui-action` | Compact action inside a table |
| `admin-ui-icon-button` | 32px icon control; provide a title and accessible name |
| `admin-ui-badge admin-ui-badge-success` | Available, active, paid, approved, good |
| `admin-ui-badge admin-ui-badge-warning` | Pending, issued, due soon |
| `admin-ui-badge admin-ui-badge-danger` | Damaged, lost, overdue, rejected |
| `admin-ui-badge admin-ui-badge-info` | New or processing |

The `--ui-*` variables define the palette. Existing Settings, Fines and Requests variables resolve to these tokens. Dark mode uses the same geometry and a corresponding accessible text palette.

Popup forms are capped at 600px and keep existing smaller module limits. Confirmations are capped at 480px; report previews and image cropping can use 864px. `--ui-modal-gutter` keeps 20px desktop and 12px mobile spacing around panels. All popup backdrops use a subtle 4px blur.

The shared layer tokens place modal backdrops at 4000, toast feedback at 5000 and tooltips at 10050. Admin toasts stay readable and dismissible above popup blur, including validation feedback while a form remains open.

Book Management's existing Details dialog uses `partials.admin-book-details-modal`, `book-details.css` and `book-details.js`. Its 860px panel is bounded by the viewport with fixed header/footer and a scrolling body. The existing `admin.books.show` route supplies read-only details on opening: copy statuses and types are grouped for that book, and distinct physical shelf locations are previewed separately from the catalogue shelf. No per-row detail queries are added. Text is populated through `textContent`; pending requests are cancelled on closing or changing books. Add Physical Copies opens the existing shared form with the selected book through its `physical-book:open` presentation event.

## Interaction and accessibility

The adapter adds names to legacy icon buttons, one shared tooltip for hover and keyboard focus, column scope to table headers, label associations and references to existing inline errors. It keeps focus inside an open modal, restores focus on closing, and forwards Escape to an existing close control when a legacy modal has no Escape handler. Busy dialogs retain their existing cancellation guards.

Existing modules remain responsible for validation, disabling submission controls, request processing and clearing their busy state. The adapter reflects existing busy states with `aria-busy`, a spinner and stable width; it never submits forms or performs AJAX requests. Short controls show a spinner when their loading label cannot fit, while retaining that label in the accessibility tree.

The Staff dashboard's list-based record cards receive the same soft header, alternating rows, separators and complete-row hover treatment as semantic data tables. Staff shortcuts use the shared blue/slate palette rather than introducing per-link accent colors.

Placeholder text uses Secondary Text (`#64748B`) for readability. Keyboard focus has an outline in addition to the blue ring. Reduced motion overrides legacy component transitions and animations through the `admin-accessibility` CSS layer.

## Verification

Use `npm run build`, `php artisan view:cache` and the existing Laravel feature tests. Browser checks should cover desktop and mobile layouts, AJAX filtering and pagination, full-row hover, modal opening/closing, Tab and Shift+Tab, focus restoration, tooltip Escape dismissal, busy-state restoration, theme switching and reduced motion. Wide tables retain their existing horizontal scrolling.
