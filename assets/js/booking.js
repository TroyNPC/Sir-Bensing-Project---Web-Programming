// assets/js/servicebooking.js
import $ from 'jquery';
import 'bootstrap';
import 'datatables.net-bs5';


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
  if (!$.fn.dataTable) {
    console.warn('DataTables plugin not loaded for servicebookingTable');
    return;
  }


  $.fn.dataTable.ext.errMode = 'none';


  const tableSelector = '#servicebookingTable';
  const $table = $(tableSelector);


  if (!$table.length) return;


  console.log("✅ initServiceBookingTable running");


  if ($.fn.dataTable.isDataTable(tableSelector)) {
    $table.DataTable().destroy();
  }


  const rowCount = $table.find('tbody tr').length;
  const hasData =
    rowCount > 0 &&
    !$table.find('tbody tr td').first().text().includes('No records');


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
   HELPER: attach AJAX submit to modal form
--------------------------- */
function attachModalFormHandler(form, modalBody, id) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();


    const action = form.action;
    const method = (form.method || 'POST').toUpperCase();
    const formData = new FormData(form);


    // Optional: show loading state
    const originalHtml = modalBody.innerHTML;
    modalBody.innerHTML = `
      <div class="text-center text-muted py-3">
        <div class="spinner-border text-dark" role="status"></div>
        <p class="mt-2 mb-0 small">Saving changes...</p>
      </div>
    `;


    fetch(action, {
      method,
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(async (res) => {
        // If controller redirects on success
        if (res.redirected) {
          // Either go exactly where Symfony wants:
          window.location.href = res.url;
          return;
        }


        const html = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newForm = doc.querySelector('form');


        if (newForm) {
          // Likely validation errors -> show new form inside modal
          modalBody.innerHTML = '';
          modalBody.appendChild(newForm);
          attachModalFormHandler(newForm, modalBody, id);
        } else {
          // No form -> assume success, just reload the page
          modalBody.innerHTML = originalHtml;
          window.location.reload();
        }
      })
      .catch((err) => {
        console.error(err);
        modalBody.innerHTML =
          '<p class="text-danger mb-0">Failed to save changes. Please try again.</p>';
      });
  });
}


/* ---------------------------
   EDIT MODAL FETCH (AJAX)
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
            if (submit) {
              submit.classList.add('btn-warning', 'fw-semibold');
              submit.setAttribute('data-turbo', 'false');
            }


            modalBody.innerHTML = '';
            modalBody.appendChild(form);


            // 🔥 Attach AJAX submit handler here
            attachModalFormHandler(form, modalBody, id);
          } else {
            modalBody.innerHTML =
              '<p class="text-danger mb-0">Could not load edit form.</p>';
          }
        })
        .catch(() => {
          modalBody.innerHTML =
            '<p class="text-danger mb-0">Failed to load form.</p>';
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





