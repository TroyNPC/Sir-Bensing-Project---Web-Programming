import $ from 'jquery';
import 'datatables.net-bs5';
import 'bootstrap';

document.addEventListener("DOMContentLoaded", () => {
    $.fn.dataTable.ext.errMode = 'none';

    const $table = $('#stocksTable');
    const hasData = $table.data('hasData') || false;
    let tableInstance = null;

    if (hasData && !$table.hasClass('dataTable')) {
        tableInstance = $table.DataTable({
            responsive: false,
            scrollX: false,
            paging: true,
            pageLength: 10,
            lengthChange: true,
            searching: true,
            order: [[0, 'asc']],
            columnDefs: [{ targets: '_all', className: 'text-center' }],
            language: {
                searchPlaceholder: "Search...",
                search: "_INPUT_",
                zeroRecords: "No matching stocks found",
                infoEmpty: "No stocks available",
                info: "Showing _START_ to _END_ of _TOTAL_ stocks",
                paginate: { previous: "Prev", next: "Next" }
            },
            autoWidth: false
        });
    }

    // 🔄 When sidebar toggles, adjust columns
    const sidebarToggler = document.getElementById('sidebarToggle');
    if (sidebarToggler && tableInstance) {
        sidebarToggler.addEventListener('click', () => {
            setTimeout(() => {
                tableInstance.columns.adjust();
            }, 300); // slight delay for sidebar animation
        });
    }

    // 🔄 Bonus: auto-adjust on window resize
    window.addEventListener('resize', () => {
        if (tableInstance) {
            tableInstance.columns.adjust();
        }
    });
});
