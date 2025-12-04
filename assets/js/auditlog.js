// assets/js/auditlog.js
import $ from 'jquery';
import 'bootstrap';
import 'datatables.net-bs5';


if (!window.auditLogInit) {
    window.auditLogInit = true;
    console.log("✅ auditlog.js loaded");
} else {
    console.log("⚠️ auditlog.js already loaded");
}


function initAuditLogTable() {
    const tableSelector = '#auditLogTable';
    const $table = $(tableSelector);


    if (!$table.length) return;


    console.log("✅ initAuditLogTable running");


    if ($.fn.dataTable.isDataTable(tableSelector)) {
        $table.DataTable().destroy();
    }


    $table.DataTable({
        responsive: false,
        scrollX: false,
        paging: true,
        pageLength: 10,
        lengthChange: true,
        searching: true,
        order: [[0, 'asc']], // newest first
        columnDefs: [{ targets: '_all', className: 'text-center align-middle' }],
        language: {
            searchPlaceholder: "Search logs...",
            search: "INPUT",
            zeroRecords: "No matching logs found",
            infoEmpty: "No logs available",
            paginate: { previous: "Prev", next: "Next" }
        },
        autoWidth: false,
        stateSave: true
    });
}


function initAuditLogPage() {
    initAuditLogTable();
}


document.addEventListener('DOMContentLoaded', initAuditLogPage);
document.addEventListener('turbo:load', initAuditLogPage);





