<section class="invoice-create-page">
  <div class="invoice-card">

    <div class="page-heading">
      <h1>تعديل الفاتورة</h1>
      <a id="backToInvoiceLink" href="#">العودة لتفاصيل الفاتورة</a>
    </div>

    <form id="editInvoiceForm" class="invoice-form" enctype="multipart/form-data" onsubmit="updateInvoice(event)">

      <input type="hidden" id="invoice_id" name="id">

      <div class="form-grid">

        <div class="form-group">
          <label for="item_name">اسم المنتج</label>
          <input type="text" id="item_name" name="item_name" required>
        </div>

        <div class="form-group">
          <label for="category_id">التصنيف</label>
          <select id="category_id" name="category_id" required>
            <option value="">اختار التصنيف</option>
          </select>
        </div>

        <div class="form-group">
          <label for="store_name">اسم المتجر</label>
          <input type="text" id="store_name" name="store_name">
        </div>

        <div class="form-group">
          <label for="contact_info">معلومات التواصل</label>
          <input type="text" id="contact_info" name="contact_info">
        </div>

        <div class="form-group">
          <label for="purchase_date">تاريخ الشراء</label>
          <input type="date" id="purchase_date" name="purchase_date" required>
        </div>

        <div class="form-group">
          <label for="cost">التكلفة</label>
          <input type="number" id="cost" name="cost" min="0" step="0.01" placeholder="0.00">
        </div>

        <div class="form-group invoice-file-group">
          <label for="invoice_file">استبدال صورة الفاتورة</label>
          <input type="file" id="invoice_file" name="invoice_file" accept="image/jpeg,image/png,image/webp">

          <label class="remove-file-option">
            <input type="checkbox" name="remove_invoice_file" value="1">
            <span>حذف الصورة الحالية</span>
          </label>
        </div>

      </div>

      <br>

      <div class="periods-grid">

        <div class="period-card">
          <label>مدة الضمان</label>

          <div class="period-fields">
            <input type="number" id="warranty_value" name="warranty_value" min="0" value="0">

            <select id="warranty_unit" name="warranty_unit">
              <option value="">الوحدة</option>
              <option value="days">أيام</option>
              <option value="months">أشهر</option>
              <option value="years">سنوات</option>
            </select>
          </div>
        </div>

        <div class="period-card">
          <label>مدة الاسترجاع</label>

          <div class="period-fields">
            <input type="number" id="return_value" name="return_value" min="0" value="0">

            <select id="return_unit" name="return_unit">
              <option value="">الوحدة</option>
              <option value="days">أيام</option>
              <option value="months">أشهر</option>
              <option value="years">سنوات</option>
            </select>
          </div>
        </div>

        <div class="period-card">
          <label>مدة الاستبدال</label>

          <div class="period-fields">
            <input type="number" id="replacement_value" name="replacement_value" min="0" value="0">

            <select id="replacement_unit" name="replacement_unit">
              <option value="">الوحدة</option>
              <option value="days">أيام</option>
              <option value="months">أشهر</option>
              <option value="years">سنوات</option>
            </select>
          </div>
        </div>

        <div class="period-card">
          <label>مدة الاشتراك</label>

          <div class="period-fields">
            <input type="number" id="renewal_value" name="renewal_value" min="0" value="0">

            <select id="renewal_unit" name="renewal_unit">
              <option value="">الوحدة</option>
              <option value="days">أيام</option>
              <option value="months">أشهر</option>
              <option value="years">سنوات</option>
            </select>
          </div>
        </div>

      </div>

      <p id="editInvoiceMessage" class="form-message"></p>

      <button type="submit" class="invoice-submit">حفظ التعديلات</button>

    </form>

  </div>
</section>