app.pages.dashboard = app.pages.dashboard || {};

function createDashboardCard(title, details, href, type) {
  const card = document.createElement(href ? 'a' : 'div');

  card.className = 'dashboard-card dashboard-card--' + type;

  if (href) {
    card.classList.add('is-clickable');
    card.href = href;
  }

  const header = document.createElement('div');
  header.className = 'dashboard-card__header';

  const heading = document.createElement('h3');
  heading.textContent = title;

  header.appendChild(heading);

  const body = document.createElement('div');
  body.className = 'dashboard-card__body';

  details.forEach(function(detail) {
    const paragraph = document.createElement('p');
    paragraph.textContent = detail;
    body.appendChild(paragraph);
  });

  card.appendChild(header);
  card.appendChild(body);

  return card;
}


app.pages.dashboard.index = async function() {
  const invoicePeriodFilter = document.getElementById('invoicePeriodFilter');
  const invoiceCategoryFilter = document.getElementById('invoiceCategoryFilter');

  const operationPeriodFilter = document.getElementById('operationPeriodFilter');
  const operationTypeFilter = document.getElementById('operationTypeFilter');

  await loadDashboardCategories();

  if (invoicePeriodFilter) invoicePeriodFilter.addEventListener('change', loadDashboard);
  if (invoiceCategoryFilter) invoiceCategoryFilter.addEventListener('change', loadDashboard);
  if (operationPeriodFilter) operationPeriodFilter.addEventListener('change', loadDashboard);
  if (operationTypeFilter) operationTypeFilter.addEventListener('change', loadDashboard);

  await loadDashboard();


  async function loadDashboardCategories() {
    const result = await api.get('categories/index');

    if (!result.ok) {
      console.error(result.error || 'تعذر تحميل التصنيفات');
      return;
    }

    const categories = result.data.categories || [];

    categories.forEach(function(category) {
      const invoiceOption = document.createElement('option');
      invoiceOption.value = category.id;
      invoiceOption.textContent = category.name;

      if (invoiceCategoryFilter) {
        invoiceCategoryFilter.appendChild(invoiceOption);
      }
    });
  }


  async function loadDashboard() {
    const params = new URLSearchParams();

    params.set('invoice_period', invoicePeriodFilter ? invoicePeriodFilter.value : 'all');
    params.set('invoice_category_id', invoiceCategoryFilter ? invoiceCategoryFilter.value : '');

    params.set('operation_period', operationPeriodFilter ? operationPeriodFilter.value : 'all');
    params.set('operation_type', operationTypeFilter ? operationTypeFilter.value : '');

    const result = await api.get('dashboard/index?' + params.toString());

    if (!result.ok) {
      console.error(result.error || 'تعذر تحميل بيانات الداشبورد');
      return;
    }

    document.getElementById('totalInvoices').textContent = result.data.total_invoices;
    document.getElementById('totalOperations').textContent = result.data.total_operations;
    document.getElementById('totalAlerts').textContent = result.data.total_alerts;

    renderDashboardAlerts(result.data.recent_alerts || []);
    renderRecentInvoices(result.data.recent_invoices || []);
    renderRecentOperations(result.data.recent_operations || []);
  }


  function renderDashboardAlerts(alerts) {
    const list = document.getElementById('dashboardAlertsList');

    list.innerHTML = '';

    if (alerts.length === 0) {
      const empty = document.createElement('p');
      empty.className = 'dashboard-alerts-empty';
      empty.textContent = 'لا توجد تنبيهات قريبة';

      list.appendChild(empty);
      return;
    }

    const alertNames = {
      warranty: 'الضمان',
      return: 'الاسترجاع',
      replacement: 'الاستبدال',
      renewal: 'التجديد'
    };

    alerts.forEach(function(alert) {
      const row = document.createElement('div');
      row.className = 'dashboard-alert-row';

      const invoiceName = document.createElement('span');
      invoiceName.textContent = alert.item_name || 'فاتورة بدون اسم';

      const alertType = document.createElement('span');
      alertType.textContent = alertNames[alert.alert_type] || 'تنبيه';

      const daysLeft = document.createElement('span');
      daysLeft.textContent = getDaysLeftText(Number(alert.days_left));

      row.appendChild(invoiceName);
      row.appendChild(alertType);
      row.appendChild(daysLeft);

      list.appendChild(row);
    });
  }


  function getDaysLeftText(days) {
    if (days === 0) {
      return 'ينتهي اليوم';
    }

    if (days === 1) {
      return 'باقي يوم';
    }

    if (days === 2) {
      return 'باقي يومان';
    }

    return 'باقي ' + days + ' أيام';
  }


  function renderRecentInvoices(invoices) {
    const recentInvoices = document.getElementById('recentInvoices');

    if (!recentInvoices) {
      return;
    }

    recentInvoices.querySelectorAll('.dashboard-card--invoice').forEach(function(card) {
      card.remove();
    });

    const addInvoiceCard = recentInvoices.querySelector('.add-card, .dashboard-card--add');

    invoices.forEach(function(invoice) {
      const invoiceCard = createDashboardCard(
        invoice.item_name,
        [
          'مكان الشراء: ' + (invoice.store_name || 'غير محدد'),
          'تاريخ الشراء: ' + formatDate(invoice.purchase_date)
        ],
        APP_CONFIG.APP_DIR + 'invoices/show?id=' + encodeURIComponent(invoice.id),
        'invoice'
      );

      recentInvoices.insertBefore(invoiceCard, addInvoiceCard);
    });
  }


  function renderRecentOperations(operations) {
    const recentOperations = document.getElementById('recentOperations');

    if (!recentOperations) {
      return;
    }

    recentOperations.querySelectorAll('.dashboard-card--operation').forEach(function(card) {
      card.remove();
    });

    const addOperationCard = recentOperations.querySelector('.add-card, .dashboard-card--add');

    const operationNames = {
      maintenance: 'أُرسل للصيانة',
      maintenance_complete: 'استُلم من الصيانة',
      replacement: 'استُبدل',
      return: 'استُرجع',
      renewal: 'جُدّد',
      cancel: 'أُلغي',
      other: 'عملية أخرى'
    };

    operations.forEach(function(operation) {
      const operationCard = createDashboardCard(
        operation.item_name || 'منتج بدون اسم',
        [
          'نوع العملية: ' + (operationNames[operation.operation_type] || 'عملية أخرى'),
          'تاريخ العملية: ' + formatDate(operation.operation_date),
          'التكلفة: ' + (operation.cost !== null ? Number(operation.cost).toFixed(2) + ' ريال' : 'غير محددة')
        ],
        '?_p=operations/show&id=' + encodeURIComponent(operation.invoice_id),
        'operation'
      );

      recentOperations.insertBefore(operationCard, addOperationCard);
    });
  }
};