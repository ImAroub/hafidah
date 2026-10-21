app.pages.invoices = app.pages.invoices || {};

app.pages.invoices.show = async function() {
  const id = new URLSearchParams(location.search).get('id');
  const message = document.getElementById('invoiceDetailsMessage');
  const editLink = document.getElementById('editInvoiceLink');
  const deleteButton = document.getElementById('deleteInvoiceButton');
  const addInvoiceOperation = document.getElementById('addInvoiceOperation');
  const showAllOperations = document.getElementById('showAllOperations');
  const operationsContainer = document.getElementById('invoiceOperations');

  if (!id) {
    message.textContent = 'رقم الفاتورة غير موجود';
    return;
  }

  editLink.href = '?_p=invoices/update&id=' + encodeURIComponent(id);
  showAllOperations.href = '?_p=operations/show&id=' + encodeURIComponent(id);

  const result = await api.get('invoices/show?id=' + encodeURIComponent(id));

  if (!result.ok) {
    message.textContent = result.error || 'تعذر تحميل الفاتورة';
    return;
  }

  const invoice = result.data.invoice;
  const operations = result.data.operations || [];

  if (invoice.item_status === 'returned') {
    addInvoiceOperation.hidden = true;
  } else {
    addInvoiceOperation.hidden = false;
    addInvoiceOperation.href = '?_p=operations/create&invoice_id=' + encodeURIComponent(id);
  }

  const units = {
    days: 'يوم',
    months: 'شهر',
    years: 'سنة'
  };

  const statusNames = {
    active: 'نشطة',
    maintenance: 'قيد الصيانة',
    returned: 'مسترجعة',
    replaced: 'مستبدلة',
    cancelled: 'ملغاة'
  };

  const operationNames = {
    maintenance: 'أُرسل للصيانة',
    maintenance_complete: 'استُلم من الصيانة',
    replacement: 'استُبدل',
    return: 'استُرجع',
    renewal: 'جُدّد',
    cancel: 'أُلغي',
    other: 'عملية أخرى'
  };

  function period(value, unit) {
    if (!value) {
      return 'غير محدد';
    }

    return value + ' ' + (units[unit] || unit);
  }

  document.getElementById('invoiceItemName').textContent = invoice.item_name || '—';
  document.getElementById('itemName').textContent = invoice.item_name || '—';
  document.getElementById('categoryName').textContent = invoice.category_name || 'غير محدد';
  document.getElementById('storeName').textContent = invoice.store_name || 'غير محدد';
  document.getElementById('invoiceCost').textContent = invoice.cost !== null && invoice.cost !== undefined ? Number(invoice.cost).toFixed(2) + ' ريال' : 'غير محددة';
  document.getElementById('purchaseDate').textContent = invoice.purchase_date || 'غير محدد';

  const contactInfo = document.getElementById('contactInfo');
  const contact = (invoice.contact_info || '').trim();

  contactInfo.textContent = contact || 'غير محدد';
  contactInfo.removeAttribute('href');

  if (contact) {
    contactInfo.addEventListener('click', function(event) {
      event.preventDefault();

      if (contact.includes('@')) {
        const confirmed = confirm('هل تريد إرسال بريد إلكتروني إلى ' + contact + '؟');

        if (confirmed) {
          location.href = 'mailto:' + contact;
        }

        return;
      }

      const phone = contact.replace(/[^\d+]/g, '');
      const confirmed = confirm('هل تريد الاتصال بالرقم ' + contact + '؟');

      if (confirmed) {
        location.href = 'tel:' + phone;
      }
    });
  }

  document.getElementById('warrantyPeriod').textContent = period(invoice.warranty_value, invoice.warranty_unit);
  document.getElementById('warrantyEndDate').textContent = invoice.warranty_end_date || 'غير محدد';

  document.getElementById('returnPeriod').textContent = period(invoice.return_value, invoice.return_unit);
  document.getElementById('returnEndDate').textContent = invoice.return_end_date || 'غير محدد';

  document.getElementById('replacementPeriod').textContent = period(invoice.replacement_value, invoice.replacement_unit);
  document.getElementById('replacementEndDate').textContent = invoice.replacement_end_date || 'غير محدد';

  document.getElementById('renewalPeriod').textContent = period(invoice.renewal_value, invoice.renewal_unit);
  document.getElementById('renewalEndDate').textContent = invoice.renewal_end_date || 'غير محدد';

  const invoiceStatus = document.getElementById('invoiceStatus');

  invoiceStatus.textContent = statusNames[invoice.item_status] || 'غير محددة';
  invoiceStatus.className = 'invoice-status invoice-status--' + (invoice.item_status || 'active');

  operationsContainer.innerHTML = '';

  if (operations.length === 0) {
    operationsContainer.innerHTML = '<p class="empty-operations">لا توجد عمليات مسجلة على هذه الفاتورة</p>';
  } else {
    operations.forEach(function(operation) {
      const link = document.createElement('a');
      link.className = 'invoice-operation-row';
      link.href = '?_p=operations/show&id=' + encodeURIComponent(id);

      const name = document.createElement('strong');
      name.textContent = operationNames[operation.operation_type] || 'عملية أخرى';

      const date = document.createElement('span');
      date.textContent = operation.operation_date ? formatDate(operation.operation_date) : 'غير محدد';

      link.appendChild(name);
      link.appendChild(date);

      operationsContainer.appendChild(link);
    });
  }

  const fileLink = document.getElementById('invoiceFileLink');
  const fileImage = document.getElementById('invoiceFileImage');
  const noFile = document.getElementById('noInvoiceFile');
  const imageModal = document.getElementById('invoiceImageModal');
  const largeImage = document.getElementById('invoiceLargeImage');
  const closeImageButton = document.getElementById('closeInvoiceImage');

  if (invoice.invoice_file_path) {
    const fileUrl = api.url('uploads/invoices/' + encodeURIComponent(invoice.invoice_file_path));

    fileImage.src = fileUrl;
    fileLink.href = fileUrl;
    fileLink.hidden = false;
    noFile.hidden = true;

    fileLink.addEventListener('click', function(event) {
      event.preventDefault();

      largeImage.src = fileUrl;
      imageModal.hidden = false;
    });

    closeImageButton.addEventListener('click', function() {
      imageModal.hidden = true;
    });

    imageModal.addEventListener('click', function(event) {
      if (event.target === imageModal) {
        imageModal.hidden = true;
      }
    });
  } else {
    fileLink.hidden = true;
    fileLink.removeAttribute('href');

    fileImage.removeAttribute('src');

    noFile.hidden = false;
  }

  deleteButton.addEventListener('click', async function() {
    const confirmed = confirm('هل أنت متأكد من حذف هذه الفاتورة؟');

    if (!confirmed) {
      return;
    }

    deleteButton.disabled = true;
    message.textContent = 'جاري حذف الفاتورة...';

    const deleteResult = await api.post('invoices/delete', {id: Number(id)});

    if (!deleteResult.ok) {
      message.textContent = deleteResult.error || 'تعذر حذف الفاتورة';
      deleteButton.disabled = false;
      return;
    }

    goTo('invoices');
  });

  message.textContent = '';
};