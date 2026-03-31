lucide.createIcons();

// Theme Toggle
const body = document.body;
const themeToggle = document.getElementById("themeToggle");
const sunIcon = document.getElementById("sunIcon");
const moonIcon = document.getElementById("moonIcon");
const savedTheme = localStorage.getItem("theme") || "light-theme";
body.classList.add(savedTheme);
updateThemeIcons(savedTheme);

themeToggle.addEventListener("click", () => {
    const isLight = body.classList.contains("light-theme");
    body.classList.toggle("light-theme", !isLight);
    body.classList.toggle("dark-theme", isLight);
    const newTheme = isLight ? "dark-theme" : "light-theme";
    localStorage.setItem("theme", newTheme);
    updateThemeIcons(newTheme);
    lucide.createIcons();
});

function updateThemeIcons(theme) {
    const isDark = theme === "dark-theme";
    sunIcon.style.display = isDark ? "none" : "block";
    moonIcon.style.display = isDark ? "block" : "none";
}

// Notification System
const notificationBtn = document.getElementById("notificationBtn");
const notificationPopup = document.getElementById("notificationPopup");
const notificationCloseBtn = document.getElementById("notificationCloseBtn");
const notificationBody = document.getElementById("notificationBody");
const notificationBadge = document.getElementById("notificationBadge");
const markAllReadBtn = document.getElementById("markAllReadBtn");

let notifications = [];

function buildNotificationUrl(template, notificationId) {
    return String(template || '')
        .replace('__ID__', String(notificationId))
        .replace(':id', String(notificationId))
        .replace('%3Aid', String(notificationId));
}

// Wait for window.notificationAPI to be defined, then initialize
function initializeNotifications() {
    if (!window.notificationAPI) {
        console.error('notificationAPI not defined, retrying in 100ms...');
        setTimeout(initializeNotifications, 100);
        return;
    }
    
    console.log('✓ Initializing notifications with API:', window.notificationAPI);
    loadNotifications();
    
    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('✓ DOMContentLoaded fired');
    initializeNotifications();
});

// Fetch notifications from API
async function loadNotifications() {
    try {
        if (!window.notificationAPI) {
            console.error('❌ API URLs not available yet');
            return;
        }
        
        console.log('📡 Loading notifications from:', window.notificationAPI.index);
        const response = await fetch(window.notificationAPI.index);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ HTTP Error', response.status, ':', errorText.substring(0, 200));
            return;
        }
        
        const data = await response.json();
        notifications = data.data || [];
        console.log('📬 Loaded', notifications.length, 'notifications:', notifications);
        renderNotifications();
        updateBadgeCount();
    } catch (error) {
        console.error('❌ Error loading notifications:', error);
    }
}

// Render notifications in the UI
function renderNotifications() {
    if (notifications.length === 0) {
        notificationBody.innerHTML = '<div class="p-4 text-center text-gray-500">No notifications</div>';
        return;
    }

    notificationBody.innerHTML = notifications.map(notification => {
        const isUnread = !notification.read_at;
        const timeAgo = getTimeAgo(new Date(notification.created_at));
        const iconClass = getNotificationIconClass(notification.type);
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}" style="position: relative;">
                <div class="notification-icon ${iconClass}">
                    <i data-lucide="${getIconName(notification.type)}" class="w-5 h-5"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${timeAgo}</div>
                </div>
                <button class="notification-delete-btn" data-id="${notification.id}" title="Delete notification" style="position: absolute; top: 8px; right: 8px; background: none; border: none; cursor: pointer; color: #ef4444; padding: 4px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        `;
    }).join('');

    // Add click handlers to new notifications
    attachNotificationHandlers();
    lucide.createIcons();
}

// Update badge count
async function updateBadgeCount() {
    try {
        console.log('🔔 Fetching unread count from:', window.notificationAPI.unreadCount);
        const response = await fetch(window.notificationAPI.unreadCount);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ HTTP Error', response.status, ':', errorText.substring(0, 200));
            return;
        }
        
        const data = await response.json();
        const count = data.unread_count || 0;
        
        console.log('✓ Unread notification count:', count);
        console.log('📌 Badge element:', notificationBadge);
        
        if (count > 0) {
            notificationBadge.textContent = count > 99 ? '99+' : count;
            notificationBadge.style.display = 'block';
            console.log('✓ Badge displayed with count:', count);
        } else {
            notificationBadge.style.display = 'none';
            console.log('✓ Badge hidden (no unread notifications)');
        }
    } catch (error) {
        console.error('❌ Error updating badge count:', error);
    }
}

// Get time ago string
function getTimeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);
    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60
    };

    for (const [key, value] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / value);
        if (interval >= 1) {
            return interval === 1 ? `${interval} ${key} ago` : `${interval} ${key}s ago`;
        }
    }
    return 'just now';
}

// Get icon name based on notification type
function getIconName(type) {
    const icons = {
        'book.overdue': 'alert-circle',
        'book.due_soon': 'clock',
        'fine.created': 'indian-rupee',
        'fine.reminder': 'alert-triangle',
        'request.approved': 'check-circle',
        'request.rejected': 'x-circle',
        'request.pending': 'clock',
        'book.new': 'book',
        'payment.confirmed': 'check-circle'
    };
    return icons[type] || 'bell';
}

// Get icon class for styling
function getNotificationIconClass(type) {
    const classes = {
        'book.overdue': 'danger',
        'book.due_soon': 'warning',
        'fine.created': 'danger',
        'fine.reminder': 'warning',
        'request.approved': 'success',
        'request.rejected': 'danger',
        'request.pending': 'info',
        'book.new': 'info',
        'payment.confirmed': 'success'
    };
    return classes[type] || 'info';
}

