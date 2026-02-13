# Admin Data Table Component - Complete Usage Guide

## Overview

The Admin Data Table Component is a highly reusable, customizable table component designed for your admin module. It matches all styling from your existing User Management page including fonts, colors, icons (Font Awesome), and both light/dark theme support.

## Components Created

### 1. **AdminDataTable.blade.php** - Main Table Component
The primary reusable table component with full configuration support.

### 2. **AdminUserCell.blade.php** - User Display Component
Displays a user with avatar, name, and email in a styled cell.

### 3. **AdminStatusBadge.blade.php** - Status Indicator Component
Shows status with icons (active, inactive, pending, warning).

### 4. **AdminRoleBadge.blade.php** - Role/Type Indicator Component
Displays role/type with appropriate icon and styling (admin, staff, student, user).

---

## Key Features

✅ **Fully Responsive** - Works on desktop, tablet, and mobile
✅ **Light & Dark Theme Support** - Automatic theme detection
✅ **Font Awesome Icons** - Pre-configured with Font Awesome 6.4.0+
✅ **Search Functionality** - Built-in search with instant filtering
✅ **Sortable Columns** - Click headers to sort (extensible)
✅ **Status & Role Badges** - Pre-styled with gradients
✅ **User Avatars** - Profile photos with fallback initials
✅ **Action Buttons** - Customizable with 7 different styles
✅ **Hover Effects** - Smooth animations and transitions
✅ **Empty States** - Custom messages when no data
✅ **Mobile Optimized** - Adjusted sizing and spacing

---

## Font & Styling Standards

- **Header Font Size**: 11px (uppercase, letter-spacing: 0.5px)
- **Cell Font Size**: 13px
- **Label Font Weight**: 600
- **Button Font Size**: 13px
- **Colors**: 
  - Light theme text: #0f172a (almost black)
  - Dark theme text: #f1f5f9 (almost white)
  - Muted text: #64748b (light) / #94a3b8 (dark)

---

## Basic Usage

### Simple Table

```blade
<x-admin-data-table 
    title="Products"
    subtitle="Manage your product catalog"
    :columns="[
        ['key' => 'name', 'label' => 'Product Name', 'width' => '200px'],
        ['key' => 'sku', 'label' => 'SKU'],
        ['key' => 'price', 'label' => 'Price'],
        ['key' => 'quantity', 'label' => 'Quantity'],
        ['key' => 'status', 'label' => 'Status'],
    ]"
    :data="$products"
    :actions="[
        ['icon' => 'fa-edit', 'class' => 'edit', 'tooltip' => 'Edit', 'onclick' => 'editProduct'],
        ['icon' => 'fa-trash-alt', 'class' => 'delete', 'tooltip' => 'Delete', 'onclick' => 'deleteProduct'],
    ]"
/>
```

---

## Props Reference

### Main Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | string | 'Data Table' | Main title of the table section |
| `subtitle` | string | 'Manage your data' | Subtitle/description text |
| `columns` | array | [] | Column definitions (see below) |
| `data` | array | [] | Table row data |
| `actions` | array | [] | Action button definitions |
| `searchable` | boolean | true | Show search box |
| `paginated` | boolean | false | Show pagination |
| `striped` | boolean | true | Alternate row background colors |
| `hover` | boolean | true | Highlight rows on hover |
| `emptyMessage` | string | 'No data available' | Message when table is empty |
| `rowClass` | string | '' | Custom CSS class for rows |
| `responsive` | boolean | true | Enable responsive design |
| `filters` | array | [] | Filter button definitions |
| `showHeader` | boolean | true | Show title/subtitle |

### Column Definition

```php
[
    'key' => 'id',              // Required: Key name from data array
    'label' => 'ID',            // Optional: Column header label
    'width' => '100px',         // Optional: Fixed column width
    'sortable' => false,        // Optional: Make column sortable
]
```

### Action Definition

```php
[
    'icon' => 'fa-edit',           // Required: Font Awesome icon
    'class' => 'edit',             // Optional: CSS class for styling (edit, view, password, toggle, download, delete)
    'label' => 'Edit',             // Optional: Button tooltip text
    'tooltip' => 'Edit Record',    // Optional: Hover tooltip
    'onclick' => 'editHandler',    // Optional: JavaScript function name
    'disabled' => false,           // Optional: Disable button
]
```

### Filter Definition

```php
[
    'label' => 'Active',           // Required: Filter label
    'value' => 'active',           // Required: Filter value
    'icon' => 'fa-check-circle',   // Optional: Font Awesome icon
    'active' => false,             // Optional: Mark as active
]
```

---

## Advanced Usage Examples

### Example 1: User Management Table

