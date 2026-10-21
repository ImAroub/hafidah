<section class="invoice-details-page">

  <div class="invoice-details-card">

    <div class="page-heading">
      <h1>عمليات الفاتورة</h1>
      <a href="?_p=operations">العودة للعمليات</a>
    </div>

    <p id="operationDetailsMessage" class="form-message"></p>

    <div class="invoice-details-header">
      <div>
        <h2 id="operationInvoiceName">—</h2>
      </div>

      <span id="operationInvoiceStatus" class="invoice-status">—</span>
    </div>

    <section class="invoice-details-section">

      <div class="invoice-section-title invoice-operations-title">
        <h3>سجل العمليات</h3>
        <a id="addOperationLink" class="invoice-action-button" href="#">إضافة عملية</a>
      </div>

      <div id="operationsList">
        <p class="empty-operations">لا توجد عمليات مسجلة على هذه الفاتورة</p>
      </div>

    </section>

  </div>

  <div id="operationImageModal" class="operation-image-modal" hidden>
    <button id="closeOperationImage" class="operation-image-modal__close" type="button">×</button>
    <img id="operationLargeImage" src="" alt="صورة العملية">
  </div>

</section>