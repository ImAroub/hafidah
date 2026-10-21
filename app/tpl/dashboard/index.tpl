<section class="dashboard-page">

  <div class="summary-grid">

    <a class="dashboard-card dashboard-card--summary dashboard-card--alerts is-clickable" href="{{APP_DIR}}alerts">

      <div class="dashboard-card__header dashboard-card__header--alerts">
        <h3>التنبيهات</h3>

        <div class="dashboard-alert-header-info">
          <span class="dashboard-alert-badge"><strong id="totalAlerts">0</strong> تنبيهات خلال 14 يوم</span>
          <span class="dashboard-card__arrow">←</span>
        </div>
      </div>

      <div class="dashboard-card__body">

        <div class="dashboard-alerts-head">
          <span>اسم الفاتورة</span>
          <span>نوع التنبيه</span>
          <span>المتبقي</span>
        </div>

        <div id="dashboardAlertsList" class="dashboard-alerts-list">
          <p class="dashboard-alerts-empty">لا توجد تنبيهات قريبة</p>
        </div>

      </div>

    </a>


    <div class="dashboard-card dashboard-card--summary dashboard-card--invoices">

      <div class="dashboard-card__header">
        <h3>الفواتير</h3>
      </div>

      <div class="dashboard-card__body">

        <div class="dashboard-total-row">
          <strong id="totalInvoices">0</strong>
          <span>فاتورة</span>
        </div>

        <div class="dashboard-filters">

          <div class="dashboard-filter-group">
            <label for="invoicePeriodFilter">المدة</label>

            <select id="invoicePeriodFilter">
              <option value="all">الكل</option>
              <option value="week">هذا الأسبوع</option>
              <option value="month">هذا الشهر</option>
              <option value="year">هذه السنة</option>
            </select>
          </div>

          <div class="dashboard-filter-group">
            <label for="invoiceCategoryFilter">التصنيف</label>

            <select id="invoiceCategoryFilter">
              <option value="">كل التصنيفات</option>
            </select>
          </div>

        </div>

      </div>

    </div>


    <div class="dashboard-card dashboard-card--summary dashboard-card--operations">

      <div class="dashboard-card__header">
        <h3>العمليات</h3>
      </div>

      <div class="dashboard-card__body">

        <div class="dashboard-total-row">
          <strong id="totalOperations">0</strong>
          <span>عملية</span>
        </div>

        <div class="dashboard-filters">

          <div class="dashboard-filter-group">
            <label for="operationPeriodFilter">المدة</label>

            <select id="operationPeriodFilter">
              <option value="all">الكل</option>
              <option value="week">هذا الأسبوع</option>
              <option value="month">هذا الشهر</option>
              <option value="year">هذه السنة</option>
            </select>
          </div>

          <div class="dashboard-filter-group">
            <label for="operationTypeFilter">نوع العملية</label>

            <select id="operationTypeFilter">
              <option value="">كل العمليات</option>
              <option value="maintenance">إرسال للصيانة</option>
              <option value="maintenance_complete">استلام من الصيانة</option>
              <option value="replacement">استبدال</option>
              <option value="return">استرجاع</option>
              <option value="renewal">تجديد</option>
              <option value="cancel">إلغاء</option>
              <option value="other">أخرى</option>
            </select>
          </div>

        </div>

      </div>

    </div>

  </div>


  <section class="dashboard-section">

    <div class="section-heading">
      <h2>أضيفت مؤخرًا</h2>
      <a href="{{APP_DIR}}invoices">مشاهدة جميع الفواتير</a>
    </div>

    <div class="items-grid" id="recentInvoices">

      <a class="add-card" href="{{APP_DIR}}invoices/create">
        <strong>＋ إضافة فاتورة جديدة</strong>
      </a>

    </div>

  </section>


  <section class="dashboard-section">

    <div class="section-heading">
      <h2>آخر العمليات</h2>
      <a href="{{APP_DIR}}operations">مشاهدة جميع العمليات</a>
    </div>

    <div class="items-grid" id="recentOperations">

      <a class="add-card" href="?_p=invoices/index&add_operation=1">
        <strong>＋ إضافة عملية جديدة</strong>
      </a>

    </div>

  </section>

</section>