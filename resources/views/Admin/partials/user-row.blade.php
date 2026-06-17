@php
    $profilePhotoUrl = filled($user->profile_photo)
        ? \App\Support\ProfilePhoto::resolveUrl($user->profile_photo)
        : null;

    $departmentName = data_get($user, 'student.department.name')
        ?? data_get($user, 'staff.department.name')
        ?? '';

    $identityValue = $user->role === 'staff'
        ? data_get($user, 'staff.staff_id', '')
        : data_get($user, 'student.student_id', data_get($user, 'student.roll_no', ''));

    $hasPassword = $user->hasCompletedRegistration();
    $registrationPending = $user->requiresSelfRegistration();

    $lastLoginLabel = $user->last_login_at
        ? \Carbon\Carbon::parse($user->last_login_at)->format('d-M-Y')
        : 'Never';
@endphp

<tr data-user-id="{{ $user->id }}"
    data-status="{{ $user->status }}"
    data-role="{{ $user->role }}"
    data-name="{{ $user->name }}"
    data-email="{{ $user->email }}"
    data-phone="{{ $user->phone ?? '' }}"
    data-gender="{{ $user->gender ?? '' }}"
    data-department-name="{{ $departmentName }}"
    data-student-roll-no="{{ data_get($user, 'student.roll_no', '') }}"
    data-student-id="{{ data_get($user, 'student.student_id', data_get($user, 'student.roll_no', '')) }}"
    data-staff-id="{{ data_get($user, 'staff.staff_id', '') }}"
    data-staff-designation="{{ data_get($user, 'staff.designation', '') }}"
    data-has-password="{{ $hasPassword ? '1' : '0' }}"
    data-profile-photo-url="{{ $profilePhotoUrl ?? '' }}"
    data-last-login="{{ $lastLoginLabel }}"
    data-is-current-user="{{ $user->id === auth()->id() ? '1' : '0' }}">
    <td>
        <div style="display: flex; align-items: center; gap: 12px;">
            @if ($profilePhotoUrl)
                <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            @else
                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #3b82f6; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <div style="font-weight: 600; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;" class="user-name">
                    <span>{{ $user->name }}</span>
                    @if ($user->id === auth()->id())
                        <small class="text-muted" style="font-size: 11px;">(you)</small>
                    @endif
                </div>
                <div style="font-size: 12px; margin-top: 2px;" class="text-muted">{{ $user->email }}</div>
            </div>
        </div>
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
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <span style="font-weight: 600;">
                {{ $identityValue !== '' ? $identityValue : 'Not assigned' }}
            </span>
            <span class="text-muted">
                {{ $departmentName !== '' ? $departmentName : 'No department assigned' }}
            </span>
        </div>
    </td>
    <td>
        <span class="status-badge {{ $user->status === 'active' ? 'status-active' : 'status-inactive' }}">
            <i class="fas {{ $user->status === 'active' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
            {{ ucfirst($user->status) }}
        </span>
        @if ($registrationPending)
            <div class="text-muted" style="font-size: 11px; margin-top: 4px;">Registration pending</div>
        @endif
    </td>
    <td>{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d-M-Y') : 'Never' }}</td>
    <td>
        <div class="action-buttons">
            <button type="button" class="action-btn view" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="action-btn edit" title="Edit User">
                <i class="fas fa-edit"></i>
            </button>
            <button
                type="button"
                class="action-btn password"
                title="{{ $registrationPending ? 'Complete registration first' : 'Reset Password' }}"
                @disabled($registrationPending)
            >
                <i class="fas fa-key"></i>
            </button>
            <button type="button" class="action-btn toggle" title="Toggle Status" data-status="{{ $user->status }}">
                <i class="fas {{ $user->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
            </button>
            <button type="button" class="action-btn delete" title="Delete User">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </td>
</tr>
