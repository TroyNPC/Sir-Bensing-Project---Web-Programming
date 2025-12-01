 let currentCategory = 'All';
  const productsPerPage = 10;
  let currentPage = 1;

  function filterCategory(cat) {
    currentCategory = cat;
    document.querySelectorAll('.btn-category').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    currentPage = 1;
    applyFilters();
  }

  function filterBrand() { 
    currentPage = 1;
    applyFilters(); 
  }

  function applyFilters() {
    const brandVal = document.getElementById('brandFilter').value;
    const allCards = Array.from(document.querySelectorAll('.product-card'));
    let filtered = allCards.filter(card => {
      const cat = card.getAttribute('data-category');
      const br = card.getAttribute('data-brand');
      const catMatch = (currentCategory === 'All') || (cat === currentCategory);
      const brandMatch = !brandVal || (br === brandVal);
      return catMatch && brandMatch;
    });

    // Hide all first
    allCards.forEach(c => c.style.display = 'none');

    // Pagination
    const totalPages = Math.ceil(filtered.length / productsPerPage);
    const start = (currentPage - 1) * productsPerPage;
    const end = start + productsPerPage;
    filtered.slice(start, end).forEach(c => c.style.display = '');

    renderPagination(totalPages);
  }
function renderPagination(totalPages) {
    const container = document.getElementById('pagination');
    container.innerHTML = '';

    // Numbered buttons (arrows + sliding window)
    const maxVisible = 3; // max visible page buttons at a time
    let startPage = Math.max(1, currentPage - 1);
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);

    // Adjust if at end
    if(endPage - startPage + 1 < maxVisible){
        startPage = Math.max(1, endPage - maxVisible + 1);
    }

    if(currentPage > 1){
        const prevBtn = document.createElement('button');
        prevBtn.innerHTML = '&laquo;';
        prevBtn.addEventListener('click', () => {
            currentPage--;
            applyFilters();
        });
        container.appendChild(prevBtn);
    }

    for(let i=startPage; i<=endPage; i++){
        const btn = document.createElement('button');
        btn.innerText = i;
        btn.classList.toggle('active', i === currentPage);
        btn.addEventListener('click', () => {
            currentPage = i;
            applyFilters();
        });
        container.appendChild(btn);
    }

    if(currentPage < totalPages){
        const nextBtn = document.createElement('button');
        nextBtn.innerHTML = '&raquo;';
        nextBtn.addEventListener('click', () => {
            currentPage++;
            applyFilters();
        });
        container.appendChild(nextBtn);
    }

    // Update dropdown
    const pageSelect = document.getElementById('pageSelect');
    if(totalPages <= 1){
        pageSelect.style.display = 'none'; // hide if only 1 page
    } else {
        pageSelect.style.display = 'block';
        pageSelect.innerHTML = '';
        for(let i=1; i<=totalPages; i++){
            const option = document.createElement('option');
            option.value = i;
            option.innerText = 'Page ' + i;
            if(i === currentPage) option.selected = true;
            pageSelect.appendChild(option);
        }
    }
}


// Dropdown page jump
function goToPage(page) {
    currentPage = parseInt(page);
    applyFilters();
}



  // Initial load
  applyFilters();