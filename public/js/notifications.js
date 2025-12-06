/**
 * Notification System JavaScript (v4)
 * - Popup outside sidebar at body level
 * - Dynamically positioned aligned with notification menu item
 */

// Global function for inline onclick
function toggleNotificationPopup(event) {
    event.preventDefault();
    event.stopPropagation();

    const popup = document.getElementById('notificationPopup');
    const navItem = document.getElementById('notificationNavItem');

    if (!popup || !navItem) return;

    // Toggle popup visibility
    popup.classList.toggle('show');

    // Position popup aligned with menu item
    if (popup.classList.contains('show')) {
        positionNotificationPopup();
        navItem.classList.add('active');
    } else if (!window.location.pathname.includes('/notifications')) {
        navItem.classList.remove('active');
    }
}

// Position popup to the right of sidebar, aligned with menu item
function positionNotificationPopup() {
    const popup = document.getElementById('notificationPopup');
    const navItem = document.getElementById('notificationNavItem');
    const sidebar = document.querySelector('.premium-sidebar');

    if (!popup || !navItem || !sidebar) return;

    // Get menu item position
    const navRect = navItem.getBoundingClientRect();
    const sidebarRect = sidebar.getBoundingClientRect();

    // Position popup to the right of sidebar, aligned with menu top
    popup.style.left = (sidebarRect.right + 10) + 'px';
    popup.style.top = navRect.top + 'px';
    popup.style.transform = 'none';

    // Make sure popup doesn't go off screen at bottom
    const popupHeight = popup.offsetHeight;
    const viewportHeight = window.innerHeight;

    if (navRect.top + popupHeight > viewportHeight - 20) {
        // Adjust to fit in viewport
        popup.style.top = Math.max(20, viewportHeight - popupHeight - 20) + 'px';
    }
}

// Update position on window resize
window.addEventListener('resize', function () {
    const popup = document.getElementById('notificationPopup');
    if (popup && popup.classList.contains('show')) {
        positionNotificationPopup();
    }
});

// Close popup when clicking outside
document.addEventListener('click', function (e) {
    const popup = document.getElementById('notificationPopup');
    const navItem = document.getElementById('notificationNavItem');
    const wrapper = document.getElementById('notificationNavWrapper');

    // If click is outside popup and nav item, close it
    if (popup && popup.classList.contains('show')) {
        if (!popup.contains(e.target) && (!wrapper || !wrapper.contains(e.target))) {
            popup.classList.remove('show');
            if (!window.location.pathname.includes('/notifications')) {
                navItem?.classList.remove('active');
            }
        }
    }
});

// Close popup on escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const popup = document.getElementById('notificationPopup');
        const navItem = document.getElementById('notificationNavItem');

        popup?.classList.remove('show');
        if (!window.location.pathname.includes('/notifications')) {
            navItem?.classList.remove('active');
        }
    }
});

// ============================================
// BULK ACTIONS (for notification page)
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAllNotifications');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');

    // Select All functionality - query checkboxes fresh each time
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const allCheckboxes = document.querySelectorAll('.notification-checkbox');
            allCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkDeleteButton();
        });
    }

    // Individual checkbox change - use event delegation for dynamic elements
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('notification-checkbox')) {
            updateBulkDeleteButton();

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.notification-checkbox');
            const checkedCount = document.querySelectorAll('.notification-checkbox:checked').length;
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allCheckboxes.length > 0 && checkedCount === allCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
            }
        }
    });

    function updateBulkDeleteButton() {
        if (!bulkDeleteBtn) return;
        const checkedCount = document.querySelectorAll('.notification-checkbox:checked').length;
        bulkDeleteBtn.disabled = checkedCount === 0;
        const countSpan = bulkDeleteBtn.querySelector('.count');
        if (countSpan) {
            countSpan.textContent = checkedCount > 0 ? `(${checkedCount})` : '';
        }
    }

    // Bulk delete form submission
    if (bulkDeleteForm) {
        bulkDeleteForm.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('.notification-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                return;
            }

            // Clear previous hidden inputs
            this.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

            // Add selected IDs
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                this.appendChild(input);
            });
        });
    }
});

// Slide out animation for deleted items
const notifStyle = document.createElement('style');
notifStyle.textContent = `
    @keyframes slideOut {
        to {
            opacity: 0;
            transform: translateX(-100%);
            height: 0;
            padding: 0;
            margin: 0;
        }
    }
`;
document.head.appendChild(notifStyle);
