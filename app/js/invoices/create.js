app.pages.invoices = app.pages.invoices || {};

app.pages.invoices.create = async function() {
  const result = await api.get('categories/index');

  if (!result.ok) {
    invoiceMessage.textContent = result.error || 'تعذر تحميل التصنيفات';
    return;
  }

  const categorySelect = document.getElementById('category_id');

  result.data.categories.forEach(function(category) {
    const option = document.createElement('option');

    option.value = category.id;
    option.textContent = category.name;

    categorySelect.appendChild(option);
  });
};

async function createInvoice(event) {
  event.preventDefault();

  const form = event.target;
  const message = document.getElementById('invoiceMessage');
  const submitButton = form.querySelector('button[type="submit"]');

  message.textContent = 'جاري حفظ الفاتورة...';
  submitButton.disabled = true;

  const formData = new FormData(form);

  const result = await api.upload('invoices/create', formData);

  submitButton.disabled = false;

  if (!result.ok) {
    message.textContent = result.error || 'تعذر حفظ الفاتورة';
    return;
  }

  message.textContent = result.data.message;

  goTo('dashboard');
}