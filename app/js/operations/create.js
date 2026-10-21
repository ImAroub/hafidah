app.pages.operations = app.pages.operations || {};

app.pages.operations.create = async function() {
  const invoiceId = new URLSearchParams(location.search).get('invoice_id');
  const message = document.getElementById('operationMessage');
  const invoiceIdInput = document.getElementById('invoice_id');
  const invoiceNameInput = document.getElementById('invoice_name');
  const operationTypeInput = document.getElementById('operation_type');
  const operationDateInput = document.getElementById('operation_date');
  const backLink = document.getElementById('backFromOperationCreate');

  if (!invoiceId) {
    message.textContent = 'رقم الفاتورة غير موجود';
    return;
  }

  invoiceIdInput.value = invoiceId;
  backLink.href = '?_p=invoices/show&id=' + encodeURIComponent(invoiceId);

  const today = new Date();
  operationDateInput.value = new Date(today.getTime() - today.getTimezoneOffset() * 60000).toISOString().split('T')[0];

  const result = await api.get('invoices/show?id=' + encodeURIComponent(invoiceId));

  if (!result.ok) {
    message.textContent = result.error || 'تعذر تحميل بيانات الفاتورة';
    return;
  }

  const invoice = result.data.invoice;

  invoiceNameInput.value = invoice.item_name + (invoice.store_name ? ' — ' + invoice.store_name : '');

  fillOperationTypes(operationTypeInput, invoice.category_type);
};

async function createOperation(event) {
  event.preventDefault();

  const form = event.target;
  const message = document.getElementById('operationMessage');
  const button = form.querySelector('button[type="submit"]');

  message.textContent = '';
  button.disabled = true;
  button.textContent = 'جاري الحفظ...';

  const formData = new FormData(form);

  const result = await api.upload('operations/create', formData);

  if (!result.ok) {
    message.textContent = result.error || 'تعذر إضافة العملية';
    button.disabled = false;
    button.textContent = 'حفظ العملية';
    return;
  }

  location.href = '?_p=operations/show&id=' + encodeURIComponent(form.elements.invoice_id.value);
}