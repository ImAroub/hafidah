app.pages.invoices = app.pages.invoices || {};

app.pages.invoices.update = async function() {
  const id = new URLSearchParams(location.search).get('id');
  const form = document.getElementById('editInvoiceForm');
  const message = document.getElementById('editInvoiceMessage');
  const backToInvoiceLink = document.getElementById('backToInvoiceLink');

  if (!id) {
    message.textContent = 'رقم الفاتورة غير موجود';
    return;
  }

  backToInvoiceLink.href = '?_p=invoices/show&id=' + encodeURIComponent(id);

  const categoriesResult = await api.get('categories/index');

  if (!categoriesResult.ok) {
    message.textContent = categoriesResult.error || 'تعذر تحميل التصنيفات';
    return;
  }

  categoriesResult.data.categories.forEach(function(category) {
    const option = document.createElement('option');

    option.value = category.id;
    option.textContent = category.name;

    form.elements.category_id.appendChild(option);
  });

  const invoiceResult = await api.get('invoices/show?id=' + encodeURIComponent(id));

  if (!invoiceResult.ok) {
    message.textContent = invoiceResult.error || 'تعذر تحميل الفاتورة';
    return;
  }

  const invoice = invoiceResult.data.invoice;

  form.elements.id.value = invoice.id;
  form.elements.item_name.value = invoice.item_name || '';
  form.elements.category_id.value = invoice.category_id;
  form.elements.store_name.value = invoice.store_name || '';
  form.elements.contact_info.value = invoice.contact_info || '';
  form.elements.purchase_date.value = invoice.purchase_date || '';
  form.elements.cost.value = invoice.cost !== null && invoice.cost !== undefined ? invoice.cost : '';

  form.elements.warranty_value.value = invoice.warranty_value || 0;
  form.elements.warranty_unit.value = invoice.warranty_unit || '';

  form.elements.return_value.value = invoice.return_value || 0;
  form.elements.return_unit.value = invoice.return_unit || '';

  form.elements.replacement_value.value = invoice.replacement_value || 0;
  form.elements.replacement_unit.value = invoice.replacement_unit || '';

  form.elements.renewal_value.value = invoice.renewal_value || 0;
  form.elements.renewal_unit.value = invoice.renewal_unit || '';

  message.textContent = '';
};

async function updateInvoice(event) {
  event.preventDefault();

  const form = event.target;
  const message = document.getElementById('editInvoiceMessage');
  const submitButton = form.querySelector('button[type="submit"]');

  message.textContent = 'جاري حفظ التعديلات...';
  submitButton.disabled = true;

  const formData = new FormData(form);
  const result = await api.upload('invoices/update', formData);

  submitButton.disabled = false;

  if (!result.ok) {
    message.textContent = result.error || 'تعذر تعديل الفاتورة';
    return;
  }

  goTo('invoices/show', {id: result.data.invoice_id});
}