/**
 * Gilgil Technical and Vocational College (GTVC) LMS - Client JavaScript App Helpers
 */

document.addEventListener('DOMContentLoaded', () => {
    // Mobile Sidebar Toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const appSidebar = document.getElementById('appSidebar');

    if (mobileToggle && appSidebar) {
        mobileToggle.addEventListener('click', () => {
            appSidebar.classList.toggle('open');
        });
    }

    // Modal Manager
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    };

    // Close modals on backdrop click
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-backdrop')) {
            e.target.style.display = 'none';
        }
    });

    // Tab Switcher
    const tabButtons = document.querySelectorAll('[data-tab-target]');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-tab-target');
            const parent = button.closest('.tab-container');

            if (parent) {
                parent.querySelectorAll('[data-tab-target]').forEach(btn => btn.classList.remove('active'));
                parent.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');

                button.classList.add('active');
                const targetContent = parent.querySelector('#' + targetId);
                if (targetContent) {
                    targetContent.style.display = 'block';
                }
            }
        });
    });

    // Toast Notification helper
    window.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : '#f43f5e'};
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-weight: 600;
            font-size: 14px;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        `;
        toast.innerText = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 4000);
    };
});
