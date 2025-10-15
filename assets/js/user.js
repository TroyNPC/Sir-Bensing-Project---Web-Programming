import $ from 'jquery';
import 'datatables.net-bs5';
import 'bootstrap';


document.addEventListener("DOMContentLoaded", function () {
    $.fn.dataTable.ext.errMode = 'none';

    const $table = $('#usersTable');
    const hasRealData = window.hasRealData === true;
    let dataTable = null;

    if (hasRealData) {
        const userPasswords = window.userPasswords || {};

        dataTable = $table.DataTable({
            responsive: false,
            paging: true,
            pageLength: 10,
            lengthChange: false,
            searching: true,
            scrollX: true,
            order: [[0, 'asc']],
            columnDefs: [{ targets: "_all", className: 'text-center' }],
            language: {
                searchPlaceholder: "Search...",
                search: "_INPUT_",
                zeroRecords: "No matching users found",
                infoEmpty: "No users available",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                paginate: { previous: "Prev", next: "Next" }
            },
            autoWidth: false
        });

        function attachPasswordToggle() {
            $('#usersTable').off('click', '.toggle-password');
            $('#usersTable').on('click', '.toggle-password', function() {
                const btn = $(this);
                const userId = btn.data('user-id');
                const mask = $('#mask-' + userId);
                mask.text(mask.text() === '****' ? userPasswords[userId] : '****');
            });
        }

        attachPasswordToggle();
        dataTable.on('draw', attachPasswordToggle);

        dataTable.on('init', function () {
            setTimeout(() => {
                try { dataTable.columns.adjust().responsive.recalc(); } catch(e) {}
            }, 250);

            $(window).on('resize.userTable', function () {
                try { dataTable.columns.adjust().responsive.recalc(); } catch(e) {}
            });

            document.getElementById('toggleSidebar')?.addEventListener('click', () => {
                setTimeout(() => {
                    try { dataTable.columns.adjust().responsive.recalc(); } catch(e) {}
                }, 420);
            });
        });
    } else {
        $table.css({ 'min-width': '100%', 'table-layout': 'fixed' });
    }

    const addUserModal = document.getElementById('addUserModal');
    if (addUserModal) {
        addUserModal.addEventListener('show.bs.modal', function () {
            const modalBody = document.getElementById('addUserModalBody');
            modalBody.innerHTML = `
                <div class="text-center text-muted py-3">
                    <div class="spinner-border text-dark"></div>
                    <p class="mt-2 mb-0 small">Loading form...</p>
                </div>`;
            fetch(window.addUserUrl)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const form = doc.querySelector('form');
                    if (form) {
                        form.action = window.addUserUrl;
                        const submit = form.querySelector('button[type="submit"]');
                        if (submit) submit.classList.add('btn-warning', 'fw-semibold');
                        modalBody.innerHTML = '';
                        modalBody.appendChild(form);
                    } else {
                        modalBody.innerHTML = '<p class="text-danger">Could not load add form.</p>';
                    }
                })
                .catch(() => modalBody.innerHTML = '<p class="text-danger">Failed to load form.</p>');
        });
    }

    document.querySelectorAll('[id^="editUserModal"]').forEach(modal => {
        modal.addEventListener('show.bs.modal', function (event) {
            const id = this.id.replace('editUserModal', '');
            const modalBody = document.getElementById('editUserModalBody' + id);
            modalBody.innerHTML = `
                <div class="text-center text-muted py-3">
                    <div class="spinner-border text-dark"></div>
                    <p class="mt-2 mb-0 small">Loading form...</p>
                </div>`;

            const button = event.relatedTarget;
            const url = button?.getAttribute('data-url') || window.editUserUrlTemplate.replace('ID_REPLACE', id);

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const form = doc.querySelector('form');
                    if (form) {
                        form.action = url;
                        const submit = form.querySelector('button[type="submit"]');
                        if (submit) submit.classList.add('btn-warning', 'fw-semibold');
                        modalBody.innerHTML = '';
                        modalBody.appendChild(form);
                    } else {
                        modalBody.innerHTML = '<p class="text-danger">Could not load edit form.</p>';
                    }
                })
                .catch(() => modalBody.innerHTML = '<p class="text-danger">Failed to load form.</p>');
        });
    });

    // Validation rules
    const validationRules = {
        fullname: {
            regex: /^[a-zA-Z\s]+$/,
            messages: [
                "Full name can only contain letters and spaces.",
                "This field cannot be blank."
            ]
        },
        email: {
            regex: /^[^@\s]+@[^@\s]+\.[^@\s]+$/,
            messages: [
                "Please enter a valid email address.",
                "Email cannot be blank."
            ]
        },
        password: {
            regex: /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/,
            messages: [
                "Password must be at least 6 characters long.",
                "Password must include both letters and numbers."
            ]
        },
        phonenumber: {
            regex: /^\+?\d{7,15}$/,
            messages: [
                "Enter a valid phone number (7–15 digits, optional + at start)."
            ]
        },
        address: {
            regex: /^[a-zA-Z0-9\s,.\-]*$/,
            messages: [
                "Address can only contain letters, numbers, spaces, commas, dots, and dashes."
            ]
        }
    };

    const attachValidation = (input, rule) => {
        const errorBox = document.createElement('div');
        errorBox.classList.add('live-validation', 'mt-1');
        input.insertAdjacentElement('afterend', errorBox);

        input.addEventListener('input', () => {
            const value = input.value.trim();
            const isEmpty = value.length === 0;
            const isValid = rule.regex.test(value);

            if (isEmpty) {
                errorBox.innerHTML = `<div class="text-danger small">❌ ${rule.messages[1] || "This field cannot be blank."}</div>`;
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                return;
            }

            if (isValid) {
                errorBox.innerHTML = `<div class="text-success small">✅ Looks good!</div>`;
                input.classList.add('is-valid');
                input.classList.remove('is-invalid');
            } else {
                const list = rule.messages.map(m => `<div class="text-danger small">❌ ${m}</div>`).join('');
                errorBox.innerHTML = list;
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
            }
        });
    };

    const observer = new MutationObserver(() => {
        const inputs = document.querySelectorAll('form input');
        inputs.forEach(input => {
            const name = input.name.toLowerCase();
            if (validationRules[name] && !input.dataset.validated) {
                input.dataset.validated = "true";
                attachValidation(input, validationRules[name]);
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });

    document.querySelectorAll('form input').forEach(input => {
        const name = input.name.toLowerCase();
        if (validationRules[name] && !input.dataset.validated) {
            input.dataset.validated = "true";
            attachValidation(input, validationRules[name]);
        }
    });
});
