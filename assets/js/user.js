/* ---------------------------
   USERS TABLE (DataTables)
--------------------------- */
function initUsersTable() {
  $.fn.dataTable.ext.errMode = 'none';


  const tableSelector = '#usersTable';
  const $table = $(tableSelector);


  if (!$table.length) return;


  console.log('✅ initUsersTable running');


  // Destroy any previous instance on this DOM (same logic as stocks)
  if ($.fn.dataTable.isDataTable(tableSelector)) {
    $table.DataTable().destroy();
  }


  const rowCount = $table.find('tbody tr').length;
  const hasData =
    rowCount > 0 &&
    !$table.find('tbody tr td').first().text().includes('No users found');


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
    columnDefs: [
      { targets: '_all', className: 'text-center align-middle' }
    ],
    language: {
      searchPlaceholder: 'Search...',
      search: 'INPUT',
      zeroRecords: 'No users found',
      infoEmpty: 'No users available',
      info: 'Showing START to END of TOTAL users',
      paginate: { previous: 'Prev', next: 'Next' }
    },
    autoWidth: true,
    stateSave: true,
  });


  // Adjust when sidebar toggles (exact same logic pattern as stocks)
  const sidebarToggler = document.getElementById('toggleSidebar');
  if (sidebarToggler && !sidebarToggler.dataset.usersBound) {
    sidebarToggler.dataset.usersBound = 'true';
    sidebarToggler.addEventListener('click', () => {
      setTimeout(() => {
        dt.columns.adjust();
      }, 300);
    });
  }


  // Bonus: auto-adjust on window resize (same as stocks)
  window.addEventListener('resize', () => {
    dt.columns.adjust();
  });
}



/* ---------------------------
   ROLES WARNINGS + VALIDATION
--------------------------- */
function initRoleValidation() {
  document
    .querySelectorAll('form[id^="form_user_"], form[id^="user_"]')
    .forEach(function (form) {
      const roleCheckboxes = form.querySelectorAll(
        'input[type="checkbox"][name$="[roles][]"]'
      );


      if (!roleCheckboxes.length) return;


      // Warn if ROLE_USER is unchecked
      roleCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
          if (checkbox.value === 'ROLE_USER' && !checkbox.checked) {
            const ok = confirm(
              'Removing ROLE_USER is usually not recommended.\n\n' +
                'Do you really want to remove ROLE_USER from this account?'
            );
            if (!ok) {
              checkbox.checked = true; // revert
            }
          }
        });
      });


      // Prevent submit if no roles at all
      form.addEventListener('submit', function (e) {
        const checked = Array.from(roleCheckboxes).filter(cb => cb.checked);
        if (checked.length === 0) {
          e.preventDefault();
          alert(
            'User must have at least one role (e.g., ROLE_USER or ROLE_ADMIN).'
          );
        }
      });
    });
}


/* ---------------------------
   MASTER INIT
--------------------------- */
function initUsersPage() {
  initUsersTable();
  initRoleValidation();
}


// For normal full reloads AND Turbo visits (harmless even with data-turbo=false)
document.addEventListener('DOMContentLoaded', initUsersPage);
document.addEventListener('turbo:load', initUsersPage);






