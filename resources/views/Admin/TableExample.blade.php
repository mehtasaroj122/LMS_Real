<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Table & Pagination (Dual Theme)</title>
    <!-- Font Awesome 6 (from original) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== BASE STYLES (light theme default) ===== */
        body {
            margin: 20px;
            font-family: system-ui, -apple-system, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        /* light theme background (for demo) */
        body.light-theme {
            background-color: #f9fafb;
            color: #0f172a;
        }
        body.dark-theme {
            background-color: #111827;
            color: #f1f5f9;
        }

        /* Theme toggle buttons */
        .theme-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .theme-btn {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: transparent;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
        }
        body.light-theme .theme-btn {
            color: #374151;
            border-color: #d1d5db;
        }
        body.dark-theme .theme-btn {
            color: #cbd5e1;
            border-color: #475569;
        }
        .theme-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        /* ===== TABLE & PAGINATION STYLES (both themes) ===== */
        .table-container {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid;
            margin-top: 12px;
            transition: background-color 0.3s, border-color 0.3s;
        }

        /* Light theme table container */
        body.light-theme .table-container {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }
        /* Dark theme table container */
        body.dark-theme .table-container {
            background-color: #1e293b;
            border-color: #334155;
        }

        .table-wrapper {
            overflow-x: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1110px;
        }

        .table th {
            padding: 6px 8px;
            font-weight: 600;
            font-size: 11px;
            border-bottom: 1px solid;
            white-space: nowrap;
            text-align: center; /* centered headers as requested */
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        /* Light theme th */
        body.light-theme .table th {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
        }
        /* Dark theme th */
        body.dark-theme .table th {
            background-color: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }

        .table td {
            padding: 6px 8px;
            border-bottom: 1px solid;
            vertical-align: middle;
            font-size: 13px;
            transition: border-color 0.3s, color 0.3s;
        }

        /* Light theme td */
        body.light-theme .table td {
            border-color: #e2e8f0;
            color: #0f172a;
        }
        /* Dark theme td */
        body.dark-theme .table td {
            border-color: #334155;
            color: #f1f5f9;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            transition: background-color 0.3s;
        }
        body.light-theme .table tr:hover {
            background-color: #f8fafc;
        }
        body.dark-theme .table tr:hover {
            background-color: #2d3748;
        }

        /* Column width constraints (same as original) */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            padding-right: 4px;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table th:nth-child(2),
        .table td:nth-child(2) {
            padding-left: 4px;
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table th:nth-child(3),
        .table td:nth-child(3),
        .table th:nth-child(4),
        .table td:nth-child(4),
        .table th:nth-child(5),
        .table td:nth-child(5) {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .table th:nth-child(6),
        .table td:nth-child(6) {
            max-width: 90px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Text muted (secondary email) */
        .text-muted {
            transition: color 0.3s;
        }
        body.light-theme .text-muted {
            color: #64748b;
        }
        body.dark-theme .text-muted {
            color: #94a3b8;
        }

        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 600;
            gap: 4px;
            transition: background 0.3s, color 0.3s;
        }
        body.light-theme .status-active {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
        }
        body.dark-theme .status-active {
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
            color: #4ade80;
        }
        body.light-theme .status-inactive {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }
        body.dark-theme .status-inactive {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            color: #f87171;
        }

        /* Action buttons (static, non-interactive in this demo) */
        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: flex-start;
        }
        .action-btn {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: background-color 0.3s, color 0.3s;
        }
        body.light-theme .action-btn {
            color: #64748b;
            background-color: #f1f5f9;
        }
        body.dark-theme .action-btn {
            color: #94a3b8;
            background-color: #334155;
        }

        /* ===== PAGINATION STYLES (dual theme) ===== */
        .pagination {
            display: flex;
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 16px 0 0;
            justify-content: end;
        }
        .pagination li a,
        .pagination li span {
            display: block;
            padding: 6px 12px;
            border: 1px solid;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        /* Light theme pagination */
        body.light-theme .pagination li a,
        body.light-theme .pagination li span {
            background-color: #ffffff;
            border-color: #e2e8f0;
            color: #3b82f6;
        }
        body.light-theme .pagination li.active span {
            background-color: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }
        body.light-theme .pagination li.disabled span {
            color: #94a3b8;
            background-color: #f1f5f9;
            border-color: #e2e8f0;
        }

        /* Dark theme pagination */
        body.dark-theme .pagination li a,
        body.dark-theme .pagination li span {
            background-color: #1e293b;
            border-color: #475569;
            color: #94a3b8;
        }
        body.dark-theme .pagination li a:hover {
            background-color: #334155;
            color: #e2e8f0;
        }
        body.dark-theme .pagination li.active span {
            background-color: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }
        body.dark-theme .pagination li.disabled span {
            color: #64748b;
            background-color: #0f172a;
            border-color: #334155;
        }

        .mt-4 {
            margin-top: 16px;
        }
    </style>
</head>
<body class="light-theme">
    <!-- Simple theme toggle -->
    <div class="theme-toggle">
        <button class="theme-btn active" id="lightThemeBtn">Light Mode</button>
        <button class="theme-btn" id="darkThemeBtn">Dark Mode</button>
    </div>

    <!-- 
        EXTRACTED TABLE AND PAGINATION 
        - Table headers centered (already in CSS)
        - Raw static data rows
        - Static pagination example with theme support
    -->
    <div class="table-container">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Row 1 -->
                    <tr data-user-id="1">
                        <td>
                            <span class="user-name">John Smith</span>
                            <div class="text-muted">john.smith@example.com</div>
                        </td>
                        <td>Admin</td>
                        <td>IT Department</td>
                        <td>
                            <span class="status-badge status-active">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> Active
                            </span>
                        </td>
                        <td>2025-02-15 09:30</td>
                        <td>
                            <div class="action-buttons">
                                <span class="action-btn edit"><i class="fas fa-edit"></i></span>
                                <span class="action-btn password"><i class="fas fa-key"></i></span>
                                <span class="action-btn toggle"><i class="fas fa-toggle-on"></i></span>
                                <span class="action-btn delete"><i class="fas fa-trash-alt"></i></span>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 2 -->
                    <tr data-user-id="2">
                        <td>
                            <span class="user-name">Emma Johnson</span>
                            <div class="text-muted">emma.j@library.edu</div>
                        </td>
                        <td>Staff</td>
                        <td>Circulation</td>
                        <td>
                            <span class="status-badge status-active">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> Active
                            </span>
                        </td>
                        <td>2025-02-16 11:15</td>
                        <td>
                            <div class="action-buttons">
                                <span class="action-btn edit"><i class="fas fa-edit"></i></span>
                                <span class="action-btn password"><i class="fas fa-key"></i></span>
                                <span class="action-btn toggle"><i class="fas fa-toggle-on"></i></span>
                                <span class="action-btn delete"><i class="fas fa-trash-alt"></i></span>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 3 (inactive) -->
                    <tr data-user-id="3">
                        <td>
                            <span class="user-name">Michael Chen</span>
                            <div class="text-muted">m.chen@student.edu</div>
                        </td>
                        <td>Student</td>
                        <td>Computer Science</td>
                        <td>
                            <span class="status-badge status-inactive">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> Inactive
                            </span>
                        </td>
                        <td>2025-02-10 14:20</td>
                        <td>
                            <div class="action-buttons">
                                <span class="action-btn edit"><i class="fas fa-edit"></i></span>
                                <span class="action-btn password"><i class="fas fa-key"></i></span>
                                <span class="action-btn toggle"><i class="fas fa-toggle-off"></i></span>
                                <span class="action-btn delete"><i class="fas fa-trash-alt"></i></span>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 4 -->
                    <tr data-user-id="4">
                        <td>
                            <span class="user-name">Sarah Williams</span>
                            <div class="text-muted">s.williams@library.org</div>
                        </td>
                        <td>Staff</td>
                        <td>Reference</td>
                        <td>
                            <span class="status-badge status-active">
                                <i class="fas fa-circle" style="font-size: 8px;"></i> Active
                            </span>
                        </td>
                        <td>2025-02-16 08:45</td>
                        <td>
                            <div class="action-buttons">
                                <span class="action-btn edit"><i class="fas fa-edit"></i></span>
                                <span class="action-btn password"><i class="fas fa-key"></i></span>
                                <span class="action-btn toggle"><i class="fas fa-toggle-on"></i></span>
                                <span class="action-btn delete"><i class="fas fa-trash-alt"></i></span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination (static) -->
    </div>
    <div id="paginationContainer" class="mt-4">
        <nav>
            <ul class="pagination">
                <li class="page-item disabled"><span class="page-link">«</span></li>
                <li class="page-item active"><span class="page-link">1</span></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">»</a></li>
            </ul>
        </nav>
    </div>

    <!-- Simple script to toggle theme -->
    <script>
        (function() {
            const body = document.body;
            const lightBtn = document.getElementById('lightThemeBtn');
            const darkBtn = document.getElementById('darkThemeBtn');

            function setTheme(theme) {
                if (theme === 'dark') {
                    body.classList.remove('light-theme');
                    body.classList.add('dark-theme');
                    lightBtn.classList.remove('active');
                    darkBtn.classList.add('active');
                } else {
                    body.classList.remove('dark-theme');
                    body.classList.add('light-theme');
                    darkBtn.classList.remove('active');
                    lightBtn.classList.add('active');
                }
            }

            lightBtn.addEventListener('click', () => setTheme('light'));
            darkBtn.addEventListener('click', () => setTheme('dark'));
        })();
    </script>
</body>
</html>