# Admin Data Table Component - Quick Reference

## Quick Copy-Paste Examples

### 1️⃣ Basic Table (Minimal Config)

```blade
<x-admin-data-table 
    title="My Table"
    subtitle="Manage your data"
    :data="$data"
    :columns="[
        ['key' => 'name'],
        ['key' => 'email'],
        ['key' => 'status'],
    ]"
/>
```

---

### 2️⃣ Table with Actions

```blade
<x-admin-data-table 
    title="Users"
    :data="$tableData"
    :columns="[
        ['key' => 'user', 'label' => 'User', 'width' => '220px'],
        ['key' => 'email', 'label' => 'Email'],
        ['key' => 'status', 'label' => 'Status'],
    ]"
    :actions="[
        ['icon' => 'fa-edit', 'class' => 'edit', 'tooltip' => 'Edit'],
        ['icon' => 'fa-trash-alt', 'class' => 'delete', 'tooltip' => 'Delete'],
    ]"
/>
```

---

### 3️⃣ Table with Search & Filters

```blade
<x-admin-data-table 
    title="Products"
    :data="$products"
    :columns="[
        ['key' => 'name'],
        ['key' => 'price'],
        ['key' => 'stock'],
        ['key' => 'status'],
    ]"
    :filters="[
        ['label' => 'All', 'value' => 'all', 'active' => true],
        ['label' => 'In Stock', 'value' => 'in_stock', 'icon' => 'fa-check'],
        ['label' => 'Out of Stock', 'value' => 'out_of_stock', 'icon' => 'fa-times'],
    ]"
    searchable="true"
/>
```

---

### 4️⃣ Complete Table Example

```blade
<x-admin-data-table 
    title="User Management"
    subtitle="Manage all system users"
    :columns="[
        ['key' => 'user', 'label' => 'User', 'width' => '220px'],
        ['key' => 'role', 'label' => 'Role', 'width' => '110px'],
        ['key' => 'department', 'label' => 'Department'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'last_login', 'label' => 'Last Login', 'sortable' => true],
    ]"
    :data="$tableData"
    :actions="[
        ['icon' => 'fa-edit', 'class' => 'edit', 'tooltip' => 'Edit', 'onclick' => 'editUser'],
        ['icon' => 'fa-key', 'class' => 'password', 'tooltip' => 'Reset Password'],
        ['icon' => 'fa-toggle-on', 'class' => 'toggle', 'tooltip' => 'Toggle Status'],
        ['icon' => 'fa-trash-alt', 'class' => 'delete', 'tooltip' => 'Delete'],
    ]"
    :filters="[
        ['label' => 'All', 'value' => 'all', 'icon' => 'fa-list', 'active' => true],
        ['label' => 'Active', 'value' => 'active', 'icon' => 'fa-check-circle'],
        ['label' => 'Inactive', 'value' => 'inactive', 'icon' => 'fa-times-circle'],
    ]"
    searchable="true"
    striped="true"
    hover="true"
/>
```

---

## Data Preparation Snippets

### Prepare User Data with Avatar

```php
$tableData = $users->map(function($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => $user->status,
        'user' => view('components.admin-user-cell', ['user' => $user])->render(),
        'role' => view('components.admin-role-badge', ['role' => $user->role])->render(),
        'status' => view('components.admin-status-badge', ['status' => $user->status])->render(),
    ];
})->toArray();
```

### Prepare with Computed Status

```php
$tableData = $books->map(function($book) {
    $statusClass = $book->quantity > 10 ? 'active' : ($book->quantity > 0 ? 'pending' : 'inactive');
    $statusLabel = $book->quantity > 10 ? 'In Stock' : ($book->quantity > 0 ? 'Low Stock' : 'Out of Stock');
    
    return [
        'id' => $book->id,
        'title' => $book->title,
        'quantity' => $book->quantity,
        'status' => view('components.admin-status-badge', [
            'status' => $statusClass,
            'label' => $statusLabel,
        ])->render(),
    ];
})->toArray();
```

---

## Component Snippets

### User with Avatar
```blade
<x-admin-user-cell :user="$user" :showEmail="true" />
```

### Status Badge
```blade
<x-admin-status-badge status="active" />
<x-admin-status-badge status="inactive" />
<x-admin-status-badge status="pending" />
<x-admin-status-badge status="warning" />
```

### Custom Status Badge
```blade
<x-admin-status-badge 
    status="active" 
    label="Published" 
    icon="fa-check" 
/>
```

