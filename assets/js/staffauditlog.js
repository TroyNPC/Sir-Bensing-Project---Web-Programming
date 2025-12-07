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

function initStaffAuditLogTable() {
    const tableSelector = '#staffAuditLogTable';
    const $table = $(tableSelector);

    if (!$table.length) return;

    console.log("✅ initStaffAuditLogTable running");

    if ($.fn.dataTable.isDataTable(tableSelector)) {
        $table.DataTable().destroy();
    }

    $table.DataTable({
        responsive: false,
        scrollX: false, // keep horizontal scroll for long old/new data
        paging: true,
        pageLength: 10,
        lengthChange: true,
        searching: true,
        order: [[0, 'asc']], // keep loop.index order ascending
        columnDefs: [
            { targets: '_all', className: 'text-center align-middle' },
            { targets: [3, 4], className: 'text-start align-middle' } // Old/New data left-aligned
        ],
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

function initStaffAuditLogPage() {
    initStaffAuditLogTable();
}

document.addEventListener('DOMContentLoaded', initStaffAuditLogPage);
document.addEventListener('turbo:load', initStaffAuditLogPage);
