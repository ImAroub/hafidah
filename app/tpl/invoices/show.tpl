<section class="invoice-details-page">
  <div class="invoice-details-card">

    <div class="page-heading">
      <h1>تفاصيل الفاتورة</h1>
      <a href="{{APP_DIR}}invoices">العودة للفواتير</a>
    </div>

    <p id="invoiceDetailsMessage" class="form-message"></p>

    <div class="invoice-details-header">
      <div>
        <h2 id="invoiceItemName">—</h2>
      </div>

      <span id="invoiceStatus" class="invoice-status">—</span>
    </div>

    <section class="invoice-details-section">

      <div class="invoice-section-title">
        <h3>معلومات الفاتورة</h3>
      </div>

      <div class="invoice-info-grid">

        <div class="invoice-info-item">
          <span>اسم المنتج</span>
          <strong id="itemName">—</strong>
        </div>

        <div class="invoice-info-item">
          <span>التصنيف</span>
          <strong id="categoryName">—</strong>
        </div>

        <div class="invoice-info-item">
          <span>مكان الشراء</span>
          <strong id="storeName">—</strong>
        </div>

        <div class="invoice-info-item invoice-info-item--file">
          <span>صورة الفاتورة</span>
          <a id="invoiceFileLink" href="#" hidden><img id="invoiceFileImage" src="" alt="صورة الفاتورة"></a>
          <p id="noInvoiceFile">لا توجد صورة مرفقة</p>
        </div>

        <div class="invoice-info-item">
          <span>التكلفة</span>
          <strong id="invoiceCost">—</strong>
        </div>

        <div class="invoice-info-item">
          <span>تاريخ الشراء</span>
          <strong id="purchaseDate">—</strong>
        </div>

        <div class="invoice-info-item">
          <span>التواصل</span>
          <a id="contactInfo" class="invoice-contact-link" href="#">—</a>
        </div>

      </div>

    </section>

    <section class="invoice-details-section">

      <div class="invoice-section-title">
        <h3>المدد</h3>
      </div>

      <div class="invoice-periods-grid">

        <div class="invoice-period-details">
          <h4>الضمان</h4>
          <p><span>المدة</span><strong id="warrantyPeriod">—</strong></p>
          <p><span>تاريخ الانتهاء</span><strong id="warrantyEndDate">—</strong></p>
        </div>

        <div class="invoice-period-details">
          <h4>الاسترجاع</h4>
          <p><span>المدة</span><strong id="returnPeriod">—</strong></p>
          <p><span>تاريخ الانتهاء</span><strong id="returnEndDate">—</strong></p>
        </div>

        <div class="invoice-period-details">
          <h4>الاستبدال</h4>
          <p><span>المدة</span><strong id="replacementPeriod">—</strong></p>
          <p><span>تاريخ الانتهاء</span><strong id="replacementEndDate">—</strong></p>
        </div>

        <div class="invoice-period-details">
          <h4>الاشتراك</h4>
          <p><span>المدة</span><strong id="renewalPeriod">—</strong></p>
          <p><span>تاريخ الانتهاء</span><strong id="renewalEndDate">—</strong></p>
        </div>

      </div>

    </section>

    <section class="invoice-details-section">

      <div class="invoice-section-title invoice-operations-title">
        <h3>العمليات المرتبطة</h3>

        <div class="invoice-operations-buttons">
          <a id="showAllOperations" class="invoice-action-button" href="#">إظهار كل العمليات</a>
          <a id="addInvoiceOperation" class="invoice-action-button" href="#">إضافة عملية</a>
        </div>
      </div>

      <div id="invoiceOperations">
        <p class="empty-operations">لا توجد عمليات مسجلة على هذه الفاتورة</p>
      </div>

    </section>

    <div class="invoice-actions">
      <a id="editInvoiceLink" class="invoice-action-button" href="#">تعديل الفاتورة</a>
      <button id="deleteInvoiceButton" class="invoice-action-button invoice-action-button--danger" type="button">حذف الفاتورة</button>
    </div>

  </div>

  <div id="invoiceImageModal" class="invoice-image-modal" hidden>
    <button id="closeInvoiceImage" class="invoice-image-modal__close" type="button" aria-label="إغلاق الصورة">×</button>
    <img id="invoiceLargeImage" src="" alt="صورة الفاتورة بالحجم الكامل">
  </div>
</section>