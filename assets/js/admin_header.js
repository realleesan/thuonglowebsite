// Admin Header JavaScript
document.addEventListener('DOMContentLoaded', function () {
    const notificationsBtn = document.getElementById('notificationsBtn');
    const notificationsMenu = document.getElementById('notificationsMenu');
    const userBtn = document.getElementById('userBtn');
    const userMenu = document.getElementById('userMenu');
    const searchInput = document.querySelector('.search-input');

    // Toggle notifications dropdown
    if (notificationsBtn && notificationsMenu) {
        notificationsBtn.addEventListener('click', function (e) {
            e.stopPropagation();

            // Close user menu if open
            if (userMenu) {
                userMenu.classList.remove('show');
            }

            // Toggle notifications menu
            notificationsMenu.classList.toggle('show');
        });
    }

    // Toggle user dropdown
    if (userBtn && userMenu) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();

            // Close notifications menu if open
            if (notificationsMenu) {
                notificationsMenu.classList.remove('show');
            }

            // Toggle user menu
            userMenu.classList.toggle('show');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (e) {
        if (notificationsMenu && !notificationsMenu.contains(e.target) && !notificationsBtn.contains(e.target)) {
            notificationsMenu.classList.remove('show');
        }

        if (userMenu && !userMenu.contains(e.target) && !userBtn.contains(e.target)) {
            userMenu.classList.remove('show');
        }
    });

    // Close dropdowns on escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (notificationsMenu) {
                notificationsMenu.classList.remove('show');
            }
            if (userMenu) {
                userMenu.classList.remove('show');
            }
        }
    });

    // Search functionality with Suggestions
    if (searchInput) {
        let searchTimeout;
        const searchForm = document.querySelector('.search-form');

        // Create suggestions dropdown if not exists
        let suggestionsDropdown = document.getElementById('searchSuggestions');
        if (!suggestionsDropdown) {
            suggestionsDropdown = document.createElement('div');
            suggestionsDropdown.id = 'searchSuggestions';
            suggestionsDropdown.className = 'search-suggestions';
            searchForm.appendChild(suggestionsDropdown);
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            } else {
                suggestionsDropdown.classList.remove('show');
            }
        });

        // Hide suggestions on blur
        searchInput.addEventListener('blur', function () {
            setTimeout(() => {
                suggestionsDropdown.classList.remove('show');
            }, 200);
        });

        // Show suggestions on focus if query exists
        searchInput.addEventListener('focus', function () {
            if (this.value.trim().length >= 2) {
                suggestionsDropdown.classList.add('show');
            }
        });

        async function fetchSuggestions(query) {
            searchInput.classList.add('searching');
            try {
                const response = await fetch(`api.php?path=admin/dashboard/search&q=${encodeURIComponent(query)}&limit=6`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) throw new Error('Search failed');

                const json = await response.json();
                const results = json?.data?.data ?? json?.data ?? [];

                renderSuggestions(results, query);
            } catch (err) {
                console.warn('[Search] Suggestion error:', err);
                suggestionsDropdown.classList.remove('show');
            } finally {
                searchInput.classList.remove('searching');
            }
        }

        function renderSuggestions(results, query) {
            if (results.length === 0) {
                suggestionsDropdown.innerHTML = `<div class="suggestion-item empty">Không tìm thấy kết quả cho "${query}"</div>`;
            } else {
                suggestionsDropdown.innerHTML = results.map(item => `
                    <a href="${item.link}" class="suggestion-item">
                        <div class="suggestion-icon"><i class="${item.icon}"></i></div>
                        <div class="suggestion-info">
                            <div class="suggestion-title">${item.title}</div>
                            <div class="suggestion-meta">${item.info}</div>
                        </div>
                    </a>
                `).join('');

                // Add "view all" link
                suggestionsDropdown.innerHTML += `
                    <div class="suggestion-footer">
                        Nhấn Enter để tìm kiếm chi tiết
                    </div>
                `;
            }
            suggestionsDropdown.classList.add('show');
        }

        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                const query = searchInput.value.trim();
                if (query.length >= 2) {
                    // Cứ để submit tự nhiên nếu muốn trang kết quả toàn diện
                    // Ở đây ta redirect sang trang sản phẩm với filter search (phổ biến nhất)
                    e.preventDefault();
                    window.location.href = `?page=admin&module=products&search=${encodeURIComponent(query)}`;
                }
            });
        }
    }

    // Notification badge animation
    const notificationBadge = document.querySelector('.notifications-dropdown .badge');
    if (notificationBadge) {
        // Animate badge on new notification (simulate)
        function animateBadge() {
            notificationBadge.style.transform = 'scale(1.2)';
            setTimeout(() => {
                notificationBadge.style.transform = 'scale(1)';
            }, 200);
        }
    }

    // Auto-hide notifications after reading
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function () {
            this.style.opacity = '0.6';
            this.style.pointerEvents = 'none';

            // Mark as read (implement actual API call)
            setTimeout(() => {
                this.remove();
                updateNotificationBadge();
            }, 300);
        });
    });

    // Update notification badge count
    function updateNotificationBadge() {
        const remainingNotifications = document.querySelectorAll('.notification-item').length;
        if (notificationBadge) {
            if (remainingNotifications > 0) {
                notificationBadge.textContent = remainingNotifications;
            } else {
                notificationBadge.style.display = 'none';
            }
        }
    }

    // Responsive header adjustments
    function handleResponsiveHeader() {
        const header = document.querySelector('.admin-header');
        const breadcrumb = document.querySelector('.admin-breadcrumb');
        const sidebar = document.querySelector('.admin-sidebar');

        if (window.innerWidth <= 768) {
            if (header) header.style.left = '0';
            if (breadcrumb) breadcrumb.style.marginLeft = '0';
        } else {
            if (sidebar && sidebar.classList.contains('collapsed')) {
                if (header) header.style.left = '70px';
                if (breadcrumb) breadcrumb.style.marginLeft = '70px';
            } else {
                if (header) header.style.left = '250px';
                if (breadcrumb) breadcrumb.style.marginLeft = '250px';
            }
        }
    }

    window.addEventListener('resize', handleResponsiveHeader);
    handleResponsiveHeader(); // Initial check

    // Sidebar toggle effect on header
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function () {
            setTimeout(handleResponsiveHeader, 300); // Wait for sidebar animation
        });
    }

    // Load Notifications from API
    async function loadNotifications() {
        try {
            const response = await fetch('api.php?path=admin/dashboard/notifications&limit=5', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) return;

            const json = await response.json();
            const data = json?.data?.data ?? json?.data ?? {};
            const notifications = data.notifications ?? [];
            const unreadCount = data.unread_count ?? 0;

            // Update badge
            const badge = document.getElementById('notifBadge');
            if (badge) {
                badge.textContent = unreadCount > 0 ? unreadCount : '';
                badge.style.display = unreadCount > 0 ? '' : 'none';
            }

            // Update menu body
            const body = document.getElementById('notificationsBody');
            if (body) {
                if (notifications.length > 0) {
                    body.innerHTML = notifications.map(n => `
                        <div class="notification-item" onclick="window.location.href='${n.link || '#'}'">
                            <div class="notification-icon"><i class="${n.icon || 'fas fa-bell'}"></i></div>
                            <div class="notification-content">
                                <p class="notification-text">${n.message}</p>
                                <span class="notification-time">${n.time_ago}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    body.innerHTML = `
                        <div class="notification-empty" style="text-align:center;padding:20px;color:#999;">
                            <i class="fas fa-check-circle" style="font-size:24px;margin-bottom:10px;display:block;"></i>
                            <p>Không có thông báo mới</p>
                        </div>
                    `;
                }
            }
        } catch (err) {
            console.warn('[Header] Failed to load notifications:', err);
        }
    }

    // Initial load and set interval
    loadNotifications();
    setInterval(loadNotifications, 60000); // Refresh every minute

    // Export for manual reload
    window.AdminHeader = { loadNotifications };
});