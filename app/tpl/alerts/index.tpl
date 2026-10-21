<section class="alerts-page">

  <div class="alerts-card">

    <div class="page-heading">
      <h1>التنبيهات</h1>
      <a href="{{APP_DIR}}dashboard">العودة للرئيسية</a>
    </div>

    <div class="alerts-main-header">

      <div>
        <h2>التنبيهات القادمة</h2>
        <p>التنبيهات التي تنتهي خلال 14 يوم</p>
      </div>

      <span class="alerts-total-badge"><strong id="alertsTotal">0</strong> تنبيهات</span>

    </div>

    <p id="alertsMessage" class="form-message"></p>

    <div id="alertsTable" class="alerts-section">

      <div class="alerts-table__head">
        <span>اسم الفاتورة</span>
        <span>نوع التنبيه</span>
        <span>تاريخ الانتهاء</span>
        <span>المتبقي</span>
      </div>

      <div id="alertsList" class="alerts-list"></div>

    </div>

  </div>

</section>