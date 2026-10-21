<section class="invoice-create-page">
  <div class="invoice-card">

    <div class="page-heading">
      <h1>إضافة عملية</h1>
      <a id="backFromOperationCreate" href="#">العودة لتفاصيل الفاتورة</a>
    </div>

    <form id="operationForm" class="invoice-form" enctype="multipart/form-data" onsubmit="createOperation(event)">

      <input type="hidden" id="invoice_id" name="invoice_id">

      <div class="form-grid">

        <div class="form-group">
          <label for="invoice_name">الفاتورة</label>
          <input type="text" id="invoice_name" disabled>
        </div>

        <div class="form-group">
          <label for="operation_type">نوع العملية</label>
          <select id="operation_type" name="operation_type" required>
            <option value="">اختر نوع العملية</option>
          </select>
        </div>

        <div class="form-group">
          <label for="operation_date">تاريخ العملية</label>
          <input type="date" id="operation_date" name="operation_date" required>
        </div>

        <div class="form-group">
          <label for="expected_end_date">تاريخ الانتهاء المتوقع</label>
          <input type="date" id="expected_end_date" name="expected_end_date">
        </div>

        <div class="form-group">
          <label for="cost">التكلفة</label>
          <input type="number" id="cost" name="cost" min="0" step="0.01" placeholder="0.00">
        </div>

        <div class="form-group">
          <label for="notes">الملاحظات</label>
          <input type="text" id="notes" name="notes" placeholder="اكتب ملاحظات العملية">
        </div>

        <div class="form-group">
          <label for="operation_file">صورة العملية</label>
          <input type="file" id="operation_file" name="operation_file" accept="image/jpeg,image/png,image/webp">
        </div>

      </div>

      <p id="operationMessage" class="form-message"></p>

      <button type="submit" class="invoice-submit">حفظ العملية</button>

    </form>

  </div>
</section>