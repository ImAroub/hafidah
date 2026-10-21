app.pages.operations = app.pages.operations || {};

app.pages.operations.update = async function() {
  const params = new URLSearchParams(location.search);
  const operationId = params.get('id');
  const invoiceId = params.get('invoice_id');
  const form = document.getElementById('operationUpdateForm');
  const message = document.getElementById('operationUpdateMessage');
  const backLink = document.getElementById('backFromOperationUpdate');
  const removeFileOption = document.getElementById('removeOperationFileOption');
  const removeFileInput = document.getElementById('remove_operation_file');

  if (!operationId || !invoiceId) {
    message.textContent = 'بيانات العملية غير مكتملة';
    return;
  }

  backLink.href = '?_p=operations/show&id=' + encodeURIComponent(invoiceId);

  const result = await api.get('operations/show?id=' + encodeURIComponent(invoiceId));

  if (!result.ok) {
    message.textContent = result.error || 'تعذر تحميل العملية';
    return;
  }

  const invoice = result.data.invoice;
  const operations = result.data.operations || [];

  const operation = operations.find(function(item) {
    return Number(item.id) === Number(operationId);
  });

  if (!operation) {
    message.textContent = 'العملية غير موجودة';
    return;
  }

  form.elements.id.value = operation.id;

  fillOperationTypes(form.elements.operation_type, invoice.category_type, operation.operation_type);

  form.elements.operation_date.value = operation.operation_date || '';
  form.elements.expected_end_date.value = operation.expected_end_date || '';
  form.elements.cost.value = operation.cost !== null ? operation.cost : '';
  form.elements.notes.value = operation.notes || '';

  if (operation.operation_file_path) {
    removeFileOption.hidden = false;
  } else {
    removeFileOption.hidden = true;
    removeFileInput.checked = false;
  }

  message.textContent = '';
};

async function updateOperation(event) {
  event.preventDefault();

  const form = event.target;
  const message = document.getElementById('operationUpdateMessage');
  const button = form.querySelector('button[type="submit"]');

  message.textContent = '';
  button.disabled = true;
  button.textContent = 'جاري الحفظ...';

  const formData = new FormData(form);

  const result = await api.upload('operations/update', formData);

  if (!result.ok) {
    message.textContent = result.error || 'تعذر تعديل العملية';
    button.disabled = false;
    button.textContent = 'حفظ التعديلات';
    return;
  }

  location.href = '?_p=operations/show&id=' + encodeURIComponent(result.data.invoice_id);
}