```blade
<x-admin-data-table 
    title="User Management"
    subtitle="Manage all system users"
    :columns="[
        ['key' => 'user', 'label' => 'User', 'width' => '200px'],
        ['key' => 'role', 'label' => 'Role', 'width' => '120px'],
        ['key' => 'department', 'label' => 'Department'],
        ['key' => 'status', 'label' => 'Status', 'width' => '100px'],
        ['key' => 'last_login', 'label' => 'Last Login', 'sortable' => true],
    ]"
    :data="$tableData"
    :actions="[
        ['icon' => 'fa-edit', 'class' => 'edit', 'tooltip' => 'Edit User', 'onclick' => 'editUser'],
        ['icon' => 'fa-key', 'class' => 'password', 'tooltip' => 'Reset Password', 'onclick' => 'resetPassword'],
        ['icon' => 'fa-toggle-on', 'class' => 'toggle', 'tooltip' => 'Toggle Status', 'onclick' => 'toggleStatus'],
        ['icon' => 'fa-trash-alt', 'class' => 'delete', 'tooltip' => 'Delete', 'onclick' => 'deleteUser'],
    ]"
    :filters="[
        ['label' => 'All', 'value' => 'all', 'icon' => 'fa-list', 'active' => true],
        ['label' => 'Active', 'value' => 'active', 'icon' => 'fa-check-circle'],
        ['label' => 'Inactive', 'value' => 'inactive', 'icon' => 'fa-times-circle'],
    ]"
/>
```

In your controller, prepare the data:

```php
public function index()
{
    $users = User::all();
    
    $tableData = $users->map(function($user) {
        return [
            'id' => $user->id,
            'user' => view('components.admin-user-cell', ['user' => $user])->render(),
            'role' => view('components.admin-role-badge', ['role' => $user->role])->render(),
            'department' => $user->department->name ?? '-',
            'status' => view('components.admin-status-badge', ['status' => $user->status])->render(),
            'last_login' => $user->last_login_at?->format('d-M-Y') ?? 'Never',
        ];
    })->toArray();

    return view('admin.users.index', compact('tableData'));
}
```

### Example 2: Books/Inventory Table

```blade
<x-admin-data-table 
    title="Book Inventory"
    subtitle="Manage library books and stock"
    :columns="[
        ['key' => 'title', 'label' => 'Title', 'width' => '250px'],
        ['key' => 'isbn', 'label' => 'ISBN'],
        ['key' => 'author', 'label' => 'Author'],
        ['key' => 'quantity', 'label' => 'Quantity', 'width' => '80px'],
        ['key' => 'status', 'label' => 'Status'],
    ]"
    :data="$books"
    :actions="[
        ['icon' => 'fa-eye', 'class' => 'view', 'tooltip' => 'View Details'],
        ['icon' => 'fa-edit', 'class' => 'edit', 'tooltip' => 'Edit Book'],
        ['icon' => 'fa-download', 'class' => 'download', 'tooltip' => 'Export'],
        ['icon' => 'fa-trash-alt', 'class' => 'delete', 'tooltip' => 'Delete'],
    ]"
    :searchable="true"
    emptyMessage="No books found in inventory"
/>
```

### Example 3: Student Enrollment Table

```blade
<x-admin-data-table 
    title="Student Enrollment"
    subtitle="View and manage student enrollments"
    :columns="[
        ['key' => 'enrollment_id', 'label' => 'Enrollment ID', 'width' => '130px'],
        ['key' => 'student', 'label' => 'Student Name', 'width' => '200px'],
        ['key' => 'course', 'label' => 'Course'],
        ['key' => 'semester', 'label' => 'Semester', 'width' => '80px'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'registration_date', 'label' => 'Registered', 'sortable' => true],
    ]"
    :data="$enrollments"
    :actions="[
        ['icon' => 'fa-file-alt', 'class' => 'view', 'tooltip' => 'View Details'],
        ['icon' => 'fa-download', 'class' => 'download', 'tooltip' => 'Export Transcript'],
        ['icon' => 'fa-trash-alt', 'class' => 'delete', 'tooltip' => 'Remove Enrollment'],
    ]"
/>
```

---

## Using Helper Components

### AdminUserCell Component

Display a user with avatar, name, and email:

```blade
<!-- In table data preparation -->
'user' => view('components.admin-user-cell', [
    'user' => $user,
    'showEmail' => true  // Show/hide email
])->render()

<!-- Or in Blade directly -->
<x-admin-user-cell :user="$user" :showEmail="true" />
```

### AdminStatusBadge Component

Display status with icon:

```blade
<!-- Predefined statuses -->
<x-admin-status-badge status="active" />
<x-admin-status-badge status="inactive" />
<x-admin-status-badge status="pending" />
<x-admin-status-badge status="warning" />

<!-- Custom label and icon -->
<x-admin-status-badge 
    status="active" 
    label="Published" 
    icon="fa-check" 
/>
```

### AdminRoleBadge Component

