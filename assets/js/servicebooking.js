import $ from 'jquery';
import 'datatables.net-bs5';
import 'bootstrap';

document.addEventListener("DOMContentLoaded", function () {
    $.fn.dataTable.ext.errMode = 'none';

    const $table = $('#servicebookingTable');
    const rowCount = $table.find('tbody tr').length;
    const hasData = rowCount > 0 && !$table.find('tbody tr td').first().text().includes('No records');

    if (hasData && !$.fn.DataTable.isDataTable('#servicebookingTable')) {
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
                search: "_INPUT_",
                zeroRecords: "No matching bookings found",
                infoEmpty: "No bookings available",
                info: "Showing _START_ to _END_ of _TOTAL_ bookings",
                paginate: { previous: "Prev", next: "Next" }
            },
            autoWidth: false,   
        });
    }

    // ---------- EDIT MODAL FETCH ----------
    document.querySelectorAll('[id^="editModal"]').forEach(modal => {
        modal.addEventListener('show.bs.modal', function (event) {
            const id = this.id.replace('editModal', '');
            const modalBody = document.getElementById('editModalBody' + id);
            modalBody.innerHTML = `
                <div class="text-center text-muted py-3">
                    <div class="spinner-border text-dark"></div>
                    <p class="mt-2 mb-0 small">Loading form...</p>
                </div>`;

            fetch(`/servicebooking/${id}/edit`)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const form = doc.querySelector('form');

                    if (form) {
                        form.action = `/servicebooking/${id}/edit`;
                        const submit = form.querySelector('button[type="submit"]');
                        if (submit) submit.classList.add('btn-warning', 'fw-semibold');

                        modalBody.innerHTML = '';
                        modalBody.appendChild(form);
                    } else {
                        modalBody.innerHTML = '<p class="text-danger">Could not load edit form.</p>';
                    }
                })
                .catch(() => {
                    modalBody.innerHTML = '<p class="text-danger">Failed to load form.</p>';
                });
        });
    });
});
