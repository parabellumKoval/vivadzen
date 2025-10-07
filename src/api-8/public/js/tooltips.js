document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips when page loads
    initTooltips();

    // Re-initialize tooltips after DataTable draws
    if (window.jQuery && $.fn.DataTable) {
        $(document).on('draw.dt', function() {
            initTooltips();
        });
    }
});

function initTooltips() {
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });
}