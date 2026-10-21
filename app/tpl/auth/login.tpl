<section class="auth-split-page">

  <div class="auth-split-form-side">

    <div class="auth-split-form-content">

      <h1>تسجيل الدخول</h1>

      <form id="loginForm" method="post" autocomplete="off" onsubmit="login(event)">

        <div class="form-group">
          <label for="email">البريد الإلكتروني</label>
          <input type="text" id="email" name="email" placeholder="example@email.com">
        </div>

        <div class="form-group">
          <label for="password">كلمة المرور</label>
          <input type="password" id="password" name="password" placeholder="اكتب كلمة المرور" autocomplete="off">
           <p class="auth-split-forgot"><a href="{{APP_DIR}}auth/forgot-password">نسيت كلمة المرور؟</a></p>
        </div>

        <p id="loginMessage" class="form-message"></p>

        <button type="submit" class="auth-button">تسجيل الدخول</button>

      </form>

    </div>

  </div>

  <div class="auth-split-info-side">

    <div class="auth-split-tabs">
      <a href="{{APP_DIR}}auth/login" class="auth-split-tab active">تسجيل الدخول</a>
      <a href="{{APP_DIR}}auth/register" class="auth-split-tab">إنشاء حساب</a>
    </div>

    <div class="auth-split-info-content">
      <h2>حافظة</h2>
      <h3>كل فواتيرك في مكان واحد</h3>
      <p>احتفظ بفواتير مشترياتك، وتابع مواعيد الضمان والاسترجاع والاستبدال والتجديد بسهولة.</p>
    </div>

  </div>

</section>