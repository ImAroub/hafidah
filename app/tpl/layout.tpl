<div class="app-layout">

  <header class="topbar">

    <a class="welcome profile-link" href="{{APP_DIR}}profile">
      <span class="material-icons">account_circle</span>
      أهلًا <span id="userName">{{userName}}</span>
    </a>

    <button type="button" class="menu-toggle" onclick="toggleMobileMenu()">☰</button>

    <nav class="main-nav">
      <a data-page="dashboard" href="{{APP_DIR}}dashboard"> الرئيسية</a>

      <a data-page="invoices" href="{{APP_DIR}}invoices"> الفواتير</a>

      <a data-page="operations" href="{{APP_DIR}}operations"> العمليات </a>
    </nav>

    <button type="button" class="logout-button" onclick="logout()"> تسجيل الخروج</button>

  </header>

  <main class="main-content">
    {{content}}
  </main>

  <footer class="app-footer">
    <strong>حافظة</strong>
    <span>نظّم فواتيرك وضماناتك بسهولة</span>
  </footer>

</div>