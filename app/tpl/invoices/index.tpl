<section class="invoices-page" dir="rtl">

  <div class="invoices-toolbar-card">

    <div class="invoices-toolbar-header">
      <h1>الفواتير</h1>

      <a
        class="invoice-add-link"
        href="{{APP_DIR}}invoices/create"
      >
        إضافة فاتورة
      </a>
    </div>

    <form id="invoiceFilters" class="invoice-form">

      <div class="invoice-filters-row">

        <input
          type="search"
          id="invoiceSearch"
          name="q"
          placeholder="ابحث باسم المنتج أو المتجر"
          aria-label="البحث"
        >

        <select
          id="categoryFilter"
          name="category_id"
          aria-label="التصنيف"
        >
          <option value="">كل التصنيفات</option>
        </select>

        <select
          id="statusFilter"
          name="status"
          aria-label="حالة المنتج"
        >
          <option value="">كل الحالات</option>
          <option value="active">نشطة</option>
          <option value="maintenance">صيانة</option>
          <option value="returned">مسترجعة</option>
          <option value="replaced">مستبدلة</option>
          <option value="cancelled">ملغاة</option>
        </select>

        <select
          id="periodTypeFilter"
          name="period_type"
          aria-label="نوع المدة"
        >
          <option value="">كل المدد</option>
          <option value="warranty">ضمان</option>
          <option value="return">استرجاع</option>
          <option value="replacement">استبدال</option>
          <option value="renewal">تجديد</option>
        </select>

        <select
          id="purchasePeriodFilter"
          name="purchase_period"
          aria-label="فترة الشراء"
        >
          <option value="">كل الفترات</option>
          <option value="week">آخر أسبوع</option>
          <option value="month">آخر شهر</option>
          <option value="year">آخر سنة</option>
        </select>

        <select
          id="sortFilter"
          name="sort"
          aria-label="الترتيب"
        >
          <option value="priority">الأولوية</option>
          <option value="closest">الأقرب انتهاءً</option>
          <option value="newest">الأحدث</option>
          <option value="oldest">الأقدم</option>
          <option value="name">اسم المنتج أبجديًا</option>
        </select>

        <button
          type="submit"
          class="invoice-filter-button"
        >
          تطبيق
        </button>

        <button type="button" id="resetInvoiceFilters" class="invoice-reset-button"> مسح الفلاتر</button>

      </div>

    </form>

  </div>

  <div class="invoices-results-heading">
    <p id="invoicesCount">
      جاري تحميل الفواتير...
    </p>
  </div>

  <p
    id="invoicesMessage"
    class="form-message"
  ></p>

  <div id="invoicesGrid" class="items-grid"></div>

  <div id="invoicesPagination" class="pagination" ></div>

</section>