// assets/js/pcproducts.js
import $ from 'jquery';
import 'bootstrap';
import 'datatables.net-bs5';

if (!window.pcProductsInit) {
    window.pcProductsInit = true;
    console.log("✅ pcproducts.js loaded");
} else {
    console.log("⚠️ pcproducts.js already loaded");
}

/* ---------------------------
   PRODUCTS TABLE
--------------------------- */
function initPcProductsTable() {
    // Match stocks.js behavior: silence DataTable errors in console
    $.fn.dataTable.ext.errMode = 'none';

    const tableSelector = '#pcproductsTable';
    const $table = $(tableSelector);

    if (!$table.length) return; // not on products page

    console.log("✅ initPcProductsTable running");

    // Destroy any existing instance on this table
    if ($.fn.dataTable.isDataTable(tableSelector)) {
        $table.DataTable().destroy();
    }

    // Avoid initializing on "No records" dummy row
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
            zeroRecords: "No matching products found",
            infoEmpty: "No products available",
            info: "Showing START to END of TOTAL products",
            paginate: { previous: "Prev", next: "Next" }
        },
        autoWidth: true,
        stateSave: true,
    });

    // ✅ Adjust columns when sidebar toggles (same as stocks.js)
    const sidebarToggler = document.getElementById('toggleSidebar');
    if (sidebarToggler && !sidebarToggler.dataset.pcProductsBound) {
        sidebarToggler.dataset.pcProductsBound = "true";
        sidebarToggler.addEventListener('click', () => {
            setTimeout(() => {
                dt.columns.adjust();
            }, 300);
        });
    }

    // ✅ Auto-adjust on window resize
    window.addEventListener('resize', () => {
        dt.columns.adjust();
    });
}

/* ---------------------------
   IMAGE PREVIEW
--------------------------- */
function initImagePreviews() {
    document.querySelectorAll('input[type="file"]').forEach(input => {
        if (input.dataset.previewBound === 'true') return;
        input.dataset.previewBound = 'true';

        input.addEventListener('change', function () {
            const file = this.files[0];
            const preview = this.closest('form')?.querySelector('.preview-img');

            if (!preview) return;

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.classList.remove('empty');
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.classList.add('empty');
            }
        });
    });
}

/* ---------------------------
   DELETE CONFIRM
--------------------------- */
function initDeleteConfirm() {
    window.deleteProductRow = function () {
        return confirm('Are you sure you want to delete this product?');
    };
}

/* ---------------------------
   MASTER INIT (Products page)
--------------------------- */
function initPcProductsPage() {
    initPcProductsTable();
    initImagePreviews();
    initDeleteConfirm();
}

document.addEventListener('DOMContentLoaded', initPcProductsPage);
document.addEventListener('turbo:load', initPcProductsPage);


