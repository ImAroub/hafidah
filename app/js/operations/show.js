app.pages.operations = app.pages.operations || {};

app.pages.operations.show = async function() {
  const id = new URLSearchParams(location.search).get('id');
  const message = document.getElementById('operationDetailsMessage');
  const list = document.getElementById('operationsList');
  const addLink = document.getElementById('addOperationLink');
  const invoiceName = document.getElementById('operationInvoiceName');
  const invoiceStatus = document.getElementById('operationInvoiceStatus');
  const imageModal = document.getElementById('operationImageModal');
  const largeImage = document.getElementById('operationLargeImage');
  const closeImageButton = document.getElementById('closeOperationImage');

  if (!id) {
    message.textContent = 'رقم الفاتورة غير موجود';
    return;
  }

  const result = await api.get('operations/show?id=' + encodeURIComponent(id));

  if (!result.ok) {
    message.textContent = result.error || 'تعذر تحميل العمليات';
    return;
  }

  const invoice = result.data.invoice;
  const operations = result.data.operations || [];

  if (invoice.item_status === 'returned') {
    addLink.hidden = true;
  } else {
    addLink.hidden = false;
    addLink.href = '?_p=operations/create&invoice_id=' + encodeURIComponent(id);
  }

  const operationNames = {
    maintenance: 'أُرسل للصيانة',
    maintenance_complete: 'استُلم من الصيانة',
    replacement: 'استُبدل',
    return: 'استُرجع',
    renewal: 'جُدّد',
    cancel: 'أُلغي',
    other: 'عملية أخرى'
  };

  const statusNames = {
    active: 'نشطة',
    maintenance: 'قيد الصيانة',
    returned: 'مسترجعة',
    replaced: 'مستبدلة',
    cancelled: 'ملغاة'
  };

  invoiceName.textContent = invoice.item_name || 'منتج بدون اسم';
  invoiceStatus.textContent = statusNames[invoice.item_status] || 'غير محددة';
  invoiceStatus.className = 'invoice-status invoice-status--' + (invoice.item_status || 'active');

  list.innerHTML = '';

  if (operations.length === 0) {
    list.innerHTML = '<p class="empty-operations">لا توجد عمليات مسجلة على هذه الفاتورة</p>';
  } else {
    operations.forEach(function(operation) {
      list.appendChild(createOperationCard(operation));
    });
  }

  closeImageButton.addEventListener('click', function() {
    imageModal.hidden = true;
  });

  imageModal.addEventListener('click', function(event) {
    if (event.target === imageModal) {
      imageModal.hidden = true;
    }
  });

  function createOperationCard(operation) {
    const card = document.createElement('article');
    card.className = 'operation-card';

    const header = document.createElement('div');
    header.className = 'operation-card__header ' + getOperationColorClass(operation.operation_type);

    const title = document.createElement('h3');
    title.textContent = operationNames[operation.operation_type] || 'عملية أخرى';

    header.appendChild(title);

    const body = document.createElement('div');
    body.className = 'operation-card__body';

    const details = document.createElement('div');
    details.className = 'operation-card__details';

    details.appendChild(createOperationDetail('تاريخ العملية', formatDate(operation.operation_date)));
    details.appendChild(createOperationDetail('الانتهاء المتوقع', operation.expected_end_date ? formatDate(operation.expected_end_date) : 'غير محدد'));
    details.appendChild(createOperationDetail('التكلفة', operation.cost !== null ? Number(operation.cost).toFixed(2) + ' ريال' : 'غير محددة'));

    const notes = document.createElement('div');
    notes.className = 'operation-card__notes';

    const notesTitle = document.createElement('strong');
    notesTitle.textContent = 'الملاحظات';

    const notesText = document.createElement('p');
    notesText.textContent = operation.notes || 'لا توجد ملاحظات';

    notes.appendChild(notesTitle);
    notes.appendChild(notesText);

    const file = document.createElement('div');
    file.className = 'operation-card__file';

    const fileTitle = document.createElement('strong');
    fileTitle.textContent = 'صورة العملية';

    file.appendChild(fileTitle);

    if (operation.operation_file_path) {
      const fileUrl = api.url('uploads/operations/' + encodeURIComponent(operation.operation_file_path));
      const image = document.createElement('img');

      image.className = 'operation-card__image';
      image.src = fileUrl;
      image.alt = 'صورة العملية';

      image.addEventListener('click', function() {
        largeImage.src = fileUrl;
        imageModal.hidden = false;
      });

      file.appendChild(image);
    } else {
      const noFile = document.createElement('p');
      noFile.className = 'operation-card__no-file';
      noFile.textContent = 'لا توجد صورة مرفقة';

      file.appendChild(noFile);
    }

    body.appendChild(details);
    body.appendChild(notes);
    body.appendChild(file);

    const actions = document.createElement('div');
    actions.className = 'invoice-actions operation-card__actions';

    const editLink = document.createElement('a');
    editLink.className = 'invoice-action-button';
    editLink.href = '?_p=operations/update&id=' + encodeURIComponent(operation.id) + '&invoice_id=' + encodeURIComponent(id);
    editLink.textContent = 'تعديل';

    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'invoice-action-button invoice-action-button--danger';
    deleteButton.textContent = 'حذف';

    deleteButton.addEventListener('click', async function() {
      const confirmed = confirm('هل أنت متأكد من حذف هذه العملية؟');

      if (!confirmed) {
        return;
      }

      deleteButton.disabled = true;

      const deleteResult = await api.post('operations/delete', {id: Number(operation.id)});

      if (!deleteResult.ok) {
        message.textContent = deleteResult.error || 'تعذر حذف العملية';
        deleteButton.disabled = false;
        return;
      }

      location.href = '?_p=operations/show&id=' + encodeURIComponent(id);
    });

    actions.appendChild(editLink);
    actions.appendChild(deleteButton);

    card.appendChild(header);
    card.appendChild(body);
    card.appendChild(actions);

    return card;
  }

  function createOperationDetail(label, value) {
    const paragraph = document.createElement('p');
    const labelElement = document.createElement('strong');
    const valueElement = document.createElement('span');

    labelElement.textContent = label + ': ';
    valueElement.textContent = value;

    paragraph.appendChild(labelElement);
    paragraph.appendChild(valueElement);

    return paragraph;
  }

  function getOperationColorClass(type) {
    const classes = {
      maintenance: 'invoice-status--maintenance',
      maintenance_complete: 'invoice-status--active',
      replacement: 'invoice-status--replaced',
      return: 'invoice-status--returned',
      renewal: 'invoice-status--active',
      cancel: 'invoice-status--cancelled',
      other: 'invoice-status--returned'
    };

    return classes[type] || 'invoice-status--returned';
  }

  message.textContent = '';
};