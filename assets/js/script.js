// =====================================================
// Library Management System - Custom JavaScript
// Used for confirmations and lightweight UI interactions.
// Important business rules are ALWAYS validated in PHP.
// =====================================================

document.addEventListener('DOMContentLoaded', function () {

    // Confirm delete/action links that carry the data-confirm attribute
    const confirmLinks = document.querySelectorAll('a[data-confirm]');
    confirmLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const message = link.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-dismiss Bootstrap alerts after 4 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const dismissBtn = alert.querySelector('.btn-close');
            if (dismissBtn) dismissBtn.click();
        }, 4000);
    });

    // Optional: simple "live search" filter for tables
    const liveSearchInput = document.getElementById('liveSearch');
    if (liveSearchInput) {
        liveSearchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('#searchableTable tbody tr');
            rows.forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(term) > -1
                    ? ''
                    : 'none';
            });
        });
    }

});
