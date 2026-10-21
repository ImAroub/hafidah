<section class="invoices-page" dir="rtl">

  <div class="invoices-toolbar-card">

    <div class="invoices-toolbar-header">
      <h1>العمليات</h1>
    </div>

    <form id="operationFilters" class="invoice-form">

      <div class="invoice-filters-row">

        <input type="search" id="operationSearch" name="q" placeholder="ابحث باسم المنتج أو المتجر" aria-label="البحث">

        <select id="operationCategoryFilter" name="category_id" aria-label="التصنيف">
          <option value="">كل التصنيفات</option>
        </select>

        <select id="operationTypeFilter" name="type" aria-label="نوع العملية">
          <option value="">كل العمليات</option>
          <option value="maintenance">إرسال للصيانة</option>
          <option value="maintenance_complete">استلام من الصيانة</option>
          <option value="replacement">استبدال</option>
          <option value="return">استرجاع</option>
          <option value="renewal">تجديد</option>
          <option value="cancel">إلغاء</option>
          <option value="other">أخرى</option>
        </select>

        <select id="operationSortFilter" name="sort" aria-label="الترتيب">
          <option value="recently_added">آخر عملية تمت إضافتها</option>
          <option value="newest">الأحدث حسب تاريخ العملية</option>
          <option value="oldest">الأقدم حسب تاريخ العملية</option>
          <option value="name">اسم المنتج أبجديًا</option>
          <option value="most_operations">الأكثر عمليات</option>
        </select>

        <button type="submit" class="invoice-filter-button">تطبيق</button>
        <button type="button" id="resetOperationFilters" class="invoice-reset-button">مسح الفلاتر</button>

      </div>

    </form>

  </div>

  <div class="invoices-results-heading">
    <p id="operationsCount">جاري تحميل العمليات...</p>
  </div>

  <p id="operationsMessage" class="form-message"></p>

  <div id="operationsGrid" class="items-grid"></div>

  <div id="operationsPagination" class="pagination"></div>

</section>