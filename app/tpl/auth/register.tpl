<section class="auth-split-page">

  <div class="auth-split-form-side">

    <div class="auth-split-form-content">

      <h1>إنشاء حساب</h1>

      <form id="registerForm" method="post" onsubmit="register(event)">

        <div class="form-group">
          <label for="name">الاسم</label>
          <input type="text" id="name" name="name" placeholder="اكتب اسمك">
        </div>

        <div class="form-group">
          <label for="email">البريد الإلكتروني</label>
          <input type="email" id="email" name="email" placeholder="example@email.com">
        </div>

        <div class="form-group">
          <label for="password">كلمة المرور</label>
          <input type="password" id="password" name="password" placeholder="8 أحرف على الأقل">
        </div>

        <div class="form-group">
          <label for="passwordConfirmation">تأكيد كلمة المرور</label>
          <input type="password" id="passwordConfirmation" name="password_confirmation" placeholder="أعد كتابة كلمة المرور">
        </div>

        <p id="registerMessage" class="form-message"></p>

        <button type="submit" class="auth-button">إنشاء الحساب</button>

      </form>

    </div>

  </div>

  <div class="auth-split-info-side">

    <div class="auth-split-tabs">
      <a href="{{APP_DIR}}auth/login" class="auth-split-tab">تسجيل الدخول</a>
      <a href="{{APP_DIR}}auth/register" class="auth-split-tab active">إنشاء حساب</a>
    </div>

    <div class="auth-split-info-content">
      <h2>حافظة</h2>
      <h3>كل فواتيرك في مكان واحد</h3>
      <p>احتفظ بفواتير مشترياتك، وتابع مواعيد الضمان والاسترجاع والاستبدال والتجديد بسهولة.</p>
    </div>

  </div>

</section>