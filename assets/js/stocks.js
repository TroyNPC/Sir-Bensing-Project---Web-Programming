// assets/js/adminlayout.js
import $ from 'jquery';
import 'bootstrap';
import 'datatables.net-bs5';

window.$ = window.jquery = $;



if (!window.stocksInit) {
  window.stocksInit = true;
  console.log("✅ stocks.js loaded");
} else {
  console.log("⚠️ stocks.js already loaded");
}

function initStocksTable() {
  $.fn.dataTable.ext.errMode = 'none';

  const tableSelector = '#stocksTable';
  const $table = $(tableSelector);

  if (!$table.length) return;

  console.log("✅ initStocksTable running");

  // Destroy any previous instance on this DOM
  if ($.fn.dataTable.isDataTable(tableSelector)) {
    $table.DataTable().destroy();
  }

  const rowCount = $table.find('tbody tr').length;
  const hasData =
    rowCount > 0 &&
    !$table.find('tbody tr td').first().text().includes('No records');

  if (!hasData) return;

  const dt = $table.DataTable({
    destroy: true,
    responsive: false,
    scrollX: true,
    paging: true,
    pageLength: 10,
    lengthChange: true,
    searching: true,
    order: [[0, 'asc']],
    columnDefs: [{ targets: '_all', className: 'text-center' }],
    language: {
      searchPlaceholder: "Search...",
      search: "INPUT",
      zeroRecords: "No matching stocks found",
      infoEmpty: "No stocks available",
      info: "Showing START to END of TOTAL stocks",
      paginate: { previous: "Prev", next: "Next" }
    },
    autoWidth: true,
    stateSave: true,
    destroy: true,
  });

  // Adjust when sidebar toggles (id is toggleSidebar in adminlayout.html.twig)
  const sidebarToggler = document.getElementById('toggleSidebar');
  if (sidebarToggler && !sidebarToggler.dataset.stocksBound) {
    sidebarToggler.dataset.stocksBound = "true";
    sidebarToggler.addEventListener('click', () => {
      setTimeout(() => {
        dt.columns.adjust();
      }, 300);
    });
  }

  // Bonus: auto-adjust on window resize
  window.addEventListener('resize', () => {
    dt.columns.adjust();
  });
}

function initStocksPage() {
  initStocksTable();
}

document.addEventListener('DOMContentLoaded', initStocksPage);
document.addEventListener('turbo:load', initStocksPage);