Display role/type with icon:

```blade
<!-- Predefined roles -->
<x-admin-role-badge role="admin" />
<x-admin-role-badge role="staff" />
<x-admin-role-badge role="student" />
<x-admin-role-badge role="user" />

<!-- Custom label and icon -->
<x-admin-role-badge 
    role="student" 
    label="CS Student" 
    icon="fa-laptop" 
/>
```

---

## Action Button Classes & Styles

Available action button classes (automatically styled):

| Class | Color | Icon Example | Use Case |
|-------|-------|--------------|----------|
| `edit` | Blue (#3b82f6) | fa-edit | Edit/modify record |
| `view` | Cyan (#06b6d4) | fa-eye | View details |
| `password` | Green (#10b981) | fa-key | Reset password |
| `toggle` | Amber (#f59e0b) | fa-toggle-on | Toggle status |
| `download` | Purple (#8b5cf6) | fa-download | Export/download |
| `delete` | Red (#ef4444) | fa-trash-alt | Delete record |

---

## Status & Role Badge Colors

### Status Badges

- **Active**: Green gradient (#dcfce7 → #bbf7d0)
- **Inactive**: Red gradient (#fee2e2 → #fecaca)
- **Pending**: Yellow gradient (#fef3c7 → #fde68a)
- **Warning**: Orange gradient (#fed7aa → #fdba74)

### Role Badges

- **Admin**: Blue (#dbeafe)
- **Staff**: Purple (#f3e8ff)
- **Student**: Green (#dcfce7)
- **User**: Light Blue (#f0f9ff)

---

## JavaScript Integration

### Custom Click Handlers

```javascript
function editUser(rowData) {
    console.log('Editing user:', rowData);
    // Open modal, make API call, etc.
}

function deleteUser(rowData) {
    if (confirm('Are you sure?')) {
        // Make delete API call
        fetch(`/api/users/${rowData.id}`, { method: 'DELETE' })
            .then(response => response.json())
            .then(data => {
                // Refresh table
                location.reload();
            });
    }
}

function toggleStatus(rowData) {
    const newStatus = rowData.status === 'active' ? 'inactive' : 'active';
    fetch(`/api/users/${rowData.id}/status`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: newStatus })
    }).then(() => location.reload());
}
```

---

## Theme Support

The components automatically detect and support light/dark themes:

```blade
<!-- Light theme (default) -->
<body class="light-theme">
    <x-admin-data-table ... />
</body>

<!-- Dark theme -->
<body class="dark-theme">
    <x-admin-data-table ... />
</body>
```

The CSS uses:
```css
body.light-theme .class { /* Light styles */ }
body.dark-theme .class { /* Dark styles */ }
```

---

## Mobile Responsiveness

Automatic responsive behavior at breakpoints:

- **Desktop**: Full table with all features
- **Tablet (≤768px)**: Adjusted padding, smaller buttons
- **Mobile (≤640px)**: Minimal padding, stacked action buttons, optimized fonts

---

## Font Awesome Icons

This component uses **Font Awesome 6.4.0+**. Make sure it's included in your layout:

```blade
<!-- In your layout header -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

Common icons used:
- Users: `fa-users`
- Edit: `fa-edit`
- Delete: `fa-trash-alt`
- Settings: `fa-cog`
- Filter: `fa-filter`
- Search: `fa-search`
- Status: `fa-check-circle`, `fa-times-circle`
- Roles: `fa-shield-alt`, `fa-user-tie`, `fa-graduation-cap`

---

## Troubleshooting

### Icons not showing?
- Ensure Font Awesome CSS is included
- Check icon name is correct (use `fa-icon-name` format)

### Dark theme not applying?
- Add `class="dark-theme"` to body tag
- Check if theme toggle script is running

### Search not working?
- Ensure `searchable="true"` prop is set
- Check browser console for JavaScript errors

### Styled props still showing?
- Use `{!! $content !!}` instead of `{{ $content }}` in data array
- This renders HTML instead of escaping it

---

## Performance Tips

1. **Limit data size**: For large datasets, implement server-side pagination
2. **Lazy load**: Consider lazy loading images in user avatars
3. **Debounce search**: Wrap search in debounce for large tables
4. **Optimize queries**: Use eager loading with Eloquent

---

## Future Enhancements

- Checkbox selection for bulk actions
- Column resizing
- Export to CSV/Excel
- Advanced filtering with date ranges
- Multi-column sorting
- Row grouping
- Inline editing

---

## Support & Customization

For custom requirements, modify the component files:
- Main styles in `AdminDataTable.blade.php` `<style>` section
- Helper components in `AdminUserCell.blade.php`, `AdminStatusBadge.blade.php`, `AdminRoleBadge.blade.php`
- JavaScript logic in the `<script>` section

All components follow your existing design system and are fully customizable!
