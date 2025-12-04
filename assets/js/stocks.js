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

    function syncDisplayWithInput(modal) {
            const input   = modal.querySelector('.stock-input-hidden');
            const display = modal.querySelector('.stock-display');
            if (!input || !display) return;

            let value = parseInt(input.value || '0', 10);
            if (isNaN(value) || value < 0) value = 0;

            input.value = value;
            display.textContent = value;
        }

        function bindStockArrowButtons() {
            document.querySelectorAll('.stock-dec, .stock-inc').forEach(btn => {
                // avoid double-binding => no +2 / -2
                if (btn.dataset.bound === 'true') return;
                btn.dataset.bound = 'true';

                btn.addEventListener('click', function () {
                    const modal = this.closest('.modal');
                    if (!modal) return;

                    const input   = modal.querySelector('.stock-input-hidden');
                    const display = modal.querySelector('.stock-display');
                    if (!input || !display) return;

                    let value = parseInt(input.value || display.textContent || '0', 10);
                    if (isNaN(value)) value = 0;

                    if (this.classList.contains('stock-dec')) {
                        value = Math.max(0, value - 1);   // step -1, not below 0
                    } else {
                        value = value + 1;                 // step +1
                    }

                    input.value = value;
                    display.textContent = value;
                });
            });
        }

        function bindStockFormSubmitSync() {
            document.querySelectorAll('form[name="stocks"]').forEach(form => {
                if (form.dataset.boundSubmit === 'true') return;
                form.dataset.boundSubmit = 'true';

                form.addEventListener('submit', function () {
                    const modal   = form.closest('.modal');
                    const input   = modal ? modal.querySelector('.stock-input-hidden') : form.querySelector('.stock-input-hidden');
                    const display = modal ? modal.querySelector('.stock-display')      : form.querySelector('.stock-display');

                    if (!input || !display) return;

                    let value = parseInt(display.textContent || input.value || '0', 10);
                    if (isNaN(value) || value < 0) value = 0;

                    // 🔐 Make sure Symfony receives the number the user actually sees
                    input.value = value;
                });
            });
        }

        function initEditStockModals() {
            // When page loads, sync all edit modals once
            document.querySelectorAll('.modal[id^="editStockModal"]').forEach(modal => {
                syncDisplayWithInput(modal);

                // Also resync each time the modal is opened
                modal.addEventListener('show.bs.modal', () => {
                    syncDisplayWithInput(modal);
                });
            });
        }

        function initStocksPageExtras() {
            initEditStockModals();
            bindStockArrowButtons();
            bindStockFormSubmitSync();
        }

        document.addEventListener('DOMContentLoaded', initStocksPageExtras);
        document.addEventListener('turbo:load', initStocksPageExtras);

document.addEventListener('DOMContentLoaded', initStocksPage);
document.addEventListener('turbo:load', initStocksPage);