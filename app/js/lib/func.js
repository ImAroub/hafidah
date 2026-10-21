
function fillOperationTypes(selectElement, categoryType, selectedType = '') {
  const operations = categoryType === 'subscription'
    ? {renewal: 'جُدّد', cancel: 'أُلغي', other: 'عملية أخرى'}
    : {maintenance: 'أُرسل للصيانة', maintenance_complete: 'استُلم من الصيانة', replacement: 'استُبدل', return: 'استُرجع', other: 'عملية أخرى'};

  selectElement.innerHTML = '<option value="">اختر نوع العملية</option>';

  Object.entries(operations).forEach(function ([value, label]) {
    const option = document.createElement('option');

    option.value = value;
    option.textContent = label;
    option.selected = value === selectedType;

    selectElement.appendChild(option);
  });
}