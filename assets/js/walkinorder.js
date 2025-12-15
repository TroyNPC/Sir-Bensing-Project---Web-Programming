import $ from 'jquery';
import 'bootstrap';
import 'datatables.net-bs5';


window.$ = window.jQuery = $;




// -----------------------
// SAFETY LOAD FLAG
// -----------------------
if (!window.walkinOrdersInit) {
  window.walkinOrdersInit = true;
  console.log("✅ walkinorder.js loaded");
} else {
  console.log("⚠️ walkinorder.js already loaded");
}




// -----------------------
// DATATABLE INIT
// -----------------------
function initWalkinOrdersTable() {


  $.fn.dataTable.ext.errMode = 'none';


  const tableSelector = '#walkinOrdersTable';
  const $table = $(tableSelector);


  if (!$table.length) return;


  console.log("✅ initWalkinOrdersTable running");


  if ($.fn.dataTable.isDataTable(tableSelector)) {
    $table.DataTable().destroy();
  }


  const rowCount = $table.find('tbody tr').length;
  const hasData =
    rowCount > 0 &&
    !$table.find('tbody tr td').first().text().includes('No walk-in orders');


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
      zeroRecords: "No matching orders found",
      infoEmpty: "No orders available",
      info: "Showing START to END of TOTAL orders",
      paginate: { previous: "Prev", next: "Next" }
    },
    autoWidth: true,
    stateSave: true,
  });


  const sidebarToggler = document.getElementById('toggleSidebar');
  if (sidebarToggler && !sidebarToggler.dataset.walkinBound) {
    sidebarToggler.dataset.walkinBound = "true";
    sidebarToggler.addEventListener('click', () => {
      setTimeout(() => {
        dt.columns.adjust();
      }, 300);
    });
  }


  window.addEventListener('resize', () => {
    dt.columns.adjust();
  });
}




// -----------------------
// VIEW MODAL LOADER
// -----------------------
function initWalkinViewModal() {


  const modalEl = document.getElementById('walkinViewModal');
  if (!modalEl) return;


  const modal   = new bootstrap.Modal(modalEl);
  const bodyEl  = modalEl.querySelector('#walkinViewModalBody');
  const titleEl = modalEl.querySelector('#walkinViewModalTitle');


  function openViewModal(e) {
    e.preventDefault();


    const url   = this.getAttribute('href');
    const title = this.dataset.modalTitle || 'View Walk-in Order';


    titleEl.textContent = title;
    bodyEl.innerHTML = '<div class="text-center py-4">Loading...</div>';


    fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(res => res.text())
      .then(html => {


        const temp = document.createElement('div');
        temp.innerHTML = html;


        const content =
          temp.querySelector('.container-fluid')
          || temp.querySelector('body')
          || temp;


        bodyEl.innerHTML = '';
        bodyEl.appendChild(content);


        modal.show();


      })
      .catch(err => {
        console.error(err);
        bodyEl.innerHTML =
          '<div class="text-danger text-center">Failed to load order.</div>';
        modal.show();
      });
  }


  function bindLinks() {
    document.querySelectorAll('.js-walkin-view').forEach(link => {


      if (link.dataset.bound === "1") return;
      link.dataset.bound = "1";


      link.addEventListener('click', openViewModal);
    });
  }


  bindLinks();
  document.addEventListener('turbo:load', bindLinks);
}




// -----------------------
// MASTER INIT
// -----------------------
function initWalkinOrdersPage() {
  initWalkinOrdersTable();
  initWalkinViewModal();
}


document.addEventListener('DOMContentLoaded', initWalkinOrdersPage);
document.addEventListener('turbo:load', initWalkinOrdersPage);






