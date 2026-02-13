<tr data-user-id="{{ $user->id }}" data-status="{{ $user->status }}" data-role="{{ $user->role }}">
    <td>
        <div style="font-weight: 600;" class="user-name">{{ $user->name }}</div>
        <div style="font-size: 12px; margin-top: 2px;" class="text-muted">{{ $user->email }}</div>
    </td>
    <td>
        @if ($user->role === 'admin')
            <span
                style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; background-color: #dbeafe; color: #1e40af; font-size: 12px; font-weight: 600;">
                <i class="fas fa-shield-alt"></i>
                Admin
            </span>
        @elseif($user->role === 'staff')
            <span
                style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; background-color: #f3e8ff; color: #7c3aed; font-size: 12px; font-weight: 600;">
                <i class="fas fa-user-tie"></i>
                Staff
            </span>
        @else
            <span
                style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; background-color: #dcfce7; color: #166534; font-size: 12px; font-weight: 600;">
                <i class="fas fa-graduation-cap"></i>
                Student
            </span>
        @endif
    </td>
    <td>
        @if ($user->student && $user->student->department && $user->student->department->name)
            {{ $user->student->department->name }}
        @elseif($user->staff && $user->staff->department && $user->staff->department->name)
            {{ $user->staff->department->name }}
        @else
            -
        @endif
    </td>
    <td>
        <span class="status-badge {{ $user->status === 'active' ? 'status-active' : 'status-inactive' }}">
            <i class="fas {{ $user->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
            {{ ucfirst($user->status) }}
        </span>
    </td>
    <td>{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d-M-Y') : 'Never' }}</td>
    <td>
        <div class="action-buttons">
            <button class="action-btn edit" title="Edit User">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn password" title="Reset Password">
                <i class="fas fa-key"></i>
            </button>
            <button class="action-btn toggle" title="Toggle Status" data-status="{{ $user->status }}">
                <i class="fas {{ $user->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
            </button>
            <button class="action-btn delete" title="Delete User">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
