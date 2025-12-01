import $ from 'jquery';
import 'datatables.net-bs5';
import 'bootstrap';

$(document).ready(function() {
    const $table = $('#usersTable');
    // Mark table as "has data" if there are rows other than the "No users found"
    const hasData = $table.find('tbody tr td').text().trim() !== "No users found";
    if (hasData) {
        $table.DataTable({
            responsive: false,
            scrollX: false,
            paging: true,
            pageLength: 10,
            searching: true,
            order: [[0, 'asc']],
            columnDefs: [
                { targets: '_all', className: 'text-center align-middle' }
            ],
            language: {
                searchPlaceholder: "Search...",
                search: "_INPUT_",
                zeroRecords: "No users found",
                infoEmpty: "No users available",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                paginate: { previous: "Prev", next: "Next" }
            },
            autoWidth: false,
        });
    }
});