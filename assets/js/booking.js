// assets/js/servicebooking.js
import $ from 'jquery';
import 'bootstrap';
import 'datatables.net-bs5';

// Make jQuery global so DataTables can attach properly
window.$ = window.jQuery = $;

if (!window.serviceBookingInit) {
  window.serviceBookingInit = true;
  console.log("✅ servicebooking.js loaded");
} else {
  console.log("⚠️ servicebooking.js already loaded");
}

/* ---------------------------
   SERVICE BOOKING TABLE
--------------------------- */
function initServiceBookingTable() {
  // Safety: if plugin didn't load, don't crash
  if (!$.fn.dataTable) {
    console.warn('DataTables plugin not loaded for servicebookingTable');
    return;
  }

  $.fn.dataTable.ext.errMode = 'none';

  const tableSelector = '#servicebookingTable';
  const $table = $(tableSelector);

  // Not on this page
  if (!$table.length) return;

  console.log("✅ initServiceBookingTable running");

  // Destroy any previous instance on this DOM
  if ($.fn.dataTable.isDataTable(tableSelector)) {
    $table.DataTable().destroy();
  }

  const rowCount = $table.find('tbody tr').length;
  const hasData =
    rowCount > 0 &&
    !$table.find('tbody tr td').first().text().includes('No records');

  // You can keep this or remove it; with real data it's true anyway
  if (!hasData) return;

  $table.DataTable({
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
      zeroRecords: "No matching bookings found",
      infoEmpty: "No bookings available",
      info: "Showing START to END of TOTAL bookings",
      paginate: { previous: "Prev", next: "Next" }
    },
    autoWidth: false,
    stateSave: true,
    destroy: true,
  });
}

/* ---------------------------
   EDIT MODAL FETCH
--------------------------- */
function initServiceBookingModals() {
  document.querySelectorAll('[id^="editModal"]').forEach(modal => {
    if (modal.dataset.listenerAdded === 'true') return;
    modal.dataset.listenerAdded = 'true';

    modal.addEventListener('show.bs.modal', function () {
      const id = this.id.replace('editModal', '');
      const modalBody = document.getElementById('editModalBody' + id);
      if (!modalBody) return;

      modalBody.innerHTML = `
        <div class="text-center text-muted py-3">
          <div class="spinner-border text-dark"></div>
          <p class="mt-2 mb-0 small">Loading form...</p>
        </div>`;

      const url = `/servicebooking/${id}/edit`;
      const actionPath = `/servicebooking/${id}/edit`;

      fetch(url)
        .then(res => res.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const form = doc.querySelector('form');

          if (form) {
            form.action = actionPath;

            const submit = form.querySelector('button[type="submit"]');
            if (submit) submit.classList.add('btn-warning', 'fw-semibold');

            modalBody.innerHTML = '';
            modalBody.appendChild(form);
          } else {
            modalBody.innerHTML =
              '<p class="text-danger">Could not load edit form.</p>';
          }
        })
        .catch(() => {
          modalBody.innerHTML =
            '<p class="text-danger">Failed to load form.</p>';
        });
    });
  });
}

/* ---------------------------
   MASTER INIT (Service Booking)
--------------------------- */
function initServiceBookingPage() {
  initServiceBookingTable();
  initServiceBookingModals();
}

document.addEventListener('DOMContentLoaded', initServiceBookingPage);
document.addEventListener('turbo:load', initServiceBookingPage);