### Role Badge
```blade
<x-admin-role-badge role="admin" />
<x-admin-role-badge role="staff" />
<x-admin-role-badge role="student" />
<x-admin-role-badge role="user" />
```

### Custom Role Badge
```blade
<x-admin-role-badge 
    role="student" 
    label="CS Student" 
    icon="fa-laptop" 
/>
```

---

## Font Awesome Icons

### Common Icons Used

```
Users & Roles:
fa-users              (multiple users)
fa-user               (single user)
fa-user-check         (active user)
fa-user-times         (inactive user)
fa-user-tie           (staff)
fa-graduation-cap     (student)
fa-shield-alt         (admin)

Actions:
fa-edit               (edit)
fa-eye                (view)
fa-key                (password)
fa-trash-alt          (delete)
fa-download           (export)
fa-toggle-on          (toggle status)

Status:
fa-check-circle       (active)
fa-times-circle       (inactive)
fa-hourglass-half     (pending)
fa-exclamation-circle (warning)

UI:
fa-search             (search)
fa-filter             (filter)
fa-sort               (sort)
fa-list               (list view)
fa-plus-circle        (add/create)
```

---

## JavaScript Action Handlers

### Simple Edit Handler

```javascript
function editUser(rowData) {
    console.log('Edit:', rowData);
    // Your logic here
}
```

### Delete with Confirmation

```javascript
function deleteUser(rowData) {
    if (confirm('Delete this user?')) {
        fetch(`/api/users/${rowData.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => data.success && location.reload())
        .catch(err => alert('Error: ' + err));
    }
}
```

### Toggle Status

```javascript
function toggleStatus(rowData) {
    const newStatus = rowData.status === 'active' ? 'inactive' : 'active';
    
    fetch(`/api/users/${rowData.id}/toggle`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Error: ' + data.message);
    });
}
```

---

## Styling Reference

### Action Button Classes

| Class | Color | Usage |
|-------|-------|-------|
| `edit` | Blue | Edit/modify |
| `view` | Cyan | View details |
| `password` | Green | Reset password |
| `toggle` | Amber | Toggle status |
| `download` | Purple | Export/download |
| `delete` | Red | Delete item |

### Status Badge Colors

| Status | Background | Text Color |
|--------|-----------|-----------|
| `active` | Green | Dark green |
| `inactive` | Red | Dark red |
| `pending` | Yellow | Dark yellow |
| `warning` | Orange | Dark orange |

### Role Badge Colors

| Role | Background | Text Color |
|------|-----------|-----------|
| `admin` | Blue | Dark blue |
| `staff` | Purple | Dark purple |
| `student` | Green | Dark green |
| `user` | Light Blue | Dark blue |

---

## Common Issues & Fixes

### Issue: Icons not showing
**Solution:** Add Font Awesome to your layout
```blade
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
```

### Issue: HTML rendering as text
**Solution:** Use triple braces in data preparation
```php
'status' => view('components.admin-status-badge', ...)->render()
```

### Issue: Actions not responding
**Solution:** Ensure onclick function is defined in script
```blade
<script>
    function editUser(rowData) {
        // Must define function before table renders
    }
</script>
```

### Issue: Table styling not matching
**Solution:** Check body theme class
```blade
<body class="light-theme">  <!-- or dark-theme -->
```

### Issue: Search not working
**Solution:** Set `searchable="true"` prop
```blade
<x-admin-data-table 
    ...
    :searchable="true"
/>
```

---

## Theme Support

### Light Theme
```blade
<body class="light-theme">
    <x-admin-data-table ... />
</body>
```

### Dark Theme
```blade
<body class="dark-theme">
    <x-admin-data-table ... />
</body>
```

CSS automatically adjusts colors based on theme class.

---

## Performance Tips

1. **Use pagination** for large datasets
2. **Limit initial data** to ~50 rows
3. **Use eager loading** with Eloquent (->with())
4. **Cache static data** (roles, departments)
5. **Lazy load images** in avatars

---

## File Locations

- **Main Component:** `resources/views/components/AdminDataTable.blade.php`
- **User Cell:** `resources/views/components/AdminUserCell.blade.php`
- **Status Badge:** `resources/views/components/AdminStatusBadge.blade.php`
- **Role Badge:** `resources/views/components/AdminRoleBadge.blade.php`

---

## Need help?

1. Check `ADMIN_DATA_TABLE_USAGE_GUIDE.md` for detailed documentation
2. Review `EXAMPLE_REFACTORED_USER_MANAGEMENT.blade.php` for full example
3. See `EXAMPLE_CONTROLLER_USAGE.php` for data preparation patterns

Happy coding! 🚀
