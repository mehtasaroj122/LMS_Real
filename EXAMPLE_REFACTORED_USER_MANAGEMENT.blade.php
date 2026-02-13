{{--
    EXAMPLE: Refactored UserManagement using AdminDataTable Component
    
    This shows how to convert from inline table HTML to using the reusable AdminDataTable component.
    You can use this as a reference to update your UserManagement.blade.php
    
    Save this file or reference it for implementation ideas.
--}}

@extends('Admin.layouts.app')

@section('title', 'User Management')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Original styles from UserManagement can be placed here or kept in separate file */
        .user-management {
            padding: 5px;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        @media (max-width: 1024px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        body.light-theme .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        body.dark-theme .stat-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .stat-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-total .stat-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .stat-active .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .stat-inactive .stat-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .stat-roles .stat-icon {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 500;
            opacity: 0.8;
        }

        .roles-list {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid;
        }

        body.light-theme .roles-list {
            border-top-color: #e2e8f0;
        }

        body.dark-theme .roles-list {
            border-top-color: #334155;
        }

        .role-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 13px;
        }

        .role-count {
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="user-management">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">User Management</h1>
            <p class="page-description">Manage all users, their roles, and permissions</p>
        </div>

        <!-- Stats Row - 4 boxes in single row -->
        <div class="stats-row" id="statsContainer">
            <!-- Total Users -->
            <div class="stat-card stat-total">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="totalUsersCount">{{ $totalUsers }}</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="stat-card stat-active">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="activeUsersCount">{{ $activeUsers }}</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>
            </div>

            <!-- Inactive Users -->
            <div class="stat-card stat-inactive">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="inactiveUsersCount">{{ $inactiveUsers }}</div>
                        <div class="stat-label">Inactive Users</div>
                    </div>
                </div>
            </div>

            <!-- By Role -->
            <div class="stat-card stat-roles">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">By Role</div>
                        <div class="stat-label">Role Distribution</div>
                    </div>
                </div>

                <div class="roles-list" id="rolesListContainer">
                    <div class="role-item">
                        <span>Admins</span>
                        <span class="role-count" id="roleCountAdmin">{{ $roleCounts['admin'] ?? 0 }}</span>
                    </div>
                    <div class="role-item">
                        <span>Staff</span>
                        <span class="role-count" id="roleCountStaff">{{ $roleCounts['staff'] ?? 0 }}</span>
                    </div>
                    <div class="role-item">
                        <span>Students</span>
                        <span class="role-count" id="roleCountStudent">{{ $roleCounts['student'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================
             USING THE NEW REUSABLE DATA TABLE COMPONENT
             ======================================== -->

        <x-admin-data-table 
            title="All Users"
            subtitle="View and manage all system users"
            :columns="[
                ['key' => 'user', 'label' => 'User', 'width' => '220px'],
                ['key' => 'role', 'label' => 'Role', 'width' => '110px'],
                ['key' => 'department', 'label' => 'Department', 'width' => '130px'],
                ['key' => 'status', 'label' => 'Status', 'width' => '100px'],
                ['key' => 'last_login', 'label' => 'Last Login', 'sortable' => true],
            ]"
            :data="$tableData"
            :actions="[
                ['icon' => 'fa-edit', 'class' => 'edit', 'tooltip' => 'Edit User', 'onclick' => 'editUser'],
                ['icon' => 'fa-key', 'class' => 'password', 'tooltip' => 'Reset Password', 'onclick' => 'resetPassword'],
                ['icon' => 'fa-toggle-on', 'class' => 'toggle', 'tooltip' => 'Toggle Status', 'onclick' => 'toggleStatus'],
                ['icon' => 'fa-trash-alt', 'class' => 'delete', 'tooltip' => 'Delete User', 'onclick' => 'deleteUser'],
            ]"
            :filters="[
                ['label' => 'All', 'value' => 'all', 'icon' => 'fa-list', 'active' => true],
                ['label' => 'Active', 'value' => 'active', 'icon' => 'fa-check-circle'],
                ['label' => 'Inactive', 'value' => 'inactive', 'icon' => 'fa-times-circle'],
            ]"
            :searchable="true"
            emptyMessage="No users found. Create one to get started."
        />

        <!-- ======================================== -->
    </div>

    <!-- Modal for Add/Edit User (keep from original) -->
    <div id="addUserModal" class="modal-overlay">
        <!-- Modal content here -->
    </div>

@endsection

@push('scripts')
    <script>
        // ========================================
        // ACTION HANDLERS
        // ========================================

        /**
         * Edit user handler
         * @param {Object} rowData - Row data from table
         */
        function editUser(rowData) {
            console.log('Edit user:', rowData);
            // Open edit modal
            // document.getElementById('editUserModal').style.display = 'flex';
            // Populate form with user data
            // Make API call to get full user details
        }

        /**
         * Reset password handler
         * @param {Object} rowData - Row data from table
         */
        function resetPassword(rowData) {
            console.log('Reset password for user:', rowData);
            // Open reset password modal
            // Make API call to send reset email
        }

        /**
         * Toggle user status handler
         * @param {Object} rowData - Row data from table
         */
        function toggleStatus(rowData) {
            const newStatus = rowData.status === 'active' ? 'inactive' : 'active';
            
            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this user?`)) {
                fetch(`/api/users/${rowData.id}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update table or reload
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }

        /**
         * Delete user handler
         * @param {Object} rowData - Row data from table
         */
        function deleteUser(rowData) {
            if (confirm(`Are you sure you want to delete ${rowData.name}? This action cannot be undone.`)) {
                fetch(`/api/users/${rowData.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }
    </script>
@endpush
