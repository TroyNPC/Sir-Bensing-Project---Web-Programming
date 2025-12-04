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


// ---- Roles warning + validation ----
document.addEventListener('DOMContentLoaded', function () {
    // For each user edit form
    document.querySelectorAll('form[id^="form_user_"], form[id^="user_"]').forEach(function (form) {
        const roleCheckboxes = form.querySelectorAll('input[type="checkbox"][name$="[roles][]"]');

        if (!roleCheckboxes.length) {
            return;
        }

        // 1) Warn if ROLE_USER is unchecked
        roleCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (checkbox.value === 'ROLE_USER' && !checkbox.checked) {
                    const ok = confirm(
                        "Removing ROLE_USER is usually not recommended.\n\n" +
                        "Do you really want to remove ROLE_USER from this account?"
                    );
                    if (!ok) {
                        checkbox.checked = true; // revert change
                    }
                }
            });
        });

        // 2) Prevent submit if no roles are selected
        form.addEventListener('submit', function (e) {
            const checked = Array.from(roleCheckboxes).filter(cb => cb.checked);
            if (checked.length === 0) {
                e.preventDefault();
                alert("User must have at least one role (e.g., ROLE_USER or ROLE_ADMIN).");
            }
        });
    });
});