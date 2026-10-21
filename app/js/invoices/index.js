app.pages.invoices = app.pages.invoices || {};

app.pages.invoices.index = async function() {
  const addOperationMode = new URLSearchParams(location.search).get('add_operation') === '1';

  const form = document.getElementById('invoiceFilters');
  const grid = document.getElementById('invoicesGrid');
  const count = document.getElementById('invoicesCount');
  const message = document.getElementById('invoicesMessage');
  const pagination = document.getElementById('invoicesPagination');
  const resetButton = document.getElementById('resetInvoiceFilters');

  async function loadCategories() {
    const result = await api.get('categories/index');

    if (!result.ok) {
      return;
    }

    const select = document.getElementById('categoryFilter');

    result.data.categories.forEach(function(category) {
      const option = document.createElement('option');
      option.value = category.id;
      option.textContent = category.name;
      select.appendChild(option);
    });
  }

  function getQuery(page) {
    const formData = new FormData(form);
    const query = new URLSearchParams();

    formData.forEach(function(value, key) {
      if (value !== '') {
        query.set(key, value);
      }
    });

    query.set('page', page);

    return query.toString();
  }

  async function loadInvoices(page = 1) {
    grid.innerHTML = '';
    pagination.innerHTML = '';
    count.textContent = 'جاري تحميل الفواتير...';
    message.textContent = '';

    const result = await api.get('invoices/index?' + getQuery(page));

    if (!result.ok) {
      count.textContent = '';
      message.textContent = result.error || 'تعذر تحميل الفواتير';
      return;
    }

    const invoices = result.data.invoices || [];
    const pageData = result.data.pagination;

    count.textContent = 'تم العثور على ' + pageData.total + ' فاتورة';

    if (addOperationMode && pageData.total > 0) {
      count.textContent += ' — اختار فاتورة لإضافة عملية ';
    }

    if (invoices.length === 0) {
      message.textContent = 'لا توجد فواتير مطابقة للبحث والفلاتر';
      return;
    }

    invoices.forEach(function(invoice) {
      grid.appendChild(createInvoiceCard(invoice));
    });

    renderPagination(pageData);
  }

  function renderPagination(pageData) {
    if (pageData.total_pages <= 1) {
      return;
    }

    const previous = document.createElement('button');
    previous.type = 'button';
    previous.className = 'pagination-button';
    previous.textContent = 'السابق';
    previous.disabled = !pageData.has_prev;

    previous.addEventListener('click', function() {
      loadInvoices(pageData.page - 1);
    });

    const pageNumber = document.createElement('span');
    pageNumber.className = 'pagination-info';
    pageNumber.textContent = pageData.page + ' من ' + pageData.total_pages;

    const next = document.createElement('button');
    next.type = 'button';
    next.className = 'pagination-button';
    next.textContent = 'التالي';
    next.disabled = !pageData.has_next;

    next.addEventListener('click', function() {
      loadInvoices(pageData.page + 1);
    });

    pagination.appendChild(previous);
    pagination.appendChild(pageNumber);
    pagination.appendChild(next);
  }

  form.addEventListener('submit', function(event) {
    event.preventDefault();
    loadInvoices(1);
  });

  resetButton.addEventListener('click', function() {
    form.reset();
    loadInvoices(1);
  });

  await loadCategories();
  await loadInvoices();
};