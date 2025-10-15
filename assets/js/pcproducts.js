/*
  PC Products modal helpers
  - Prevents duplicate script initialization
  - Ensures file preview works immediately after injecting form
  - Prevents multiple form submissions (safe with validation)
*/

import $ from 'jquery';
import 'datatables.net-bs5';
import 'bootstrap';

if (!window.pcProductsScriptInit) {
  window.pcProductsScriptInit = true;
  console.log("✅ PC Products script initialized");
} else {
  console.log("⚠️ PC Products script already initialized - skipping re-init");
}

document.addEventListener("DOMContentLoaded", function () {
  $.fn.dataTable.ext.errMode = 'none';
  const $table = $('#pcproductsTable');
  const rowCount = $table.find('tbody tr').length;
  const hasData = rowCount > 0 && !$table.find('tbody tr td').first().text().includes('No records');

  if (hasData && !$.fn.DataTable.isDataTable('#pcproductsTable')) {
    $('#pcproductsTable').DataTable({
      responsive: false,
      paging: true,
      pageLength: 10,
      lengthChange: false,
      searching: true,
      order: [[0, 'asc']],
      columnDefs: [{ targets: '_all', className: 'text-center' }],
      language: {
        searchPlaceholder: "Search...",
        search: "_INPUT_",
        zeroRecords: "No matching products found",
        infoEmpty: "No products available",
        info: "Showing _START_ to _END_ of _TOTAL_ products",
        paginate: { previous: "Prev", next: "Next" }
      },
      autoWidth: false,
      scrollX: false,
    });
  }

  function setupImagePreview(form) {
    let fileInput = form.querySelector('input[type="file"]');
    if (!fileInput) return;

    const fresh = fileInput.cloneNode(true);
    fileInput.parentNode.replaceChild(fresh, fileInput);
    fileInput = fresh;
    fileInput.setAttribute('accept', 'image/*');

    let preview = form.querySelector('.preview-img');
    if (!preview) {
      preview = document.createElement('img');
      preview.classList.add('preview-img', 'empty');
      fileInput.parentNode.insertBefore(preview, fileInput);
    }

    let error = form.querySelector('.file-error');
    if (!error) {
      error = document.createElement('div');
      error.classList.add('file-error');
      fileInput.parentNode.insertBefore(error, fileInput.nextSibling);
    }

    fileInput.addEventListener('change', e => {
      const file = e.target.files && e.target.files[0];
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = evt => {
          preview.src = evt.target.result;
          preview.classList.remove('empty');
        };
        reader.readAsDataURL(file);
        error.textContent = '';
      } else {
        preview.src = '';
        preview.classList.add('empty');
        error.textContent = '❌ Please select a valid image file.';
        e.target.value = '';
      }
    }, { passive: true });
  }

  function attachSafeSubmit(form) {
    if (!form || form.dataset.safeSubmitAttached === "true") return;
    form.dataset.safeSubmitAttached = "true";

    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
      }

      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        if (!btn.dataset.origHtml) btn.dataset.origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
      }
    }, { passive: false });
  }

  async function loadFormIntoModal(modalBody, url, formActionPath) {
    try {
      modalBody.innerHTML = '';
      const res = await fetch(url, { credentials: 'same-origin' });
      const html = await res.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const form = doc.querySelector('form');

      if (!form) {
        modalBody.innerHTML = '<p class="text-danger">Could not load form.</p>';
        return null;
      }

      if (formActionPath) form.action = formActionPath;

      modalBody.style.transition = 'opacity 0.18s ease';
      modalBody.style.opacity = '0';

      setTimeout(() => {
        modalBody.innerHTML = '';
        modalBody.appendChild(form);
        requestAnimationFrame(() => {
          modalBody.style.opacity = '1';
          setupImagePreview(form);
          attachSafeSubmit(form);
        });
      }, 120);

      return form;
    } catch (err) {
      modalBody.innerHTML = '<p class="text-danger">Failed to load form.</p>';
      console.error('Failed to load form:', err);
      return null;
    }
  }

  // Attach edit modal
  document.querySelectorAll('[id^="editModal"]').forEach(modal => {
    if (modal.dataset.listenerAdded === 'true') return;
    modal.dataset.listenerAdded = 'true';

    modal.addEventListener('show.bs.modal', function () {
      const id = this.id.replace('editModal', '');
      const modalBody = document.getElementById('editModalBody' + id);
      if (!modalBody) return;

      const url = `/pcproducts/${id}/edit`;
      const actionPath = `/pcproducts/${id}/edit`;
      loadFormIntoModal(modalBody, url, actionPath);
    });
  });

  // Add modal
  const addProductModal = document.getElementById('addProductModal');
  if (addProductModal && addProductModal.dataset.listenerAdded !== 'true') {
    addProductModal.dataset.listenerAdded = 'true';

    addProductModal.addEventListener('show.bs.modal', function () {
      const modalBody = document.getElementById('addProductModalBody');
      if (!modalBody) return;

      if (modalBody.dataset.loaded === 'true') return;

      const url = modalBody.dataset.newUrl;
      const actionPath = modalBody.dataset.newAction;
      loadFormIntoModal(modalBody, url, actionPath).then(form => {
        if (form) modalBody.dataset.loaded = 'true';
      });
    });
  }
});
