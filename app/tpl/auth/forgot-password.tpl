<section class="auth-page">

  <div class="auth-card">

    <h1>نسيت كلمة المرور</h1>

    <form id="forgotPasswordForm"  method="post" onsubmit="forgotPassword(event)">

      <div class="form-group">
        <label for="email">البريد الإلكتروني</label>
        <input type="email" id="email" name="email" placeholder="example@email.com" required >
      </div>

      <p id="forgotPasswordMessage" class="form-message"></p>

      <button type="submit" class="auth-button"> إرسال رمز التحقق </button>

    </form>

    <p class="auth-switch"> تذكرت كلمة المرور؟ <a href="{{APP_DIR}}auth/login">تسجيل الدخول</a> </p>

  </div>

</section>