// Attach click handlers to notifications
function attachNotificationHandlers() {
    if (!notificationBody) {
        return;
    }

    notificationBody.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', async function(e) {
            // Don't mark as read if delete button was clicked
            if (e.target.closest('.notification-delete-btn')) {
                return;
            }
            const notificationId = this.dataset.id;
            if (this.classList.contains('unread')) {
                await markNotificationAsRead(notificationId);
            }
        });
    });
    
    // Attach delete button handlers
    notificationBody.querySelectorAll('.notification-delete-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const notificationId = this.dataset.id;
            await deleteNotification(notificationId);
        });
    });
}

// Mark single notification as read
async function markNotificationAsRead(notificationId) {
    try {
        const markReadUrl = buildNotificationUrl(window.notificationAPI.markRead, notificationId);
        await fetch(markReadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });
        
        // Update UI
        const element = document.querySelector(`[data-id="${notificationId}"]`);
        if (element) {
            element.classList.remove('unread');
        }
        
        updateBadgeCount();
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

// Mark all notifications as read
if (markAllReadBtn) {
    markAllReadBtn.addEventListener('click', async (e) => {
        e.stopPropagation();
        
        try {
            await fetch(window.notificationAPI.markAllRead, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            
            // Update UI
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            
            updateBadgeCount();
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    });
}

// Delete single notification
async function deleteNotification(notificationId) {
    try {
        if (!window.notificationAPI || !window.notificationAPI.delete) {
            console.error('Delete API URL not available');
            return;
        }

        const deleteUrl = buildNotificationUrl(window.notificationAPI.delete, notificationId);
        console.log('🗑️ Deleting notification from:', deleteUrl);

        // Call backend to delete from database
        const response = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (!response.ok) {
            console.error('❌ Delete failed:', data.message);
            alert('Failed to delete notification: ' + data.message);
            return;
        }

        console.log('✓ Notification deleted from database');

        // Delete from UI with animation
        const element = document.querySelector(`[data-id="${notificationId}"]`);
        if (element) {
            element.style.transition = 'opacity 0.3s ease';
            element.style.opacity = '0';
            setTimeout(() => {
                element.remove();
                // Check if any notifications left
                if (document.querySelectorAll('.notification-item').length === 0) {
                    loadNotifications();
                }
            }, 300);
        }

        updateBadgeCount();
    } catch (error) {
        console.error('❌ Error deleting notification:', error);
        alert('Error deleting notification. Please try again.');
    }
}

// Delete all notifications
const deleteAllBtn = document.getElementById('deleteAllBtn');
if (deleteAllBtn) {
    deleteAllBtn.addEventListener('click', async (e) => {
        e.stopPropagation();
        
        if (confirm('Are you sure you want to delete all notifications?')) {
            try {
                if (!window.notificationAPI || !window.notificationAPI.deleteAll) {
                    console.error('Delete all API URL not available');
                    alert('Delete functionality not available');
                    return;
                }

                console.log('🗑️ Deleting all notifications from:', window.notificationAPI.deleteAll);

                // Call backend to delete all from database
                const response = await fetch(window.notificationAPI.deleteAll, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    console.error('❌ Delete all failed:', data.message);
                    alert('Failed to delete notifications: ' + data.message);
                    return;
                }

                console.log('✓ All notifications deleted from database');

                // Delete all from UI with animation
                const items = document.querySelectorAll('.notification-item');
                items.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.transition = 'opacity 0.3s ease';
                        item.style.opacity = '0';
                        setTimeout(() => item.remove(), 300);
                    }, index * 50);
                });
                
                setTimeout(() => loadNotifications(), 500);
            } catch (error) {
                console.error('❌ Error deleting all notifications:', error);
                alert('Error deleting notifications. Please try again.');
            }
        }
    });
}

// Notification Popup Toggle
if (notificationBtn) {
    notificationBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        notificationPopup.classList.toggle("active");
        if (notificationPopup.classList.contains("active")) {
            loadNotifications();
        }
    });
}

if (notificationCloseBtn) {
    notificationCloseBtn.addEventListener("click", () => {
        notificationPopup.classList.remove("active");
    });
}

document.addEventListener("click", (e) => {
    if (notificationPopup &&
        !notificationPopup.contains(e.target) &&
        !notificationBtn.contains(e.target)
    ) {
        notificationPopup.classList.remove("active");
    }
});

// Remove duplicate initialization
// loadNotifications() and setInterval() moved to initializeNotifications()

// Mobile Sidebar
const mobileMenuBtn = document.getElementById("mobileMenuBtn");
const closeSidebarBtn = document.getElementById("closeSidebarBtn");
const sidebar = document.getElementById("sidebar");
const sidebarOverlay = document.getElementById("sidebarOverlay");

mobileMenuBtn.addEventListener("click", () => {
    sidebar.classList.add("active");
    sidebarOverlay.classList.add("active");
});

closeSidebarBtn.addEventListener("click", closeSidebar);
sidebarOverlay.addEventListener("click", closeSidebar);

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        if (sidebar.classList.contains("active")) closeSidebar();
        if (notificationPopup.classList.contains("active"))
            notificationPopup.classList.remove("active");
    }
});

function closeSidebar() {
    sidebar.classList.remove("active");
    sidebarOverlay.classList.remove("active");
}

const sidebarItems = document.querySelectorAll(".sidebar-item");
sidebarItems.forEach((item) => {
    item.addEventListener("click", () => {
        if (window.innerWidth <= 768) closeSidebar();
    });
});
