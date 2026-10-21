app.pages.operations = app.pages.operations || {};

app.pages.operations.index = async function () {
  const form = document.getElementById('operationFilters');
  const grid = document.getElementById('operationsGrid');
  const count = document.getElementById('operationsCount');
  const message = document.getElementById('operationsMessage');
  const pagination = document.getElementById('operationsPagination');
  const resetButton = document.getElementById('resetOperationFilters');

  const operationNames = {
    maintenance: 'إرسال للصيانة',
    maintenance_complete: 'استلام من الصيانة',
    replacement: 'استبدال',
    return: 'استرجاع',
    renewal: 'تجديد',
    cancel: 'إلغاء',
    other: 'أخرى'
  };

  const statusNames = {
    active: 'نشطة',
    maintenance: 'صيانة',
    returned: 'مسترجعة',
    replaced: 'مستبدلة',
    cancelled: 'ملغاة'
  };

  async function loadCategories() {
    const result = await api.get('categories/index');

    if (!result.ok) {
      return;
    }

    const select = document.getElementById('operationCategoryFilter');

    result.data.categories.forEach(function (category) {
      const option = document.createElement('option');

      option.value = category.id;
      option.textContent = category.name;

      select.appendChild(option);
    });
  }

  function getQuery(page) {
    const formData = new FormData(form);
    const query = new URLSearchParams();

    formData.forEach(function (value, key) {
      if (value !== '') {
        query.set(key, value);
      }
    });

    query.set('page', page);

    return query.toString();
  }

  function createOperationCard(invoice) {
    const card = document.createElement('article');
    card.className = 'dashboard-card is-clickable';

    const header = document.createElement('div');
    header.className = 'dashboard-card__header';

    const title = document.createElement('h3');
    title.textContent = invoice.item_name || 'منتج بدون اسم';

    const badges = document.createElement('div');
    badges.className = 'card-badges';

    const status = document.createElement('span');
    status.className = 'card-badge card-badge--' + (invoice.item_status || 'active');
    status.textContent = statusNames[invoice.item_status] || 'نشطة';

    badges.appendChild(status);
    header.appendChild(title);
    header.appendChild(badges);

    const body = document.createElement('div');
    body.className = 'dashboard-card__body';

    const store = document.createElement('p');
    store.textContent = 'مكان الشراء: ' + (invoice.store_name || 'غير محدد');

    const category = document.createElement('p');
    category.textContent = 'التصنيف: ' + (invoice.category_name || 'غير محدد');

    const operationCount = document.createElement('p');
    operationCount.textContent = 'عدد العمليات: ' + invoice.operation_count;

    const lastOperation = document.createElement('p');
    lastOperation.textContent = 'آخر عملية: ' + (operationNames[invoice.last_operation_type] || 'غير محددة');

    body.appendChild(store);
    body.appendChild(category);
    body.appendChild(operationCount);
    body.appendChild(lastOperation);

    card.appendChild(header);
    card.appendChild(body);

    card.addEventListener('click', function () {
        location.href = '?_p=operations/show&id=' + encodeURIComponent(invoice.invoice_id);
        });

    return card;
  }

  async function loadOperations(page = 1) {
    grid.innerHTML = '';
    pagination.innerHTML = '';
    count.textContent = 'جاري تحميل العمليات...';
    message.textContent = '';

    const result = await api.get('operations/index?' + getQuery(page));

    if (!result.ok) {
      count.textContent = '';
      message.textContent = result.error || 'تعذر تحميل العمليات';
      return;
    }

    const invoices = result.data.invoices || [];
    const pageData = result.data.pagination;

    count.textContent = 'تم العثور على ' + pageData.total + ' فواتير لديها عمليات';

    if (invoices.length === 0) {
      message.textContent = 'لا توجد عمليات مطابقة للبحث والفلاتر';
      return;
    }

    invoices.forEach(function (invoice) {
      grid.appendChild(createOperationCard(invoice));
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

    previous.addEventListener('click', function () {
      loadOperations(pageData.page - 1);
    });

    const pageNumber = document.createElement('span');
    pageNumber.className = 'pagination-info';
    pageNumber.textContent = pageData.page + ' من ' + pageData.total_pages;

    const next = document.createElement('button');
    next.type = 'button';
    next.className = 'pagination-button';
    next.textContent = 'التالي';
    next.disabled = !pageData.has_next;

    next.addEventListener('click', function () {
      loadOperations(pageData.page + 1);
    });

    pagination.appendChild(previous);
    pagination.appendChild(pageNumber);
    pagination.appendChild(next);
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    loadOperations(1);
  });

  resetButton.addEventListener('click', function () {
    form.reset();
    loadOperations(1);
  });

  await loadCategories();
  await loadOperations();
};