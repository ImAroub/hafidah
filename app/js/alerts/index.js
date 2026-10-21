app.pages.alerts = app.pages.alerts || {};

app.pages.alerts.index = async function() {
  const total = document.getElementById('alertsTotal');
  const message = document.getElementById('alertsMessage');
  const table = document.getElementById('alertsTable');
  const list = document.getElementById('alertsList');

  message.textContent = '';
  list.innerHTML = '';

  const result = await api.get('alerts/index');

  if (!result.ok) {
    total.textContent = '0';
    table.hidden = true;
    message.textContent = result.error || 'تعذر تحميل التنبيهات';
    return;
  }

  const alerts = result.data.alerts || [];

  total.textContent = result.data.total;

  if (alerts.length === 0) {
    table.hidden = true;
    message.textContent = 'لا توجد تنبيهات خلال 14 يوم القادمة';
    return;
  }

  table.hidden = false;

  const alertNames = {
    warranty: 'الضمان',
    return: 'الاسترجاع',
    replacement: 'الاستبدال',
    renewal: 'التجديد'
  };

  alerts.forEach(function(alert) {
    const row = document.createElement('a');

    row.className = 'alert-row';
    row.href = '?_p=invoices/show&id=' + encodeURIComponent(alert.invoice_id);

    const invoiceName = document.createElement('strong');
    invoiceName.textContent = alert.item_name || 'فاتورة بدون اسم';

    const alertType = document.createElement('span');
    alertType.textContent = alertNames[alert.alert_type] || 'تنبيه';

    const endDate = document.createElement('span');
    endDate.textContent = formatDate(alert.end_date);

    const remaining = document.createElement('span');
    remaining.textContent = getDaysLeftText(Number(alert.days_left));

    row.appendChild(invoiceName);
    row.appendChild(alertType);
    row.appendChild(endDate);
    row.appendChild(remaining);

    list.appendChild(row);
  });


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
};